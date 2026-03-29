<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Manager;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use App\Support\Sort;

class ManagerController extends Controller
{
    public function __construct()
    {
        $permissionMiddleware = \Spatie\Permission\Middleware\PermissionMiddleware::class;

        $this->middleware($permissionMiddleware . ':view-managers|view-managers-all', ['only' => ['index']]);
        $this->middleware($permissionMiddleware . ':create-managers', ['only' => ['create', 'store']]);
        $this->middleware($permissionMiddleware . ':edit-managers', ['only' => ['edit', 'update']]);
        $this->middleware($permissionMiddleware . ':delete-managers', ['only' => ['destroy']]);
        $this->middleware($permissionMiddleware . ':ban-managers', ['only' => ['ban', 'unban']]);
        $this->middleware($permissionMiddleware . ':logout-managers', ['only' => ['forceLogout', 'forceLogoutAll']]);
        $this->middleware($permissionMiddleware . ':impersonate-managers', ['only' => ['impersonate']]);
        $this->middleware($permissionMiddleware . ':view-trashed-managers', ['only' => ['trash']]);
        $this->middleware($permissionMiddleware . ':restore-managers', ['only' => ['restore']]);
        $this->middleware($permissionMiddleware . ':force-delete-managers', ['only' => ['forceDelete']]);
    }

