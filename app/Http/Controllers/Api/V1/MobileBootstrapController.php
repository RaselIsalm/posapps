<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MobileDemoGuestTicket;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MobileBootstrapController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json([
            'data' => [
                'application' => 'ONEPOS',
                'api_version' => (int) config('onepos.mobile.api_version'),
                'minimum_app_version' => config('onepos.mobile.minimum_app_version'),
                'web_login' => true,
                'demo_guest' => (bool) config('onepos.demo.enabled')
                    && (bool) config('onepos.demo.database_configured'),
            ],
        ])->header('Cache-Control', 'no-store, private');
    }

    public function createDemoGuestTicket(Request $request): JsonResponse
    {
        abort_unless(config('onepos.demo.enabled'), 404);

        abort_unless(config('onepos.demo.database_configured'), 503, 'Demo environment is not configured.');

        MobileDemoGuestTicket::query()
            ->where('expires_at', '<', now()->subHour())
            ->delete();

        $demoUser = User::on('demo')
            ->where('email', config('onepos.demo.email'))
            ->where('is_active', true)
            ->first();

        abort_unless(
            $demoUser && $demoUser->roles()->where('name', 'Demo-Guest')->exists(),
            503,
            'Demo access is not ready.',
        );

        $ticket = Str::random(64);
        MobileDemoGuestTicket::query()->create([
            'token_hash' => hash('sha256', $ticket),
            'mode' => 'demo',
            'expires_at' => now()->addSeconds((int) config('onepos.demo.ticket_ttl_seconds', 60)),
        ]);

        return response()->json([
            'data' => [
                'ticket' => $ticket,
                'exchange_path' => '/mobile/session/exchange',
                'expires_in' => (int) config('onepos.demo.ticket_ttl_seconds', 60),
            ],
        ])->header('Cache-Control', 'no-store, private');
    }
}
