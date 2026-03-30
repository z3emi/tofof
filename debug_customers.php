<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Customer;
use App\Models\User;

echo "Total Customers: " . Customer::count() . "\n";
echo "Total Users: " . User::count() . "\n";
echo "Soft Deleted Users: " . User::onlyTrashed()->count() . "\n";
echo "Customers with User: " . Customer::whereHas('user')->count() . "\n";
echo "Customers without User: " . Customer::whereDoesntHave('user')->count() . "\n";
if (Customer::count() > 0) {
    echo "\nFirst 5 Customers:\n";
    foreach (Customer::take(5)->get() as $c) {
        echo "ID: {$c->id}, Name: {$c->name}, UserID: {$c->user_id}\n";
    }
}
