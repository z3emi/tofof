<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('orders', 'sale_type')) {
            DB::table('orders')->where('sale_type', 'retail')->update(['sale_type' => 'cash']);
            DB::table('orders')->where('sale_type', 'wholesale')->update(['sale_type' => 'credit']);
            DB::table('orders')->where('sale_type', 'agent')->update(['sale_type' => 'partial_delivery']);

            Schema::table('orders', function (Blueprint $table) {
                $table->string('sale_type', 30)->default('cash')->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'sale_type')) {
            DB::table('orders')->where('sale_type', 'cash')->update(['sale_type' => 'retail']);
            DB::table('orders')->where('sale_type', 'credit')->update(['sale_type' => 'wholesale']);
            DB::table('orders')->where('sale_type', 'partial_delivery')->update(['sale_type' => 'agent']);
            DB::table('orders')->where('sale_type', 'quotation')->update(['sale_type' => 'agent']);

            Schema::table('orders', function (Blueprint $table) {
                $table->string('sale_type', 20)->default('retail')->change();
            });
        }
    }
};
