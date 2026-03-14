<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\SendsWhatsAppOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    use SendsWhatsAppOtp;

    /**
     * Display the password reset request form expected by Laravel's auth routes.
     *
     * Laravel still registers the default "password.request" route which calls
     * this method. We redirect users to the custom phone based reset flow so
     * that the framework does not throw a missing method exception.
     */
    public function showLinkRequestForm()
    {
        return redirect()->route('password.reset.phone.form');
    }

    /**
     * عرض صفحة إدخال رقم الهاتف لطلب إعادة التعيين.
     */
    public function showResetPhoneForm()
    {
        // تجديد رمز CSRF في كل زيارة لتفادي انتهاء صلاحية الجلسة السابقة
        session()->regenerateToken();

        return view('auth.passwords.phone');
    }

    /**
     * التحقق من رقم الهاتف وإرسال رمز التحقق.
     */
    public function sendOtp(Request $request)
    {
        $request->validate(['phone_number' => 'required|exists:users,phone_number']);

        $user = User::where('phone_number', $request->phone_number)->first();

        // لا تسمح بإعادة التعيين لحساب غير مفعل
        if (is_null($user->phone_verified_at)) {
            return back()->withErrors(['phone_number' => 'هذا الحساب غير مفعل. يرجى إكمال عملية التسجيل أولاً.']);
        }

        $otp = random_int(100000, 999999);
        $user->update([
            'whatsapp_otp' => $otp,
            'whatsapp_otp_expires_at' => Carbon::now()->addMinutes(10),
        ]);

        $this->sendOtpViaWhatsApp($user->phone_number, $otp);

        // تم تغيير التوجيه ليحمل رقم الهاتف في الجلسة بدلاً من الرابط
        return redirect()->route('password.reset.otp.form')
            ->with(['phone_number_for_reset' => $user->phone_number, 'status' => 'تم إرسال رمز إعادة التعيين إلى واتساب.']);
    }

    /**
     * عرض صفحة إدخال الرمز وكلمة السر الجديدة.
     */
    public function showResetFormWithOtp(Request $request)
    {
        // التأكد من أن المستخدم قادم من الخطوة السابقة
        if (!session('phone_number_for_reset')) {
            return redirect()->route('password.reset.phone.form')->withErrors(['phone_number' => 'يرجى طلب رمز جديد أولاً.']);
        }
        return view('auth.passwords.reset_with_otp')->with(
            ['phone_number' => session('phone_number_for_reset')]
        );
    }

    /**
     * التحقق من الرمز وتحديث كلمة السر.
     */
    public function resetPasswordWithOtp(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|exists:users,phone_number',
            'otp' => 'required|numeric|digits:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::where('phone_number', $request->phone_number)->first();

        if (!$user || $user->whatsapp_otp !== $request->otp || Carbon::now()->isAfter($user->whatsapp_otp_expires_at)) {
            return back()->withErrors(['otp' => 'رمز التحقق غير صحيح أو انتهت صلاحيته.']);
        }

        $user->password = Hash::make($request->password);
        $user->whatsapp_otp = null;
        $user->whatsapp_otp_expires_at = null;
        $user->save();

        return redirect()->route('login')->with('status', 'تم تغيير كلمة المرور بنجاح. يمكنك الآن تسجيل الدخول.');
    }
}
