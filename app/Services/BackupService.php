<?php

namespace App\Services;

use App\Mail\BackupStatusMail;
use App\Models\BackupRun;
use App\Models\GeneralSettings;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use ZipArchive;

class BackupService
{
    public const INTERVALS = [
        '1min' => 'Every 1 minute (testing only)',
        '5min' => 'Every 5 minutes (testing only)',
        '1hour' => 'Every hour',
        '6hours' => 'Every 6 hours',
        '12hours' => 'Every 12 hours',
        'daily' => 'Once a day',
        'weekly' => 'Once a week',
    ];

    public const WEEKDAYS = [
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
    ];

    private const SKIP_TABLES = [
        'cache',
        'cache_locks',
        'sessions',
        'jobs',
        'job_batches',
        'failed_jobs',
        'telescope_entries',
        'telescope_entries_tags',
        'telescope_monitoring',
    ];

    private $lockHandle = null;

    public static function isDue(): bool
    {
        return self::dueReason() === null;
    }

    public static function applyScheduleTimezone(): void
    {
        $tz = (string) self::setting('timezone', 'Europe/Madrid');
        if (! in_array($tz, timezone_identifiers_list(), true)) {
            $tz = 'Europe/Madrid';
        }
        config(['app.timezone' => $tz]);
        date_default_timezone_set($tz);
    }

    public static function dueReason(): ?string
    {
        self::applyScheduleTimezone();

        try {
            if (! Schema::hasTable('backup_runs')) {
                return 'backup_runs table is missing';
            }
            if ((string) self::setting('backup_enabled', '0') !== '1') {
                return 'automatic backup is off';
            }

            $last = self::lastSuccessAt();
            $interval = (string) self::setting('backup_interval', 'daily');
            $now = now();

            $due = match ($interval) {
                '1min' => ! $last || $last->lte($now->copy()->subSeconds(55)),
                '5min' => ! $last || $last->lte($now->copy()->subMinutes(5)),
                '1hour' => ! $last || $last->lte($now->copy()->subHour()),
                '6hours' => ! $last || $last->lte($now->copy()->subHours(6)),
                '12hours' => ! $last || $last->lte($now->copy()->subHours(12)),
                'weekly' => self::calendarSlotDue($last, true),
                default => self::calendarSlotDue($last, false),
            };

            return $due ? null : 'interval not reached ('.$interval.')';
        } catch (\Throwable $e) {
            Log::warning('Backup due check failed: '.$e->getMessage());

            return $e->getMessage();
        }
    }

    public static function nextRunDescription(): string
    {
        self::applyScheduleTimezone();

        if ((string) self::setting('backup_enabled', '0') !== '1') {
            return 'Automatic backup is off. Backup now still works.';
        }

        $last = self::lastSuccessAt();
        $interval = (string) self::setting('backup_interval', 'daily');

        if (self::isDue()) {
            return 'A backup is due. It will run on the next scheduler check, about one minute.';
        }

        $next = match ($interval) {
            '1min' => ($last ?: now())->copy()->addMinute(),
            '5min' => ($last ?: now())->copy()->addMinutes(5),
            '1hour' => ($last ?: now())->copy()->addHour(),
            '6hours' => ($last ?: now())->copy()->addHours(6),
            '12hours' => ($last ?: now())->copy()->addHours(12),
            'weekly' => self::nextCalendarSlot(true),
            default => self::nextCalendarSlot(false),
        };

        return 'Next automatic backup: '.$next->format('d M Y, h:i A');
    }

    public static function encryptionAvailable(): bool
    {
        return class_exists(ZipArchive::class) && defined('ZipArchive::EM_AES_256');
    }

    public static function setting(string $key, mixed $default = null): mixed
    {
        $value = GeneralSettings::getGeneralSetting($key);

        return $value === null || $value === '' ? $default : $value;
    }

    public static function ensurePassword(): string
    {
        $current = (string) self::setting('backup_zip_password', '');
        if ($current !== '') {
            self::writePasswordFile($current);

            return $current;
        }

        $password = Str::upper(Str::random(4)).'-'.Str::upper(Str::random(4)).'-'.Str::upper(Str::random(4));
        GeneralSettings::setGeneralSetting('backup_zip_password', $password);
        self::writePasswordFile($password);

        return $password;
    }

    public function run(string $trigger = 'manual'): BackupRun
    {
        self::applyScheduleTimezone();

        if (! $this->acquireLock()) {
            throw new \RuntimeException('A backup is already running. Wait for it to finish.');
        }

        try {
            return $this->perform($trigger);
        } finally {
            $this->releaseLock();
        }
    }

