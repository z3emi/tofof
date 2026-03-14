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
        Schema::table('products', function (Blueprint $table) {
            // حذف الأعمدة القديمة التي سيتم استبدالها بالنظام الجديد
            $table->dropColumn('cost_price');
            $table->dropColumn('stock_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // تعريف الأعمدة مرة أخرى في حال التراجع عن الـ migration
            $table->decimal('cost_price', 10, 2)->default(0)->after('price');
            $table->integer('stock_quantity')->default(0)->after('image_url');
        });
    }
};
