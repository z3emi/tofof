<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('orders', 'partial_payment_received')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->decimal('partial_payment_received', 12, 2)
                      ->nullable()
                      ->after('discount_code_id');
            });
        }

        if (!Schema::hasColumn('customers', 'origin')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('origin', 20)
                      ->default('admin')
                      ->after('pricing_type');
                $table->index('origin');
            });

            DB::table('customers')
                ->whereNull('origin')
                ->update(['origin' => 'admin']);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'partial_payment_received')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('partial_payment_received');
            });
        }

        if (Schema::hasColumn('customers', 'origin')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropIndex('customers_origin_index');
                $table->dropColumn('origin');
            });
        }
    }
};
