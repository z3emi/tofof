<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        // يمنع الوصول بدون إذن واضح
        $this->middleware(\Spatie\Permission\Middleware\PermissionMiddleware::class . ':view-activity-log');
    }

    /**
     * عرض سجل الأنشطة مع فلترة بحسب صلاحيات المستخدم الحالي.
     */
    public function index(Request $request)
    {
        // === وقت بغداد (أو خليه من config/app.php) ===
        $timezone = config('app.timezone', 'Asia/Baghdad');

        // ===== 1) حدّد الموديلات المسموح يشوفها المستخدم حسب أذوناته =====
        // نستخدم أسماء الـ Model بالـ "basename" حتى ما نعتمد على الـ namespace كامل.
        $allowedBasenames = [];

        $u = $request->user();

        // عدّل الأسماء حسب موديلاتك الموجودة فعلاً بالمشروع
        if ($u->can('view-orders'))          $allowedBasenames[] = 'Order';
        if ($u->can('view-products'))        $allowedBasenames[] = 'Product';
        if ($u->can('view-categories'))      $allowedBasenames[] = 'Category';
        if ($u->can('view-customers'))       $allowedBasenames[] = 'Customer';
        if ($u->can('view-suppliers'))       $allowedBasenames[] = 'Supplier';
        if ($u->can('view-purchases'))       $allowedBasenames[] = 'Purchase';
        if ($u->can('view-expenses'))        $allowedBasenames[] = 'Expense';
        if ($u->can('view-inventory'))       $allowedBasenames[] = 'Inventory';
        if ($u->can('view-discount-codes'))  $allowedBasenames[] = 'DiscountCode';
        if ($u->can('view-users'))           $allowedBasenames[] = 'User';
        if ($u->can('view-roles')) {         // أدوار وصلاحيات (spatie)
            $allowedBasenames[] = 'Role';
            $allowedBasenames[] = 'Permission';
        }

        // أحداث الدخول/الخروج نسمح بها فقط للي عنده عرض المستخدمين
        $includeAuthEvents = $u->can('view-users');

        // إذا ما عنده غير إذن view-activity-log بدون أي إذن عرض ثاني،
        // راح نخلّي النتائج فاضية (إلا إذا سمحنا login/logout).
        $hasAnyModelPermission = !empty($allowedBasenames);

        // ===== 2) ابدأ بناء الاستعلام =====
        $query = ActivityLog::with([
            'user:id,name,phone_number',
            'loggable'
        ]);

        // افتراضيًا: لا تعرض سجلات المستخدمين أصحاب دور user (العادي)
        // إلا إذا انطلب من الفلاتر show_users_role_user=1
        if (!$request->boolean('show_users_role_user')) {
            $query->where(function ($q) {
                $q->whereNull('user_id')
                  ->orWhereHas('user.roles', function ($r) {
                      $r->where('name', '!=', 'user');
                  });
            });
        }

        // فلترة بحسب صلاحيات العرض: إمّا موديلات مسموح بها، أو أحداث login/logout (حسب الإذن)
        $query->where(function ($q) use ($allowedBasenames, $includeAuthEvents, $hasAnyModelPermission) {
            // إذا عنده صلاحية على موديلات معيّنة
            if ($hasAnyModelPermission) {
                $q->where(function ($qq) use ($allowedBasenames) {
                    foreach ($allowedBasenames as $bn) {
                        // loggable_type يخزن namespace كامل (مثلاً App\Models\Order)
                        // نستهدف النهاية بالـ LIKE
                        $qq->orWhere('loggable_type', 'like', "%\\{$bn}");
                    }
                });
            }

            // و/أو أحداث الدخول/الخروج إذا مسموحة
            if ($includeAuthEvents) {
                $q->orWhereIn('action', ['login', 'logout']);
            }

            // لو ما عنده أي صلاحية موديلات ولا مسموح أحداث الدخول/الخروج، فرّغ النتائج
            if (!$hasAnyModelPermission && !$includeAuthEvents) {
                $q->whereRaw('1=0');
            }
        });

        // ===== 3) تطبيق فلاتر البحث =====
        if ($request->filled('q')) {
            $term = '%' . trim($request->q) . '%';
            $query->where(function ($x) use ($term) {
                $x->where('ip_address', 'like', $term)
                  ->orWhere('user_agent', 'like', $term)
                  ->orWhere('action', 'like', $term)
                  ->orWhere('loggable_type', 'like', $term)
                  ->orWhereHas('user', function ($u) use ($term) {
                      $u->where('name', 'like', $term)
                        ->orWhere('phone_number', 'like', $term);
                  });
            });
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('model')) {
            // فلترة باسم الموديل (الـ basename فقط)
            $bn = trim($request->model);
            $query->where('loggable_type', 'like', "%\\{$bn}");
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', (int) $request->user_id);
        }

        if ($request->filled('ip')) {
            $query->where('ip_address', 'like', '%' . trim($request->ip) . '%');
        }

        // التاريخ
        if ($request->filled('date_from')) {
            $from = Carbon::parse($request->date_from, $timezone)->startOfDay()->utc();
            $query->where('created_at', '>=', $from);
        }
        if ($request->filled('date_to')) {
            $to = Carbon::parse($request->date_to, $timezone)->endOfDay()->utc();
            $query->where('created_at', '<=', $to);
        }

        // ترتيب + تقطيع
        $sortBy  = in_array($request->get('sort_by'), ['id','action','loggable_type','loggable_id','ip_address','created_at']) ? $request->get('sort_by') : 'id';
        $sortDir = $request->get('sort_dir') === 'asc' ? 'asc' : 'desc';
        $perPage = (int) $request->get('per_page', 10);
        if ($perPage < 5)  $perPage = 5;
        if ($perPage > 100) $perPage = 100;

        $logs = $query->orderBy($sortBy, $sortDir)->paginate($perPage);

        // قائمة مستخدمين للفلاتر (اسم + هاتف) — فقط اللي مو role=user حتى ما تكثر القائمة
        $users = User::select('id','name','phone_number')
                     ->whereDoesntHave('roles', function ($r) { $r->where('name','user'); })
                     ->orderBy('name')
                     ->get();

        return view('admin.logs.index', [
            'logs'      => $logs,
            'users'     => $users,
            'timezone'  => $timezone,
            'filters'   => $request->all(),
            'sortBy'    => $sortBy,
            'sortDir'   => $sortDir,
        ]);
    }
}
