<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('purchase_invoice_items')) {
            Schema::table('purchase_invoice_items', function (Blueprint $table) {
                if (!Schema::hasColumn('purchase_invoice_items', 'warehouse_id')) {
                    $table->foreignId('warehouse_id')->nullable()->after('product_id')->constrained('inventory_warehouses')->nullOnDelete();
                }
                if (!Schema::hasColumn('purchase_invoice_items', 'variant_sku')) {
                    $table->string('variant_sku')->nullable()->after('warehouse_id');
                }
                if (!Schema::hasColumn('purchase_invoice_items', 'variant_name')) {
                    $table->string('variant_name')->nullable()->after('variant_sku');
                }
                if (!Schema::hasColumn('purchase_invoice_items', 'batch_number')) {
                    $table->string('batch_number')->nullable()->after('variant_name');
                }
                if (!Schema::hasColumn('purchase_invoice_items', 'expires_at')) {
                    $table->date('expires_at')->nullable()->after('batch_number');
                }
                if (!Schema::hasColumn('purchase_invoice_items', 'reorder_point')) {
                    $table->integer('reorder_point')->default(0)->after('quantity_remaining');
                }
                if (!Schema::hasColumn('purchase_invoice_items', 'notes')) {
                    $table->text('notes')->nullable()->after('reorder_point');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('purchase_invoice_items')) {
            Schema::table('purchase_invoice_items', function (Blueprint $table) {
                if (Schema::hasColumn('purchase_invoice_items', 'notes')) {
                    $table->dropColumn('notes');
                }
                if (Schema::hasColumn('purchase_invoice_items', 'reorder_point')) {
                    $table->dropColumn('reorder_point');
                }
                if (Schema::hasColumn('purchase_invoice_items', 'expires_at')) {
                    $table->dropColumn('expires_at');
                }
                if (Schema::hasColumn('purchase_invoice_items', 'batch_number')) {
                    $table->dropColumn('batch_number');
                }
                if (Schema::hasColumn('purchase_invoice_items', 'variant_name')) {
                    $table->dropColumn('variant_name');
                }
                if (Schema::hasColumn('purchase_invoice_items', 'variant_sku')) {
                    $table->dropColumn('variant_sku');
                }
                if (Schema::hasColumn('purchase_invoice_items', 'warehouse_id')) {
                    $table->dropConstrainedForeignId('warehouse_id');
                }
            });
        }
    }
};
