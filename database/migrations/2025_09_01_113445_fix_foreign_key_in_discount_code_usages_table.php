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
            // الخطوة 1: حذف القيد الأجنبي القديم الخاطئ
            // اسم القيد أخذناه من رسالة الخطأ
            $table->dropForeign('discount_code_usages_customer_id_foreign');

            // الخطوة 2: إضافة القيد الأجنبي الجديد الصحيح الذي يربط بجدول "users"
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discount_code_usages', function (Blueprint $table) {
            // للتراجع: نحذف القيد الجديد ونعيد القيد القديم
            $table->dropForeign(['user_id']);
            
            $table->foreign('user_id', 'discount_code_usages_customer_id_foreign')
                  ->references('id')
                  ->on('customers')
                  ->onDelete('cascade');
        });
    }
};