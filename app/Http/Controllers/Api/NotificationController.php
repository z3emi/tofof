<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user || ! method_exists($user, 'notifications')) {
            return response()->json([
                'success' => false,
                'message' => __('المستخدم الحالي لا يدعم الإشعارات.'),
                'data' => [],
            ], Response::HTTP_FORBIDDEN);
        }

        $limit = (int) $request->input('limit', 20);
        $limit = max(1, min($limit, 100));

        $notifications = $user->notifications()
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn ($notification) => [
                'id' => $notification->id,
                'type' => $notification->type,
                'title' => data_get($notification->data, 'title'),
                'body' => data_get($notification->data, 'message', data_get($notification->data, 'body')),
                'data' => $notification->data,
                'read' => $notification->read_at !== null,
                'read_at' => optional($notification->read_at)?->toIso8601String(),
                'created_at' => optional($notification->created_at)?->toIso8601String(),
            ])
            ->values();

        return response()->json([
            'success' => true,
            'message' => __('تم جلب الإشعارات بنجاح.'),
            'data' => $notifications,
            'meta' => [
                'unread_count' => (int) $user->unreadNotifications()->count(),
            ],
        ], Response::HTTP_OK);
    }
}
