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

        // قائمة شاملة بكل الصلاحيات في النظام
        $permissions = [
            'view-admin-panel', 'view-activity-log', 'edit-settings',
            
            // Products
            'view-products', 'create-products', 'edit-products', 'delete-products',
            
            // Categories
            'view-categories', 'create-categories', 'edit-categories', 'delete-categories',
            
            // Orders
            'view-orders', 'create-orders', 'edit-orders', 'delete-orders',
            'view-trashed-orders', 'restore-orders', 'force-delete-orders',

            // Users & Roles
            'view-users', 'create-users', 'edit-users', 'delete-users', 'ban-users',
            'impersonate-users', 'logout-users',
            'view-roles', 'create-roles', 'edit-roles', 'delete-roles',

            // Customers
            'view-customers', 'create-customers', 'edit-customers', 'delete-customers', 'ban-customers',
            'manage-wallet', // صلاحية لإدارة محفظة العميل

            // Suppliers & Purchases
            'view-suppliers', 'create-suppliers', 'edit-suppliers', 'delete-suppliers',
            'view-purchases', 'create-purchases', 'edit-purchases', 'delete-purchases',

            // Financial & Inventory
            'view-expenses', 'create-expenses', 'edit-expenses', 'delete-expenses',
            'view-inventory',

            // Discount Codes
            'view-discount-codes', 'create-discount-codes', 'edit-discount-codes', 'delete-discount-codes',
            

            // Reports
            'view-reports', 'view-reports-financial', 'view-reports-inventory', 'view-reports-customers',

            // Backups & Imports
            'manage-backups', 'manage-imports',

            // Blog
            'view-blog', 'create-blog', 'edit-blog', 'delete-blog',
            
            // Barcodes
            'manage-barcodes',
            
            // Customer Tiers
            'manage-customer-tiers',
            
            // Reviews
            'manage-reviews'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'admin']);
        }

        $adminPermissions = Permission::query()
            ->where('guard_name', 'admin')
            ->get();

        // المدير العام (Super-Admin)
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