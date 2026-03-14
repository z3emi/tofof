<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

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

    public function handle(DatabaseBackupService $databaseBackupService): int
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

            $disk->put($relativePath, $sql);
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
            : 'db-backup-' . now()->format('Y-m-d-His') . '.sql';

        return ltrim($fileName, '/\\');
    }
}