    public function index(Request $request)
    {
        $currentManager = $request->user();
        $protectedPhone = (string) config('admin.super_admin_phone', 'admin');
        $canViewContactDetails = $currentManager?->can('view-manager-contact') ?? false;

        $query = Manager::query()
            ->with(['roles', 'manager'])
            ->withCount('teamMembers');

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('phone_number', 'like', "%{$searchTerm}%");
            });
        }

        $canViewAllStaff = $currentManager->isSuperAdmin()
            || $currentManager->can('view-managers-all');

        if (!$canViewAllStaff) {
            $visibleIds = $currentManager->teamMemberIds();
            if (!in_array($currentManager->id, $visibleIds, true)) {
                $visibleIds[] = $currentManager->id;
            }
            $query->whereIn('id', $visibleIds);
            $query->where('phone_number', '!=', $protectedPhone);
        } elseif (!$currentManager->isSuperAdmin()) {
            $query->where('phone_number', '!=', $protectedPhone);
        }

        if ($request->filled('manager_id')) {
            if ($request->manager_id === 'top') {
                $query->whereNull('manager_id');
            } else {
                $query->where('manager_id', (int) $request->manager_id);
            }
        }

        if ($request->filled('status')) {
            if ($request->status === 'banned') {
                $query->whereNotNull('banned_at');
            } elseif ($request->status === 'active') {
                $query->whereNull('banned_at')->whereNotNull('phone_verified_at');
            } elseif ($request->status === 'inactive') {
                $query->whereNull('phone_verified_at');
            }
        }

        $allowedSorts = ['id', 'name', 'phone_number', 'team_members_count', 'created_at'];
        [$sortBy, $sortDir] = Sort::resolve($request, $allowedSorts, 'id');

        $query->orderBy($sortBy, $sortDir);

        $perPage = (int) $request->input('per_page', 10);
        if ($perPage < 5) {
            $perPage = 5;
        } elseif ($perPage > 500) {
            $perPage = 500;
        }

        $managers = $query->paginate($perPage)->withQueryString();

        $managerQuery = Manager::query()
            ->where('phone_number', '!=', $protectedPhone)
            ->orderBy('name');
        if (!$canViewAllStaff) {
            $managerQuery->whereIn('id', $currentManager->teamMemberIds());
        }
        $availableManagers = $managerQuery->pluck('name', 'id');

        $hierarchyQuery = Manager::query()
            ->select(['id', 'name', 'email', 'phone_number', 'manager_id', 'banned_at', 'phone_verified_at'])
            ->with(['roles:id,name,guard_name'])
            ->withCount('teamMembers')
            ->orderBy('name');

        if (!$canViewAllStaff) {
            $hierarchyQuery->whereIn('id', $currentManager->teamMemberIds());
            $hierarchyQuery->where('phone_number', '!=', $protectedPhone);
        } elseif (!$currentManager->isSuperAdmin()) {
            $hierarchyQuery->where('phone_number', '!=', $protectedPhone);
        }

        $managerHierarchy = $this->buildManagerHierarchy($hierarchyQuery->get());

        return view('admin.managers.index', [
            'managers' => $managers,
            'allowedSorts' => $allowedSorts,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
            'perPage' => $perPage,
            'availableManagers' => $availableManagers,
            'canViewContactDetails' => $canViewContactDetails,
            'managerHierarchy' => $managerHierarchy,
        ]);
    }

    protected function buildManagerHierarchy(Collection $managers): array
    {
        if ($managers->isEmpty()) {
            return [];
        }

        $grouped = $managers->groupBy('manager_id');

        $buildBranch = function ($parentId) use (&$buildBranch, $grouped) {
            return $grouped->get($parentId, collect())->map(function (Manager $manager) use (&$buildBranch) {
                return [
                    'id' => $manager->id,
                    'name' => $manager->name,
                    'manager_id' => $manager->manager_id,
                    'email' => $manager->email,
                    'status' => $manager->banned_at
                        ? 'banned'
                        : (is_null($manager->phone_verified_at) ? 'inactive' : 'active'),
                    'roles' => $manager->roles->pluck('name')->all(),
                    'team_members_count' => (int) ($manager->team_members_count ?? 0),
                    'children' => $buildBranch($manager->id),
                ];
            })->values()->all();
        };

        return $buildBranch(null);
    }

    public function create()
    {
        $roles = $this->assignableRoles();
        $protectedPhone = (string) config('admin.super_admin_phone', 'admin');
        $managers = Manager::query()
            ->where('phone_number', '!=', $protectedPhone)
            ->orderBy('name')
            ->get();
        $governorates = $this->iraqiGovernorates();

        return view('admin.managers.create', compact('roles', 'managers', 'governorates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:managers,name',
            'email' => 'nullable|string|email|max:255|unique:managers,email',
            'phone_number' => 'required|string|max:20|unique:managers,phone_number',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', 'string', Rule::exists('roles', 'name')->where('guard_name', 'admin')],
            'manager_id' => ['nullable', Rule::exists('managers', 'id')],
            'governorates' => ['nullable', 'array'],
            'governorates.*' => ['string', Rule::in($this->iraqiGovernorates())],
        ], [
            'name.unique' => 'اسم اليوزر مستخدم بالفعل لمدير آخر.',
        ]);

        $roleName = $validated['role'];
        $availableRoleNames = $this->assignableRoles()->pluck('name')->all();

        if (!in_array($roleName, $availableRoleNames, true)) {
            return back()->withInput()->withErrors(['role' => 'الدور المحدد غير متاح لك.']);
        }

        $manager = DB::transaction(function () use ($validated, $roleName, $request) {
            $manager = Manager::create([
                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'phone_number' => $validated['phone_number'],
                'password' => Hash::make($validated['password']),
                'manager_id' => $validated['manager_id'] ?? null,
                'phone_verified_at' => now(),
            ]);

            $manager->syncRoles([$roleName]);
            $manager->syncGovernorates((array) ($validated['governorates'] ?? []));

            return $manager;
        });

        return redirect()->route('admin.managers.index')->with('success', 'تم إنشاء المدير بنجاح.');
    }

    public function edit(Manager $manager)
    {
        $currentManager = Auth::guard('admin')->user();
        if ($manager->isProtectedSuperAdmin() && $currentManager?->id !== $manager->id) {
            abort(403, 'لا يمكنك تعديل حساب السوبر أدمن.');
        }

        if ($manager->isSuperAdmin() && (!$currentManager || !$currentManager->isSuperAdmin())) {
            abort(403, 'لا يمكنك تعديل حساب السوبر أدمن.');
        }

        $manager->load('governorateAssignments');

        $fieldPermissions = [
            'name' => $currentManager?->can('edit-manager-name') ?? false,
            'email' => $currentManager?->can('edit-manager-email') ?? false,
            'phone' => $currentManager?->can('edit-manager-phone') ?? false,
            'password' => $currentManager?->can('edit-manager-password') ?? false,
            'status' => $currentManager?->can('edit-manager-status') ?? false,
            'contact' => $currentManager?->can('view-manager-contact') ?? false,
        ];

        $roles = $this->assignableRoles();
        $protectedPhone = (string) config('admin.super_admin_phone', 'admin');
        $managers = Manager::query()
            ->where('id', '!=', $manager->id)
            ->where('phone_number', '!=', $protectedPhone)
            ->orderBy('name')
            ->get();
        $governorates = $this->iraqiGovernorates();
        $selectedGovernorates = $manager->assignedGovernorates();

        return view('admin.managers.edit', [
            'manager' => $manager,
            'roles' => $roles,
            'managers' => $managers,
            'governorates' => $governorates,
            'selectedGovernorates' => $selectedGovernorates,
            'fieldPermissions' => $fieldPermissions,
        ]);
    }

    public function update(Request $request, Manager $manager)
    {
        $currentManager = Auth::guard('admin')->user();
        if ($manager->isProtectedSuperAdmin() && $currentManager?->id !== $manager->id) {
            abort(403, 'لا يمكنك تعديل حساب السوبر أدمن.');
        }

        if ($manager->isProtectedSuperAdmin()) {
            $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ], [
                'password.required' => 'يجب إدخال كلمة المرور الجديدة.',
            ]);

            $manager->update([
                'password' => Hash::make($request->password),
            ]);

            return back()->with('success', 'تم تحديث كلمة مرور السوبر أدمن بنجاح.');
        }

        if ($manager->isSuperAdmin() && (!$currentManager || !$currentManager->isSuperAdmin())) {
            abort(403, 'لا يمكنك تعديل حساب السوبر أدمن.');
        }

        $canEditName = $currentManager?->can('edit-manager-name') ?? false;
        $canEditEmail = $currentManager?->can('edit-manager-email') ?? false;
        $canEditPhone = $currentManager?->can('edit-manager-phone') ?? false;
        $canEditPassword = $currentManager?->can('edit-manager-password') ?? false;
        $canEditStatus = $currentManager?->can('edit-manager-status') ?? false;
        $canViewContact = $currentManager?->can('view-manager-contact') ?? false;

        $rules = [
            'name'         => ['required','string','max:255',"unique:managers,name,{$manager->id}"],
            'email'        => ($canEditEmail || $canViewContact)
                ? [
                    'nullable',
                    'string',
                    'email',
                    'max:255',
                    Rule::unique('managers', 'email')->ignore($manager->id),
                ]
                : [
                    'sometimes',
                    'nullable',
                    'string',
                    'email',
                    'max:255',
                    Rule::unique('managers', 'email')->ignore($manager->id),
                ],
            'phone_number' => ($canEditPhone || $canViewContact)
                ? [
                    'required',
                    'string',
                    'max:20',
                    Rule::unique('managers', 'phone_number')->ignore($manager->id),
                ]
                : [
                    'sometimes',
                    'string',
                    'max:20',
                    Rule::unique('managers', 'phone_number')->ignore($manager->id),
                ],
            'password'     => 'nullable|string|min:8|confirmed',
            'role'         => ['required', 'string', Rule::exists('roles', 'name')->where('guard_name', 'admin')],
            'avatar'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'reset_avatar' => 'nullable|boolean',
            'manager_id'   => ['nullable', Rule::exists('managers', 'id')],
            'governorates' => ['nullable', 'array'],
            'governorates.*' => ['string', Rule::in($this->iraqiGovernorates())],
            'is_active'    => ['nullable', 'boolean'],
        ];

        $validated = $request->validate($rules, [
            'name.unique' => 'اسم اليوزر مستخدم بالفعل لمدير آخر.',
        ]);

        if (!$canEditName && $request->name !== $manager->name) {
            return back()->withInput()->withErrors(['name' => 'ليس لديك صلاحية لتعديل اسم المدير.']);
        }

        $incomingEmail = $request->has('email')
            ? $request->input('email')
            : $manager->email;
        if (!$canEditEmail && (string) $incomingEmail !== (string) ($manager->email ?? '')) {
            return back()->withInput()->withErrors(['email' => 'ليس لديك صلاحية لتعديل البريد الإلكتروني لهذا المدير.']);
        }

        $incomingPhone = $request->has('phone_number')
            ? $request->input('phone_number')
            : $manager->phone_number;
        if (!$canEditPhone && (string) $incomingPhone !== (string) $manager->phone_number) {
            return back()->withInput()->withErrors(['phone_number' => 'ليس لديك صلاحية لتعديل رقم هاتف المدير.']);
        }

        $currentStatus = !is_null($manager->phone_verified_at);
        $desiredStatus = $request->has('is_active')
            ? $request->boolean('is_active')
            : $currentStatus;
        if (!$canEditStatus && $desiredStatus !== $currentStatus) {
            return back()->withInput()->withErrors(['is_active' => 'ليس لديك صلاحية لتغيير حالة التفعيل لهذا المدير.']);
        }

        if ($request->filled('password') && !$canEditPassword) {
            return back()->withInput()->withErrors(['password' => 'ليس لديك صلاحية لتعديل كلمة مرور هذا المدير.']);
        }

        $data = [];
        if ($canEditName) {
            $data['name'] = $request->name;
        }
        if ($canEditEmail && $request->has('email')) {
            $data['email'] = $incomingEmail;
        }
        if ($canEditPhone && $request->has('phone_number')) {
            $data['phone_number'] = $incomingPhone;
        }
        $data['manager_id'] = $validated['manager_id'] ?? null;

        if ($request->filled('manager_id') && (int) $validated['manager_id'] === $manager->id) {
            return redirect()->back()->withErrors(['manager_id' => 'لا يمكن تعيين المستخدم كمشرف على نفسه.']);
        }

        $teamMemberIds = array_diff($manager->teamMemberIds(), [$manager->id]);
        if ($request->filled('manager_id') && in_array((int) $validated['manager_id'], $teamMemberIds, true)) {
            return redirect()->back()->withErrors(['manager_id' => 'لا يمكن تعيين أحد أعضاء الفريق الحالي كمشرف.']);
        }

        if ($request->filled('password') && $canEditPassword) {
            $data['password'] = Hash::make($validated['password']);
        }

        if ($request->boolean('reset_avatar')) {
            $data['avatar'] = null;
        } elseif ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $path;
        }

        if ($canEditStatus && $request->has('is_active')) {
            $data['phone_verified_at'] = $request->boolean('is_active')
                ? ($manager->phone_verified_at ?? now())
                : null;
        }

        $roleName = $validated['role'];
        $availableRoleNames = $this->assignableRoles()->pluck('name')->all();

        if (!in_array($roleName, $availableRoleNames, true)) {
            return back()->withInput()->withErrors(['role' => 'الدور المحدد غير متاح لك.']);
        }

        DB::transaction(function () use ($manager, $data, $roleName, $validated) {
            $manager->update($data);

            $manager->syncRoles([$roleName]);
            $manager->syncGovernorates((array) ($validated['governorates'] ?? []));
        });

        return redirect()->route('admin.managers.index')->with('success', 'تم تحديث بيانات المدير بنجاح.');
    }

    public function showOrders(Manager $manager)
    {
        $currentManager = Auth::guard('admin')->user();

        if ($currentManager && !$currentManager->can('view-all-orders')) {
            $visibleUserIds = $currentManager->accessibleOrderUserIds();
            if (!in_array($manager->id, $visibleUserIds, true)) {
                abort(403, 'لا تملك صلاحية عرض فواتير هذا المدير.');
            }
        }

        $orders = Order::where('user_id', $manager->id)->latest()->paginate(15);

        return view('admin.managers.orders', ['manager' => $manager, 'orders' => $orders]);
    }

    public function ban(Manager $manager)
    {
        if ($manager->isProtectedSuperAdmin()) {
            return redirect()->back()->with('error', 'لا يمكن حظر حساب السوبر أدمن.');
        }

        if ($manager->id === Auth::guard('admin')->id()) {
            return redirect()->back()->with('error', 'لا يمكنك حظر حسابك الخاص.');
        }

        $manager->update(['banned_at' => Carbon::now()]);

        return redirect()->route('admin.managers.index')->with('success', 'تم حظر المدير بنجاح.');
    }

    public function unban(Manager $manager)
    {
        if ($manager->isProtectedSuperAdmin()) {
            return redirect()->back()->with('error', 'لا يمكن تعديل حالة السوبر أدمن.');
        }

        $manager->update(['banned_at' => null]);

        return redirect()->route('admin.managers.index')->with('success', 'تم إلغاء حظر المدير بنجاح.');
    }

    public function destroy(Manager $manager)
    {
        if ($manager->isProtectedSuperAdmin()) {
            return redirect()->back()->with('error', 'لا يمكن حذف حساب السوبر أدمن.');
        }

        if ($manager->id === Auth::guard('admin')->id()) {
            return redirect()->back()->with('error', 'لا يمكنك حذف حسابك الخاص.');
        }

        $manager->delete();

        $this->deleteManagerSessions($manager->id);

        return redirect()->route('admin.managers.index')->with('success', 'تم حذف المدير بنجاح.');
    }

    public function trash(Request $request)
    {
        $currentManager = Auth::guard('admin')->user();
        $canViewContactDetails = $currentManager?->can('view-manager-contact') ?? false;

        $query = Manager::onlyTrashed()
            ->with(['roles', 'manager']);

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('phone_number', 'like', "%{$searchTerm}%");
            });
        }

        $allowedSorts = ['id', 'name', 'phone_number', 'deleted_at'];
        [$sortBy, $sortDir] = Sort::resolve($request, $allowedSorts, 'deleted_at');

        $query->orderBy($sortBy, $sortDir);

        $perPage = (int) $request->input('per_page', 10);
        if ($perPage < 5) {
            $perPage = 5;
        } elseif ($perPage > 100) {
            $perPage = 100;
        }

        $managers = $query->paginate($perPage)->withQueryString();

        return view('admin.managers.trash', [
            'managers' => $managers,
            'allowedSorts' => $allowedSorts,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
            'perPage' => $perPage,
            'canViewContactDetails' => $canViewContactDetails,
        ]);
    }

    public function restore(int $managerId)
    {
        $manager = Manager::onlyTrashed()->findOrFail($managerId);

        $manager->restore();

        return redirect()->route('admin.managers.trash')->with('success', 'تم استعادة حساب المدير بنجاح.');
    }

    public function forceDelete(int $managerId)
    {
        $manager = Manager::onlyTrashed()->findOrFail($managerId);

        $this->deleteManagerSessions($manager->id);

        DB::transaction(function () use ($manager) {
            DB::table('model_has_roles')
                ->where('model_type', Manager::class)
                ->where('model_id', $manager->id)
                ->delete();

            DB::table('model_has_permissions')
                ->where('model_type', Manager::class)
                ->where('model_id', $manager->id)
                ->delete();

            $manager->forceDelete();
        });

        return redirect()->route('admin.managers.trash')->with('success', 'تم حذف المدير نهائياً.');
    }

    public function forceLogout(Manager $manager)
    {
        if ($manager->isProtectedSuperAdmin()) {
            return back()->with('error', 'لا يمكن تسجيل خروج حساب السوبر أدمن من هنا.');
        }

        if ($manager->id === Auth::guard('admin')->id()) {
            return back()->with('error', 'لا يمكنك تسجيل خروج حسابك الحالي من هنا.');
        }

        $this->deleteManagerSessions($manager->id);

        return redirect()->route('admin.managers.index')->with('success', 'تم تسجيل خروج المدير بنجاح.');
    }

    public function forceLogoutAll()
    {
        $protectedPhone = (string) config('admin.super_admin_phone', 'admin');
        $managerIds = Manager::query()
            ->where('phone_number', '!=', $protectedPhone)
            ->pluck('id');

        foreach ($managerIds as $managerId) {
            $this->deleteManagerSessions($managerId);
        }

        return back()->with('success', 'تم تسجيل خروج جميع المدراء.');
    }

    public function impersonate(Manager $manager)
    {
        if ($manager->isProtectedSuperAdmin()) {
            return back()->with('error', 'لا يمكن انتحال حساب السوبر أدمن.');
        }

        session(['impersonator_id' => Auth::guard('admin')->id(), 'impersonator_guard' => 'admin']);
        Auth::guard('admin')->login($manager);

        return redirect('/')->with('success', 'تم تسجيل الدخول كمستخدم آخر.');
    }

    public function stopImpersonate()
    {
        $id = session('impersonator_id');

        if ($id) {
            Auth::guard('admin')->loginUsingId($id);
            session()->forget(['impersonator_id', 'impersonator_guard']);
        }

        return redirect()->route('admin.managers.index')->with('success', 'تم إيقاف وضع الانتحال.');
    }

    protected function iraqiGovernorates(): array
    {
        return [
            'بغداد', 'نينوى', 'البصرة', 'صلاح الدين', 'دهوك', 'أربيل', 'السليمانية', 'ديالى',
            'واسط', 'ميسان', 'ذي قار', 'المثنى', 'بابل', 'كربلاء', 'النجف', 'الانبار',
            'الديوانية', 'كركوك', 'حلبجة',
        ];
    }

    protected function assignableRoles()
    {
        $query = Role::query()->where('guard_name', 'admin')->orderBy('name');

        if (!Auth::guard('admin')->user()?->isSuperAdmin()) {
            $query->where('name', '!=', 'Super-Admin');
        }

        return $query->withCount('permissions')->get();
    }

    protected function deleteManagerSessions(int $managerId): void
    {
        DB::table('sessions')->where('user_id', $managerId)->get()->each(function ($session) {
            $decoded = @unserialize(base64_decode($session->payload));
            $guard = is_array($decoded) ? ($decoded['_auth_guard'] ?? null) : null;

            if ($guard === 'admin') {
                DB::table('sessions')->where('id', $session->id)->delete();
            }
        });
    }
}
