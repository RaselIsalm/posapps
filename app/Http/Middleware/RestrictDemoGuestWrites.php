<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictDemoGuestWrites
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->hasSession() && $request->session()->get('onepos_demo_guest') === true) {
            if (! in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)
                && $request->route()?->getName() !== 'logout') {
                return response()->json(['message' => 'Demo access is read-only.'], 403);
            }
        }

        return $next($request);
    }
}
