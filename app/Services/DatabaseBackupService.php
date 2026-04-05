<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DatabaseBackupService
{
    public function exportDatabase()
    {
        $sqlDump = $this->generateSqlDump();

        $filename = 'backup_' . now()->format('Y_m_d_His') . '.sql';
        $path = storage_path("app/backups/{$filename}");
        File::ensureDirectoryExists(storage_path('app/backups'));
        File::put($path, $sqlDump);

        return $path;
    }

    public function generateSqlDump(): string
    {
        $tables = DB::select('SHOW TABLES');
        $dbName = env('DB_DATABASE');
        $tableKey = 'Tables_in_' . $dbName;
        $sqlDump = "-- Database backup for `{$dbName}`\n";
        $sqlDump .= "-- Generated at " . now()->toDateTimeString() . "\n\n";
        $sqlDump .= "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS = 0;\n\n";

        foreach ($tables as $tableObj) {
            $table = $tableObj->$tableKey;

            // Table structure
            $createTableStmt = DB::select("SHOW CREATE TABLE `$table`")[0]->{'Create Table'};
            $sqlDump .= "-- --------------------------------------------------------\n";
            $sqlDump .= "-- Table structure for table `$table`\n";
            $sqlDump .= "-- --------------------------------------------------------\n";
            $sqlDump .= "DROP TABLE IF EXISTS `$table`;\n";
            $sqlDump .= $createTableStmt . ";\n\n";

            // Table data
            $rows = DB::table($table)->get();
            if ($rows->count() > 0) {
                $sqlDump .= "-- Dumping data for table `$table`\n";
                foreach ($rows->chunk(100) as $chunk) {
                    $insert = "INSERT INTO `$table` VALUES ";
                    $values = [];
                    foreach ($chunk as $row) {
                        $rowData = array_map(function ($value) {
                            if (is_null($value)) return 'NULL';
                            return "'" . str_replace(["\\", "'"], ["\\\\", "\'"], $value) . "'";
                        }, (array) $row);
                        $values[] = "(" . implode(',', $rowData) . ")";
                    }
                    $sqlDump .= $insert . implode(",\n", $values) . ";\n\n";
                }
            }
        }

        $sqlDump .= "SET FOREIGN_KEY_CHECKS = 1;\n";

        return $sqlDump;
    }
}
