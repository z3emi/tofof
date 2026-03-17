<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WhatsAppWebService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.whatsapp_web.url', env('WHATSAPP_SERVICE_URL', 'http://localhost:3001')), '/');
        $this->apiKey = (string) config('services.whatsapp_web.key', env('WHATSAPP_SERVICE_KEY', ''));
    }

    public function getStatus(): array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(10)
                ->get($this->baseUrl . '/api/status');

            if (!$response->ok()) {
                Log::warning('WhatsApp Web status request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'status' => 'offline',
                    'phone' => null,
                ];
            }

            $payload = $response->json();

            return [
                'status' => (string) data_get($payload, 'status', 'offline'),
                'phone' => data_get($payload, 'phone'),
                'last_error' => data_get($payload, 'lastError'),
            ];
        } catch (Throwable $e) {
            report($e);

            Log::warning('WhatsApp Web status exception', [
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => 'offline',
                'phone' => null,
                'last_error' => $e->getMessage(),
            ];
        }
    }

    public function getQrDataUrl(): ?string
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(10)
                ->get($this->baseUrl . '/api/qr');

            if (!$response->ok()) {
                return null;
            }

            $payload = $response->json();

            return data_get($payload, 'qr');
        } catch (Throwable $e) {
            report($e);

            Log::warning('WhatsApp Web QR exception', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function sendMessage(string $phone, string $message): bool
    {
        $chatId = $this->toChatId($phone);

        if (!$chatId || trim($message) === '') {
            return false;
        }

        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(15)
                ->post($this->baseUrl . '/api/send', [
                    'phone' => $chatId,
                    'message' => $message,
                ]);

            if (!$response->ok()) {
                Log::warning('WhatsApp Web send failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'phone' => $phone,
                ]);

                return false;
            }

            return (bool) data_get($response->json(), 'success', false);
        } catch (Throwable $e) {
            report($e);

            Log::warning('WhatsApp Web send exception', [
                'error' => $e->getMessage(),
                'phone' => $phone,
            ]);

            return false;
        }
    }

    public function logout(): bool
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(15)
                ->post($this->baseUrl . '/api/logout');

            if (!$response->ok()) {
                Log::warning('WhatsApp Web logout failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            return (bool) data_get($response->json(), 'success', false);
        } catch (Throwable $e) {
            report($e);

            Log::warning('WhatsApp Web logout exception', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    protected function headers(): array
    {
        if ($this->apiKey === '') {
            return [];
        }

        return [
            'X-API-Key' => $this->apiKey,
            'Accept' => 'application/json',
        ];
    }

    protected function toChatId(string $phone): ?string
    {
        $normalized = preg_replace('/\D+/', '', $phone) ?? '';

        if ($normalized === '') {
            return null;
        }

        if (str_starts_with($normalized, '00')) {
            $normalized = substr($normalized, 2);
        }

        if (str_starts_with($normalized, '0')) {
            $normalized = '964' . ltrim($normalized, '0');
        }

        return $normalized . '@c.us';
    }
}
