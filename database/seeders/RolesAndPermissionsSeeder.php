<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // قائمة شاملة بكل الصلاحيات في النظام (بعد الحذف والترتيب)
        $permissions = [
            // --- نظام عام ---
            'view-admin-panel', 
            'view-activity-log', 
            'edit-settings',
            'manage-backups', 
            'manage-imports',
            'manage-barcodes',

            // --- المنتجات والأقسام ---
            'view-products', 
            'create-products', 
            'edit-products', 
            'delete-products',
            'view-categories', 
            'create-categories', 
            'edit-categories', 
            'delete-categories',
            
            // --- الطلبات والمبيعات ---
            'view-orders', 
            'create-orders', 
            'edit-orders', 
            'delete-orders',
            'view-trashed-orders', 
            'restore-orders', 
            'force-delete-orders',

            // --- المستخدمين والمدراء ---
            'view-users', 
            'create-users', 
            'edit-users', 
            'delete-users', 
            'ban-users',
            'impersonate-users', 
            'logout-users',
            'view-roles', 
            'create-roles', 
            'edit-roles', 
            'delete-roles',

            // --- العملاء ---
            'view-customers', 
            'create-customers', 
            'edit-customers', 
            'delete-customers', 
            'ban-customers',
            'manage-wallet', 
            'manage-customer-tiers',

            // --- المدونة والمراجعات ---
            'view-blog', 
            'create-blog', 
            'edit-blog', 
            'delete-blog',
            'manage-reviews',

            // --- كود الخصم ---
            'view-discount-codes', 
            'create-discount-codes', 
            'edit-discount-codes', 
            'delete-discount-codes',

            // --- التقارير ---
            'view-reports', 
            'view-reports-customers',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'admin']);
        }

        $adminPermissions = Permission::query()
            ->where('guard_name', 'admin')
            ->get();

        // المدير العام (Super-Admin) - يمتلك كل الصلاحيات
        Role::firstOrCreate(['name' => 'Super-Admin', 'guard_name' => 'admin'])
            ->syncPermissions($adminPermissions);

        // مدير الطلبات (Order-Manager)
        Role::firstOrCreate(['name' => 'Order-Manager', 'guard_name' => 'admin'])
            ->syncPermissions([
                'view-admin-panel', 'view-orders', 'create-orders', 'edit-orders',
                'view-customers', 'view-reports',
            ]);

        // كاتب المحتوى (Content-Creator)
        Role::firstOrCreate(['name' => 'Content-Creator', 'guard_name' => 'admin'])
            ->syncPermissions([
                'view-admin-panel', 'view-products', 'create-products', 'edit-products',
                'view-categories', 'create-categories', 'edit-categories', 'view-blog',
                'create-blog', 'edit-blog',
            ]);

        // المستخدم العادي (user)
        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web'])->syncPermissions([]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}