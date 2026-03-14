<?php

namespace App\Console;

use App\Models\Setting;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Throwable;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('backup:clean')->daily()->at('01:00');
        $schedule->command('backup:run --only-db')->daily()->at('02:00');

        $sqlBackupSchedule = $this->resolveAutomaticSqlBackupSchedule();
        if ($sqlBackupSchedule['enabled']) {
            $schedule->command('backup:database-sql')
                ->dailyAt($sqlBackupSchedule['time'])
                ->onOneServer()
                ->withoutOverlapping();
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    /**
     * @return array{enabled: bool, time: string}
     */
    private function resolveAutomaticSqlBackupSchedule(): array
    {
        $defaultTime = '03:00';

        try {
            $settings = Setting::whereIn('key', ['backup_daily_enabled', 'backup_daily_time'])
                ->pluck('value', 'key')
                ->all();
        } catch (Throwable $exception) {
            return ['enabled' => false, 'time' => $defaultTime];
        }

        $enabled = ($settings['backup_daily_enabled'] ?? 'off') === 'on';
        $time = $settings['backup_daily_time'] ?? $defaultTime;

        if (!is_string($time) || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time)) {
            $time = $defaultTime;
        }

        return [
            'enabled' => $enabled,
            'time' => $time,
        ];
    }
}
