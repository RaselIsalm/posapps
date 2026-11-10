<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StockAlertNotification extends Notification
{
    use Queueable;

    protected $message;

    protected $action_url;

    protected $product_branch_id;

    /**
     * Create a new notification instance.
     */
    public function __construct($message, $action_url = '#', $product_branch_id = null)
    {
        $this->message = $message;
        $this->action_url = $action_url;
        $this->product_branch_id = $product_branch_id;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database']; // Keeping it database-only for now as per production base
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
            'icon' => 'alert-circle',
            'type' => 'warning',
            'product_branch_id' => $this->product_branch_id,
        ];
    }
}
