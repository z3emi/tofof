<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('managers')) {
            return;
        }

        Schema::table('managers', function (Blueprint $table) {
            if (!Schema::hasColumn('managers', 'nationality_card_path')) {
                $table->string('nationality_card_path')->nullable()->after('housing_card_path');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('managers')) {
            return;
        }

        Schema::table('managers', function (Blueprint $table) {
            if (Schema::hasColumn('managers', 'nationality_card_path')) {
                $table->dropColumn('nationality_card_path');
            }
        });
    }
};
