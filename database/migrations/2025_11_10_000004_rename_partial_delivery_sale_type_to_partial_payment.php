<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orders')) {
            DB::table('orders')
                ->where('sale_type', 'partial_delivery')
                ->update(['sale_type' => 'partial_payment']);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders')) {
            DB::table('orders')
                ->where('sale_type', 'partial_payment')
                ->update(['sale_type' => 'partial_delivery']);
        }
    }
};
