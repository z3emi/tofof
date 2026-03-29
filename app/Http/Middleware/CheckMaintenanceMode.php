namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next)
    {
        // 1) تجاوز للأصول والملفات العامة
        if (
            $request->is('storage/*') || $request->is('assets/*') ||
            $request->is('vendor/*')  || $request->is('build/*')  ||
            $request->is('favicon.*') || $request->is('robots.txt')
        ) {
            return $next($request);
        }

        // 2) تجاوز لمسارات الإدارة المحتملة (عدّل/زد حسب مشروعك)
        if (
            $request->is('admin') || $request->is('admin/*') ||
            $request->is('dashboard') || $request->is('dashboard/*') ||
            $request->is('panel') || $request->is('panel/*') ||
            $request->is('cp') || $request->is('cp/*') ||
            $request->routeIs('maintenance.page') || // صفحة الصيانة نفسها
            $request->is('login') || $request->is('register') // auth pages
        ) {
            return $next($request);
        }

        // 3) تجاوز للمسؤولين بصلاحية Super-Admin
        $isSuperAdmin = false;

        // فحص جارد الأدمن أولاً
        if (Auth::guard('admin')->check()) {
            $manager = Auth::guard('admin')->user();
            if (method_exists($manager, 'isSuperAdmin') && $manager->isSuperAdmin()) {
                $isSuperAdmin = true;
            }
        }
        // فحص جارد الويب (للمستخدمين العاديين إن وجد سوبر أدمن فيهم)
        elseif (Auth::check()) {
            $user = Auth::user();
            if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                $isSuperAdmin = true;
            }
        }

        if ($isSuperAdmin) {
            return $next($request);
        }

        // 4) قراءة وضع الصيانة
        if (\App\Models\Setting::isMaintenanceMode()) {
            return redirect()->route('maintenance.page');
        }

        return $next($request);
    }
}