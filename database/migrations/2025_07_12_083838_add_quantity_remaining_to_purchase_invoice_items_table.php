<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\PurchaseInvoiceItem;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('purchase_invoice_items', function (Blueprint $table) {
            // هذا العمود سيحتوي على الكمية المتبقية من كل وجبة شراء
            $table->integer('quantity_remaining')->default(0)->after('quantity');
        });

        // تحديث كل السجلات القديمة لجعل الكمية المتبقية تساوي الكمية الأصلية
        // هذا يضمن أن البيانات القديمة ستعمل مع النظام الجديد
        if (Schema::hasTable('purchase_invoice_items')) {
            PurchaseInvoiceItem::chunk(100, function ($items) {
                foreach ($items as $item) {
                    $item->update(['quantity_remaining' => $item->quantity]);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_invoice_items', function (Blueprint $table) {
            $table->dropColumn('quantity_remaining');
        });
    }
};
