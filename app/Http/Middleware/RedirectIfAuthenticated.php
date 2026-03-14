<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return redirect($this->redirectPath($guard));
            }
        }

        return $next($request);
    }

    protected function redirectPath(?string $guard): string
    {
        if ($guard === 'admin') {
            return route('admin.dashboard');
        }

        return route('homepage');
    }
}
