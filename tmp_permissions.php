<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

$permissions = [
    'view-trashed-managers',
    'restore-managers',
    'force-delete-managers',
    'delete-managers'
];

foreach ($permissions as $p) {
    if (!Permission::where('name', $p)->where('guard_name', 'admin')->exists()) {
        Permission::create(['name' => $p, 'guard_name' => 'admin']);
        echo "Created: $p\n";
    } else {
        echo "Exists: $p\n";
    }
}

$superAdmin = Role::where('name', 'Super-Admin')->where('guard_name', 'admin')->first();
if ($superAdmin) {
    $superAdmin->givePermissionTo($permissions);
    echo "Assigned to Super-Admin\n";
} else {
    echo "Super-Admin role not found\n";
}

echo "Done.\n";
