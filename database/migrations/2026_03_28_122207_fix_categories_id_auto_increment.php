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
        // Fix categories table
        \DB::statement('ALTER TABLE categories MODIFY id BIGINT(20) UNSIGNED AUTO_INCREMENT');
        
        // Fix primary_categories table
        \DB::statement('ALTER TABLE primary_categories MODIFY id BIGINT(20) UNSIGNED AUTO_INCREMENT');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \DB::statement('ALTER TABLE categories MODIFY id BIGINT(20) UNSIGNED');
        \DB::statement('ALTER TABLE primary_categories MODIFY id BIGINT(20) UNSIGNED');
    }
};
