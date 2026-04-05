<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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

        try {
            $sql = $databaseBackupService->generateSqlDump();

            $disk = Storage::build([
                'driver' => 'local',
                'root'   => storage_path('app'),
            ]);

            if ($directory !== '' && !$disk->exists($directory)) {
                $disk->makeDirectory($directory);
            }

            $absoluteZipPath = $disk->path($relativePath);
            $sqlEntryName = pathinfo($relativePath, PATHINFO_FILENAME) . '.sql';

            $zip = new ZipArchive();
            if ($zip->open($absoluteZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException('تعذر إنشاء ملف ZIP للنسخة الاحتياطية.');
            }

            $zip->addFromString($sqlEntryName, $sql);
            $zip->close();
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
            "نسخة احتياطية ZIP جديدة\nالملف: %s\nالوقت: %s",
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
}
