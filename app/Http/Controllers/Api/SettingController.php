<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class SettingController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => __('تم جلب البيانات بنجاح.'),
            'data' => [
                'language' => 'ar',
                'notifications_enabled' => true,
                'timezone' => 'Asia/Bahrain',
            ],
        ], Response::HTTP_OK);
    }
}
