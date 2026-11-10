<?php

namespace App\Http\Middleware;

use Closure;
use Fruitcake\LaravelDebugbar\Facades\Debugbar;
use Illuminate\Http\Request;

class DisableDebugbarForOnePosApp
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (
            str_contains($request->userAgent() ?? '', 'ONEPOSApp/')
            && class_exists(Debugbar::class)
        ) {
            Debugbar::disable();
        }

        return $next($request);
    }
}
