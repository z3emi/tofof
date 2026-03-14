<?php

namespace App\Http\Middleware;

use App\Services\PersistentLoginService;
use Closure;
use Illuminate\Http\Request;

class AutoLoginFromPersistentCookie
{
    public function __construct(protected PersistentLoginService $service) {}

    public function handle(Request $request, Closure $next)
    {
        // إذا مو مسجل دخول، حاول الدخول من الكوكي
        $this->service->attemptAutoLogin();

        return $next($request);
    }
}
