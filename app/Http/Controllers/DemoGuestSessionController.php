<?php

namespace App\Http\Controllers;

use App\Models\MobileDemoGuestTicket;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class DemoGuestSessionController extends Controller
{
    public function exchange(Request $request): RedirectResponse
    {
        $validated = $request->validate(['ticket' => ['required', 'string', 'size:64']]);

        $ticket = DB::transaction(function () use ($validated) {
            $record = MobileDemoGuestTicket::query()
                ->where('token_hash', hash('sha256', $validated['ticket']))
                ->lockForUpdate()
                ->first();

            abort_unless(
                $record && ! $record->consumed_at && $record->expires_at->isFuture(),
                401,
                'This demo link has expired. Please try again.',
            );

            $record->forceFill(['consumed_at' => now()])->save();

            return $record;
        });

        abort_unless(in_array($ticket->mode, ['user', 'demo'], true), 401, 'This login link is invalid.');

        if ($ticket->mode === 'user') {
            $user = User::query()
                ->whereKey($ticket->user_id)
                ->where('is_active', true)
                ->first();

            abort_unless($user, 401, 'This account is no longer available. Please sign in again.');

            Auth::guard('web')->login($user);
            $request->session()->regenerate();
            $request->session()->forget('onepos_demo_guest');

            return redirect()->route('dashboard')->header('Cache-Control', 'no-store, private');
        }

        abort_unless(config('onepos.demo.enabled'), 404);
        abort_unless(config('onepos.demo.database_configured'), 503, 'Demo access is not configured.');

        $previousConnection = DB::getDefaultConnection();
        $previousPermissionCacheKey = config('permission.cache.key');
        DB::purge('demo');
        DB::setDefaultConnection('demo');
        config(['permission.cache.key' => $previousPermissionCacheKey.'.demo']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        try {
            $demoUser = User::query()
                ->where('email', config('onepos.demo.email'))
                ->where('is_active', true)
                ->first();

            abort_unless($demoUser && $demoUser->hasRole('Demo-Guest'), 503, 'Demo access is not ready.');

            Auth::guard('web')->login($demoUser);
            $request->session()->regenerate();
            $request->session()->put('onepos_demo_guest', true);

            return redirect()->route('dashboard')->header('Cache-Control', 'no-store, private');
        } finally {
            DB::disconnect('demo');
            DB::setDefaultConnection($previousConnection);
            config(['permission.cache.key' => $previousPermissionCacheKey]);
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }
}
