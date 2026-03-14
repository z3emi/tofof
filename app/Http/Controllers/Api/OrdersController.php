<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class OrdersController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => __('تم جلب البيانات بنجاح.'),
            'data' => [
                [
                    'id' => 101,
                    'customer' => 'شركة الأفق التجاري',
                    'total' => 2500.75,
                    'status' => 'مكتمل',
                ],
                [
                    'id' => 102,
                    'customer' => 'مؤسسة الرؤية الحديثة',
                    'total' => 980.00,
                    'status' => 'قيد التنفيذ',
                ],
            ],
        ], Response::HTTP_OK);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => __('تم جلب البيانات بنجاح.'),
            'data' => [
                'id' => $id,
                'customer' => 'شركة الأفق التجاري',
                'items' => [
                    ['name' => 'منتج 1', 'quantity' => 2, 'price' => 500.00],
                    ['name' => 'منتج 2', 'quantity' => 1, 'price' => 1500.75],
                ],
                'total' => 2500.75,
                'status' => 'مكتمل',
            ],
        ], Response::HTTP_OK);
    }
}
