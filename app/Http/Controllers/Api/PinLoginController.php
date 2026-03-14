<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Auth\PinAuthenticationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class PinLoginController extends Controller
{
    public function __construct(private readonly PinAuthenticationService $authService)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        if (! $request->isMethod('post')) {
            return response()->json([
                'message' => __('يرجى استخدام طلب من نوع POST لتسجيل الدخول عبر رمز الدخول.'),
            ], Response::HTTP_METHOD_NOT_ALLOWED);
        }

        $validated = $request->validate([
            'pin' => ['required', 'string', 'min:3', 'max:64'],
        ]);

        $employee = $this->authService->findEmployeeByPin($validated['pin']);

        if (! $employee) {
            return response()->json([
                'message' => __('رمز الدخول غير صحيح أو غير مرتبط بأي موظف.'),
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (! $this->authService->employeeHasPin($employee)) {
            return response()->json([
                'message' => __('لا يملك هذا الموظف رمز دخول فعالاً.'),
            ], Response::HTTP_BAD_REQUEST);
        }

        [$token, $tokenType] = $this->authService->createApiToken($employee);

        $department = Arr::first([
            $employee->getAttribute('department'),
            $employee->getAttribute('department_name'),
            $employee->getAttribute('department_id'),
        ], fn ($value) => ! is_null($value));

        $name = $employee->getAttribute('name');

        if (! $name) {
            $fullName = (string) $employee->getAttribute('full_name');
            $trimmed = Str::of($fullName)->trim()->toString();
            $name = $trimmed !== '' ? $trimmed : null;
        }

        return response()->json([
            'token' => $token,
            'token_type' => $tokenType,
            'employee' => [
                'id' => $employee->getKey(),
                'name' => $name,
                'department' => $department,
            ],
        ]);
    }
}
