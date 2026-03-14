<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\SendsWhatsAppOtp;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OtpVerificationController extends Controller
{
    use SendsWhatsAppOtp;

    public function show(Request $request)
    {
        if (!$request->session()->has('phone_for_verification')) {
            return redirect()->route('register');
        }

        // ✅ إذا الـ OTP متعطّل، نفعل الحساب مباشرة بدون عرض صفحة الرمز
        if (env('OTP_DISABLED', false)) {
            return $this->autoVerifyAndLogin($request);
        }

        return view('auth.verify-otp');
    }

    public function verify(Request $request)
    {
        // ✅ إذا الـ OTP متعطل، نتجاهل حقل otp ونفعل الحساب مباشرة
        if (env('OTP_DISABLED', false)) {
            return $this->autoVerifyAndLogin($request);
        }

        $request->validate(['otp' => 'required|numeric|digits:6']);
        $phoneNumber = $request->session()->get('phone_for_verification');
        
        if (!$phoneNumber) {
            return redirect()->route('register')->withErrors(['otp' => 'انتهت جلسة التحقق، يرجى المحاولة مجدداً.']);
        }

        $user = User::where('phone_number', $phoneNumber)->firstOrFail();

        if ($user->phone_verified_at) {
            return redirect()->route('login')->withErrors(['otp' => 'تم تفعيل هذا الحساب مسبقاً.']);
        }

        if ($user->whatsapp_otp !== $request->otp || Carbon::now()->isAfter($user->whatsapp_otp_expires_at)) {
            return back()->withErrors(['otp' => 'رمز التحقق غير صحيح أو انتهت صلاحيته.']);
        }

        $user->forceFill([
            'phone_verified_at'       => Carbon::now(),
            'whatsapp_otp'            => null,
            'whatsapp_otp_expires_at' => null,
        ])->save();
        
        Auth::login($user);
        $request->session()->forget('phone_for_verification');

        return redirect('/home')->with('status', 'تم تأكيد حسابك بنجاح!');
    }

    /**
     * دالة جديدة لإعادة إرسال الرمز
     */
    public function resend(Request $request)
    {
        // ✅ إذا الـ OTP متعطّل، نرجع رسالة عادية بدون إرسال أي شيء
        if (env('OTP_DISABLED', false)) {
            return response()->json([
                'message' => 'تم تعطيل التحقق عبر واتساب مؤقتاً، لست بحاجة لرمز جديد.'
            ], 200);
        }

        $phoneNumber = $request->session()->get('phone_for_verification');
        if (!$phoneNumber) {
            return response()->json(['message' => 'انتهت الجلسة.'], 408);
        }

        $user = User::where('phone_number', $phoneNumber)->firstOrFail();
        
        // التحقق من مرور 60 ثانية على الأقل منذ آخر إرسال
        // الرمز صالح لـ 10 دقائق (600 ثانية). نمنع الإرسال إذا كان الوقت المتبقي أكثر من 9 دقائق (540 ثانية)
        if ($user->whatsapp_otp_expires_at && $user->whatsapp_otp_expires_at->diffInSeconds(now()) > 540) {
            return response()->json(['message' => 'يرجى الانتظار 60 ثانية قبل طلب رمز جديد.'], 429);
        }

        $otp = random_int(100000, 999999);
        $user->update([
            'whatsapp_otp'            => $otp,
            'whatsapp_otp_expires_at' => Carbon::now()->addMinutes(10),
        ]);

        $this->sendOtpViaWhatsApp($user->phone_number, $otp);

        return response()->json(['message' => 'تم إرسال رمز تحقق جديد بنجاح.']);
    }

    /**
     * ✅ دالة داخلية لتفعيل الحساب وتسجيل الدخول بدون الحاجة للرمز
     */
    protected function autoVerifyAndLogin(Request $request)
    {
        $phoneNumber = $request->session()->get('phone_for_verification');

        if (!$phoneNumber) {
            return redirect()->route('register')
                ->withErrors(['otp' => 'انتهت جلسة التحقق، يرجى التسجيل مجدداً.']);
        }

        $user = User::where('phone_number', $phoneNumber)->first();

        if (!$user) {
            return redirect()->route('register')
                ->withErrors(['otp' => 'حدث خطأ أثناء إنشاء الحساب.']);
        }

        // إذا كان متفعّل مسبقاً، ندخله مباشرة
        if ($user->phone_verified_at) {
            Auth::login($user);
            $request->session()->forget('phone_for_verification');
            return redirect('/home');
        }

        // تفعيل الحساب بدون التحقق من الرمز
        $user->forceFill([
            'phone_verified_at'       => Carbon::now(),
            'whatsapp_otp'            => null,
            'whatsapp_otp_expires_at' => null,
        ])->save();

        Auth::login($user);
        $request->session()->forget('phone_for_verification');

        return redirect('/home')->with('status', 'تم إنشاء حسابك بدون الحاجة إلى رمز التحقق.');
    }
}