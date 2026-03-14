<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class Localization
{
    public function handle(Request $request, Closure $next)
    {
        $available = ['ar', 'en'];

        // Try session first (most reliable after StartSession middleware)
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
