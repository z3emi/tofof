<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;
use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Illuminate\Support\Facades\Artisan;


class BackupController extends Controller
{
    private $disk;
    private $backupFolderName;

    public function __construct()
    {
        $this->disk = Storage::build([
            'driver' => 'local',
            'root'   => storage_path('app'),
        ]);

        $this->backupFolderName = config('backup.backup.name', 'backups');
    }

    public function index()
    {
        if (!$this->disk->exists($this->backupFolderName)) {
            $this->disk->makeDirectory($this->backupFolderName);
        }

        $backups = collect($this->disk->allFiles($this->backupFolderName))
            ->filter(fn($f) => preg_match('~\.(sql|zip)$~i', $f))
            ->map(function ($file) {
                return [
                    'name' => basename($file),
                    'date' => date('Y-m-d H:i:s', $this->disk->lastModified($file)),
                    'size' => round($this->disk->size($file) / 1024 / 1024, 2) . ' MB',
                ];
            })
            ->sortByDesc('date')
            ->values()
            ->all();

        return view('admin.backups.index', compact('backups'));
    }

    public function createDbBackup()
    {
        try {
            $sqlContent = $this->getDatabaseDump();
            $fileName = $this->backupFolderName . '/db-backup-' . date('Y-m-d-His') . '.sql';
            $this->disk->put($fileName, $sqlContent);

            return redirect()->route('admin.backups.index')
                ->with('success', 'تم إنشاء نسخة احتياطية لقاعدة البيانات بنجاح.');
        } catch (\Exception $e) {
            Log::error("DB Backup creation failed: " . $e->getMessage());
            return redirect()->back()->with('error', 'فشل إنشاء النسخة الاحتياطية: ' . $e->getMessage());
        }
    }

