<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // لا تنسَ إضافة هذا السطر

class WhatsAppWebhookController extends Controller
{
    /**
     * هذه الدالة تستقبل الرسائل الواردة من Pipedream
     */
    public function handleIncomingMessage(Request $request)
    {
        // تسجيل كل البيانات القادمة في ملف السجلات للتأكد من أنها تصل
        Log::info('WhatsApp Message Received from Pipedream:', $request->all());

        // يمكنك لاحقاً إضافة الكود لمعالجة الرسالة هنا
        // مثال:
        // $message_body = $request->input('entry.0.changes.0.value.messages.0.text.body');
        // $from_number = $request->input('entry.0.changes.0.value.messages.0.from');
        
        // قم بالرد باستجابة نجاح
        return response()->json(['status' => 'success'], 200);
    }
}