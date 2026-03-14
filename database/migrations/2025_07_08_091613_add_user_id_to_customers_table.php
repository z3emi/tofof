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
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('user_id')
                  ->nullable() // للسماح بوجود عملاء غير مرتبطين بحسابات مستخدمين
                  ->unique()   // للتأكد أن كل مستخدم مرتبط بعميل واحد فقط
                  ->after('id')
                  ->constrained('users') // الربط مع جدول users
                  ->onDelete('set null'); // إذا حُذف المستخدم، اجعل القيمة null بدلاً من حذف العميل
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};