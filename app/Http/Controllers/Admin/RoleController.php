<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    /**
     * Apply permission middleware to protect controller actions.
     */
    public function __construct()
    {
        $permissionMiddleware = \Spatie\Permission\Middleware\PermissionMiddleware::class;

        $this->middleware($permissionMiddleware . ':view-roles', ['only' => ['index']]);
        $this->middleware($permissionMiddleware . ':create-roles', ['only' => ['create', 'store']]);
        $this->middleware($permissionMiddleware . ':edit-roles', ['only' => ['edit', 'update']]);
        $this->middleware($permissionMiddleware . ':delete-roles', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of all roles.
     */
    public function index()
    {
        // عرض الأدوار الخاصة بلوحة التحكم فقط (admin guard) لتجنب التكرار
        $roles = Role::where('guard_name', 'admin')->paginate(10);
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        // جلب الصلاحيات الخاصة بنظام الإدارة فقط
        $permissions = Permission::where('guard_name', 'admin')->get();
        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'admin' // إنشاء أدوار لوحة التحكم دائماً بـ guard: admin
        ]);
        
        // جلب أسماء الصلاحيات من الـ IDs الخاصة بـ guard: admin حصراً
        $permissionIds = $request->input('permissions', []);
        $permissions = Permission::whereIn('id', $permissionIds)
            ->where('guard_name', 'admin')
            ->pluck('name')
            ->toArray();
            
        $role->syncPermissions($permissions);

        return redirect()->route('admin.roles.index')->with('success', 'تم إنشاء الدور بنجاح.');
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        if ($role->name === 'Super-Admin' && $role->guard_name === 'admin') {
            return redirect()->route('admin.roles.index')->with('error', 'لا يمكن تعديل صلاحيات المدير العام.');
        }
        
        // جلب الصلاحيات المتوافقة مع الـ Guard الخاص بهذا الدور
        $permissions = Permission::where('guard_name', $role->guard_name)->get();
        
        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, Role $role)
    {
        if ($role->name === 'Super-Admin' && $role->guard_name === 'admin') {
            return redirect()->route('admin.roles.index')->with('error', 'لا يمكن تعديل صلاحيات المدير العام.');
        }

        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        $role->update(['name' => $request->name]);

        // جلب أسماء الصلاحيات المتوافق مع الـ guard الخاص بالدور
        $permissionIds = $request->input('permissions', []);
        $permissions = Permission::whereIn('id', $permissionIds)
            ->where('guard_name', $role->guard_name)
            ->pluck('name')
            ->toArray();
        
        $role->syncPermissions($permissions);

        return redirect()->route('admin.roles.index')->with('success', 'تم تحديث الدور بنجاح.');
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role)
    {
        // Prevent deleting the Super-Admin role
        if ($role->name === 'Super-Admin') {
            return redirect()->route('admin.roles.index')->with('error', 'لا يمكن حذف دور المدير العام.');
        }
        
        $role->delete();
        return redirect()->route('admin.roles.index')->with('success', 'تم حذف الدور بنجاح.');
    }
}
