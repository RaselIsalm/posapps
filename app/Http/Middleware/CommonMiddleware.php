<?php

namespace App\Http\Middleware;

use App\Models\GeneralSettings;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class CommonMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $general_settings = GeneralSettings::where('category', 'general')->pluck('value', 'key_name')->toArray();

        $tz = $general_settings['timezone'] ?? 'Europe/Madrid';
        if (! in_array($tz, timezone_identifiers_list(), true)) {
            $tz = 'Europe/Madrid';
        }
        config(['app.timezone' => $tz]);
        date_default_timezone_set($tz);
        $general_settings['timezone'] = $tz;

        View::share('general_settings', $general_settings);

        if (Auth::check() && ! Auth::user()->is_active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('login_error_toast', 'Your account is not active.');
        }

        return $next($request);
    }
}
