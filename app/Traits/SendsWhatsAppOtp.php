<?php

namespace App\Traits;

use App\Services\WhatsAppWebService;
use Illuminate\Support\Facades\Log;
use Throwable;

trait SendsWhatsAppOtp
{
    /**
     * دالة لإرسال رسالة واتساب مع متغيرات النص والزر
     */
    protected function sendOtpViaWhatsApp($recipientPhoneNumber, $otp): bool
    {
        // ⛔️ إذا الـ OTP متعطّل، لا ترسل أي شيء
        if (env('OTP_DISABLED', false)) {
            $this->safeLog('info', 'OTP disabled – skipping WhatsApp send', [
                'phone' => $recipientPhoneNumber,
            ]);
            return true;
        }

        try {
            $message = "رمز التحقق الخاص بك: {$otp}";
            $sent = app(WhatsAppWebService::class)->sendMessage((string) $recipientPhoneNumber, $message);

            if (!$sent) {
                $this->safeLog('error', 'WhatsApp Web OTP send failed', [
                    'phone' => $recipientPhoneNumber,
                ]);
            }

            return $sent;
        } catch (Throwable $e) {
            // Never let OTP/logging transport errors break auth requests.
            $this->safeLog('error', 'WhatsApp OTP unexpected exception', [
                'phone' => $recipientPhoneNumber,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function safeLog(string $level, string $message, array $context = []): void
    {
        try {
            Log::{$level}($message, $context);
        } catch (Throwable) {
            // Ignore logging failures (e.g., storage/logs permission denied).
        }
    }
}