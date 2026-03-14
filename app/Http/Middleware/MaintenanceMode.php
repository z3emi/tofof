<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;

class MaintenanceMode
{
    /**
     * هل الصيانة مفعّلة من الإعدادات؟
     */
    protected function maintenanceEnabled(): bool
    {
        // إذا عندك كاش للإعدادات، تقدر تبدله بـ cache()->remember(...)
        $value = Setting::where('key', 'maintenance_enabled')->value('value');
        return (int) $value === 1;
    }

    /**
     * قائمة عناوين IP المسموح لها التجاوز أثناء الصيانة (اختياري).
     * اضبطها من env مثل: MAINTENANCE_ALLOW_IPS=127.0.0.1,1.2.3.4
     */
    protected function allowedIps(): array
    {
        $ips = env('MAINTENANCE_ALLOW_IPS', '');
        if (trim($ips) === '') return [];
        return array_map('trim', explode(',', $ips));
    }

    /**
     * هل هذا المستخدم أدمن؟
     * إذا تستخدم حقل is_admin أو صلاحيات أخرى عدّل حسب نظامك.
     */
    protected function isAdmin(): bool
    {
        return Auth::check() && (property_exists(Auth::user(), 'is_admin') ? (bool) Auth::user()->is_admin : false);
    }

    /**
     * مسارات ولوحات لازم تتجاوز الصيانة (تقدر تعدلها حسب مشروعك).
     */
    protected function shouldBypassForPath(Request $request): bool
    {
        // مسارات الإدارة
        if ($request->is('admin') || $request->is('admin/*')) {
            return true;
        }

        // أدوات/خدمات داخلية (عدّل حسب استخدامك)
        if ($request->is('horizon*') || $request->is('telescope*') || $request->is('_debugbar*')) {
            return true;
        }

        // أصول/ملفات ثابتة
        if ($request->is('storage/*') || $request->is('vendor/*') || $request->is('assets/*')) {
            return true;
        }

        // نقطة فحص صحة السيرفر (إن وجدت)
        if ($request->is('_health') || $request->routeIs('health')) {
            return true;
        }

        return false;
    }

    public function handle(Request $request, Closure $next)
    {
        // تجاوز للصناعة/الكونسول والكرون
        if (app()->runningInConsole()) {
            return $next($request);
        }

        // تجاوز للأدمن
        if ($this->isAdmin()) {
            return $next($request);
        }

        // تجاوز لمسارات محددة
        if ($this->shouldBypassForPath($request)) {
            return $next($request);
        }

        // تجاوز لعناوين IP مسموحة (إن وُجدت)
        if (in_array($request->ip(), $this->allowedIps(), true)) {
            return $next($request);
        }

        // إذا الصيانة مفعّلة، نعرض صفحة maintenance.blade.php (بدون 503)
        if ($this->maintenanceEnabled()) {
            // خليه 200 حتى ما يظهر كخطأ
            return response()->view('maintenance', [], 200);
        }

        return $next($request);
    }
}
