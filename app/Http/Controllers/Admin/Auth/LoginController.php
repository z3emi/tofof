<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Manager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest:admin')->except('logout');
    }

    public function showLoginForm()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $this->ensureSuperAdminAccountExists();

        $loginValue = trim($data['login']);

        $manager = Manager::query()
            ->where('phone_number', $loginValue)
            ->orWhere('email', $loginValue)
            ->orWhere('name', $loginValue)
            ->first();

        if (!$manager || !Hash::check($data['password'], $manager->getAuthPassword())) {
            throw ValidationException::withMessages([
                'login' => 'بيانات الدخول غير صحيحة.',
            ]);
        }

        if ($manager->banned_at) {
            throw ValidationException::withMessages([
                'login' => 'تم حظر هذا الحساب. يرجى التواصل مع الإدارة.',
            ]);
        }

        // --- Maintenance Mode Check ---
        if (\App\Models\Setting::isMaintenanceMode() && !$manager->isSuperAdmin()) {
            throw ValidationException::withMessages([
                'login' => 'الموقع في وضع الصيانة حالياً. تسجيل الدخول متاح فقط للمدير العام.',
            ]);
        }
        // ------------------------------

        Auth::guard('admin')->login($manager, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    protected function ensureSuperAdminAccountExists(): void
    {
        if (!Schema::hasTable('managers')) {
            return;
        }

        $phone = (string) config('admin.super_admin_phone', 'admin');
        $email = (string) config('admin.super_admin_email', 'admin@tofof.test');
        $password = (string) config('admin.super_admin_password', 'admin');

        $manager = Manager::firstOrCreate(
            ['phone_number' => $phone],
            [
                'name' => 'Super Admin',
                'email' => $email,
                'password' => $password,
                'phone_verified_at' => now(),
            ]
        );

        try {
            if (Schema::hasTable('roles') && !Role::where('name', 'Super-Admin')->where('guard_name', 'admin')->exists()) {
                Role::create([
                    'name' => 'Super-Admin',
                    'guard_name' => 'admin',
                ]);
            }

            if (!$manager->isSuperAdmin()) {
                $manager->assignRole('Super-Admin');
            }
        } catch (\Throwable $e) {
            // تجاهل أي أخطاء في حال كانت الجداول غير متاحة أثناء التثبيت الأولي.
        }
    }
}
