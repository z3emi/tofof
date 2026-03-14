<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // التأكد من وجود الحقل قبل محاولة حذفه لتجنب الأخطاء
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // هذا الكود يسمح لك بالتراجع عن الحذف إذا احتجت لذلك
            $table->string('role')->default('user')->after('email');
        });
    }
};