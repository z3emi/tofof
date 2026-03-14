<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'phone_number_secondary')) {
                $table->string('phone_number_secondary')->nullable()->after('phone_number');
            }
            if (!Schema::hasColumn('customers', 'location_label')) {
                $table->string('location_label')->nullable()->after('address_details');
            }
            if (!Schema::hasColumn('customers', 'location_latitude')) {
                $table->decimal('location_latitude', 10, 7)->nullable()->after('location_label');
            }
            if (!Schema::hasColumn('customers', 'location_longitude')) {
                $table->decimal('location_longitude', 10, 7)->nullable()->after('location_latitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'phone_number_secondary')) {
                $table->dropColumn('phone_number_secondary');
            }
            if (Schema::hasColumn('customers', 'location_label')) {
                $table->dropColumn('location_label');
            }
            if (Schema::hasColumn('customers', 'location_latitude')) {
                $table->dropColumn('location_latitude');
            }
            if (Schema::hasColumn('customers', 'location_longitude')) {
                $table->dropColumn('location_longitude');
            }
        });
    }
};
