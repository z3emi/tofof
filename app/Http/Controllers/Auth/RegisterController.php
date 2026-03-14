<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Customer;
use App\Models\WalletTransaction;
use App\Traits\SendsWhatsAppOtp;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Services\WalletService;

class RegisterController extends Controller
{
    use RegistersUsers, SendsWhatsAppOtp;

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:20', 'unique:users,phone_number'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'referral_code' => ['nullable', 'string', 'exists:users,referral_code'],
        ]);
    }

 public function register(Request $request)
{
    $this->validator($request->all())->validate();
    
    $referrer = null;
    if ($request->filled('referral_code')) {
        $referrer = User::where('referral_code', $request->referral_code)->first();
    }

    $otp = random_int(100000, 999999);

    $user = User::create([
        'name' => $request->name,
        'phone_number' => $request->phone_number,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'avatar' => 'avatars/default.png',
        'referred_by' => $referrer ? $referrer->id : null,
        'whatsapp_otp' => $otp,
        'whatsapp_otp_expires_at' => Carbon::now()->addMinutes(10),
        'referral_reward_claimed' => false, // مهم لتتبع المكافأة
        'referrer_bonus_awarded' => false, // ✅ [إضافة] لضمان عدم حصول الداعي على مكافأة بعد
    ]);

    $user->assignRole('user');

    // ✅ [تعديل] إضافة مكافأة التسجيل للمدعو فوراً
    if ($referrer) {
        WalletService::creditRegistrationBonus($user);
        // تحديث الحالة لمنع منح المكافأة مرة أخرى
        $user->update(['referral_reward_claimed' => true]);
    }

    // Send OTP to the new user
    $this->sendOtpViaWhatsApp($user->phone_number, $otp);
    $this->linkCustomerToUser($user);

    $request->session()->put('phone_for_verification', $user->phone_number);

    return redirect()->route('otp.verification.show')
                     ->with('status', 'تم إرسال رمز التحقق إلى رقم هاتفك عبر واتساب.');
}

    protected function linkCustomerToUser(User $user)
    {
        $customer = Customer::where('phone_number', $user->phone_number)->first();

        if ($customer && is_null($customer->user_id)) {
            $customer->update(['user_id' => $user->id, 'name' => $user->name, 'email' => $user->email]);
        } elseif (!$customer) {
            Customer::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'phone_number' => $user->phone_number,
                'email' => $user->email,
            ]);
        }
    }
}