    public function createFullBackup()
    {
        try {
            $sqlContent = $this->getDatabaseDump();
            $sqlFileName = 'db-backup-' . date('Y-m-d-His') . '.sql';
            $tempSqlPath = storage_path('app/' . $sqlFileName);
            file_put_contents($tempSqlPath, $sqlContent);

            if (!$this->disk->exists($this->backupFolderName)) {
                $this->disk->makeDirectory($this->backupFolderName);
            }
            $zipFileName = $this->backupFolderName . '/full-backup-' . date('Y-m-d-His') . '.zip';
            $zipPath = $this->disk->path($zipFileName);

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
                throw new \Exception('لا يمكن إنشاء ملف الـ zip.');
            }

            $zip->addFile($tempSqlPath, $sqlFileName);

            $filesPath = storage_path('app/public');
            if (is_dir($filesPath)) {
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($filesPath, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );
                foreach ($files as $name => $file) {
                    if (!$file->isDir()) {
                        $filePath = $file->getRealPath();
                        $relativePath = 'storage/' . substr($filePath, strlen($filesPath) + 1);
                        $zip->addFile($filePath, $relativePath);
                    }
                }
            }

            $zip->close();
            @unlink($tempSqlPath);

            return redirect()->route('admin.backups.index')
                ->with('success', 'تم إنشاء نسخة احتياطية كاملة بنجاح.');
        } catch (\Exception $e) {
            Log::error("Full Backup creation failed: " . $e->getMessage());
            return redirect()->back()->with('error', 'فشل إنشاء النسخة الاحتياطية الكاملة: ' . $e->getMessage());
        }
    }

    /**
     * ترتيب التصدير:
     * 1) CREATE TABLE بلا (INDEX/FK/AUTO_INCREMENT)
     * 2) INSERT data
     * 3) ALTER TABLE لإضافة PRIMARY/INDEX + ضبط AUTO_INCREMENT
     * 4) ALTER TABLE لإضافة قيود FOREIGN KEY
     * 5) CREATE VIEW بعد الجداول
     */
    private function getDatabaseDump()
    {
        $dbName = DB::getDatabaseName();
        $serverVersion = DB::select("SELECT VERSION() as version")[0]->version ?? 'unknown';

        $out = "-- phpMyAdmin SQL Dump\n";
        $out .= "-- version 5.2.x (style-like)\n";
        $out .= "-- https://www.phpmyadmin.net/\n";
        $out .= "--\n-- Host: localhost:3306\n";
        $out .= "-- Generation Time: " . date('M d, Y \a\t H:i A') . "\n";
        $out .= "-- Server version: {$serverVersion}\n";
        $out .= "-- PHP Version: " . phpversion() . "\n\n";
        $out .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $out .= "START TRANSACTION;\n";
        $out .= "SET time_zone = \"+00:00\";\n\n";
        $out .= "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n";
        $out .= "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n";
        $out .= "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n";
        $out .= "/*!40101 SET NAMES utf8mb4 */;\n";
        $out .= "/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;\n";
        $out .= "/*!40103 SET TIME_ZONE='+00:00' */;\n";
        $out .= "/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE */;\n";
        $out .= "/*!40101 SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;\n";
        $out .= "/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES */;\n";
        $out .= "/*!40111 SET SQL_NOTES=0 */;\n";
        $out .= "/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS */;\n";
        $out .= "/*!40014 SET FOREIGN_KEY_CHECKS=0 */;\n\n";
        $out .= "--\n-- Database: `{$dbName}`\n--\n\n";

        // فصل الجداول عن الـVIEWs
        $fullTables = DB::select("SHOW FULL TABLES FROM `{$dbName}`");
        $tableKey = "Tables_in_{$dbName}";
        $baseTables = [];
        $views = [];

        foreach ($fullTables as $row) {
            $name = $row->$tableKey;
            $type = $row->Table_type ?? $row->{"Table_type"} ?? null;
            if (!$type) {
                $status = DB::selectOne("SHOW TABLE STATUS LIKE ?", [$name]);
                $type = (isset($status->Comment) && stripos($status->Comment, 'VIEW') !== false) ? 'VIEW' : 'BASE TABLE';
            }
            if (strtoupper($type) === 'VIEW') {
                $views[] = $name;
            } else {
                $baseTables[] = $name;
            }
        }

        sort($baseTables);
        sort($views);

        // مجاميع للمرحلة اللاحقة
        $idxAlters = []; // table => ["ADD PRIMARY KEY(...)", "ADD KEY `...` (...)"...]
        $fkAlters  = []; // ["ALTER TABLE `t` ADD CONSTRAINT ...;", ...]
        $autoIncs  = []; // table => int|null

        // 1) CREATE TABLE نظيف + 2) INSERT
        foreach ($baseTables as $table) {
            $out .= "-- --------------------------------------------------------\n\n";
            $out .= "--\n-- Table structure for table `{$table}`\n--\n\n";

            $createRes = DB::select("SHOW CREATE TABLE `{$table}`");
            $createSql = $createRes[0]->{'Create Table'};

            // *** التقط أسطر الفهارس ***
            $idxLines = [];
            if (preg_match_all('/^\s*(PRIMARY KEY|UNIQUE KEY|KEY|FULLTEXT KEY|SPATIAL KEY)\s+.+$/mi', $createSql, $m)) {
                $idxLines = $m[0];
            }

            // *** التقط أسطر قيود FK ***
            $fkLines = [];
            if (preg_match_all('/^\s*CONSTRAINT\s+`[^`]+`\s+FOREIGN KEY\s*\([^)]+\)\s+REFERENCES\s+`[^`]+`\s*\([^)]+\)(?:[^,\n]*)?/mi', $createSql, $m2)) {
                $fkLines = $m2[0];
            }

            // خزّن الفهارس كأوامر ADD لاحقًا
            $adds = [];
            foreach ($idxLines as $line) {
                $normalizedLine = rtrim(trim($line), ", \t\n\r\0\x0B");
                if ($normalizedLine !== '') {
                    $adds[] = "ADD " . $normalizedLine;
                }
            }
            $idxAlters[$table] = $adds;

            // خزّن قيود FK كأوامر ALTER لاحقًا
            foreach ($fkLines as $fk) {
                $normalizedFk = rtrim(trim($fk), ", \t\n\r\0\x0B");
                if ($normalizedFk !== '') {
                    $fkAlters[] = "ALTER TABLE `{$table}` ADD " . $normalizedFk . ";";
                }
            }

            // *** تنظيف CREATE: إزالة الفهارس + قيود FK ***
            $clean = preg_replace(
                [
                    // indexes
                    '/,\s*\n\s*(PRIMARY KEY|UNIQUE KEY|KEY|FULLTEXT KEY|SPATIAL KEY)\s+.*?(?=\n\)|,\n\s*(PRIMARY KEY|UNIQUE KEY|KEY|FULLTEXT KEY|SPATIAL KEY)|\n\)\s*ENGINE)/si',
                    // fks
                    '/,\s*\n\s*CONSTRAINT\s+`[^`]+`\s+FOREIGN KEY\s*\([^)]+\)\s+REFERENCES\s+`[^`]+`\s*\([^)]+\)(?:[^,\n]*)?(?=\n\)|,\n\s*CONSTRAINT|\n\)\s*ENGINE)/si',
                ],
                '',
                $createSql
            );

            // إزالة "NOT NULL AUTO_INCREMENT" من تعريف الأعمدة
            $clean = preg_replace('/\bNOT\s+NULL\s+AUTO_INCREMENT\b/i', 'NOT NULL', $clean);

            // إزالة "AUTO_INCREMENT=123" من ذيل CREATE (خيارات الجدول)
            $clean = preg_replace('/\sAUTO_INCREMENT=\d+\b/i', '', $clean);

            // إزالة فاصلة زائدة قبل الإغلاق إن ظهرت
            $clean = preg_replace('/,\s*\n\)/', "\n)", $clean);

            // اكتب CREATE نظيف
            $out .= $clean . ";\n\n";

            // 2) بيانات الجدول
            $rows = DB::table($table)->get();
            if ($rows->count() > 0) {
                $out .= "--\n-- Dumping data for table `{$table}`\n--\n\n";
                $first = (array)$rows[0];
                $columns = array_keys($first);
                $colsSql = '`' . implode('`, `', $columns) . '`';

                $chunkSize = 500;
                $rowsArr = $rows->map(fn($r) => (array)$r)->values()->all();
                $chunks = array_chunk($rowsArr, $chunkSize);

                foreach ($chunks as $chunk) {
                    $valsParts = [];
                    foreach ($chunk as $row) {
                        $vals = [];
                        foreach ($columns as $c) {
                            $v = $row[$c] ?? null;
                            if (is_null($v)) {
                                $vals[] = "NULL";
                            } else {
                                $vals[] = "'". str_replace(
                                    ["\\", "\0", "\n", "\r", "'", "\"", "\x1a"],
                                    ["\\\\","\\0","\\n","\\r","\\'","\\\"","\\Z"],
                                    (string)$v
                                ) ."'";
                            }
                        }
                        $valsParts[] = "(" . implode(", ", $vals) . ")";
                    }
                    $out .= "INSERT INTO `{$table}` ({$colsSql}) VALUES\n" . implode(",\n", $valsParts) . ";\n\n";
                }
            }

            // قيمة الـAUTO_INCREMENT الحالية
            $ai = DB::selectOne("
                SELECT AUTO_INCREMENT FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
            ", [$dbName, $table]);
            $autoIncs[$table] = $ai && $ai->AUTO_INCREMENT ? (int)$ai->AUTO_INCREMENT : null;
        }

        // 3) الفهارس + AUTO_INCREMENT
        $out .= "--\n-- Indexes and AUTO_INCREMENT for dumped tables\n--\n\n";
        foreach ($baseTables as $table) {
            $adds = $idxAlters[$table] ?? [];
            if (!empty($adds)) {
                $adds = array_values(array_filter(array_map(function ($add) {
                    return preg_replace('/,+\s*$/', '', trim($add));
                }, $adds)));

                if (empty($adds)) {
                    continue;
                }

                $out .= "ALTER TABLE `{$table}`\n  " . implode(",\n  ", $adds) . ";\n\n";
            }
            if (!empty($autoIncs[$table])) {
                $out .= "ALTER TABLE `{$table}` AUTO_INCREMENT=" . $autoIncs[$table] . ";\n\n";
            }
        }

        // 4) قيود الـFK في الآخر
        if (!empty($fkAlters)) {
            $out .= "--\n-- Constraints for dumped tables\n--\n\n";
            foreach ($fkAlters as $stmt) {
                $out .= $stmt . "\n";
            }
            $out .= "\n";
        }

        // 5) الـVIEWs
        if (!empty($views)) {
            $out .= "-- --------------------------------------------------------\n\n";
            $out .= "--\n-- Views\n--\n\n";
            foreach ($views as $v) {
                $viewRes = DB::select("SHOW CREATE VIEW `{$v}`");
                if (!empty($viewRes)) {
                    $viewSql = $viewRes[0]->{'Create View'} ?? null;
                    if ($viewSql) {
                        $out .= "DROP VIEW IF EXISTS `{$v}`;\n";
                        $out .= $viewSql . ";\n\n";
                    }
                }
            }
        }

        $out .= "COMMIT;\n";
        $out .= "/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;\n";
        $out .= "/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;\n";
        $out .= "/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;\n";
        $out .= "/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;\n";
        $out .= "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n";
        $out .= "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n";
        $out .= "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n";

        return $out;
    }

    public function download($fileName)
    {
        $filePath = $this->backupFolderName . '/' . $fileName;
        if ($this->disk->exists($filePath)) {
            return $this->disk->download($filePath);
        }
        return redirect()->back()->with('error', 'الملف غير موجود.');
    }

    public function destroy(Request $request)
    {
        $request->validate(['backups' => 'required|array']);

        foreach ($request->backups as $fileName) {
            $filePath = $this->backupFolderName . '/' . $fileName;
            if ($this->disk->exists($filePath)) {
                $this->disk->delete($filePath);
            }
        }
        return redirect()->route('admin.backups.index')->with('success', 'تم حذف النسخ الاحتياطية المحددة بنجاح.');
    }

    public function upload(Request $request)
    {
        // Detect post_max_size exceeded — PHP drops the body silently
        $contentLength = (int) $request->server('CONTENT_LENGTH', 0);
        $postMaxBytes  = $this->parsePhpBytes(ini_get('post_max_size'));
        if ($contentLength > 0 && $postMaxBytes > 0 && $contentLength > $postMaxBytes) {
            return redirect()->back()->with('error',
                'فشل في رفع الملف: حجم الملف يتجاوز الحد الأقصى المسموح به (' . ini_get('post_max_size') . ').');
        }

        // Detect upload_max_filesize exceeded
        if (isset($_FILES['backup_file']) && $_FILES['backup_file']['error'] === UPLOAD_ERR_INI_SIZE) {
            return redirect()->back()->with('error',
                'فشل في رفع الملف: حجم الملف يتجاوز الحد الأقصى المسموح به (' . ini_get('upload_max_filesize') . ').');
        }

        try {
            $request->validate([
                'backup_file' => 'required|file'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $messages = implode(' ', $e->validator->errors()->all());
            return redirect()->back()->with('error', 'فشل في رفع الملف: ' . ($messages ?: 'تأكد من اختيار ملف صالح.'));
        }

        $file = $request->file('backup_file');
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, ['zip', 'sql'])) {
            return redirect()->back()->with('error', 'فشل في رفع الملف. تأكد من أن الملف بصيغة .zip أو .sql.');
        }

        $fileName = $file->getClientOriginalName();
        $this->disk->putFileAs($this->backupFolderName, $file, $fileName);

        return redirect()->route('admin.backups.index')->with('success', 'تم رفع ملف النسخة الاحتياطية بنجاح.');
    }

    /** Convert php.ini size string (e.g. "40M") to bytes */
    private function parsePhpBytes(string $val): int
    {
        $val  = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        $num  = (int) $val;
        switch ($last) {
            case 'g': $num *= 1024;
            case 'm': $num *= 1024;
            case 'k': $num *= 1024;
        }
        return $num;
    }

    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|string',
            'replace_data' => 'nullable|string',
            'restore_trash' => 'nullable|string'
        ]);
        
        $fileName = $request->backup_file;
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);

        if (!in_array($extension, ['sql', 'zip'])) {
            return redirect()->back()->with('error', 'الاستعادة ممكنة فقط من ملفات .sql أو .zip حالياً.');
        }

        $filePath = $this->disk->path($this->backupFolderName . '/' . $fileName);
        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'ملف النسخة الاحتياطية غير موجود.');
        }

        try {
            if ($extension === 'zip') {
                $zip = new ZipArchive();
                if ($zip->open($filePath) === TRUE) {
                    $tempDir = storage_path('app/temp_restore_' . time());
                    if (!is_dir($tempDir)) mkdir($tempDir, 0777, true);
                    
                    $zip->extractTo($tempDir);
                    $zip->close();

                    // Search for .sql file
                    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tempDir));
                    $sqlFile = null;
                    foreach ($files as $file) {
                        if ($file->isFile() && $file->getExtension() === 'sql') {
                            $sqlFile = $file->getRealPath();
                            break;
                        }
                    }

                    if (!$sqlFile) {
                        $this->removeDirectory($tempDir);
                        return redirect()->back()->with('error', 'لم يتم العثور على ملف قاعدة بيانات (.sql) داخل ملف الـ zip.');
                    }

                    // Restore DB
                    $this->restoreDatabase($sqlFile);

                    // Restore storage from common backup layouts.
                    $this->restorePublicStorageFromExtractedBackup($tempDir);

                    // Normalize media paths in DB to avoid duplicated URL prefixes like /storage/storage/...
                    $this->normalizeRestoredMediaPaths();

                    $this->removeDirectory($tempDir);
                } else {
                    return redirect()->back()->with('error', 'فشل فتح ملف الـ zip.');
                }
            } else {
                $this->restoreDatabase($filePath);
                $this->normalizeRestoredMediaPaths();
            }

            return redirect()->route('admin.backups.index')->with('success', 'تم استعادة النسخة الاحتياطية بنجاح.');
        } catch (\Exception $e) {
            Log::error("Backup restore failed: " . $e->getMessage());
            return redirect()->back()->with('error', 'فشلت عملية الاستعادة: ' . $e->getMessage());
        }
    }

    private function restoreDatabase($filePath)
    {
        DB::beginTransaction();
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Drop all tables and views
            $dbName = DB::getDatabaseName();
            $tables = DB::select("SHOW FULL TABLES FROM `{$dbName}`");
            $tableKey = "Tables_in_{$dbName}";
            
            foreach ($tables as $table) {
                $name = $table->$tableKey;
                $type = $table->Table_type ?? 'BASE TABLE';
                if (strtoupper($type) === 'VIEW') {
                    DB::statement("DROP VIEW IF EXISTS `{$name}`");
                } else {
                    DB::statement("DROP TABLE IF EXISTS `{$name}`");
                }
            }

            // More robust SQL parsing for large/complex files
            $file = fopen($filePath, 'r');
            $query = '';
            $delimiter = ';';

            while (($line = fgets($file)) !== false) {
                // Skip empty lines and full line comments
                $trimmed = trim($line);
                if ($trimmed === '' || strpos($trimmed, '--') === 0 || strpos($trimmed, '#') === 0 || strpos($trimmed, '/*') === 0) {
                    continue;
                }

                $query .= $line;

                // Check if statement is complete by checking for delimiter at end of line (ignoring trailing whitespace)
                if (preg_match('/' . preg_quote($delimiter, '/') . '\s*$/S', $line)) {
                    try {
                        $queryToRun = $this->sanitizeSqlStatement($query);
                        if (trim($queryToRun) !== '') {
                            DB::unprepared($queryToRun);
                        }
                    } catch (\Exception $qe) {
                        Log::error("SQL Restore Error in query: " . $queryToRun . " Error: " . $qe->getMessage());
                        throw $qe;
                    }
                    $query = '';
                }
            }
            fclose($file);

            // Execute any remaining query
            if (trim($query) !== '') {
                $queryToRun = $this->sanitizeSqlStatement($query);
                if (trim($queryToRun) !== '') {
                    DB::unprepared($queryToRun);
                }
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function removeDirectory($path)
    {
        if (is_dir($path)) {
            $files = array_diff(scandir($path), array('.', '..'));
            foreach ($files as $file) {
                $this->removeDirectory($path . '/' . $file);
            }
            rmdir($path);
        } else {
            unlink($path);
        }
    }

    private function sanitizeSqlStatement(string $sql): string
    {
        $normalized = trim($sql);

        // Legacy backups may contain malformed ALTER TABLE clauses like ",," or ",;".
        if (preg_match('/^\s*ALTER\s+TABLE\b/i', $normalized) === 1) {
            $normalized = preg_replace('/,\s*,+/m', ',', $normalized);
            $normalized = preg_replace('/,\s*;/m', ';', $normalized);
        }

        return $normalized;
    }

    private function copyDirectory($source, $target)
    {
        if (!is_dir($target)) mkdir($target, 0777, true);
        $files = array_diff(scandir($source), array('.', '..'));
        foreach ($files as $file) {
            if (is_dir("$source/$file")) {
                $this->copyDirectory("$source/$file", "$target/$file");
            } else {
                copy("$source/$file", "$target/$file");
            }
        }
    }

    private function restorePublicStorageFromExtractedBackup(string $tempDir): void
    {
        $targetStorage = storage_path('app/public');

        // Support different zip internal layouts from old/manual backups.
        $candidates = [
            $tempDir . '/storage/app/public',
            $tempDir . '/public/storage',
            $tempDir . '/app/public',
            $tempDir . '/storage',
        ];

        foreach ($candidates as $candidate) {
            if ($this->directoryHasEntries($candidate)) {
                $this->copyDirectory($candidate, $targetStorage);
                return;
            }
        }

        Log::warning('No storage directory detected in restored zip backup.', [
            'temp_dir' => $tempDir,
            'candidates' => $candidates,
        ]);
    }

    private function directoryHasEntries(string $dir): bool
    {
        if (!is_dir($dir)) {
            return false;
        }

        $entries = @scandir($dir);
        if ($entries === false) {
            return false;
        }

        foreach ($entries as $entry) {
            if ($entry !== '.' && $entry !== '..') {
                return true;
            }
        }

        return false;
    }

    private function normalizeRestoredMediaPaths(): void
    {
        $targets = [
            ['table' => 'product_images', 'column' => 'image_path', 'allowExternal' => false],
            ['table' => 'product_option_combination_images', 'column' => 'image_path', 'allowExternal' => false],
            ['table' => 'categories', 'column' => 'image', 'allowExternal' => false],
            ['table' => 'primary_categories', 'column' => 'icon', 'allowExternal' => false],
            ['table' => 'primary_categories', 'column' => 'image', 'allowExternal' => false],
            ['table' => 'homepage_slides', 'column' => 'background_image', 'allowExternal' => true],
            ['table' => 'posts', 'column' => 'image', 'allowExternal' => false],
            ['table' => 'users', 'column' => 'avatar', 'allowExternal' => true],
            ['table' => 'managers', 'column' => 'profile_photo_path', 'allowExternal' => true],
            ['table' => 'managers', 'column' => 'housing_card_path', 'allowExternal' => true],
            ['table' => 'managers', 'column' => 'nationality_card_path', 'allowExternal' => true],
        ];

        foreach ($targets as $target) {
            $table = $target['table'];
            $column = $target['column'];
            $allowExternal = (bool) $target['allowExternal'];

            if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column) || !Schema::hasColumn($table, 'id')) {
                continue;
            }

            DB::table($table)
                ->select(['id', $column])
                ->orderBy('id')
                ->chunkById(500, function ($rows) use ($table, $column, $allowExternal) {
                    foreach ($rows as $row) {
                        $original = $row->{$column};
                        $normalized = $this->normalizeMediaPathValue($original, $allowExternal);

                        if ($normalized !== $original) {
                            DB::table($table)
                                ->where('id', $row->id)
                                ->update([$column => $normalized]);
                        }
                    }
                });
        }
    }

    private function normalizeMediaPathValue($value, bool $allowExternal): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);
        if ($normalized === '') {
            return $normalized;
        }

        if ($allowExternal && (str_starts_with($normalized, 'http://') || str_starts_with($normalized, 'https://'))) {
            return $normalized;
        }

        $normalized = ltrim(str_replace('\\', '/', $normalized), '/');

        $prefixes = [
            'storage/app/public/',
            'public/storage/',
            'app/public/',
            'public/',
            'storage/',
        ];

        foreach ($prefixes as $prefix) {
            if (str_starts_with($normalized, $prefix)) {
                $normalized = substr($normalized, strlen($prefix));
            }
        }

        return ltrim($normalized, '/');
    }

    public function settings()
    {
        $settings = Setting::pluck('value', 'key')->all();
        return view('admin.backups.settings', compact('settings'));
    }

    public function storeSettings(Request $request)
    {
        $settingsKeys = [
            'backup_daily_enabled',
            'backup_daily_time',
            'backup_google_drive_enabled',
            'backup_auto_delete_after_days',
            'cron_token'
        ];
        foreach ($settingsKeys as $key) {
            if ($key === 'cron_token' && !$request->has($key)) continue;
            $value = $request->has($key) ? ($request->input($key) === 'on' ? 'on' : $request->input($key)) : 'off';
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        return redirect()->back()->with('success', 'تم حفظ إعدادات النسخ الاحتياطي بنجاح.');
    }

    public function runScheduler(Request $request)
    {
        $token = Setting::getValue('cron_token');
        if (!$token || $request->query('token') !== $token) {
            return response('Unauthorized. Please check your cron token in settings.', 403);
        }

        try {
            Artisan::call('schedule:run');
            return response('Scheduler executed successfully.' . "\n\n" . Artisan::output());
        } catch (\Exception $e) {
            return response('Error running scheduler: ' . $e->getMessage(), 500);
        }
    }
}
