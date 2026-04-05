<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;
use ZipArchive;

class GenerateDatabaseBackup extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'backup:database-sql {--directory=} {--filename=}';

    /**
     * The console command description.
     */
    protected $description = 'Create a SQL backup that includes schema definitions and data.';

    public function handle(DatabaseBackupService $databaseBackupService, TelegramService $telegramService): int
    {
        $directory = $this->resolveDirectory($this->option('directory'));
        $fileName = $this->resolveFilename($this->option('filename'));
        $relativePath = $directory === '' ? $fileName : $directory . '/' . $fileName;
        $backupFormat = 'ZIP';

        try {
            $sql = $databaseBackupService->generateSqlDump();

            $disk = Storage::build([
                'driver' => 'local',
                'root'   => storage_path('app'),
            ]);

            if ($directory !== '' && !$disk->exists($directory)) {
                $disk->makeDirectory($directory);
            }

            $zipCreated = false;
            if (class_exists(ZipArchive::class)) {
                $absoluteZipPath = $disk->path($relativePath);
                $sqlEntryName = pathinfo($relativePath, PATHINFO_FILENAME) . '.sql';

                $zip = new ZipArchive();
                if ($zip->open($absoluteZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                    $zip->addFromString($sqlEntryName, $sql);
                    $this->addPublicStorageToZip($zip);
                    $zip->close();
                    $zipCreated = true;
                } else {
                    Log::warning('ZIP backup creation failed, falling back to SQL backup.', [
                        'relative_path' => $relativePath,
                    ]);
                }
            } else {
                Log::warning('ZipArchive extension is not available; falling back to SQL backup file.');
            }

            if (!$zipCreated) {
                $relativePath = preg_replace('/\.zip$/i', '.sql', $relativePath) ?: ($relativePath . '.sql');
                $disk->put($relativePath, $sql);
                $backupFormat = 'SQL';
            }
        } catch (Throwable $exception) {
            $this->error('تعذر إنشاء النسخة الاحتياطية: ' . $exception->getMessage());
            Log::error('Automatic SQL backup failed.', [
                'error' => $exception->getMessage(),
            ]);

            return self::FAILURE;
        }

        $absolutePath = storage_path('app/' . $relativePath);
        $this->info("تم إنشاء النسخة الاحتياطية: {$relativePath}");
        $this->line("المسار الكامل: {$absolutePath}");

        $caption = sprintf(
            "نسخة احتياطية %s جديدة\nالملف: %s\nالوقت: %s",
            $backupFormat,
            basename($relativePath),
            now()->format('Y-m-d H:i:s')
        );

        $telegramResult = $telegramService->sendBackupFile($relativePath, $caption);
        if (!((bool) data_get($telegramResult, 'ok', false))) {
            Log::warning('SQL backup created but failed to send to Telegram.', [
                'relative_path' => $relativePath,
                'telegram_response' => data_get($telegramResult, 'response'),
            ]);

            $this->warn('تم إنشاء النسخة الاحتياطية، لكن فشل إرسالها إلى تلغرام.');
        } else {
            $this->info('تم إرسال النسخة الاحتياطية إلى تلغرام بنجاح.');
        }

        return self::SUCCESS;
    }

    private function resolveDirectory(?string $directory): string
    {
        if ($directory !== null && trim($directory) !== '') {
            return trim($directory, '/\\');
        }

        $configured = trim((string) config('backup.backup.folder', config('backup.backup.name', 'backups')));
        $configured = trim($configured, '/\\');

        return $configured === '' ? 'backups' : $configured;
    }

    private function resolveFilename(?string $fileName): string
    {
        $fileName = $fileName !== null && trim($fileName) !== ''
            ? trim($fileName)
            : 'db-backup-' . now()->format('Y-m-d-His') . '.zip';

        if (!str_ends_with(strtolower($fileName), '.zip')) {
            $fileName = pathinfo($fileName, PATHINFO_FILENAME) . '.zip';
        }

        return ltrim($fileName, '/\\');
    }

    private function addPublicStorageToZip(ZipArchive $zip): void
    {
        $publicStoragePath = storage_path('app/public');
        if (!File::isDirectory($publicStoragePath)) {
            return;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($publicStoragePath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        $baseLength = strlen($publicStoragePath);
        foreach ($files as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $absolutePath = $file->getRealPath();
            if (!is_string($absolutePath) || $absolutePath === '') {
                continue;
            }

            $relativePath = 'storage/' . ltrim(substr($absolutePath, $baseLength), '/\\');
            $relativePath = str_replace('\\', '/', $relativePath);

            $zip->addFile($absolutePath, $relativePath);
        }
    }
}
