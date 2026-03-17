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

        $databaseName = DB::getDatabaseName();

        $idColumn = DB::selectOne(
            'SELECT EXTRA FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$databaseName, 'activity_logs', 'id']
        );

        $primaryKey = DB::selectOne(
            'SELECT COUNT(*) AS aggregate FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? AND CONSTRAINT_NAME = ?',
            [$databaseName, 'activity_logs', 'id', 'PRIMARY']
        );

        if (($primaryKey->aggregate ?? 0) === 0) {
            DB::statement('ALTER TABLE `activity_logs` ADD PRIMARY KEY (`id`)');
        }

        $extra = strtolower((string) ($idColumn->EXTRA ?? ''));

        if (! str_contains($extra, 'auto_increment')) {
            $nextId = ((int) DB::table('activity_logs')->max('id')) + 1;

            DB::statement('ALTER TABLE `activity_logs` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
            DB::statement('ALTER TABLE `activity_logs` AUTO_INCREMENT = ' . max($nextId, 1));
        }
    }

    public function down(): void
    {
        // Intentionally left blank: this migration repairs broken production schema.
    }
};