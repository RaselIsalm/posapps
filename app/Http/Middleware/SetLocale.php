<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public const LOCALES = ['en', 'bn'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->cookie('pos_lang');

        if (! in_array($locale, self::LOCALES, true)) {
            $locale = $request->session()->get('locale', $request->cookie('locale', config('app.locale', 'en')));
        }

        if (! in_array($locale, self::LOCALES, true)) {
            $locale = 'en';
        }

        $request->session()->put('locale', $locale);
        App::setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
