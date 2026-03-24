<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('homepage_slides') || ! Schema::hasColumn('homepage_slides', 'id')) {
            return;
        }

        $primaryIndex = DB::select("SHOW INDEX FROM `homepage_slides` WHERE Key_name = 'PRIMARY'");

        if (empty($primaryIndex)) {
            DB::statement('ALTER TABLE `homepage_slides` ADD PRIMARY KEY (`id`)');
        }

        $columnDefinition = collect(DB::select("SHOW COLUMNS FROM `homepage_slides` WHERE Field = ?", ['id']))->first();

        if (! $columnDefinition) {
            return;
        }

        $extra = strtolower((string) ($columnDefinition->Extra ?? ''));

        if (! str_contains($extra, 'auto_increment')) {
            DB::statement('ALTER TABLE `homepage_slides` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        }

        $nextId = ((int) DB::table('homepage_slides')->max('id')) + 1;

        DB::statement('ALTER TABLE `homepage_slides` AUTO_INCREMENT = ' . max($nextId, 1));
    }

    public function down(): void
    {
        // Intentionally left blank to avoid risking existing slide identifiers.
    }
};
