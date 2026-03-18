<?php

namespace App\Providers;

use Throwable;
use App\Models\Order;
use App\Models\Setting;
use App\Observers\OrderObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->repairMigrationsTableForConsole();

        // 1) روابط الصفحات Bootstrap 5
        Paginator::useBootstrapFive();
        // 2) صلاحية شاملة للـ Super-Admin
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super-Admin') ? true : null;
        });

        // 3) ضبط مدة الجلسة من الإعدادات (إن وجدت)
        try {
            $lifetime = Setting::where('key', 'session_lifetime')->value('value');
            if ($lifetime) {
                Config::set('session.lifetime', (int) $lifetime);
            }
        } catch (\Exception $e) {
            // تجاهل لو جدول الإعدادات غير موجود
        }

        // 4) مشاركة إعدادات الإشعارات مع layouts.app
        View::composer('layouts.app', function ($view) {
            try {
                $settings = Setting::whereIn('key', [
                    'show_dashboard_notification',
                    'dashboard_notification_content',
                    'dashboard_notification_animation',
                    'show_welcome_screen',
                    'welcome_screen_content',
                ])->pluck('value', 'key');

                $view->with($settings->all());
            } catch (\Exception $e) {
                // تجاهل أثناء التثبيت أو عدم وجود الجدول
            }
        });

        // 5) ملاحظة إنشاء الطلبات لإرسال إشعار تيليجرام
        Order::observe(OrderObserver::class);
    }

    private function repairMigrationsTableForConsole(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $command = $_SERVER['argv'][1] ?? null;

        if (! is_string($command) || ! str_starts_with($command, 'migrate')) {
            return;
        }

        try {
            if (! Schema::hasTable('migrations') || ! Schema::hasColumn('migrations', 'id')) {
                return;
            }

            $databaseName = DB::getDatabaseName();

            $idColumn = DB::selectOne(
                'SELECT COLUMN_KEY, EXTRA FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?',
                [$databaseName, 'migrations', 'id']
            );

            if (! $idColumn) {
                return;
            }

            if (($idColumn->COLUMN_KEY ?? '') !== 'PRI') {
                $primaryKey = DB::selectOne(
                    'SELECT COUNT(*) AS aggregate FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_TYPE = ?',
                    [$databaseName, 'migrations', 'PRIMARY KEY']
                );

                if ((int) ($primaryKey->aggregate ?? 0) === 0) {
                    DB::statement('ALTER TABLE `migrations` ADD PRIMARY KEY (`id`)');
                }
            }

            $extra = strtolower((string) ($idColumn->EXTRA ?? ''));

            if (! str_contains($extra, 'auto_increment')) {
                $nextId = ((int) DB::table('migrations')->max('id')) + 1;

                DB::statement('ALTER TABLE `migrations` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
                DB::statement('ALTER TABLE `migrations` AUTO_INCREMENT = ' . max($nextId, 1));
            }
        } catch (Throwable) {
            // Ignore schema repair failures here and allow the original command error to surface.
        }
    }
}
