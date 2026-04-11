<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('orders', 'source')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('source')->default('website')->after('customer_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'source')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('source');
            });
        }
    }
};