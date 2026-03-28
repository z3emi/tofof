<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('homepage_slides', function (Blueprint $table) {
            if (!Schema::hasColumn('homepage_slides', 'background_image_en')) {
                $table->string('background_image_en')->nullable()->after('background_image');
            }
            if (!Schema::hasColumn('homepage_slides', 'click_type')) {
                $table->enum('click_type', ['button', 'image'])->default('button')->after('button_url');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('homepage_slides', function (Blueprint $table) {
            $table->dropColumn(['background_image_en', 'click_type']);
        });
    }
};
