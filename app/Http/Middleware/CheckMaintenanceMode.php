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

        // 3) تجاوز للأدمن (بغض النظر عن المسار)
        $isAdmin = false;
        if (Auth::check()) {
            $user = Auth::user();
            // حقل is_admin (Boolean)
            if (property_exists($user, 'is_admin') && $user->is_admin) {
                $isAdmin = true;
            }
            // دعم Spatie (اختياري)
            if (!$isAdmin && method_exists($user, 'hasRole') && $user->hasRole('admin')) {
                $isAdmin = true;
            }
        }
        if ($isAdmin) {
            return $next($request);
        }

        // 4) قراءة وضع الصيانة من الداتابيس
        $mode = Setting::where('key', 'maintenance_mode')->value('value') ?? 'off';

        if ($mode === 'on') {
            // حوّل لصفحة الصيانة (أو اعرضها مباشرة)
            return redirect()->route('maintenance.page');
            // أو:
            // return response()->view('frontend.maintenance', [], 200);
        }

        return $next($request);
    }
}