<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // <-- مفقودة سابقًا

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::pluck('value', 'key')->all();
        $settings['maintenance_mode'] = app()->isDownForMaintenance() ? 'on' : 'off';
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $settingsKeys = [
            // واجهة + عامة
            'show_dashboard_notification',
            'dashboard_notification_content',
            'dashboard_notification_animation',
            'show_welcome_screen',
            'welcome_screen_content',
            'maintenance_mode',
            'session_lifetime',
            'referral_reward_percentage',
            'referral_reward_max_amount',
            'shipping_enabled',
            'free_shipping_enabled',
            'shipping_cost',
            'free_shipping_threshold',

            // SEO (عام قديم + جديد متعدد اللغات)
            'site_title',
            'meta_description',
            'site_title_ar',
            'site_title_en',
            'meta_description_ar',
            'meta_description_en',

            // (اختياري مستقبلاً) رابط الأساس للكانونيكال
            'site_url',

            // Telegram Settings
            'telegram_bot_token',
            'telegram_chat_id',
            'telegram_backup_chat_id',
        ];

        foreach ($settingsKeys as $key) {
            if ($key === 'shipping_enabled' || $key === 'free_shipping_enabled') {
                $value = $request->has($key) ? '1' : '0';
            } elseif (str_starts_with($key, 'show_') || $key === 'maintenance_mode') {
                $value = $request->has($key) ? 'on' : 'off';
            } elseif ($key === 'shipping_cost') {
                $value = $request->input($key);
                $value = is_numeric($value) ? max(0, (float) $value) : null;
            } elseif ($key === 'free_shipping_threshold') {
                $value = $request->input($key);
                $value = is_numeric($value) ? max(0, (int) $value) : null;
            } else {
                $value = $request->input($key);
            }
            if (!is_null($value)) {
                Setting::updateOrCreate(['key' => $key], ['value' => $value]);
            }
        }

        // ✅ تفعيل/إيقاف OTP عبر تعديل قيمة OTP_DISABLED في ملف .env
        $otpEnabled = $request->boolean('otp_enabled'); // true إذا السويتش مفعّل
        // إذا مفعّل → OTP_DISABLED=false ، إذا مطفّى → OTP_DISABLED=true
        $this->setEnvironmentValue('OTP_DISABLED', $otpEnabled ? 'false' : 'true');

        // تبديل وضع الصيانة فعليًا
        if ($request->has('maintenance_mode')) {
            Artisan::call('down');
        } else {
            Artisan::call('up');
        }

        return redirect()->back()->with('success', 'تم تحديث الإعدادات بنجاح.');
    }

    /**
     * تحديث أو إضافة قيمة في ملف .env
     */
    protected function setEnvironmentValue(string $key, string $value): void
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            return;
        }

        $env = file_get_contents($envPath);

        $pattern = "/^{$key}=.*$/m";

        if (preg_match($pattern, $env)) {
            // إذا السطر موجود نعدّله
            $env = preg_replace($pattern, "{$key}={$value}", $env);
        } else {
            // إذا غير موجود نضيفه في آخر الملف
            $env .= PHP_EOL . "{$key}={$value}";
        }

        file_put_contents($envPath, $env);
    }

    public function logoutAllUsers()
    {
        DB::table('sessions')->where('user_id', '!=', Auth::id())->delete();
        return redirect()->back()->with('success', 'تم تسجيل خروج جميع المستخدمين الآخرين بنجاح.');
    }
}