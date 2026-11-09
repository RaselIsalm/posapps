<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function switch(Request $request, string $locale): RedirectResponse
    {
        abort_unless(in_array($locale, SetLocale::LOCALES, true), 404);

        $request->session()->put('locale', $locale);

        $fallback = $request->user() ? route('dashboard') : route('login');

        return redirect()->to($request->headers->get('referer', $fallback))
            ->withCookie(cookie()->forever('locale', $locale))
            ->withCookie(cookie('pos_lang', $locale, 60 * 24 * 365, '/', null, false, false));
    }
}
