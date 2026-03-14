<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Config;
use App\Models\Setting;
use App\Models\Order;
use App\Observers\OrderObserver;

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
}
