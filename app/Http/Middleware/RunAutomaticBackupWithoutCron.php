<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RunAutomaticBackupWithoutCron
{
    private const REQUEST_THROTTLE_KEY = 'backups:auto:request-throttle';
    private const EXECUTION_LOCK_KEY = 'backups:auto:execution-lock';

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldAttemptNow()) {
            $request->attributes->set('run_automatic_backup_without_cron', true);
        }

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if (!$request->attributes->get('run_automatic_backup_without_cron', false)) {
            return;
        }

        if (!Cache::add(self::EXECUTION_LOCK_KEY, 1, now()->addMinutes(30))) {
            return;
        }

        try {
            $exitCode = Artisan::call('backup:database-sql');

            if ($exitCode === 0) {
                Setting::updateOrCreate(
                    ['key' => 'backup_last_auto_run_at'],
                    ['value' => now()->toDateTimeString()]
                );

                return;
            }

            Log::warning('Automatic backup without cron returned a non-zero exit code.', [
                'exit_code' => $exitCode,
                'output' => Artisan::output(),
            ]);
        } catch (Throwable $exception) {
            Log::error('Automatic backup without cron failed.', [
                'error' => $exception->getMessage(),
            ]);
        } finally {
            Cache::forget(self::EXECUTION_LOCK_KEY);
        }
    }

    private function shouldAttemptNow(): bool
    {
        // Reduces database reads and heavy checks to once every minute at most.
        if (!Cache::add(self::REQUEST_THROTTLE_KEY, 1, now()->addMinute())) {
            return false;
        }

        $enabled = Setting::getValue('backup_daily_enabled', 'off') === 'on';
        if (!$enabled) {
            return false;
        }

        $time = Setting::getValue('backup_daily_time', '03:00');
        if (!is_string($time) || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time)) {
            $time = '03:00';
        }

        $now = now();
        $scheduledAt = Carbon::today($now->getTimezone())->setTimeFromTimeString($time);

        if ($now->lt($scheduledAt)) {
            return false;
        }

        $lastRunAt = $this->parseDateTime(Setting::getValue('backup_last_auto_run_at'));

        return !$lastRunAt || !$lastRunAt->isSameDay($now);
    }

    private function parseDateTime($value): ?Carbon
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable $exception) {
            return null;
        }
    }
}