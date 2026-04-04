<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Manager;
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
        // === وقت بغداد (قابل للتعيين من config/app.php أو خليه افتراضي للبصرة وبغداد) ===
        $timezone = config('app.timezone') === 'UTC' ? 'Asia/Baghdad' : config('app.timezone');

        // ===== 1) حدّد الموديلات المسموح يشوفها المستخدم حسب أذوناته =====
        // نستخدم أسماء الـ Model بالـ "basename" حتى ما نعتمد على الـ namespace كامل.
        $allowedBasenames = [];

        $u = $request->user();

        // عدّل الأسماء حسب موديلاتك الموجودة فعلاً بالمشروع
        if ($u->can('view-orders') || $u->can('create-orders') || $u->can('edit-orders') || $u->can('delete-orders') || $u->can('restore-orders') || $u->can('force-delete-orders')) $allowedBasenames[] = 'Order';
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
        if ($u->can('view-users'))           $allowedBasenames[] = 'Manager';   // أنشطة المدراء
        if ($u->can('edit-settings'))        $allowedBasenames[] = 'Setting';   // تغييرات الإعدادات

        // أحداث الدخول/الخروج نسمح بها فقط للي عنده عرض المستخدمين
        $includeAuthEvents = $u->can('view-users');

        // أحداث الخلفية الخاصة بتتبع طلبات لوحة الإدارة (admin_*) تبقى مسجلة
        // لكنها لا تُعرض نهائياً في الواجهة حتى لا تربك العميل.
        $includeAdminRequestEvents = false;

        // إذا ما عنده غير إذن view-activity-log بدون أي إذن عرض ثاني،
        // راح نخلّي النتائج فاضية (إلا إذا سمحنا login/logout).
        $hasAnyModelPermission = !empty($allowedBasenames);

        // ===== 2) ابدأ بناء الاستعلام =====
        $query = ActivityLog::with([
            'user:id,name,phone_number',
            'loggable'
        ]);

                // أحداث الخلفية (admin_*) تبقى محفوظة لكن مخفية نهائياً من واجهة السجل.
                $query->where(function ($q) {
                        $q->whereNull('action')
                            ->orWhere('action', 'not like', 'admin_%');
                });

        // افتراضيًا: لا تعرض سجلات المستخدمين أصحاب دور user (العادي)
        // إلا إذا انطلب من الفلاتر show_users_role_user=1
        if (!$request->boolean('show_users_role_user')) {
            $query->where(function ($q) {
                $q->whereNull('user_id')
                  // إذا كان المدير موجوداً وليس لديه دور user فقط
                  ->orWhereHas('user.roles', function ($r) {
                      $r->where('name', '!=', 'user');
                  })
                  // أو إذا كان المدير محذوفاً (لا يوجد في الجدول) — نبقي سجلاته مرئية
                  ->orWhereDoesntHave('user');
            });
        }

        // فلترة بحسب صلاحيات العرض: إمّا موديلات مسموح بها، أو أحداث login/logout (حسب الإذن)
        $query->where(function ($q) use ($allowedBasenames, $includeAuthEvents, $includeAdminRequestEvents, $hasAnyModelPermission) {
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

            if ($includeAdminRequestEvents) {
                $q->orWhere('action', 'like', 'admin_%');
            }

            // لو ما عنده أي صلاحية موديلات ولا مسموح أحداث الدخول/الخروج، فرّغ النتائج
            if (!$hasAnyModelPermission && !$includeAuthEvents && !$includeAdminRequestEvents) {
                $q->whereRaw('1=0');
            }
        });

        // ===== 3) تطبيق فلاتر البحث =====
        if ($request->filled('q')) {
            $term = '%' . trim($request->q) . '%';
            $query->where(function ($x) use ($term) {
                $x->where('id', 'like', $term)
                  ->orWhere('loggable_id', 'like', $term)
                  ->orWhere('ip_address', 'like', $term)
                  ->orWhere('user_agent', 'like', $term)
                  ->orWhere('action', 'like', $term)
                  ->orWhere('loggable_type', 'like', $term)
                  ->orWhere('before', 'like', $term)
                  ->orWhere('after', 'like', $term)
                  ->orWhereHas('user', function ($u) use ($term) {
                      $u->where('name', 'like', $term)
                        ->orWhere('phone_number', 'like', $term);
                  })
                  ->orWhereHasMorph('loggable', [
                      \App\Models\Product::class,
                      \App\Models\Category::class,
                      \App\Models\DiscountCode::class,
                      \App\Models\Manager::class,
                      \App\Models\Order::class,
                      \App\Models\User::class,
                      \App\Models\Supplier::class,
                      \App\Models\Customer::class,
                  ], function ($m) use ($term) {
                      $m->where(function ($z) use ($term, $m) {
                          $type = get_class($m->getModel());

                          $z->where('id', 'like', $term);

                                                    if ($type === \App\Models\Product::class) {
                                                            $z->orWhere('name_ar', 'like', $term)
                                                                ->orWhere('name_en', 'like', $term)
                                                                ->orWhere('sku', 'like', $term);
                                                    }

                                                    if ($type === \App\Models\Category::class) {
                                                            $z->orWhere('name_ar', 'like', $term)
                                                                ->orWhere('name_en', 'like', $term)
                                                                ->orWhere('slug', 'like', $term);
                                                    }

                                                    if (in_array($type, [\App\Models\Supplier::class, \App\Models\Customer::class], true)) {
                                                            $z->orWhere('name', 'like', $term)
                                                                ->orWhere('phone_number', 'like', $term)
                                                                ->orWhere('email', 'like', $term);
                          }

                          if (in_array($type, [\App\Models\Manager::class, \App\Models\User::class], true)) {
                              $z->orWhere('name', 'like', $term)
                                ->orWhere('phone_number', 'like', $term)
                                ->orWhere('email', 'like', $term);
                          }

                          if ($type === \App\Models\DiscountCode::class) {
                                                            $z->orWhere('code', 'like', $term)
                                                                ->orWhere('type', 'like', $term);
                                                    }

                                                    if ($type === \App\Models\Order::class) {
                                                            $z->orWhere('status', 'like', $term)
                                                                ->orWhere('city', 'like', $term)
                                                                ->orWhere('governorate', 'like', $term);
                          }
                      });
                  });
            });
        }

        if ($request->filled('action')) {
            $selectedAction = trim((string) $request->action);

            switch ($selectedAction) {
                case 'create':
                    $query->where('action', 'created');
                    break;

                case 'update':
                    $query->where('action', 'updated');
                    break;

                case 'delete':
                    $query->where('action', 'deleted');
                    break;

                case 'ban':
                    $query->where('action', 'updated')
                          ->where('after', 'like', '%"banned_at"%')
                          ->where('after', 'not like', '%"new":null%');
                    break;

                case 'unban':
                    $query->where('action', 'updated')
                          ->where('after', 'like', '%"banned_at"%')
                          ->where('after', 'like', '%"new":null%');
                    break;

                case 'activate':
                    $query->where('action', 'updated')
                          ->where(function ($q) {
                              $q->where(function ($qq) {
                                    $qq->where('after', 'like', '%"is_active"%')
                                       ->where(function ($q2) {
                                           $q2->where('after', 'like', '%"new":true%')
                                              ->orWhere('after', 'like', '%"new":1%');
                                       });
                                })
                                ->orWhere(function ($qq) {
                                    $qq->where('after', 'like', '%"status"%')
                                       ->where(function ($q2) {
                                           $q2->where('after', 'like', '%"new":"active"%')
                                              ->orWhere('after', 'like', '%"new":"enabled"%');
                                       });
                                });
                          });
                    break;

                case 'deactivate':
                    $query->where('action', 'updated')
                          ->where(function ($q) {
                              $q->where(function ($qq) {
                                    $qq->where('after', 'like', '%"is_active"%')
                                       ->where(function ($q2) {
                                           $q2->where('after', 'like', '%"new":false%')
                                              ->orWhere('after', 'like', '%"new":0%');
                                       });
                                })
                                ->orWhere(function ($qq) {
                                    $qq->where('after', 'like', '%"status"%')
                                       ->where(function ($q2) {
                                           $q2->where('after', 'like', '%"new":"inactive"%')
                                              ->orWhere('after', 'like', '%"new":"disabled"%')
                                              ->orWhere('after', 'like', '%"new":"blocked"%')
                                              ->orWhere('after', 'like', '%"new":"banned"%');
                                       });
                                });
                          });
                    break;

                case 'login':
                case 'logout':
                case 'failed_login':
                    $query->where('action', $selectedAction);
                    break;

                default:
                    $query->where('action', $selectedAction);
                    break;
            }
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
        $users = Manager::select('id','name','phone_number')
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
