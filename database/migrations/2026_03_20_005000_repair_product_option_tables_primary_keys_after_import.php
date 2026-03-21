<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'product_options',
            'product_option_values',
            'product_option_combinations',
            'product_option_combination_images',
        ];

        foreach ($tables as $table) {
            $this->repairTableId($table);
        }
    }

    public function down(): void
    {
        // Intentionally left blank to avoid risking existing identifiers.
    }

    private function repairTableId(string $table): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'id')) {
            return;
        }

        $primaryIndex = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = 'PRIMARY'");

        if (empty($primaryIndex)) {
            DB::statement("ALTER TABLE `{$table}` ADD PRIMARY KEY (`id`)");
        }

        $columnDefinition = collect(DB::select("SHOW COLUMNS FROM `{$table}` WHERE Field = ?", ['id']))->first();

        if (! $columnDefinition) {
            return;
        }

        $extra = strtolower((string) ($columnDefinition->Extra ?? ''));

        if (! str_contains($extra, 'auto_increment')) {
            DB::statement("ALTER TABLE `{$table}` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT");
        }

        $nextId = ((int) DB::table($table)->max('id')) + 1;

        DB::statement("ALTER TABLE `{$table}` AUTO_INCREMENT = " . max($nextId, 1));
    }
};
