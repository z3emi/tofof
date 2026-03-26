<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    $table = 'addresses';
    $column = 'id';
    
    echo "Checking table $table...\n";
    if (!Schema::hasTable($table)) {
        die("Table $table does not exist.\n");
    }
    
    // We already know it's missing auto_increment from the error.
    echo "Updating $table.$column to AUTO_INCREMENT...\n";
    DB::statement("ALTER TABLE `$table` MODIFY `$column` bigint(20) unsigned NOT NULL AUTO_INCREMENT");
    echo "Successfully updated!\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
