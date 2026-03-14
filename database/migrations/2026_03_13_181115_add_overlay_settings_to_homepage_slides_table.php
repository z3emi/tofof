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
            $table->boolean('show_overlay')->default(true)->after('alt_text');
            $table->string('overlay_color', 20)->default('#000000')->after('show_overlay');
            $table->decimal('overlay_strength', 3, 2)->default(0.50)->after('overlay_color');
        });
    }

    public function down(): void
    {
        Schema::table('homepage_slides', function (Blueprint $table) {
            $table->dropColumn(['show_overlay', 'overlay_color', 'overlay_strength']);
        });
    }
};
