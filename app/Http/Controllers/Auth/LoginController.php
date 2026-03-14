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
        $this->validateLogin($request);

        // --- فحص التفعيل قبل محاولة تسجيل الدخول القياسية ---
        $user = User::where($this->username(), $request->input($this->username()))->first();

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
        // --- نهاية فحص التفعيل ---

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
