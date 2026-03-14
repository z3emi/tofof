<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // إذا العمود غير موجود أضفه، وإلا فقط جهّز البيانات
        if (!Schema::hasColumn('purchase_invoice_items', 'quantity_remaining')) {
            Schema::table('purchase_invoice_items', function (Blueprint $table) {
                $table->integer('quantity_remaining')->default(0)->after('quantity');
            });

            // تهيئة أولية: اجعل الكمية المتبقية = الكمية الأصلية
            DB::table('purchase_invoice_items')
                ->update(['quantity_remaining' => DB::raw('quantity')]);
        } else {
            // العمود موجود مسبقاً — فقط نضمن تهيئة القيم الناقصة/الخاطئة
            // 1) أي NULL نخليه يساوي الكمية الأصلية
            DB::table('purchase_invoice_items')
                ->whereNull('quantity_remaining')
                ->update(['quantity_remaining' => DB::raw('quantity')]);

            // 2) (اختياري) إذا تريد صفوف 0 تتعيّن للكمية الأصلية فقط للصفوف القديمة
            // انتبه: لو عندك صفوف يجب أن تبقى 0 (بسبب مبيعات سابقة)، امسح هذا الجزء.
            DB::table('purchase_invoice_items')
                ->where('quantity_remaining', 0)
                ->update(['quantity_remaining' => DB::raw('quantity')]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('purchase_invoice_items', 'quantity_remaining')) {
            Schema::table('purchase_invoice_items', function (Blueprint $table) {
                $table->dropColumn('quantity_remaining');
            });
        }
    }
};