    public function restore(BackupRun $run, string $confirmation): void
    {
        if (strtoupper(trim($confirmation)) !== 'RESTORE') {
            throw new \RuntimeException('Type RESTORE to confirm.');
        }
        if ($run->status !== 'success' || ! $run->filename) {
            throw new \RuntimeException('This backup cannot be restored.');
        }

        $zipPath = $this->pathFor($run->filename);
        if (! is_file($zipPath)) {
            throw new \RuntimeException('The backup file is missing from the server.');
        }

        if (! $this->acquireLock()) {
            throw new \RuntimeException('A backup is already running. Wait for it to finish.');
        }

        $temp = null;
        try {
            $safety = $this->perform('safety');
            if ($safety->status !== 'success') {
                throw new \RuntimeException('Restore stopped. A safety backup could not be created. '.$safety->message);
            }

            $temp = $this->extractZip($zipPath, $this->passwordFor($run));
            $manifest = $this->readManifest($temp);
            $driver = $this->driver();
            if (($manifest['driver'] ?? '') !== $driver) {
                throw new \RuntimeException('This backup was made for '.($manifest['driver'] ?? 'another database').'. This server uses '.$driver.'.');
            }

            if ($driver === 'sqlite') {
                $this->restoreSqlite($temp);
            } else {
                $this->restoreMysql($temp);
            }

            $this->restoreFiles($temp);
            $this->syncDiskFiles();
        } finally {
            if ($temp) {
                File::deleteDirectory($temp);
            }
            $this->releaseLock();
        }
    }

    public function delete(BackupRun $run): void
    {
        $filename = (string) $run->filename;
        $sentAway = str_contains((string) $run->message, 'other server');
        if ($filename !== '' && $sentAway && ! $this->removeRemoteCopy($filename)) {
            throw new \RuntimeException('Could not delete the file on the other server.');
        }
        if ($filename !== '') {
            $path = $this->pathFor($filename);
            if (is_file($path)) {
                @unlink($path);
            }
        }
        $run->delete();
    }

    public function withLocalCopy(BackupRun $run, callable $callback): mixed
    {
        $filename = (string) $run->filename;
        $path = $this->pathFor($filename);
        $temporary = $filename !== '' && ! is_file($path);
        if ($temporary) {
            $this->pullFromRemote($filename);
        }

        try {
            return $callback();
        } finally {
            if ($temporary && is_file($path)) {
                @unlink($path);
            }
        }
    }

    private function removeRemoteCopy(string $filename): bool
    {
        if (self::readSecret('backup_remote_host') === '' || self::readSecret('backup_remote_password') === '') {
            return true;
        }

        try {
            $connection = $this->openFtp(
                self::readSecret('backup_remote_host'),
                (int) self::setting('backup_remote_port', 21),
                self::readSecret('backup_remote_username'),
                self::readSecret('backup_remote_password'),
                self::readSecret('backup_remote_path')
            );
        } catch (\Throwable $e) {
            Log::warning('Remote backup delete failed: '.$e->getMessage());

            return false;
        }

        $size = @ftp_size($connection, $filename);
        if ($size < 0) {
            @ftp_delete($connection, $filename);
            ftp_close($connection);

            return true;
        }

        $deleted = @ftp_delete($connection, $filename);
        ftp_close($connection);

        return (bool) $deleted;
    }

    public function pullFromRemote(string $filename): void
    {
        $name = basename($filename);
        if (! self::isBackupZip($name)) {
            throw new \RuntimeException('That backup file is no longer on the server.');
        }
        $path = $this->pathFor($name);
        if (is_file($path)) {
            return;
        }
        if ((string) self::setting('backup_remote_enabled', '0') !== '1' && self::readSecret('backup_remote_host') === '') {
            throw new \RuntimeException('That backup file is no longer on this server.');
        }

        $connection = $this->openFtp(
            self::readSecret('backup_remote_host'),
            (int) self::setting('backup_remote_port', 21),
            self::readSecret('backup_remote_username'),
            self::readSecret('backup_remote_password'),
            self::readSecret('backup_remote_path')
        );
        if (defined('FTP_USEPASVADDRESS')) {
            @ftp_set_option($connection, FTP_USEPASVADDRESS, false);
        }
        @ftp_pasv($connection, true);
        $saved = @ftp_get($connection, $path, $name, FTP_BINARY);
        ftp_close($connection);
        if (! $saved || ! is_file($path)) {
            @unlink($path);
            if (! $this->downloadWithCurl($path, $name)) {
                throw new \RuntimeException('That backup file is not on this server or the other server.');
            }
        }
    }

