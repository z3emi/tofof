<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CustomerController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => __('تم جلب البيانات بنجاح.'),
            'data' => [
                [
                    'id' => 301,
                    'name' => 'شركة الريادة للتسويق',
                    'phone' => '+97312345678',
                    'city' => 'المنامة',
                ],
                [
                    'id' => 302,
                    'name' => 'مؤسسة البناء الحديث',
                    'phone' => '+97387654321',
                    'city' => 'الرفاع',
                ],
            ],
        ], Response::HTTP_OK);
    }
}
