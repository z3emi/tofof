<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $this->ensureAutoIncrementId('products');
        $this->ensureAutoIncrementId('product_images');
        $this->ensureAutoIncrementId('product_options');
        $this->ensureAutoIncrementId('product_option_values');
        $this->ensureAutoIncrementId('product_option_combinations');
        $this->ensureAutoIncrementId('product_option_combination_images');
    }

    public function down(): void
    {
        // Irreversible safety migration: keep schema in repaired state.
    }

    private function ensureAutoIncrementId(string $table): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'id')) {
            return;
        }

        $primaryIndex = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = 'PRIMARY'");
        if (empty($primaryIndex)) {
            DB::statement("ALTER TABLE `{$table}` ADD PRIMARY KEY (`id`)");
        }

        $column = collect(DB::select("SHOW COLUMNS FROM `{$table}` WHERE Field = 'id'"))->first();
        if (!$column) {
            return;
        }

        $extra = strtolower((string) ($column->Extra ?? ''));
        if (!str_contains($extra, 'auto_increment')) {
            DB::statement("ALTER TABLE `{$table}` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT");
        }

        $nextId = ((int) DB::table($table)->max('id')) + 1;
        DB::statement('ALTER TABLE `' . $table . '` AUTO_INCREMENT = ' . max($nextId, 1));
    }
};
