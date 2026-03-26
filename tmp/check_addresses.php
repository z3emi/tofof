<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    echo "Columns:\n";
    print_r(Schema::getColumnListing('addresses'));
    echo "\nCreate Table Statement:\n";
    $result = DB::select('SHOW CREATE TABLE`addresses`');
    print_r($result[0]);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
