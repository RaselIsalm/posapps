<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountDeletionVerification extends Notification
{
    use Queueable;

    public function __construct(public string $confirmationUrl) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Confirm your ONEPOS account deletion request')
            ->greeting('Confirm your request')
            ->line('We received a request to delete the ONEPOS account linked to this email address.')
            ->line('Confirm the request below. The link expires in 60 minutes.')
            ->action('Confirm account deletion request', $this->confirmationUrl)
            ->line('An administrator will review the verified request. If you did not make it, you can ignore this email.');
    }
}
