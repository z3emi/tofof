<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $brokenTables = DB::select(
            "SELECT TABLE_NAME
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND COLUMN_NAME = 'id'
                             AND DATA_TYPE IN ('tinyint', 'smallint', 'mediumint', 'int', 'bigint')
               AND (COLUMN_KEY <> 'PRI' OR EXTRA NOT LIKE '%auto_increment%')
             ORDER BY TABLE_NAME"
        );

        foreach ($brokenTables as $tableInfo) {
            $table = $tableInfo->TABLE_NAME ?? null;

            if (! is_string($table) || $table === '') {
                continue;
            }

            $this->ensurePrimaryKeyAndAutoIncrement($table);
        }
    }

    public function down(): void
    {
        // Intentionally left blank to avoid risking data integrity.
    }

    private function ensurePrimaryKeyAndAutoIncrement(string $table, string $column = 'id'): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        $primaryIndex = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = 'PRIMARY'");

        if (empty($primaryIndex)) {
            DB::statement("ALTER TABLE `{$table}` ADD PRIMARY KEY (`{$column}`)");
        }

        $columnDefinition = collect(DB::select("SHOW COLUMNS FROM `{$table}` WHERE Field = ?", [$column]))->first();

        if (! $columnDefinition) {
            return;
        }

        $extra = strtolower($columnDefinition->Extra ?? '');

        if (! str_contains($extra, 'auto_increment')) {
            DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` bigint unsigned NOT NULL AUTO_INCREMENT");
        }
    }
};