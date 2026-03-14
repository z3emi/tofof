<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'price_usd')) {
                $table->decimal('price_usd', 10, 2)->nullable()->after('price');
            }

            if (!Schema::hasColumn('products', 'sale_price_usd')) {
                $table->decimal('sale_price_usd', 10, 2)->nullable()->after('sale_price');
            }

            if (!Schema::hasColumn('products', 'wholesale_price_usd')) {
                $table->decimal('wholesale_price_usd', 10, 2)->nullable()->after('wholesale_price');
            }

            if (!Schema::hasColumn('products', 'agent_price_usd')) {
                $table->decimal('agent_price_usd', 10, 2)->nullable()->after('agent_price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $columns = [
                'agent_price_usd',
                'wholesale_price_usd',
                'sale_price_usd',
                'price_usd',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
