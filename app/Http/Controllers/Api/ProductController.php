<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => __('تم جلب البيانات بنجاح.'),
            'data' => [
                [
                    'id' => 501,
                    'name' => 'حاسوب محمول احترافي',
                    'price' => 4200.00,
                    'stock' => 12,
                ],
                [
                    'id' => 502,
                    'name' => 'هاتف ذكي متطور',
                    'price' => 1899.99,
                    'stock' => 35,
                ],
            ],
        ], Response::HTTP_OK);
    }
}