    public static function storageMode(): string
    {
        $mode = (string) self::setting('backup_storage', '');
        if (in_array($mode, ['local', 'both', 'remote'], true)) {
            return $mode;
        }

        return (string) self::setting('backup_remote_enabled', '0') === '1' ? 'both' : 'local';
    }

    public function syncDiskFiles(): void
    {
        $dir = storage_path('app/backups');
        if (! is_dir($dir)) {
            return;
        }

        foreach (File::files($dir) as $file) {
            $name = $file->getFilename();
            if (! self::isBackupZip($name)) {
                continue;
            }
            if (BackupRun::where('filename', $name)->exists()) {
                continue;
            }
            BackupRun::create([
                'filename' => $name,
                'size_bytes' => $file->getSize(),
                'status' => 'success',
                'trigger' => 'manual',
                'message' => 'Backup file found on the server.',
                'zip_password' => $this->passwordFromFile($name),
                'includes_files' => true,
                'started_at' => Carbon::createFromTimestamp($file->getMTime()),
                'finished_at' => Carbon::createFromTimestamp($file->getMTime()),
            ]);
        }
    }

    public function forgetMissingLocalFiles(): void
    {
        BackupRun::where('status', 'success')
            ->whereNotNull('filename')
            ->orderByDesc('id')
            ->get()
            ->each(function (BackupRun $run) {
                if ($this->localZipMissing($run)) {
                    $run->delete();
                }
            });
    }

    private function localZipMissing(BackupRun $run): bool
    {
        $name = basename((string) $run->filename);
        if (! self::isBackupZip($name)) {
            return false;
        }
        if (is_file($this->pathFor($name))) {
            return false;
        }

        return ! str_contains((string) $run->message, 'Saved on the other server only');
    }

