<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => __('تم جلب البيانات بنجاح.'),
            'data' => [
                [
                    'title' => 'تقرير المبيعات الشهري',
                    'period' => 'يوليو 2026',
                    'summary' => 'زيادة في المبيعات بنسبة 18٪ مقارنة بالشهر السابق.',
                ],
                [
                    'title' => 'تقرير أداء الفريق',
                    'period' => 'الربع الثاني 2026',
                    'summary' => 'تحقيق 92٪ من الأهداف التشغيلية المخطط لها.',
                ],
            ],
        ], Response::HTTP_OK);
    }
}
