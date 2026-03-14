<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('discount_code_usages', function (Blueprint $table) {
            // الأمر الصحيح لإعادة تسمية العمود
            $table->renameColumn('customer_id', 'user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discount_code_usages', function (Blueprint $table) {
            // الأمر العكسي في حال احتجت للتراجع
            $table->renameColumn('user_id', 'customer_id');
        });
    }
};