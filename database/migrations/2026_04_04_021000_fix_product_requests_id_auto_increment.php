<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('product_requests') || ! Schema::hasColumn('product_requests', 'id')) {
            return;
        }

        $primaryKey = DB::selectOne("SHOW INDEX FROM `product_requests` WHERE Key_name = 'PRIMARY'");
        if (! $primaryKey) {
            DB::statement('ALTER TABLE `product_requests` ADD PRIMARY KEY (`id`)');
        }

        $idColumn = DB::selectOne("SHOW COLUMNS FROM `product_requests` WHERE Field = 'id'");
        if (! $idColumn) {
            return;
        }

        $extra = strtolower((string) ($idColumn->Extra ?? ''));
        if (! str_contains($extra, 'auto_increment')) {
            $nextId = ((int) DB::table('product_requests')->max('id')) + 1;
            DB::statement('ALTER TABLE `product_requests` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
            DB::statement('ALTER TABLE `product_requests` AUTO_INCREMENT = ' . max($nextId, 1));
        }
    }

    public function down(): void
    {
        // No destructive rollback for safety.
    }
};
