<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'manual_discount_type')) {
                $table->string('manual_discount_type', 20)->nullable()->after('discount_code_id');
            }

            if (!Schema::hasColumn('orders', 'manual_discount_value')) {
                $table->decimal('manual_discount_value', 10, 2)->nullable()->after('manual_discount_type');
            }

            if (!Schema::hasColumn('orders', 'manual_discount_amount')) {
                $table->decimal('manual_discount_amount', 10, 2)->default(0)->after('manual_discount_value');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'manual_discount_amount')) {
                $table->dropColumn('manual_discount_amount');
            }

            if (Schema::hasColumn('orders', 'manual_discount_value')) {
                $table->dropColumn('manual_discount_value');
            }

            if (Schema::hasColumn('orders', 'manual_discount_type')) {
                $table->dropColumn('manual_discount_type');
            }
        });
    }
};
