<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('primary_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('primary_categories', 'parent_id')) {
                $table->foreignId('parent_id')
                      ->nullable()
                      ->constrained('primary_categories')
                      ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('primary_categories', function (Blueprint $table) {
            if (Schema::hasColumn('primary_categories', 'parent_id')) {
                $table->dropConstrainedForeignId('parent_id');
            }
        });
    }
};