    private function perform(string $trigger): BackupRun
    {
        $this->markStaleRuns();
        $temp = storage_path('app/backup-tmp/'.Str::uuid());
        File::ensureDirectoryExists($temp);
        File::ensureDirectoryExists(storage_path('app/backups'));

        $run = new BackupRun([
            'status' => 'running',
            'trigger' => $trigger,
            'includes_files' => $this->includeFiles(),
            'started_at' => now(),
        ]);
        $run->save();

        try {
            if (! class_exists(ZipArchive::class)) {
                throw new \RuntimeException('PHP zip extension is not enabled on this server.');
            }

            $driver = $this->driver();
            $manifest = [
                'app' => 'pos-backup',
                'created_at' => now()->toIso8601String(),
                'driver' => $driver,
                'includes_files' => $this->includeFiles(),
            ];

            if ($driver === 'sqlite') {
                $source = config('database.connections.'.config('database.default').'.database');
                if (! is_file($source)) {
                    throw new \RuntimeException('SQLite database file was not found.');
                }
                if (! @copy($source, $temp.DIRECTORY_SEPARATOR.'database.sqlite')) {
                    throw new \RuntimeException('Could not copy the SQLite database.');
                }
            } elseif ($driver === 'mysql') {
                $this->dumpMysql($temp.DIRECTORY_SEPARATOR.'database.sql');
            } else {
                throw new \RuntimeException('Backup supports MySQL and SQLite. This server uses '.$driver.'.');
            }

            File::put($temp.DIRECTORY_SEPARATOR.'manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));

            $filename = $this->uniqueFilename();
            $destination = $this->pathFor($filename);
            $password = null;
            $encrypted = false;
            if (self::encryptionAvailable()) {
                $password = self::ensurePassword();
                $encrypted = true;
            }

            $this->buildZip($temp, $destination, $encrypted ? $password : null);
            $this->rememberPassword($filename, $password);

            $message = $encrypted
                ? 'Database'.($this->includeFiles() ? ' and uploaded files' : '').' saved.'
                : 'Saved without zip encryption. This server cannot encrypt zip files. The file is still outside the public website.';
            $size = filesize($destination) ?: 0;
            $remoteError = $this->copyToRemote($destination, $filename);
            $storage = self::storageMode();
            if ($storage === 'remote' && $remoteError === null) {
                @unlink($destination);
                $message = 'Saved on the other server only.';
            } elseif ($remoteError !== null && $storage === 'remote') {
                $message .= ' The other server did not accept the file, so it was kept on this server: '.$remoteError;
            } elseif ($remoteError !== null) {
                $message .= ' The copy to the other server failed: '.$remoteError;
            } elseif ($storage === 'both') {
                $message .= ' A copy was sent to the other server.';
            }

            $run->fill([
                'filename' => $filename,
                'size_bytes' => $size,
                'status' => 'success',
                'zip_password' => $password,
                'message' => $message,
                'finished_at' => now(),
            ])->save();

            $this->prune();
            $this->notify($run->fresh(), true);

            return $run->fresh();
        } catch (\Throwable $e) {
            Log::error('Backup failed', ['error' => $e->getMessage()]);
            $run->fill([
                'status' => 'failed',
                'message' => $e->getMessage(),
                'finished_at' => now(),
            ])->save();
            $this->notify($run->fresh(), false);

            return $run->fresh();
        } finally {
            File::deleteDirectory($temp);
        }
    }

    private function buildZip(string $temp, string $destination, ?string $password): void
    {
        $zip = new ZipArchive;
        if ($zip->open($destination, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Could not create the backup file.');
        }

        $names = [];
        foreach (['manifest.json', 'database.sql', 'database.sqlite'] as $base) {
            $full = $temp.DIRECTORY_SEPARATOR.$base;
            if (is_file($full)) {
                $zip->addFile($full, $base);
                $names[] = $base;
            }
        }

        if ($this->includeFiles()) {
            foreach ($this->uploadFiles() as $relative => $absolute) {
                $entry = 'files/'.str_replace('\\', '/', $relative);
                $zip->addFile($absolute, $entry);
                $names[] = $entry;
            }
        }

        if ($password) {
            $zip->setPassword($password);
            foreach ($names as $name) {
                if (! $zip->setEncryptionName($name, ZipArchive::EM_AES_256)) {
                    $zip->close();
                    @unlink($destination);
                    throw new \RuntimeException('This server cannot encrypt the backup zip.');
                }
            }
        }

        $zip->close();

        if (! is_file($destination)) {
            throw new \RuntimeException('The backup file was not written.');
        }
    }

    private function dumpMysql(string $path): void
    {
        $handle = fopen($path, 'wb');
        if ($handle === false) {
            throw new \RuntimeException('Could not write the database export.');
        }

        fwrite($handle, "-- POS backup\nSET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\nSET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n");

        $pdo = DB::connection()->getPdo();
        $tables = DB::select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"');
        foreach ($tables as $row) {
            $table = (string) array_values((array) $row)[0];
            if (in_array($table, self::SKIP_TABLES, true)) {
                continue;
            }

            $create = DB::select('SHOW CREATE TABLE `'.str_replace('`', '``', $table).'`');
            $createSql = (string) array_values((array) $create[0])[1];
            fwrite($handle, 'DROP TABLE IF EXISTS `'.str_replace('`', '``', $table)."`;\n");
            fwrite($handle, $createSql.";\n\n");

            $statement = $pdo->query('SELECT * FROM `'.str_replace('`', '``', $table).'`');
            $batch = [];
            $columns = null;
            while ($record = $statement->fetch(\PDO::FETCH_ASSOC)) {
                if ($columns === null) {
                    $columns = array_keys($record);
                }
                $batch[] = $record;
                if (count($batch) >= 100) {
                    $this->writeInsert($handle, $table, $columns, $batch);
                    $batch = [];
                }
            }
            if ($batch && $columns) {
                $this->writeInsert($handle, $table, $columns, $batch);
            }
            fwrite($handle, "\n");
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);
    }

    private function writeInsert($handle, string $table, array $columns, array $rows): void
    {
        $pdo = DB::connection()->getPdo();
        $columnSql = implode(', ', array_map(fn ($column) => '`'.str_replace('`', '``', $column).'`', $columns));
        $values = [];
        foreach ($rows as $row) {
            $quoted = [];
            foreach ($columns as $column) {
                $value = $row[$column];
                if ($value === null) {
                    $quoted[] = 'NULL';
                } else {
                    $quoted[] = $pdo->quote((string) $value);
                }
            }
            $values[] = '('.implode(', ', $quoted).')';
        }

        fwrite($handle, 'INSERT INTO `'.str_replace('`', '``', $table)."` ({$columnSql}) VALUES\n".implode(",\n", $values).";\n");
    }

    private function restoreMysql(string $temp): void
    {
        $sqlPath = $temp.DIRECTORY_SEPARATOR.'database.sql';
        if (! is_file($sqlPath)) {
            throw new \RuntimeException('This backup does not contain a database export.');
        }

        $handle = fopen($sqlPath, 'rb');
        $buffer = '';
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        while (($line = fgets($handle)) !== false) {
            $trim = ltrim($line);
            if ($buffer === '' && (str_starts_with($trim, '--') || str_starts_with($trim, '/*') || trim($line) === '')) {
                continue;
            }
            $buffer .= $line;
            if ($this->statementComplete($buffer)) {
                DB::unprepared($buffer);
                $buffer = '';
            }
        }
        fclose($handle);
        if (trim($buffer) !== '') {
            DB::unprepared($buffer);
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function statementComplete(string $sql): bool
    {
        $inString = false;
        $escape = false;
        $last = '';
        $length = strlen($sql);
        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            if ($inString) {
                if ($escape) {
                    $escape = false;
                    continue;
                }
                if ($char === '\\') {
                    $escape = true;
                    continue;
                }
                if ($char === "'") {
                    $inString = false;
                }
                continue;
            }
            if ($char === "'") {
                $inString = true;
                continue;
            }
            if (! ctype_space($char)) {
                $last = $char;
            }
        }

        return ! $inString && $last === ';';
    }

    private function restoreSqlite(string $temp): void
    {
        $source = $temp.DIRECTORY_SEPARATOR.'database.sqlite';
        $target = config('database.connections.'.config('database.default').'.database');
        if (! is_file($source) || ! $target) {
            throw new \RuntimeException('This backup does not contain a SQLite database.');
        }
        DB::disconnect();
        if (! @copy($source, $target)) {
            throw new \RuntimeException('Could not replace the SQLite database file.');
        }
        DB::purge();
    }

    private function restoreFiles(string $temp): void
    {
        $filesRoot = $temp.DIRECTORY_SEPARATOR.'files';
        if (! is_dir($filesRoot)) {
            return;
        }

        $target = storage_path('app/public');
        File::ensureDirectoryExists($target);
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($filesRoot, \FilesystemIterator::SKIP_DOTS)
        );
        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }
            $relative = ltrim(str_replace('\\', '/', substr($file->getPathname(), strlen($filesRoot))), '/');
            if ($relative === '' || str_contains($relative, '..')) {
                continue;
            }
            $destination = $target.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
            File::ensureDirectoryExists(dirname($destination));
            File::copy($file->getPathname(), $destination);
            $this->mirrorPublicFile($relative, (string) file_get_contents($file->getPathname()));
        }
    }

