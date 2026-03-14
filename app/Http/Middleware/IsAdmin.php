<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // التحقق إذا كان المستخدم مسجل دخوله وإذا كان نوعه 'admin'
        if (auth()->check() && auth()->user()->type === 'admin') {
            // إذا كان أدمن، اسمح له بالمرور
            return $next($request);
        }

        // إذا لم يكن أدمن، أعده إلى الصفحة الرئيسية مع رسالة خطأ
        abort(403, 'Unauthorized action.');
    }
}
