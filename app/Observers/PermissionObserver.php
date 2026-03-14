<?php

namespace App\Observers;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionObserver
{
    public function created(Permission $permission): void
    {
        $this->syncSuperAdminPermissions();
    }

    public function deleted(Permission $permission): void
    {
        $this->syncSuperAdminPermissions();
    }

    protected function syncSuperAdminPermissions(): void
    {
        try {
            $superRole = Role::findByName('Super-Admin', 'admin');

            if ($superRole) {
                $superRole->syncPermissions(Permission::all());
            }
        } catch (\Throwable $e) {
            // يتم التجاهل في حال كانت الجداول غير متوفرة بعد أو أثناء الترحيلات
        }
    }
}
