<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\WhatsAppWebService;
use Illuminate\Http\JsonResponse;

class WhatsAppSessionController extends Controller
{
    public function index()
    {
        return view('admin.whatsapp.index');
    }

    public function status(WhatsAppWebService $whatsAppWebService): JsonResponse

    {
        $statusData = $whatsAppWebService->getStatus();
        $status = (string) ($statusData['status'] ?? 'offline');
        $phone = $statusData['phone'] ?? null;
        $lastError = $statusData['last_error'] ?? null;
        $qr = null;

        if ($status !== 'connected') {
            $qr = $whatsAppWebService->getQrDataUrl();
        }

        return response()->json([
            'status' => $status,
            'phone' => $phone,
            'qr' => $qr,
            'last_error' => $lastError,
        ]);
    }

    public function logout(WhatsAppWebService $whatsAppWebService): JsonResponse
    {
        $success = $whatsAppWebService->logout();

        return response()->json([
            'success' => $success,
            'message' => $success
                ? 'تم تسجيل خروج واتساب بنجاح.'
                : 'تعذر تسجيل الخروج من واتساب.',
        ], $success ? 200 : 500);
    }
}
