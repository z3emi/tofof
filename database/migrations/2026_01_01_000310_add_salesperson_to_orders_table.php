<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'salesperson_id')) {
                $table->foreignId('salesperson_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('managers')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'salesperson_id')) {
                $table->dropConstrainedForeignId('salesperson_id');
            }
        });
    }
};
