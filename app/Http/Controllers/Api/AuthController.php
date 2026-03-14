<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Manager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pin' => ['required', 'string', 'regex:/^\d{4,8}$/'],
            'device_name' => ['required', 'string', 'max:255'],
        ]);

        $manager = Manager::findByTrackingPin($validated['pin']);

        if (! $manager) {
            throw ValidationException::withMessages([
                'pin' => [__('رمز الدخول غير صحيح أو غير مُفعّل.')],
            ]);
        }

        if (! $manager->hasTrackingPin()) {
            throw ValidationException::withMessages([
                'pin' => [__('لا يملك هذا الموظف رمز دخول مفعل حالياً.')],
            ]);
        }

        // ✅ إنشاء توكن يدوي بدون Sanctum
        $token = bin2hex(random_bytes(32));

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'employee' => [
                'id' => $manager->id,
                'name' => $manager->name,
                'department' => $manager->department ?? null,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        // ماكو حاجة لحذف التوكن من قاعدة بيانات لأنه تولّد يدوي
        return response()->json([
            'message' => __('تم تسجيل الخروج بنجاح.'),
        ], Response::HTTP_OK);
    }

    public function me(Request $request): JsonResponse
    {
        $manager = $request->user();

        return response()->json([
            'id' => $manager->id,
            'name' => $manager->name,
            'department' => $manager->department ?? null,
            'phone_number' => $manager->phone_number ?? null,
            'email' => $manager->email ?? null,
        ]);
    }
}
