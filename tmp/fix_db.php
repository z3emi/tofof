<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    $table = 'wishlists';
    $column = 'id';
    
    echo "Checking table $table...\n";
    if (!Schema::hasTable($table)) {
        echo "Table $table does not exist.\n";
        exit;
    }
    
    if (!Schema::hasColumn($table, $column)) {
        echo "Column $column does not exist in $table.\n";
        exit;
    }

    $columnDefinition = collect(DB::select("SHOW COLUMNS FROM `$table` WHERE Field = ?", [$column]))->first();
    $extra = strtolower($columnDefinition->Extra ?? '');

    if (!str_contains($extra, 'auto_increment')) {
        echo "Updating $table.$column to AUTO_INCREMENT...\n";
        DB::statement("ALTER TABLE `$table` MODIFY `$column` bigint(20) unsigned NOT NULL AUTO_INCREMENT");
        echo "Successfully updated!\n";
    } else {
        echo "Column $column is already AUTO_INCREMENT.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
