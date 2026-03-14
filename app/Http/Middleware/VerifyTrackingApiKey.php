<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyTrackingApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $forwardedProto = $request->header('X-Forwarded-Proto');
        $isSecure = $request->secure() || ($forwardedProto && strtolower($forwardedProto) === 'https');

        if (! $isSecure) {
            return response()->json([
                'message' => 'يجب استخدام بروتوكول HTTPS للوصول إلى واجهة التتبع.',
            ], Response::HTTP_FORBIDDEN);
        }

        $apiKey = config('tracking.api_key');

        if (empty($apiKey)) {
            return response()->json([
                'message' => 'لم يتم تهيئة مفتاح تتبع الأجهزة.',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $providedKey = $request->header('X-Tracking-Key');

        if (! hash_equals($apiKey, (string) $providedKey)) {
            return response()->json([
                'message' => 'مفتاح الوصول إلى واجهة التتبع غير صالح.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
