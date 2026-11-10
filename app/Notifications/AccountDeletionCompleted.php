<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountDeletionCompleted extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your ONEPOS account data request is complete')
            ->greeting('Request completed')
            ->line('The personal profile data linked to your ONEPOS account has been removed.')
            ->line('Business transaction records required for operational or legal purposes may remain in de-identified form.');
    }
}
