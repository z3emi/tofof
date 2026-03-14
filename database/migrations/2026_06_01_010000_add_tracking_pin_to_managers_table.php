<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('managers')) {
            return;
        }

        Schema::table('managers', function (Blueprint $table) {
            if (! Schema::hasColumn('managers', 'tracking_pin_hash')) {
                $table->string('tracking_pin_hash', 255)->nullable()->after('password');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('managers')) {
            return;
        }

        Schema::table('managers', function (Blueprint $table) {
            if (Schema::hasColumn('managers', 'tracking_pin_hash')) {
                $table->dropColumn('tracking_pin_hash');
            }
        });
    }
};
