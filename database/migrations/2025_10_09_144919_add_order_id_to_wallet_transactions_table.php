<?php

// php artisan make:migration add_order_id_to_wallet_transactions_table --table=wallet_transactions

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable()
                  ->constrained()->cascadeOnDelete();
            $table->index('type');
            $table->index('description');
        });
    }

    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('order_id');
            $table->dropIndex(['type']);
            $table->dropIndex(['description']);
        });
    }
};
