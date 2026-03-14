<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('orders', 'invoice_type')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('invoice_type', 30)->default('retail')->after('sale_type');
            });

            DB::table('orders')->whereNull('invoice_type')->update(['invoice_type' => 'retail']);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'invoice_type')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('invoice_type');
            });
        }
    }
};
