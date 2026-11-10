<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MobileDemoGuestTicket;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MobileLoginController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'identifier' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:1024'],
        ]);

        $identifier = trim($credentials['identifier']);
        $user = User::query()
            ->where('email', $identifier)
            ->orWhere('username', $identifier)
            ->first();

        if (! $user || ! $user->is_active || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'The provided credentials are invalid.',
                'errors' => ['identifier' => ['The provided credentials are invalid.']],
            ], 401)->header('Cache-Control', 'no-store, private');
        }

        MobileDemoGuestTicket::query()
            ->where('expires_at', '<', now()->subHour())
            ->delete();

        $ticket = Str::random(64);
        MobileDemoGuestTicket::query()->create([
            'token_hash' => hash('sha256', $ticket),
            'mode' => 'user',
            'user_id' => $user->id,
            'expires_at' => now()->addSeconds((int) config('onepos.demo.ticket_ttl_seconds', 60)),
        ]);

        return response()->json([
            'message' => 'Credentials verified.',
            'data' => [
                'ticket' => $ticket,
                'exchange_path' => '/mobile/session/exchange',
                'expires_in' => (int) config('onepos.demo.ticket_ttl_seconds', 60),
            ],
        ])->header('Cache-Control', 'no-store, private');
    }
}
