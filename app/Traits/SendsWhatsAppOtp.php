<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
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

        $accessToken   = env('WHATSAPP_ACCESS_TOKEN');
        $phoneNumberId = env('WHATSAPP_PHONE_NUMBER_ID');
        $version       = env('WHATSAPP_VERSION', 'v23.0');
        $templateName  = 'register_otp'; // تأكد أن هذا هو اسم القالب الصحيح

        $response = Http::withToken($accessToken)->post(
            "https://graph.facebook.com/{$version}/{$phoneNumberId}/messages",
            [
                'messaging_product' => 'whatsapp',
                'to'   => $recipientPhoneNumber,
                'type' => 'template',
                'template' => [
                    'name'     => $templateName,
                    'language' => ['code' => 'ar'],
                    'components' => [
                        [
                            'type'       => 'body',
                            'parameters' => [
                                ['type' => 'text', 'text' => (string) $otp],
                            ],
                        ],
                        [
                            'type'     => 'button',
                            'sub_type' => 'url',
                            'index'    => '0',
                            'parameters' => [
                                [
                                    'type' => 'text',
                                    'text' => 'verify',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        if ($response->failed()) {
            Log::error('WhatsApp API Error: ' . $response->body());
        }
    }
}