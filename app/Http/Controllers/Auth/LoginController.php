<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\SendsWhatsAppOtp;
use App\Services\PersistentLoginService; // ✅ خدمتنا المخصصة للتذكّر
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuthenticatesUsers, SendsWhatsAppOtp;

    /**
     * بعد تسجيل الدخول الناجح
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * نحقن خدمة التذكّر المخصصة هنا
     */
    public function __construct(protected PersistentLoginService $persistent)
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * تسجيل الدخول مع فحص حالة التفعيل بالـOTP
     */
    public function login(Request $request)
    {
        $resolvedUser = $this->findUserByPhoneInput((string) $request->input($this->username(), ''));

        // نوحّد قيمة رقم الهاتف بالصيغة المخزنة فعلياً إن وجد المستخدم
        if ($resolvedUser) {
            $request->merge([$this->username() => (string) $resolvedUser->phone_number]);
        } else {
            $request->merge([$this->username() => $this->normalizePhoneNumber((string) $request->input($this->username(), ''))]);
        }

        $this->validateLogin($request);

        // --- فحص التفعيل قبل محاولة تسجيل الدخول القياسية ---
        $user = $resolvedUser ?: User::where($this->username(), $request->input($this->username()))->first();

        if ($user && Hash::check($request->password, $user->password)) {
            if ($user->banned_at) {
                throw ValidationException::withMessages([
                    $this->username() => 'تم حظر حسابك. يرجى التواصل مع الدعم إذا كنت تعتقد أن هذا خطأ.'
                ]);
            }
            // إذا الحساب غير مفعّل بالهاتف → أرسل OTP وحوّل لصفحة التحقق
            if (is_null($user->phone_verified_at)) {
                $otp = random_int(100000, 999999);

                $user->update([
                    'whatsapp_otp' => $otp,
                    'whatsapp_otp_expires_at' => Carbon::now()->addMinutes(10),
                ]);

                $this->sendOtpViaWhatsApp($user->phone_number, $otp);

                // نخزّن رقم الهاتف للصفحة القادمة + حالة "تذكّرني" ليُطبَّق بعد التفعيل
                $request->session()->put('phone_for_verification', $user->phone_number);
                $request->session()->put('remember_after_otp', $request->boolean('remember'));

                return redirect()->route('otp.verification.show')
                    ->with('status', 'حسابك غير مفعل. لقد أرسلنا رمز تحقق جديد إلى واتساب.');
            }
        }
        // --- Maintenance Mode Check ---
        if (\App\Models\Setting::isMaintenanceMode()) {
            throw ValidationException::withMessages([
                $this->username() => 'الموقع في وضع الصيانة حالياً. يرجى المحاولة لاحقاً.'
            ]);
        }
        // ------------------------------

        // حماية من محاولات كثيرة
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }

        // محاولة الدخول الاعتيادية
        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        // فشل → نزيد العدّاد ونرجع رد الفشل
        $this->incrementLoginAttempts($request);
        return $this->sendFailedLoginResponse($request);
    }

    /**
     * نحدّد أن اسم المستخدم هو رقم الهاتف
     */
    public function username()
    {
        return 'phone_number';
    }

    protected function credentials(Request $request)
    {
        return [
            'phone_number' => $request->input('phone_number'),
            'password' => $request->input('password'),
            'banned_at' => null,
        ];
    }

    /**
     * نثبّت تمرير فلاج "remember" كـ boolean صريح
     */
    protected function attemptLogin(Request $request)
    {
        return $this->guard()->attempt(
            $this->credentials($request),
            $request->boolean('remember')
        );
    }

    /**
     * Normalize user-entered phone number to a consistent numeric format.
     */
    private function normalizePhoneNumber(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        // شائع محلياً: 96407xxxxxxxx -> 9647xxxxxxxx
        if (str_starts_with($digits, '9640')) {
            $digits = '964' . substr($digits, 4);
        }

        return $digits;
    }

    /**
     * Find a user by trying common phone representations.
     */
    private function findUserByPhoneInput(string $phoneInput): ?User
    {
        $normalized = $this->normalizePhoneNumber($phoneInput);
        $candidates = collect([$phoneInput, $normalized]);

        if ($normalized !== '' && str_starts_with($normalized, '0')) {
            $candidates->push(ltrim($normalized, '0'));
        }

        if ($normalized !== '') {
            $candidates->push('+' . $normalized);
        }

        $phones = $candidates
            ->map(fn ($v) => trim((string) $v))
            ->filter(fn ($v) => $v !== '')
            ->unique()
            ->values()
            ->all();

        if (empty($phones)) {
            return null;
        }

        return User::whereIn('phone_number', $phones)->first();
    }

    /**
     * ما يحدث بعد نجاح attemptLogin
     * - نجدد الجلسة
     * - ننظف محاولات الفشل
     * - نصدر/نلغي كوكي التذكّر المخصص حسب اختيار المستخدم
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();
        $this->clearLoginAttempts($request);

        // ✅ إذا المستخدم معلّم "تذكّرني" نصدر توكننا المخصص
        if ($request->boolean('remember') && Auth::check()) {
            $this->persistent->issue(
                Auth::id(),
                $request->ip(),
                (string) $request->userAgent()
            );
        } else {
            // إذا مو معلّم، ننظّف أي كوكي قديم (احتياط)
            $this->persistent->invalidateCurrentCookie();
        }

        return $this->authenticated($request, Auth::user())
                ?: redirect()->intended($this->redirectPath());
    }

    /**
     * تسجيل الخروج:
     * - نمسح جميع سجلات "تذكّرني" للمستخدم + الكوكي
     * - نسوي لوغ آوت عالـguard
     * - نبطل الجلسة
     */
    public function logout(Request $request)
    {
        if (Auth::check()) {
            $this->persistent->invalidateForUser(Auth::id()); // ✅ مسح كل التذكّر
        }

        $this->guard()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $this->loggedOut($request) ?: redirect('/');
    }
}
