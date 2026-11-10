<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AccountDeletionRequested extends Notification
{
    use Queueable;

    public function __construct(public int $requestId) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'An account deletion request is ready for review.',
            'action_url' => route('users.deletionRequests.index'),
            'request_id' => $this->requestId,
            'icon' => 'trash-2',
            'type' => 'danger',
        ];
    }
}
