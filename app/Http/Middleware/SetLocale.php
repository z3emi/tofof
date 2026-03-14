<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        // NOTE: This middleware runs AFTER StartSession.
        // Locale is applied here from session, then Localization middleware also applies it.
        $available = ['ar', 'en'];

        $locale = $request->session()->get('locale')
            ?? $request->cookie('app_locale')
            ?? config('app.locale', 'ar');

        if (!in_array($locale, $available)) {
            $locale = 'ar';
        }

        App::setLocale($locale);

        return $next($request);
    }
}