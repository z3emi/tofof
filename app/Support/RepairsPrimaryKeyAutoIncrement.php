<?php

namespace App\Support;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RepairsPrimaryKeyAutoIncrement
{
    public static function isMissingAutoIncrementError(QueryException $exception, string $table, string $column = 'id'): bool
    {
        $errorCode = (string) ($exception->errorInfo[1] ?? '');
        $message = strtolower($exception->getMessage());

        return $errorCode === '1364'
            && str_contains($message, strtolower($table))
            && str_contains($message, "field '{$column}' doesn't have a default value");
    }

    public static function ensure(string $table, string $column = 'id'): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        $primaryIndex = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = 'PRIMARY'");

        if (empty($primaryIndex)) {
            DB::statement("ALTER TABLE `{$table}` ADD PRIMARY KEY (`{$column}`)");
        }

        $columnDefinition = collect(DB::select("SHOW COLUMNS FROM `{$table}` WHERE Field = ?", [$column]))->first();

        if (! $columnDefinition) {
            return;
        }

        $extra = strtolower((string) ($columnDefinition->Extra ?? ''));

        if (! str_contains($extra, 'auto_increment')) {
            DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT");
        }

        $nextId = ((int) DB::table($table)->max($column)) + 1;

        DB::statement('ALTER TABLE `' . $table . '` AUTO_INCREMENT = ' . max($nextId, 1));
    }
}