<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('discount_code_usages') || ! Schema::hasColumn('discount_code_usages', 'id')) {
            return;
        }

        $primaryKey = DB::selectOne("SHOW INDEX FROM `discount_code_usages` WHERE Key_name = 'PRIMARY'");
        if (! $primaryKey) {
            DB::statement('ALTER TABLE `discount_code_usages` ADD PRIMARY KEY (`id`)');
        }

        $idColumn = DB::selectOne("SHOW COLUMNS FROM `discount_code_usages` WHERE Field = 'id'");
        if (! $idColumn) {
            return;
        }

        $extra = strtolower((string) ($idColumn->Extra ?? ''));
        if (! str_contains($extra, 'auto_increment')) {
            $nextId = ((int) DB::table('discount_code_usages')->max('id')) + 1;
            DB::statement('ALTER TABLE `discount_code_usages` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
            DB::statement('ALTER TABLE `discount_code_usages` AUTO_INCREMENT = ' . max($nextId, 1));
        }
    }

    public function down(): void
    {
        // No destructive rollback for safety.
    }
};
