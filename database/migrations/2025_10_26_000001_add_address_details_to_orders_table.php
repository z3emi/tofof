<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('orders', 'address_details')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->text('address_details')->nullable()->after('city');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'address_details')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('address_details');
            });
        }
    }
};
