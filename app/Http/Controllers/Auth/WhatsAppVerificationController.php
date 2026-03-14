<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class WhatsAppVerificationController extends Controller
{
    /**
     * عرض صفحة إدخال الرمز
     */
    public function show(Request $request)
    {
        // التأكد من أن المستخدم قادم من صفحة التسجيل
        if (!$request->session()->has('phone_for_verification')) {
            return redirect('/register');
        }
        return view('auth.whatsapp-verify');
    }

    /**
     * التحقق من الرمز المدخل
     */
    public function verify(Request $request)
    {
        $request->validate(['otp' => 'required|numeric|digits:6']);

        $phoneNumber = $request->session()->get('phone_for_verification');
        if (!$phoneNumber) {
            return redirect('/register')->withErrors(['otp' => 'انتهت جلسة التحقق، يرجى التسجيل مجدداً.']);
        }

        $user = User::where('phone_number', $phoneNumber)->first();

        if (!$user) {
            return back()->withErrors(['otp' => 'حدث خطأ ما.']);
        }

        // التحقق من صحة الرمز وعدم انتهاء صلاحيته
        if ($user->whatsapp_otp !== $request->otp || Carbon::now()->gt($user->whatsapp_otp_expires_at)) {
            return back()->withErrors(['otp' => 'رمز التحقق غير صحيح أو انتهت صلاحيته.']);
        }

        // تحديث بيانات المستخدم وتفعيل الحساب
        $user->forceFill([
            'phone_verified_at' => Carbon::now(),
            'whatsapp_otp' => null,
            'whatsapp_otp_expires_at' => null,
        ])->save();
        
        // تسجيل دخول المستخدم
        Auth::login($user);
        
        $request->session()->forget('phone_for_verification');

        return redirect('/home')->with('status', 'تم تأكيد رقم هاتفك بنجاح!');
    }
}
