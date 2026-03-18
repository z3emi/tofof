<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('wallet_transactions')) {
            return;
        }

        Schema::table('wallet_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('wallet_transactions', 'related_order_id')) {
                $table->unsignedBigInteger('related_order_id')->nullable()->after('balance_after');
            }

            if (! Schema::hasColumn('wallet_transactions', 'order_id')) {
                $table->unsignedBigInteger('order_id')->nullable()->after('related_order_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('wallet_transactions')) {
            return;
        }

        Schema::table('wallet_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('wallet_transactions', 'order_id')) {
                $table->dropColumn('order_id');
            }

            if (Schema::hasColumn('wallet_transactions', 'related_order_id')) {
                $table->dropColumn('related_order_id');
            }
        });
    }
};