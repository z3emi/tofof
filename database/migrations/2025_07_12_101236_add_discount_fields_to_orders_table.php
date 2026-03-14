<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // إضافة عمود لتخزين قيمة الخصم
            $table->decimal('discount_amount', 10, 2)->default(0)->after('total_cost');
            // إضافة عمود لربط الطلب بكود الخصم المستخدم
            $table->foreignId('discount_code_id')->nullable()->constrained('discount_codes')->onDelete('set null')->after('discount_amount');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // حذف الأعمدة في حال التراجع
            $table->dropForeign(['discount_code_id']);
            $table->dropColumn(['discount_amount', 'discount_code_id']);
        });
    }
};