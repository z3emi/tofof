<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('wallet_transactions') || ! Schema::hasColumn('wallet_transactions', 'id')) {
            return;
        }

        $primaryKey = DB::selectOne("SHOW INDEX FROM `wallet_transactions` WHERE Key_name = 'PRIMARY'");
        if (! $primaryKey) {
            DB::statement('ALTER TABLE `wallet_transactions` ADD PRIMARY KEY (`id`)');
        }

        $idColumn = DB::selectOne("SHOW COLUMNS FROM `wallet_transactions` WHERE Field = 'id'");
        if (! $idColumn) {
            return;
        }

        $extra = strtolower((string) ($idColumn->Extra ?? ''));
        if (! str_contains($extra, 'auto_increment')) {
            $nextId = ((int) DB::table('wallet_transactions')->max('id')) + 1;
            DB::statement('ALTER TABLE `wallet_transactions` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
            DB::statement('ALTER TABLE `wallet_transactions` AUTO_INCREMENT = ' . max($nextId, 1));
        }
    }

    public function down(): void
    {
        // No destructive rollback for safety.
    }
};
