<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CustomerAuthController extends Controller
{
    /**
     * Register a new customer account
     */
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'phone_number' => 'required|string|unique:users,phone_number',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone_number' => $validated['phone_number'],
                'password' => Hash::make($validated['password']),
                'referral_code' => $this->generateReferralCode(),
            ]);

            $token = $user->createToken('mobile-app', ['*'])->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء الحساب بنجاح',
                'data' => [
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
        }
    }

    /**
     * Login with email and password
     */
    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            $user = User::where('email', $validated['email'])->first();

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

            $token = $user->createToken('mobile-app', ['*'])->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'تم الدخول بنجاح',
                'data' => [
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
}
