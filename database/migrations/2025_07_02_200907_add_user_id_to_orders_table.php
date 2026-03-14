<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * يقوم بتشغيل الهجرة لإضافة عمود 'user_id' إلى جدول 'orders'.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // إضافة عمود user_id كـ foreign key (مفتاح خارجي).
            // هذا العمود سيربط الطلب بالمستخدم الذي قام بإنشائه.
            // constrained() ينشئ قيداً يضمن أن القيمة في user_id موجودة في عمود 'id' بجدول 'users'.
            // onDelete('cascade') يعني أنه إذا تم حذف المستخدم، فسيتم حذف جميع طلباته المرتبطة به تلقائياً.
            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * يقوم بالتراجع عن الهجرة، أي حذف عمود 'user_id' إذا تم التراجع عن الهجرة.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // لحذف المفتاح الخارجي والعمود بشكل صحيح عند التراجع عن الهجرة.
            $table->dropConstrainedForeignId('user_id');
        });
    }
};