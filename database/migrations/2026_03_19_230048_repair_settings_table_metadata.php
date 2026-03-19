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

        $idColumn = DB::selectOne(
            "
            SELECT COLUMN_KEY, EXTRA
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = 'settings'
              AND COLUMN_NAME = 'id'
            ",
            [$database]
        );

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

        if ($idColumn) {
            $isPrimaryId = strtoupper((string) ($idColumn->COLUMN_KEY ?? '')) === 'PRI';
            $isAutoIncrement = str_contains(
                strtolower((string) ($idColumn->EXTRA ?? '')),
                'auto_increment'
            );

            if (!$isPrimaryId || !$isAutoIncrement) {
                if (!$hasPrimaryKey) {
                    DB::statement("ALTER TABLE `settings` ADD PRIMARY KEY (`id`)");
                }

                DB::statement("ALTER TABLE `settings` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT");
            }
        }

        $hasUniqueKeyOnSettingKey = (int) (DB::selectOne(
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

        if (!$hasUniqueKeyOnSettingKey) {
            // Keep the newest record per key so unique index can be created safely.
            DB::statement(
                "
                DELETE s_old
                FROM `settings` AS s_old
                INNER JOIN `settings` AS s_new
                    ON s_old.`key` = s_new.`key`
                   AND s_old.`id` < s_new.`id`
                "
            );

            DB::statement("ALTER TABLE `settings` ADD UNIQUE `settings_key_unique` (`key`)");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentional no-op: this migration repairs live table metadata.
    }
};
