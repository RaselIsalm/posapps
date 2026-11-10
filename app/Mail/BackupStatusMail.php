<?php

namespace App\Mail;

use App\Models\BackupRun;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BackupStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public bool $success,
        public string $company,
        public BackupRun $run
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->company.' backup '.($this->success ? 'completed' : 'failed'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.backup_status',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
