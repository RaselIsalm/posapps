<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get unread notifications for the authenticated user.
     */
    public function getUnreadNotifications()
    {
        $user = Auth::user();
        $notifications = $this->withHighlightLinks(
            $user->unreadNotifications()->latest()->limit(10)->get()
        );
        $unreadCount = $user->unreadNotifications()->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Mark all notifications as read for the authenticated user.
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json(['status' => 'success']);
    }

    /**
     * List all notifications (for a dedicated notifications page if needed).
     */
    public function index()
    {
        $notifications = Auth::user()->notifications()->paginate(20);
        $this->withHighlightLinks($notifications->getCollection());

        return view('backend.notifications.index', compact('notifications'));
    }

    /**
     * Point account-deletion notices at the exact user row, including older notices that only stored the users page.
     */
    private function withHighlightLinks($notifications)
    {
        return $notifications->map(function ($notification) {
            $data = is_array($notification->data) ? $notification->data : [];
            $url = $this->resolveActionUrl($data);

            if ($url) {
                $data['action_url'] = $url;
                $notification->setAttribute('data', $data);
            }

            return $notification;
        });
    }

    private function resolveActionUrl(array $data): ?string
    {
        $url = $data['action_url'] ?? null;
        if (! is_string($url) || $url === '' || $url === '#') {
            $url = null;
        }

        if ($url && str_contains($url, 'highlight=')) {
            return $url;
        }

        $isUsersLink = ! $url || str_contains($url, '/users');
        if (! $isUsersLink) {
            return $url;
        }

        $userId = isset($data['user_id']) ? (int) $data['user_id'] : 0;
        if ($userId < 1 && preg_match('/\(([^)\s]+@[^)\s]+)\)/', (string) ($data['message'] ?? ''), $matches)) {
            $userId = (int) User::query()->where('email', $matches[1])->value('id');
        }

        if ($userId > 0) {
            return route('users.index', ['highlight' => $userId]);
        }

        return $url;
    }
}
