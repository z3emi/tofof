<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'payment_status_is_manual')) {
                $column = $table->boolean('payment_status_is_manual')
                    ->default(false);

                if (Schema::hasColumn('orders', 'payment_status')) {
                    $column->after('payment_status');
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'payment_status_is_manual')) {
                $table->dropColumn('payment_status_is_manual');
            }
        });
    }
};
