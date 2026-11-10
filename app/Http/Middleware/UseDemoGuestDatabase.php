<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

class UseDemoGuestDatabase
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->hasSession() || $request->session()->get('onepos_demo_guest') !== true) {
            return $next($request);
        }

        abort_unless(config('onepos.demo.enabled') && config('onepos.demo.database_configured'), 503, 'Demo environment is unavailable.');

        $previousConnection = DB::getDefaultConnection();
        $previousPermissionCacheKey = config('permission.cache.key');
        DB::purge('demo');
        DB::setDefaultConnection('demo');
        config(['permission.cache.key' => $previousPermissionCacheKey.'.demo']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        try {
            return $next($request);
        } finally {
            DB::disconnect('demo');
            DB::setDefaultConnection($previousConnection);
            config(['permission.cache.key' => $previousPermissionCacheKey]);
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }
}