    private function mirrorPublicFile(string $relative, string $contents): void
    {
        $publicRoot = public_path('storage');
        if (! is_dir($publicRoot)) {
            return;
        }
        $realPublic = realpath($publicRoot);
        $realDisk = realpath(storage_path('app/public'));
        if ($realPublic && $realDisk && $realPublic === $realDisk) {
            return;
        }
        $destination = $publicRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
        File::ensureDirectoryExists(dirname($destination));
        File::put($destination, $contents);
    }

    private function extractZip(string $zipPath, ?string $password): string
    {
        $temp = storage_path('app/backup-tmp/restore-'.Str::uuid());
        File::ensureDirectoryExists($temp);
        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('Could not open the backup file.');
        }
        if ($password) {
            $zip->setPassword($password);
        }
        if (! $zip->extractTo($temp)) {
            $zip->close();
            File::deleteDirectory($temp);
            throw new \RuntimeException('Could not open the backup. The password may not match this file.');
        }
        $zip->close();

        return $temp;
    }

    private function readManifest(string $temp): array
    {
        $path = $temp.DIRECTORY_SEPARATOR.'manifest.json';
        if (! is_file($path)) {
            throw new \RuntimeException('This file is not a POS backup.');
        }
        $data = json_decode((string) file_get_contents($path), true);
        if (! is_array($data) || ($data['app'] ?? '') !== 'pos-backup') {
            throw new \RuntimeException('This file is not a POS backup.');
        }

        return $data;
    }

    /**
     * @return array<string, string>
     */
    private function uploadFiles(): array
    {
        $files = [];
        foreach ($this->uploadRoots() as $root) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)
            );
            foreach ($iterator as $file) {
                if (! $file->isFile()) {
                    continue;
                }
                $relative = ltrim(str_replace('\\', '/', substr($file->getPathname(), strlen($root))), '/');
                if ($relative === '' || isset($files[$relative]) || str_contains($relative, '..')) {
                    continue;
                }
                $files[$relative] = $file->getPathname();
            }
        }

        return $files;
    }

    private function uploadRoots(): array
    {
        $roots = [];
        $disk = storage_path('app/public');
        if (is_dir($disk)) {
            $roots[] = $disk;
        }
        $public = public_path('storage');
        if (is_dir($public)) {
            $realPublic = realpath($public);
            $realDisk = realpath($disk);
            if (! $realPublic || ! $realDisk || $realPublic !== $realDisk) {
                $roots[] = $public;
            }
        }

        return $roots;
    }

    private function prune(): void
    {
        $keep = max(3, min(30, (int) self::setting('backup_keep_count', 10)));
        $extra = BackupRun::where('status', 'success')->orderByDesc('id')->skip($keep)->take(100)->get();
        foreach ($extra as $run) {
            try {
                $this->delete($run);
            } catch (\Throwable $e) {
                Log::warning('Old backup could not be removed: '.$e->getMessage());
            }
        }

        BackupRun::where('status', 'failed')
            ->where('created_at', '<', now()->subDays(30))
            ->delete();
    }

    private function notify(BackupRun $run, bool $success): void
    {
        if ((string) self::setting('backup_notify', '0') !== '1') {
            return;
        }
        $email = trim((string) self::setting('backup_notify_email', ''));
        if ($email === '') {
            return;
        }

        try {
            $company = (string) (self::setting('company_name') ?: config('app.name'));
            MailService::send($email, new BackupStatusMail($success, $company, $run));
        } catch (\Throwable $e) {
            Log::warning('Backup notification email failed: '.$e->getMessage());
        }
    }

    private function markStaleRuns(): void
    {
        BackupRun::where('status', 'running')
            ->where('started_at', '<', now()->subHours(2))
            ->update([
                'status' => 'failed',
                'message' => 'Backup did not finish.',
                'finished_at' => now(),
            ]);
    }

    private function includeFiles(): bool
    {
        return (string) self::setting('backup_include_files', '1') !== '0';
    }

    private function driver(): string
    {
        $name = config('database.default');

        return (string) config('database.connections.'.$name.'.driver');
    }

    public static function isBackupZip(string $name): bool
    {
        $name = basename(str_replace('\\', '/', $name));

        return (bool) preg_match(
            '/^(?:backup-\d{8}-\d{6}|\d{2}-[A-Za-z]{3}-\d{4}_\d{2}-\d{2}-\d{2}-(?:AM|PM))(?:-\d+)?\.zip$/',
            $name
        );
    }

    public static function backupSortKey(string $name): string
    {
        $name = basename(str_replace('\\', '/', $name));
        if (preg_match('/^backup-(\d{8})-(\d{6})/', $name, $match)) {
            return $match[1].$match[2];
        }
        if (preg_match('/^(\d{2})-([A-Za-z]{3})-(\d{4})_(\d{2})-(\d{2})-(\d{2})-(AM|PM)/', $name, $match)) {
            $parsed = Carbon::createFromFormat(
                'd-M-Y h-i-s A',
                $match[1].'-'.$match[2].'-'.$match[3].' '.$match[4].'-'.$match[5].'-'.$match[6].' '.$match[7]
            );
            if ($parsed) {
                return $parsed->format('YmdHis');
            }
        }

        return $name;
    }

    private function uniqueFilename(): string
    {
        $stamp = now()->format('d-M-Y_h-i-s-A');
        $name = $stamp.'.zip';
        $i = 2;
        while (is_file($this->pathFor($name))) {
            $name = $stamp.'-'.$i.'.zip';
            $i++;
        }

        return $name;
    }

    private function pathFor(string $filename): string
    {
        return storage_path('app/backups/'.basename($filename));
    }

    private function passwordFor(BackupRun $run): ?string
    {
        if ($run->zip_password) {
            return $run->zip_password;
        }

        return $this->passwordFromFile((string) $run->filename) ?: ((string) self::setting('backup_zip_password', '') ?: null);
    }

    private function acquireLock(): bool
    {
        File::ensureDirectoryExists(storage_path('app/backups'));
        $handle = fopen(storage_path('app/backups/.lock'), 'c');
        if ($handle === false || ! flock($handle, LOCK_EX | LOCK_NB)) {
            if (is_resource($handle)) {
                fclose($handle);
            }

            return false;
        }
        $this->lockHandle = $handle;

        return true;
    }

    private function releaseLock(): void
    {
        if (is_resource($this->lockHandle)) {
            flock($this->lockHandle, LOCK_UN);
            fclose($this->lockHandle);
            $this->lockHandle = null;
        }
    }

    private static function lastSuccessAt(): ?Carbon
    {
        $last = BackupRun::where('status', 'success')->latest('finished_at')->first();

        return $last?->finished_at;
    }

    private static function calendarSlotDue(?Carbon $last, bool $weekly): bool
    {
        $slot = self::todaySlot();
        if ($weekly && (int) now()->dayOfWeek !== (int) self::setting('backup_weekly_day', 1)) {
            return false;
        }
        if (now()->lt($slot)) {
            return false;
        }

        return ! $last || $last->lt($slot);
    }

    private static function nextCalendarSlot(bool $weekly): Carbon
    {
        $slot = self::todaySlot();
        if (! $weekly) {
            return now()->gte($slot) ? $slot->copy()->addDay() : $slot;
        }

        $wanted = (int) self::setting('backup_weekly_day', 1);
        $daysAhead = ($wanted - (int) now()->dayOfWeek + 7) % 7;
        $candidate = $slot->copy()->addDays($daysAhead);
        if ($daysAhead === 0 && now()->gte($slot)) {
            $last = self::lastSuccessAt();
            if ($last && $last->gte($slot)) {
                $candidate->addWeek();
            }
        }

        return $candidate;
    }

    private static function todaySlot(): Carbon
    {
        $time = (string) self::setting('backup_daily_time', '02:00');
        [$hour, $minute] = array_pad(explode(':', $time), 2, '0');

        return now()->setTime((int) $hour, (int) $minute, 0);
    }

    private static function writePasswordFile(string $password): void
    {
        File::ensureDirectoryExists(storage_path('app/backups'));
        File::put(storage_path('app/backups/.password'), $password);
    }

    private function rememberPassword(string $filename, ?string $password): void
    {
        if (! $password) {
            return;
        }
        $line = $filename.'|'.$password.PHP_EOL;
        file_put_contents(storage_path('app/backups/passwords.txt'), $line, FILE_APPEND | LOCK_EX);
    }

    private function passwordFromFile(string $filename): ?string
    {
        $path = storage_path('app/backups/passwords.txt');
        if ($filename === '' || ! is_file($path)) {
            return null;
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES) ?: [];
        foreach (array_reverse($lines) as $line) {
            [$name, $password] = array_pad(explode('|', $line, 2), 2, null);
            if ($name === $filename && $password) {
                return $password;
            }
        }

        return null;
    }

    public static function storeSecret(string $key, string $plain): void
    {
        GeneralSettings::setGeneralSetting($key, 'enc1:'.Crypt::encryptString($plain));
    }

    public static function readSecret(string $key): string
    {
        $stored = (string) self::setting($key, '');
        if (! str_starts_with($stored, 'enc1:')) {
            return '';
        }

        try {
            $value = Crypt::decryptString(substr($stored, 5));
        } catch (\Throwable $e) {
            return '';
        }

        return is_string($value) ? $value : '';
    }

    public function testRemote(string $host, int $port, string $username, string $password, string $path): void
    {
        $connection = $this->openFtp($host, $port, $username, $password, $path);
        ftp_close($connection);
    }

    public function copyToRemote(string $localFile, string $filename): ?string
    {
        if (self::storageMode() === 'local') {
            return null;
        }

        try {
            $connection = $this->openFtp(
                self::readSecret('backup_remote_host'),
                (int) self::setting('backup_remote_port', 21),
                self::readSecret('backup_remote_username'),
                self::readSecret('backup_remote_password'),
                self::readSecret('backup_remote_path')
            );
            try {
                $uploaded = $this->putRemoteFile($connection, $filename, $localFile);
                if ($uploaded) {
                    $this->pruneRemote($connection);
                }
            } catch (\Throwable $e) {
                Log::warning('Remote backup upload failed: '.$e->getMessage());
                $uploaded = false;
            }
            $this->closeFtp($connection);
            if ($uploaded) {
                return null;
            }
        } catch (\Throwable $e) {
            Log::warning('Remote backup copy failed: '.$e->getMessage());
        }

        return $this->uploadWithCurl($localFile, $filename);
    }

    private function openFtp(string $host, int $port, string $username, string $password, string $path)
    {
        if (! function_exists('ftp_connect')) {
            throw new \RuntimeException('This server cannot use FTP.');
        }
        $host = trim($host);
        $username = trim($username);
        $path = trim(str_replace('\\', '/', $path), '/');
        if ($host === '' || $username === '' || $password === '' || $path === '' || str_contains($path, '..')) {
            throw new \RuntimeException('The other server settings are incomplete.');
        }
        if ($port < 1 || $port > 65535) {
            throw new \RuntimeException('The port is not valid.');
        }

        $connection = @ftp_connect($host, $port, 20);
        if ($connection === false) {
            throw new \RuntimeException('Could not connect to the other server.');
        }
        @ftp_set_option($connection, FTP_TIMEOUT_SEC, 15);
        if (! @ftp_login($connection, $username, $password)) {
            ftp_close($connection);
            throw new \RuntimeException('The other server rejected the username or password.');
        }
        if (defined('FTP_USEPASVADDRESS')) {
            @ftp_set_option($connection, FTP_USEPASVADDRESS, false);
        }
        @ftp_pasv($connection, true);
        foreach (explode('/', $path) as $part) {
            if ($part === '') {
                continue;
            }
            if (! @ftp_chdir($connection, $part)) {
                if (! @ftp_mkdir($connection, $part) || ! @ftp_chdir($connection, $part)) {
                    ftp_close($connection);
                    throw new \RuntimeException('Could not open the folder on the other server.');
                }
            }
        }

        return $connection;
    }

    private function putRemoteFile($connection, string $filename, string $localFile): bool
    {
        if (defined('FTP_USEPASVADDRESS')) {
            @ftp_set_option($connection, FTP_USEPASVADDRESS, false);
        }
        @ftp_pasv($connection, true);
        try {
            return (bool) @ftp_put($connection, $filename, $localFile, FTP_BINARY);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function closeFtp($connection): void
    {
        try {
            @ftp_close($connection);
        } catch (\Throwable $e) {
        }
    }

    private function uploadWithCurl(string $localFile, string $filename): ?string
    {
        if (! function_exists('curl_init')) {
            return 'Could not upload the backup file.';
        }
        $handle = fopen($localFile, 'rb');
        if ($handle === false) {
            return 'Could not upload the backup file.';
        }
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->remoteFileUrl($filename),
            CURLOPT_USERPWD => self::readSecret('backup_remote_username').':'.self::readSecret('backup_remote_password'),
            CURLOPT_UPLOAD => true,
            CURLOPT_INFILE => $handle,
            CURLOPT_INFILESIZE => filesize($localFile) ?: 0,
            CURLOPT_FTP_CREATE_MISSING_DIRS => 1,
            CURLOPT_FTP_SKIP_PASV_IP => true,
            CURLOPT_TIMEOUT => 120,
        ]);
        $ok = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        fclose($handle);
        if ($ok) {
            return null;
        }

        return 'Could not upload the backup file.'.($error !== '' ? ' '.$error : '');
    }

    private function downloadWithCurl(string $localFile, string $filename): bool
    {
        if (! function_exists('curl_init')) {
            return false;
        }
        $handle = fopen($localFile, 'wb');
        if ($handle === false) {
            return false;
        }
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->remoteFileUrl($filename),
            CURLOPT_USERPWD => self::readSecret('backup_remote_username').':'.self::readSecret('backup_remote_password'),
            CURLOPT_FILE => $handle,
            CURLOPT_FTP_SKIP_PASV_IP => true,
            CURLOPT_TIMEOUT => 120,
        ]);
        $ok = curl_exec($ch);
        curl_close($ch);
        fclose($handle);
        if (! $ok || ! is_file($localFile) || filesize($localFile) === 0) {
            @unlink($localFile);

            return false;
        }

        return true;
    }

    private function remoteFileUrl(string $filename): string
    {
        $host = self::readSecret('backup_remote_host');
        $port = (int) self::setting('backup_remote_port', 21);
        $path = trim(str_replace('\\', '/', self::readSecret('backup_remote_path')), '/');

        return 'ftp://'.$host.':'.$port.'/'.$path.'/'.basename($filename);
    }

    private function pruneRemote($connection): void
    {
        $keep = max(3, min(30, (int) self::setting('backup_keep_count', 10)));
        $names = @ftp_nlist($connection, '.');
        if (! is_array($names)) {
            return;
        }
        $files = [];
        foreach ($names as $name) {
            $base = basename(str_replace('\\', '/', (string) $name));
            if (self::isBackupZip($base)) {
                $files[] = $base;
            }
        }
        usort($files, function (string $a, string $b) {
            return strcmp(self::backupSortKey($b), self::backupSortKey($a));
        });
        foreach (array_slice($files, $keep) as $old) {
            @ftp_delete($connection, $old);
        }
    }
}
