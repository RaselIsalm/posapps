<?php

namespace App\Http\Controllers;

use App\Models\BackupRun;
use App\Models\GeneralSettings;
use App\Services\BackupService;
use Illuminate\Http\Request;

class BackupController extends Controller
{
    public function index(BackupService $backup)
    {
        $backup->syncDiskFiles();
        $backup->forgetMissingLocalFiles();
        if (BackupService::encryptionAvailable()) {
            BackupService::ensurePassword();
        }

        $settings = $this->settings();

        return view('backend.settings.backup', [
            'settings' => $settings,
            'runs' => BackupRun::latest('id')->limit(30)->get(),
            'intervals' => BackupService::INTERVALS,
            'weekdays' => BackupService::WEEKDAYS,
            'encryption' => BackupService::encryptionAvailable(),
            'nextRun' => BackupService::nextRunDescription(),
            'password' => $settings['backup_zip_password'],
            'cronCommand' => '* * * * * cd '.escapeshellarg(base_path()).' && php artisan schedule:run',
        ]);
    }

    public function update(Request $request)
    {
        $time = (string) $request->input('backup_daily_time', '02:00');
        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $time)) {
            $request->merge(['backup_daily_time' => substr($time, 0, 5)]);
        }

        $validated = $request->validate([
            'backup_enabled' => 'required|in:0,1',
            'backup_interval' => 'required|in:'.implode(',', array_keys(BackupService::INTERVALS)),
            'backup_daily_time' => 'required|date_format:H:i',
            'backup_weekly_day' => 'required|integer|between:0,6',
            'backup_keep_count' => 'required|integer|min:3|max:30',
            'backup_notify' => 'required|in:0,1',
            'backup_notify_email' => 'nullable|email|max:150|required_if:backup_notify,1',
            'backup_include_files' => 'required|in:0,1',
            'backup_storage' => 'required|in:local,both,remote',
            'backup_remote_host' => 'nullable|string|max:255|required_unless:backup_storage,local',
            'backup_remote_port' => 'nullable|integer|min:1|max:65535|required_unless:backup_storage,local',
            'backup_remote_username' => 'nullable|string|max:190|required_unless:backup_storage,local',
            'backup_remote_password' => 'nullable|string|max:190',
            'backup_remote_path' => 'nullable|string|max:255|required_unless:backup_storage,local',
        ]);

        $validated['backup_remote_enabled'] = $validated['backup_storage'] === 'local' ? '0' : '1';

        if ($validated['backup_storage'] !== 'local') {
            $this->assertRemoteInput($validated);
            if (trim((string) ($validated['backup_remote_password'] ?? '')) === '' && BackupService::readSecret('backup_remote_password') === '') {
                return back()->withInput()->withErrors(['backup_remote_password' => 'Enter the FTP password.']);
            }
        }

        foreach ($validated as $key => $value) {
            if (in_array($key, ['backup_remote_host', 'backup_remote_username', 'backup_remote_password', 'backup_remote_path'], true)) {
                continue;
            }
            GeneralSettings::setGeneralSetting($key, $value);
        }
        $this->storeRemoteSecrets($validated);

        if (BackupService::encryptionAvailable()) {
            BackupService::ensurePassword();
        }

        return back()->with('success', 'Backup settings saved.');
    }

    public function run(BackupService $backup)
    {
        try {
            $result = $backup->run('manual');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        if ($result->status !== 'success') {
            return back()->with('error', $result->message ?: 'Backup failed.');
        }

        $note = str_contains((string) $result->message, 'other server') ? ' '.$result->message : '';

        return back()->with('success', 'Backup saved: '.$result->filename.' ('.$result->sizeLabel().').'.$note);
    }

    public function testRemote(Request $request, BackupService $backup)
    {
        $validated = $request->validate([
            'backup_remote_host' => 'required|string|max:255',
            'backup_remote_port' => 'required|integer|min:1|max:65535',
            'backup_remote_username' => 'required|string|max:190',
            'backup_remote_password' => 'nullable|string|max:190',
            'backup_remote_path' => 'required|string|max:255',
        ]);
        $this->assertRemoteInput($validated);
        $password = trim((string) ($validated['backup_remote_password'] ?? ''));
        if ($password === '') {
            $password = BackupService::readSecret('backup_remote_password');
        }
        if ($password === '') {
            return back()->withInput()->withErrors(['backup_remote_password' => 'Enter the FTP password.']);
        }

        try {
            $backup->testRemote(
                $validated['backup_remote_host'],
                (int) $validated['backup_remote_port'],
                $validated['backup_remote_username'],
                $password,
                $validated['backup_remote_path']
            );
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->withInput()->with('success', 'Connected to the other server. Save settings to keep this login.');
    }

    public function regeneratePassword()
    {
        if (! BackupService::encryptionAvailable()) {
            return back()->with('error', 'This server cannot encrypt zip files.');
        }

        $password = strtoupper(\Illuminate\Support\Str::random(4)).'-'.strtoupper(\Illuminate\Support\Str::random(4)).'-'.strtoupper(\Illuminate\Support\Str::random(4));
        GeneralSettings::setGeneralSetting('backup_zip_password', $password);

        return back()->with('success', 'New backups will use the new password. Older backup files keep their old password.');
    }

    public function download(BackupRun $backup, BackupService $service)
    {
        $path = $this->filePath($backup);
        $temporary = ! is_file($path);
        if ($temporary && str_contains((string) $backup->message, 'Saved on the other server only')) {
            try {
                $service->pullFromRemote($backup->filename);
            } catch (\Throwable $e) {
                return back()->with('error', $e->getMessage());
            }
        } elseif ($temporary) {
            $backup->delete();

            return back()->with('success', 'That backup was removed from the list because the file is no longer on the server.');
        }
        if (! is_file($path)) {
            return back()->with('error', 'That backup file is no longer on the server.');
        }

        return response()->download($path, $backup->filename)->deleteFileAfterSend($temporary);
    }

    public function destroy(BackupRun $backup, BackupService $service)
    {
        try {
            $service->delete($backup);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Backup deleted.');
    }

    public function restore(Request $request, BackupRun $backup, BackupService $service)
    {
        $request->validate([
            'confirm' => 'required|in:RESTORE',
        ]);

        try {
            $service->withLocalCopy($backup, function () use ($service, $backup, $request) {
                $service->restore($backup, (string) $request->input('confirm'));
            });
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Restore finished. A safety copy was saved on the server first.');
    }

    private function settings(): array
    {
        return [
            'backup_enabled' => (string) BackupService::setting('backup_enabled', '0'),
            'backup_interval' => (string) BackupService::setting('backup_interval', 'daily'),
            'backup_daily_time' => substr((string) BackupService::setting('backup_daily_time', '02:00'), 0, 5),
            'backup_weekly_day' => (string) BackupService::setting('backup_weekly_day', '1'),
            'backup_keep_count' => (int) BackupService::setting('backup_keep_count', 10),
            'backup_notify' => (string) BackupService::setting('backup_notify', '0'),
            'backup_notify_email' => (string) BackupService::setting('backup_notify_email', ''),
            'backup_include_files' => (string) BackupService::setting('backup_include_files', '1'),
            'backup_zip_password' => (string) BackupService::setting('backup_zip_password', ''),
            'backup_storage' => BackupService::storageMode(),
            'backup_remote_enabled' => (string) BackupService::setting('backup_remote_enabled', '0'),
            'backup_remote_host' => BackupService::readSecret('backup_remote_host'),
            'backup_remote_port' => (int) (BackupService::setting('backup_remote_port', 21) ?: 21),
            'backup_remote_username' => BackupService::readSecret('backup_remote_username'),
            'backup_remote_password_set' => BackupService::readSecret('backup_remote_password') !== '',
            'backup_remote_path' => BackupService::readSecret('backup_remote_path'),
        ];
    }

    private function assertRemoteInput(array $input): void
    {
        $host = trim((string) ($input['backup_remote_host'] ?? ''));
        $path = trim((string) ($input['backup_remote_path'] ?? ''));
        if (! preg_match('/^[A-Za-z0-9.\-]+$/', $host)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'backup_remote_host' => 'Enter the server address only, without http or a folder.',
            ]);
        }
        if (! preg_match('/^[A-Za-z0-9_\-\/]+$/', $path) || str_contains($path, '..')) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'backup_remote_path' => 'Use a folder name such as private-backups. Do not use public_html.',
            ]);
        }
    }

    private function storeRemoteSecrets(array $input): void
    {
        $password = trim((string) ($input['backup_remote_password'] ?? ''));
        if ($password !== '') {
            BackupService::storeSecret('backup_remote_password', $password);
        }
        foreach (['backup_remote_host', 'backup_remote_username', 'backup_remote_path'] as $key) {
            $value = trim((string) ($input[$key] ?? ''));
            if ($value !== '') {
                BackupService::storeSecret($key, $value);
            }
        }
    }

    private function filePath(BackupRun $backup): string
    {
        $name = basename((string) $backup->filename);
        if (! BackupService::isBackupZip($name)) {
            abort(404);
        }

        return storage_path('app/backups/'.$name);
    }
}
