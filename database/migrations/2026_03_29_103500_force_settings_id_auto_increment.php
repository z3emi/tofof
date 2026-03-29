<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        $database = DB::getDatabaseName();

        // 1. Force add PRIMARY KEY if missing
        $hasPrimaryKey = (int) (DB::selectOne(
            "
            SELECT COUNT(*) AS aggregate
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = 'settings'
              AND CONSTRAINT_TYPE = 'PRIMARY KEY'
            ",
            [$database]
        )->aggregate ?? 0) > 0;

        if (!$hasPrimaryKey) {
            try {
                DB::statement("ALTER TABLE `settings` ADD PRIMARY KEY (`id`)");
            } catch (\Throwable $e) {
                // Ignore if it already has primary key or failed for other reasons
            }
        }

        // 2. Force apply AUTO_INCREMENT to id
        try {
            DB::statement("ALTER TABLE `settings` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT");
        } catch (\Throwable $e) {
            // Log error if needed or handle it
        }

        // 3. Ensure key is unique
        $hasUniqueKey = (int) (DB::selectOne(
            "
            SELECT COUNT(*) AS aggregate
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = 'settings'
              AND COLUMN_NAME = 'key'
              AND NON_UNIQUE = 0
            ",
            [$database]
        )->aggregate ?? 0) > 0;

        if (!$hasUniqueKey) {
            try {
                // Remove duplicates before adding unique key
                DB::statement(
                    "
                    DELETE s_old FROM settings AS s_old
                    INNER JOIN settings AS s_new ON s_old.`key` = s_new.`key`
                    AND s_old.`id` < s_new.`id`
                    "
                );
                DB::statement("ALTER TABLE `settings` ADD UNIQUE `settings_key_unique` (`key`)");
            } catch (\Throwable $e) {
                // 
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op for repairs
    }
};
