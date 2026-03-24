<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('product_reviews') || ! Schema::hasColumn('product_reviews', 'id')) {
            return;
        }

        $databaseName = DB::getDatabaseName();

        $idColumn = DB::selectOne(
            'SELECT COLUMN_KEY, EXTRA FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$databaseName, 'product_reviews', 'id']
        );

        if (! $idColumn) {
            return;
        }

        if (($idColumn->COLUMN_KEY ?? '') !== 'PRI') {
            $primaryKey = DB::selectOne(
                'SELECT COUNT(*) AS aggregate FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_TYPE = ?',
                [$databaseName, 'product_reviews', 'PRIMARY KEY']
            );

            if ((int) ($primaryKey->aggregate ?? 0) === 0) {
                DB::statement('ALTER TABLE `product_reviews` ADD PRIMARY KEY (`id`)');
            }
        }

        $extra = strtolower((string) ($idColumn->EXTRA ?? ''));
        if (! str_contains($extra, 'auto_increment')) {
            $nextId = ((int) DB::table('product_reviews')->max('id')) + 1;
            DB::statement('ALTER TABLE `product_reviews` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
            DB::statement('ALTER TABLE `product_reviews` AUTO_INCREMENT = ' . max($nextId, 1));
        }
    }

    public function down(): void
    {
        // Intentionally left empty. This migration is a forward-only schema repair.
    }
};
