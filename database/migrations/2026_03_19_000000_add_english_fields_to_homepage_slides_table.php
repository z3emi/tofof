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
            $table->string('title_en')->nullable()->after('title');
            $table->text('subtitle_en')->nullable()->after('subtitle');
            $table->string('button_text_en')->nullable()->after('button_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('homepage_slides', function (Blueprint $table) {
            $table->dropColumn(['title_en', 'subtitle_en', 'button_text_en']);
        });
    }
};
