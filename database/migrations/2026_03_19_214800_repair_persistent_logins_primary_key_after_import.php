<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('persistent_logins') || ! Schema::hasColumn('persistent_logins', 'id')) {
            return;
        }

        $primaryIndex = DB::select("SHOW INDEX FROM `persistent_logins` WHERE Key_name = 'PRIMARY'");

        if (empty($primaryIndex)) {
            DB::statement('ALTER TABLE `persistent_logins` ADD PRIMARY KEY (`id`)');
        }

        $columnDefinition = collect(DB::select("SHOW COLUMNS FROM `persistent_logins` WHERE Field = ?", ['id']))->first();

        if (! $columnDefinition) {
            return;
        }

        $extra = strtolower((string) ($columnDefinition->Extra ?? ''));

        if (! str_contains($extra, 'auto_increment')) {
            DB::statement('ALTER TABLE `persistent_logins` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        }

        $nextId = ((int) DB::table('persistent_logins')->max('id')) + 1;

        DB::statement('ALTER TABLE `persistent_logins` AUTO_INCREMENT = ' . max($nextId, 1));
    }

    public function down(): void
    {
        // Intentionally left blank to avoid risking existing login token identifiers.
    }
};