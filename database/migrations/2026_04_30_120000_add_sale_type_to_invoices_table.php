<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('invoices', 'sale_type')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->string('sale_type', 30)->default('cash')->after('payment_type');
            });

            DB::table('invoices')->whereNull('sale_type')->update(['sale_type' => 'cash']);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('invoices', 'sale_type')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn('sale_type');
            });
        }
    }
};
