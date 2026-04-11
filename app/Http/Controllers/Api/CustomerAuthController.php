<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\RepairsPrimaryKeyAutoIncrement;
use App\Traits\SendsWhatsAppOtp;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class CustomerAuthController extends Controller
{
    use SendsWhatsAppOtp;

    /**
     * Register a new customer account
     */
    public function register(Request $request)
    {
        try {
            $request->merge([
                'phone_number' => $this->normalizePhoneNumber((string) $request->input('phone_number', '')),
            ]);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|unique:users,email',
                'phone_number' => 'required|string|unique:users,phone_number',
                'password' => 'required|string|min:8|confirmed',
                'referral_code' => 'nullable|string|exists:users,referral_code',
            ]);

            $referrer = null;
            if (!empty($validated['referral_code'])) {
                $referrer = User::where('referral_code', $validated['referral_code'])->first();
            }

            $otp = random_int(100000, 999999);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'phone_number' => $validated['phone_number'],
                'password' => Hash::make($validated['password']),
                'referral_code' => $this->generateReferralCode(),
                'referred_by' => $referrer?->id,
                'whatsapp_otp' => (string) $otp,
                'whatsapp_otp_expires_at' => Carbon::now()->addMinutes(10),
                'phone_verified_at' => env('OTP_DISABLED', false) ? Carbon::now() : null,
            ]);

            if (!env('OTP_DISABLED', false)) {
                $this->sendOtpViaWhatsApp($user->phone_number, $otp);

                return response()->json([
                    'success' => true,
                    'message' => 'تم إنشاء الحساب وإرسال رمز التحقق عبر واتساب.',
                    'data' => [
                        'otp_required' => true,
                        'phone_number' => $user->phone_number,
                    ],
                ], 201);
            }

            $token = $this->issueAccessToken($user, 'mobile-app');

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء الحساب بنجاح',
                'data' => [
                    'otp_required' => false,
                    'user' => $user->only(['id', 'name', 'email', 'phone_number', 'avatar']),
                    'token' => $token,
                ]
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في البيانات المدخلة',
                'errors' => $e->errors()
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'تعذر إكمال التسجيل حاليًا. يرجى المحاولة مرة أخرى.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Login with phone number and password
     */
    public function login(Request $request)
    {
        try {
            $request->merge([
                'phone_number' => $this->normalizePhoneNumber((string) $request->input('phone_number', '')),
            ]);

            $validated = $request->validate([
                'phone_number' => 'required|string|max:32',
                'password' => 'required|string',
            ]);

            $user = $this->findUserByPhoneInput($validated['phone_number']);

            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'بيانات دخول غير صحيحة',
                ], 401);
            }

            if ($user->banned_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'تم حظر هذا الحساب',
                ], 403);
            }

            if (is_null($user->phone_verified_at)) {
                if (!env('OTP_DISABLED', false)) {
                    $otp = random_int(100000, 999999);
                    $user->forceFill([
                        'whatsapp_otp' => (string) $otp,
                        'whatsapp_otp_expires_at' => Carbon::now()->addMinutes(10),
                    ])->save();

                    $this->sendOtpViaWhatsApp($user->phone_number, $otp);

                    return response()->json([
                        'success' => true,
                        'message' => 'الحساب غير مفعل. تم إرسال رمز واتساب.',
                        'data' => [
                            'otp_required' => true,
                            'phone_number' => $user->phone_number,
                        ],
                    ], 202);
                }

                $user->forceFill([
                    'phone_verified_at' => Carbon::now(),
                    'whatsapp_otp' => null,
                    'whatsapp_otp_expires_at' => null,
                ])->save();
            }

            $token = $this->issueAccessToken($user, 'mobile-app');

            return response()->json([
                'success' => true,
                'message' => 'تم الدخول بنجاح',
                'data' => [
                    'otp_required' => false,
                    'user' => $user->only(['id', 'name', 'email', 'phone_number', 'avatar', 'wallet_balance']),
                    'token' => $token,
                ]
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في البيانات المدخلة',
                'errors' => $e->errors()
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'تعذر تسجيل الدخول حاليًا. يرجى المحاولة مرة أخرى.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Request a WhatsApp OTP for login or registration.
     */
    public function requestOtp(Request $request)
    {
        try {
            $validated = $request->validate([
                'phone_number' => 'required|string|max:32',
                'purpose' => 'nullable|in:login,register',
            ]);

            $phoneNumber = $this->normalizePhoneNumber($validated['phone_number']);
            $purpose = $validated['purpose'] ?? 'login';

            $user = $this->findUserByPhoneInput($phoneNumber);

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يوجد حساب بهذا الرقم.',
                ], 404);
            }

            $otp = random_int(100000, 999999);

            $user->forceFill([
                'whatsapp_otp' => (string) $otp,
                'whatsapp_otp_expires_at' => Carbon::now()->addMinutes(10),
            ])->save();

            $this->sendOtpViaWhatsApp($user->phone_number, $otp);

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال رمز التحقق عبر واتساب.',
                'data' => [
                    'otp_required' => true,
                    'phone_number' => $user->phone_number,
                    'purpose' => $purpose,
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في البيانات المدخلة',
                'errors' => $e->errors(),
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'تعذر إرسال رمز التحقق حاليًا.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Verify a WhatsApp OTP and issue a Sanctum token.
     */
    public function verifyOtp(Request $request)
    {
        try {
            $validated = $request->validate([
                'phone_number' => 'required|string|max:32',
                'otp' => 'required|digits:6',
            ]);

            $phoneNumber = $this->normalizePhoneNumber($validated['phone_number']);
            $user = $this->findUserByPhoneInput($phoneNumber);

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'هذا الرقم غير مسجل.',
                ], 404);
            }

            if (! $user->whatsapp_otp || ! $user->whatsapp_otp_expires_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يوجد رمز تحقق صالح لهذا الرقم. أعد طلب الرمز.',
                ], 422);
            }

            if ($user->whatsapp_otp !== $validated['otp'] || Carbon::now()->greaterThan($user->whatsapp_otp_expires_at)) {
                return response()->json([
                    'success' => false,
                    'message' => 'رمز التحقق غير صحيح أو انتهت صلاحيته.',
                ], 422);
            }

            $user->forceFill([
                'phone_verified_at' => Carbon::now(),
                'whatsapp_otp' => null,
                'whatsapp_otp_expires_at' => null,
            ])->save();

            $token = $this->issueAccessToken($user, 'mobile-app');

            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل الدخول بنجاح.',
                'data' => [
                    'otp_required' => false,
                    'user' => $user->only(['id', 'name', 'email', 'phone_number', 'avatar', 'wallet_balance', 'phone_verified_at', 'referral_code']),
                    'token' => $token,
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في البيانات المدخلة',
                'errors' => $e->errors(),
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'تعذر تأكيد رمز التحقق حاليًا.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Request a WhatsApp OTP for password reset.
     */
    public function requestPasswordResetOtp(Request $request)
    {
        try {
            $validated = $request->validate([
                'phone_number' => 'required|string|max:32',
            ]);

            $phoneNumber = $this->normalizePhoneNumber($validated['phone_number']);
            $user = $this->findUserByPhoneInput($phoneNumber);

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يوجد حساب بهذا الرقم.',
                ], 404);
            }

            if (is_null($user->phone_verified_at)) {
                return response()->json([
                    'success' => false,
                    'message' => 'هذا الحساب غير مفعل بعد.',
                ], 422);
            }

            $otp = random_int(100000, 999999);

            $user->forceFill([
                'whatsapp_otp' => (string) $otp,
                'whatsapp_otp_expires_at' => Carbon::now()->addMinutes(10),
            ])->save();

            $this->sendOtpViaWhatsApp($user->phone_number, $otp);

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال رمز إعادة تعيين كلمة المرور عبر واتساب.',
                'data' => [
                    'phone_number' => $user->phone_number,
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في البيانات المدخلة',
                'errors' => $e->errors(),
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'تعذر إرسال رمز إعادة التعيين حاليًا.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Reset password using the WhatsApp OTP.
     */
    public function resetPassword(Request $request)
    {
        try {
            $validated = $request->validate([
                'phone_number' => 'required|string|max:32',
                'otp' => 'required|digits:6',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $phoneNumber = $this->normalizePhoneNumber($validated['phone_number']);
            $user = $this->findUserByPhoneInput($phoneNumber);

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'هذا الرقم غير مسجل.',
                ], 404);
            }

            if (! $user->whatsapp_otp || ! $user->whatsapp_otp_expires_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يوجد رمز صالح لإعادة التعيين. أعد طلب الرمز.',
                ], 422);
            }

            if ($user->whatsapp_otp !== $validated['otp'] || Carbon::now()->greaterThan($user->whatsapp_otp_expires_at)) {
                return response()->json([
                    'success' => false,
                    'message' => 'رمز التحقق غير صحيح أو انتهت صلاحيته.',
                ], 422);
            }

            $user->forceFill([
                'password' => bcrypt($validated['password']),
                'whatsapp_otp' => null,
                'whatsapp_otp_expires_at' => null,
            ])->save();

            return response()->json([
                'success' => true,
                'message' => 'تم تغيير كلمة المرور بنجاح. يمكنك تسجيل الدخول الآن.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في البيانات المدخلة',
                'errors' => $e->errors(),
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'تعذر إعادة تعيين كلمة المرور حاليًا.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get current authenticated user
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user->only([
                    'id', 'name', 'email', 'phone_number', 'avatar',
                    'phone_verified_at', 'wallet_balance', 'referral_code'
                ]),
            ]
        ], 200);
    }

    /**
     * Logout and revoke current token
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الخروج بنجاح',
        ], 200);
    }

    private function normalizePhoneNumber(string $phoneNumber): string
    {
        $normalized = preg_replace('/\D+/', '', $phoneNumber) ?? '';

        if (str_starts_with($normalized, '00')) {
            $normalized = substr($normalized, 2);
        }

        if (str_starts_with($normalized, '0')) {
            $normalized = '964' . ltrim($normalized, '0');
        }

        return $normalized;
    }

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
     * Generate unique referral code
     */
    private function generateReferralCode()
    {
        do {
            $code = strtoupper(\Illuminate\Support\Str::random(8));
        } while (User::where('referral_code', $code)->exists());

        return $code;
    }

    private function issueAccessToken(User $user, string $name, array $abilities = ['*']): string
    {
        // Prefer native Sanctum token creation to guarantee compatibility with auth:sanctum.
        try {
            $token = $user->createToken($name, $abilities);
            if (!empty($token->plainTextToken)) {
                return $token->plainTextToken;
            }
        } catch (Throwable $exception) {
            // Fall back to manual insertion logic below for legacy schema edge cases.
        }

        if (! Schema::hasTable('personal_access_tokens')) {
            Schema::create('personal_access_tokens', function ($table) {
                $table->id();
                $table->morphs('tokenable');
                $table->string('name');
                $table->string('token', 64)->unique();
                $table->text('abilities')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        }

        $plainTextToken = Str::random(40);

        try {
            $tokenId = $this->insertAccessTokenRecord($user, $name, $abilities, $plainTextToken);
        } catch (QueryException $exception) {
            if (! RepairsPrimaryKeyAutoIncrement::isMissingAutoIncrementError($exception, 'personal_access_tokens')) {
                throw $exception;
            }

            RepairsPrimaryKeyAutoIncrement::ensure('personal_access_tokens');
            $tokenId = $this->insertAccessTokenRecord($user, $name, $abilities, $plainTextToken);
        }

        return $tokenId.'|'.$plainTextToken;
    }

    private function insertAccessTokenRecord(User $user, string $name, array $abilities, string $plainTextToken): int
    {
        return DB::table('personal_access_tokens')->insertGetId([
            'tokenable_type' => $user::class,
            'tokenable_id' => $user->getKey(),
            'name' => $name,
            'token' => hash('sha256', $plainTextToken),
            'abilities' => json_encode($abilities, JSON_UNESCAPED_UNICODE),
            'last_used_at' => null,
            'expires_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
