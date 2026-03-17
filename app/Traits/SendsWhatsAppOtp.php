<?php

namespace App\Traits;

use App\Services\WhatsAppWebService;
use Illuminate\Support\Facades\Log;

trait SendsWhatsAppOtp
{
    /**
     * دالة لإرسال رسالة واتساب مع متغيرات النص والزر
     */
    protected function sendOtpViaWhatsApp($recipientPhoneNumber, $otp)
    {
        // ⛔️ إذا الـ OTP متعطّل، لا ترسل أي شيء
        if (env('OTP_DISABLED', false)) {
            Log::info('OTP disabled – skipping WhatsApp send', [
                'phone' => $recipientPhoneNumber,
                'otp'   => $otp,
            ]);
            return;
        }

        $message = "رمز التحقق الخاص بك: {$otp}";
        $sent = app(WhatsAppWebService::class)->sendMessage((string) $recipientPhoneNumber, $message);

        if (!$sent) {
            Log::error('WhatsApp Web OTP send failed', [
                'phone' => $recipientPhoneNumber,
            ]);
        }
    }
}