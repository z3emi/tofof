<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class NotificationController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => __('تم جلب البيانات بنجاح.'),
            'data' => [
                [
                    'id' => 1,
                    'title' => 'تنبيه مهم',
                    'body' => 'يرجى مراجعة الطلبات الجديدة التي وصلت اليوم.',
                    'read' => false,
                ],
                [
                    'id' => 2,
                    'title' => 'تذكير دوري',
                    'body' => 'تم تحديث إعدادات نظام الفوترة الأسبوع الماضي.',
                    'read' => true,
                ],
            ],
        ], Response::HTTP_OK);
    }
}
