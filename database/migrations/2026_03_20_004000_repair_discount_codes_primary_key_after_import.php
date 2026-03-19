<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('discount_codes') || ! Schema::hasColumn('discount_codes', 'id')) {
            return;
        }

        $primaryIndex = DB::select("SHOW INDEX FROM `discount_codes` WHERE Key_name = 'PRIMARY'");

        if (empty($primaryIndex)) {
            DB::statement('ALTER TABLE `discount_codes` ADD PRIMARY KEY (`id`)');
        }

        $columnDefinition = collect(DB::select("SHOW COLUMNS FROM `discount_codes` WHERE Field = ?", ['id']))->first();

        if (! $columnDefinition) {
            return;
        }

        $extra = strtolower((string) ($columnDefinition->Extra ?? ''));

        if (! str_contains($extra, 'auto_increment')) {
            DB::statement('ALTER TABLE `discount_codes` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        }

        $nextId = ((int) DB::table('discount_codes')->max('id')) + 1;

        DB::statement('ALTER TABLE `discount_codes` AUTO_INCREMENT = ' . max($nextId, 1));
    }

    public function down(): void
    {
        // Intentionally left blank to avoid risking existing discount code identifiers.
    }
};
