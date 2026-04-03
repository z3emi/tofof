<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $this->ensureAutoIncrementId('category_discount_code');
        $this->ensureAutoIncrementId('discount_code_product');
    }

    public function down(): void
    {
        // No destructive rollback for safety.
    }

    private function ensureAutoIncrementId(string $table, string $column = 'id'): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        $primaryKey = DB::selectOne("SHOW INDEX FROM `{$table}` WHERE Key_name = 'PRIMARY'");
        if (! $primaryKey) {
            DB::statement("ALTER TABLE `{$table}` ADD PRIMARY KEY (`{$column}`)");
        }

        $idColumn = DB::selectOne("SHOW COLUMNS FROM `{$table}` WHERE Field = '{$column}'");
        if (! $idColumn) {
            return;
        }

        $extra = strtolower((string) ($idColumn->Extra ?? ''));
        if (! str_contains($extra, 'auto_increment')) {
            $nextId = ((int) DB::table($table)->max($column)) + 1;
            DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT");
            DB::statement('ALTER TABLE `' . $table . '` AUTO_INCREMENT = ' . max($nextId, 1));
        }
    }
};
