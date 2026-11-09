<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AccountDeletionRequest extends Notification
{
    use Queueable;

    protected $message;
    protected $action_url;
    protected $userId;

    /**
     * Create a new notification instance.
     */
    public function __construct($message, $action_url = '#', $userId = null)
    {
        $this->message = $message;
        $this->userId = $userId ? (int) $userId : null;
        $this->action_url = $this->userId
            ? route('users.index', ['highlight' => $this->userId])
            : $action_url;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->message,
            'action_url' => $this->action_url,
            'user_id' => $this->userId,
            'icon' => 'trash-2',
            'type' => 'danger',
        ];
    }
}


