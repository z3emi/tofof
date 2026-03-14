<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('managers', function (Blueprint $table) {
            if (!Schema::hasColumn('managers', 'nationality')) {
                $table->string('nationality')->nullable()->after('phone_number');
            }

            if (!Schema::hasColumn('managers', 'secondary_phone_number')) {
                $table->string('secondary_phone_number')->nullable()->after('nationality');
            }

            if (!Schema::hasColumn('managers', 'address')) {
                $table->string('address')->nullable()->after('secondary_phone_number');
            }

            if (!Schema::hasColumn('managers', 'profile_photo_path')) {
                $table->string('profile_photo_path')->nullable()->after('avatar');
            }

            if (!Schema::hasColumn('managers', 'housing_card_path')) {
                $table->string('housing_card_path')->nullable()->after('profile_photo_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('managers', function (Blueprint $table) {
            if (Schema::hasColumn('managers', 'housing_card_path')) {
                $table->dropColumn('housing_card_path');
            }

            if (Schema::hasColumn('managers', 'profile_photo_path')) {
                $table->dropColumn('profile_photo_path');
            }

            if (Schema::hasColumn('managers', 'address')) {
                $table->dropColumn('address');
            }

            if (Schema::hasColumn('managers', 'secondary_phone_number')) {
                $table->dropColumn('secondary_phone_number');
            }

            if (Schema::hasColumn('managers', 'nationality')) {
                $table->dropColumn('nationality');
            }
        });
    }
};
