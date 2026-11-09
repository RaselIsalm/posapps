<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

class RunBackup extends Command
{
    protected $signature = 'backup:run {--scheduled : Run only when the saved interval is due}';

    protected $description = 'Back up the database and uploaded files';

    public function handle(BackupService $backup): int
    {
        if ($this->option('scheduled')) {
            $reason = BackupService::dueReason();
            if ($reason !== null) {
                $this->line('Backup skipped: '.$reason);

                return self::SUCCESS;
            }
        }

        try {
            $run = $backup->run($this->option('scheduled') ? 'schedule' : 'manual');
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        if ($run->status !== 'success') {
            $this->error($run->message ?: 'Backup failed.');

            return self::FAILURE;
        }

        $this->info('Backup saved: '.$run->filename.' ('.$run->sizeLabel().')');

        return self::SUCCESS;
    }
}
