<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->ensureAutoIncrement('orders');
        $this->ensureAutoIncrement('permissions');
    }

    public function down(): void
    {
        // intentionally left blank - removing AUTO_INCREMENT could compromise existing data integrity.
    }

    private function ensureAutoIncrement(string $table, string $column = 'id'): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        $columnDefinition = collect(DB::select("SHOW COLUMNS FROM `{$table}` WHERE Field = ?", [$column]))->first();

        if (!$columnDefinition) {
            return;
        }

        $extra = strtolower($columnDefinition->Extra ?? '');

        if (str_contains($extra, 'auto_increment')) {
            return;
        }

        DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` bigint unsigned NOT NULL AUTO_INCREMENT");
    }
};
