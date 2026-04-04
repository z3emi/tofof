<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('activity_logs') || ! Schema::hasColumn('activity_logs', 'id')) {
            return;
        }

        $database = DB::getDatabaseName();

        $pk = DB::selectOne(
            'SELECT COUNT(*) AS aggregate FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? AND CONSTRAINT_NAME = ?',
            [$database, 'activity_logs', 'id', 'PRIMARY']
        );

        if ((int) ($pk->aggregate ?? 0) === 0) {
            DB::statement('ALTER TABLE `activity_logs` ADD PRIMARY KEY (`id`)');
        }

        $idColumn = DB::selectOne(
            'SELECT EXTRA FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$database, 'activity_logs', 'id']
        );

        $extra = strtolower((string) ($idColumn->EXTRA ?? ''));

        if (! str_contains($extra, 'auto_increment')) {
            DB::statement('ALTER TABLE `activity_logs` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        }

        $nextId = ((int) DB::table('activity_logs')->max('id')) + 1;
        DB::statement('ALTER TABLE `activity_logs` AUTO_INCREMENT = ' . max($nextId, 1));
    }

    public function down(): void
    {
        // This migration is a production schema repair and is intentionally irreversible.
    }
};
