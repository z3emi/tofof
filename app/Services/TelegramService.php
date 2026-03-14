<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;
use App\Models\ContactMessage;

class TelegramService
{
    protected string $token;
    protected string $chatId;
    /**
     * @var array<int, string>
     */
    protected array $chatIds = [];
    protected string $parseMode;

    public function __construct()
    {
        // اقرأ من config حتى يشتغل مع config:cache
        $this->token     = (string) config('services.telegram.bot_token', env('TELEGRAM_BOT_TOKEN'));
        $rawChatId       = (string) config('services.telegram.chat_id', env('TELEGRAM_CHAT_ID'));
        $this->chatIds   = $this->parseChatIds($rawChatId);
        $this->chatId    = $this->chatIds[0] ?? $rawChatId;
        $this->parseMode = (string) config('services.telegram.parse_mode', env('TELEGRAM_PARSE_MODE', 'HTML'));
    }

    /**
     * حول الحقول النصية إلى قائمة مع دعم الفواصل والأسطر الجديدة.
     *
     * @return array<int, string>
     */
    protected function parseChatIds(string $value): array
    {
        $normalized = preg_replace("/\r\n?/", "\n", trim($value));
        if ($normalized === '') {
            return [];
        }

        $parts = preg_split('/[\s,]+/', $normalized) ?: [];

        $parts = array_map(static fn ($part) => trim($part), $parts);
        $parts = array_filter($parts, static fn ($part) => $part !== '');

        return array_values(array_unique($parts));
    }

    /** إرسال نص عام — يرجّع ok و message_id */
    public function sendMessage(string $text, ?array $inlineKeyboard = null): array
    {
        $chatIds = $this->chatIds ?: array_filter([$this->chatId]);

        if (empty($chatIds)) {
            Log::warning('Telegram sendMessage skipped: no chat id configured.');

            return ['ok' => false, 'message_id' => null, 'response' => null];
        }

        $firstMessageId = null;
        $anySuccess = false;
        $responses = [];

        foreach ($chatIds as $chatId) {
            $payload = [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => $this->parseMode,
                'disable_web_page_preview' => true,
            ];

            if ($inlineKeyboard) {
                $payload['reply_markup'] = json_encode(['inline_keyboard' => $inlineKeyboard], JSON_UNESCAPED_UNICODE);
            }

            try {
                $res = Http::withOptions(['force_ip_resolve' => 'v4'])
                    ->asForm()->timeout(15)
                    ->post("https://api.telegram.org/bot{$this->token}/sendMessage", $payload);

                $json = $res->json();
                $ok = $res->ok() && data_get($json, 'ok') === true;

                if ($ok && !$anySuccess) {
                    $anySuccess = true;
                    $firstMessageId = (int) data_get($json, 'result.message_id');
                }

                if (!$ok) {
                    Log::warning('Telegram sendMessage failed', [
                        'http_status' => $res->status(),
                        'response'    => $json,
                        'payload'     => [
                            'chat_id' => $chatId,
                            'text_len' => mb_strlen($text, 'UTF-8'),
                            'has_keyboard' => (bool) $inlineKeyboard,
                        ],
                    ]);
                }

                $responses[] = [
                    'chat_id' => $chatId,
                    'status' => $res->status(),
                    'body' => $json,
                ];
            } catch (Throwable $e) {
                report($e);

                Log::warning('Telegram sendMessage exception', [
                    'chat_id' => $chatId,
                    'error' => $e->getMessage(),
                ]);

                $responses[] = [
                    'chat_id' => $chatId,
                    'status' => null,
                    'body' => null,
                ];
            }
        }

        return [
            'ok' => $anySuccess,
            'message_id' => $anySuccess ? $firstMessageId : null,
            'response' => count($responses) === 1 ? $responses[0] : $responses,
        ];
    }

    protected function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public function notifyContact(ContactMessage $message): bool
    {
        $text = $this->formatContactMessage($message);

        if (trim($text) === '') {
            return false;
        }

        $result = $this->sendMessage($text);
        $ok = (bool) data_get($result, 'ok', false);

        if (!$ok) {
            Log::warning('Telegram contact notification not delivered.', [
                'contact_message_id' => $message->id,
                'response' => $result,
            ]);
        }

        return $ok;
    }

    public function formatContactMessage(ContactMessage $message): string
    {
        $lines = [];

        $lines[] = '💌 <b>رسالة جديدة من صفحة اتصل بنا</b>';
        $lines[] = '• الاسم: <b>' . $this->escape($message->name ?? '') . '</b>';
        $lines[] = '• البريد: <code>' . $this->escape($message->email ?? '') . '</code>';

        if (!empty($message->phone)) {
            $lines[] = '• الهاتف: <code>' . $this->escape($message->phone) . '</code>';
        }

        if (!empty($message->description)) {
            $desc = mb_substr((string) $message->description, 0, 450);
            if (mb_strlen((string) $message->description) > 450) {
                $desc .= '…';
            }
            $lines[] = '• الرسالة: ' . $this->escape($desc);
        }

        if (!empty($message->created_at)) {
            $timezone = config('app.timezone') ?: 'UTC';
            $lines[] = '— ' . $message->created_at->timezone($timezone)->format('Y-m-d H:i');
        }

        return implode("\n", array_filter($lines));
    }


    /** تعديل نص رسالة معينة — يعتبر "message is not modified" نجاح */
    public function editMessageText(int $messageId, string $newText, ?array $inlineKeyboard = null): bool
    {
        $chatId = $this->chatIds[0] ?? $this->chatId;
        if (!$chatId) {
            return false;
        }

        $payload = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $newText,
            'parse_mode' => $this->parseMode,
            'disable_web_page_preview' => true,
        ];
        if ($inlineKeyboard) {
            $payload['reply_markup'] = json_encode(['inline_keyboard' => $inlineKeyboard], JSON_UNESCAPED_UNICODE);
        }

        try {
            $res = Http::withOptions(['force_ip_resolve' => 'v4'])
                ->asForm()->timeout(15)
                ->post("https://api.telegram.org/bot{$this->token}/editMessageText", $payload);

            $json = $res->json();
            $ok = $res->ok() && data_get($json, 'ok') === true;

            if (!$ok) {
                $desc = (string) data_get($json, 'description', '');
                if (stripos($desc, 'message is not modified') !== false) {
                    return true; // اعتبره نجاح
                }
                Log::warning('Telegram editMessageText failed', [
                    'http_status' => $res->status(),
                    'response'    => $json,
                    'message_id'  => $messageId,
                    'chat_id'     => $chatId,
                ]);
            }

            return $ok;
        } catch (Throwable $e) {
            report($e);
            return false;
        }
    }

    /** يبني نص رسالة الطلب */
    public function formatOrderMessage($order): string
    {
            \Illuminate\Support\Facades\Log::info('[TelegramService] Formatting message for order.', [
        'order_id' => $order->id,
        'order_status_received' => $order->status, // هذا أهم حقل
    ]);
        // تحميل العلاقات الموجودة فقط
        $relations = ['customer', 'user'];
        if (method_exists($order, 'items'))      $relations[] = 'items.product';
        if (method_exists($order, 'orderItems')) $relations[] = 'orderItems.product';
        if ($relations) $order->loadMissing($relations);

        // اجمع العناصر من أي علاقة متوفرة
        $items = collect();
        if (method_exists($order, 'items')) {
            $items = $items->merge($order->relationLoaded('items') ? $order->items : $order->items()->get());
        }
        if (method_exists($order, 'orderItems')) {
            $items = $items->merge($order->relationLoaded('orderItems') ? $order->orderItems : $order->orderItems()->get());
        }
        $items = $items->filter();

        // بيانات عامة
        $orderId     = $order->id;
        $name        = e(optional($order->customer)->name ?? '—');
        $phone       = e(optional($order->customer)->phone_number ?? '—');
        $governorate = e($order->governorate ?? '—');
        $city        = e($order->city ?? '—');
        $notes       = e($order->notes ?? '—');
        $status      = $order->status ?? 'pending';
        $created     = $order->created_at ? $order->created_at->format('Y-m-d H:i') : '—';

        // تسميات + إيموجي الحالة
        $statusLabels = [
            'pending'    => 'قيد الانتظار',
            'processing' => 'قيد المعالجة',
            'shipped'    => 'تم الشحن',
            'delivered'  => 'تم التوصيل',
            'cancelled'  => 'ملغي',
            'returned'   => 'مرتجع',
        ];
        $statusEmojis = [
            'pending'    => '🟡',
            'processing' => '🔵',
            'shipped'    => '📦',
            'delivered'  => '🟢',
            'cancelled'  => '⚫️',
            'returned'   => '🔴',
        ];
        $statusLabel = $statusLabels[$status] ?? $status;
        $stateIcon   = $statusEmojis[$status] ?? '🔔';

        // حساب الإجمالي
        $itemsSubtotal = $items->sum(fn($it) => (float)($it->price ?? $it->unit_price ?? 0) * (int)($it->quantity ?? 1));
        $discount   = (float)($order->discount_amount ?? 0);
        $shipping   = (float)($order->shipping_cost ?? 0);
        $walletUsed = (float)($order->wallet_used_amount ?? $order->wallet_amount_used ?? $order->wallet_applied ?? 0);

        $grossTotal = $itemsSubtotal > 0
            ? max(0, ($itemsSubtotal - $discount) + $shipping)
            : max(0, (float)($order->total_amount ?? 0) + $walletUsed);

        // بناء الرسالة
        $lines = [];
        $lines[] = "🆕 <b>طلب جديد</b>";
        $lines[] = "رقم الطلب: <b>#{$orderId}</b>";
        $lines[] = "الحالة: {$stateIcon} <b>{$statusLabel}</b>";
        $lines[] = "التاريخ: <b>{$created}</b>";
        $lines[] = "— — — — — —";
        $lines[] = "<b>العميل</b>";
        $lines[] = "الاسم: <b>{$name}</b>";
        $lines[] = "الهاتف: <b>{$phone}</b>";
        $lines[] = "المحافظة: <b>{$governorate}</b>";
        $lines[] = "المدينة: <b>{$city}</b>";
        $lines[] = "ملاحظات: <b>{$notes}</b>";
        $lines[] = "— — — — — —";
        $lines[] = "<b>المنتجات:</b>";

        if ($items->count()) {
            foreach ($items as $it) {
                $pname = e($it->product_name ?? optional($it->product)->name ?? 'منتج');
                $qty   = (int)($it->quantity ?? 1);
                $price = number_format($it->price ?? $it->unit_price ?? 0, 0);
                $lines[] = "• {$pname} × {$qty} — {$price} د.ع";
            }
        } else {
            $lines[] = "• (لا توجد عناصر مسجّلة للطلب حتى الآن)";
        }

        $lines[] = "— — — — — —";
        $lines[] = "الإجمالي: <b>" . number_format($grossTotal, 0) . " د.ع</b>";

        $text = implode("\n", $lines);
        if (mb_strlen($text, 'UTF-8') > 4000) $text = mb_substr($text, 0, 3990, 'UTF-8') . "\n…";

        return $text;
    }

    /**
     * إرسال رسالة الطلب وإرجاع message_id لتخزينه بالطلب (من أول محادثة ناجحة).
     */
    public function sendOrderAndReturnMessageId($order): ?int
    {
        $text = $this->formatOrderMessage($order);
        $adminUrl = rtrim(config('app.url', env('APP_URL', '')), '/') . '/admin/orders/' . $order->id;
        $buttons = [[['text' => '👀 فتح الطلب بلوحة الإدارة', 'url' => $adminUrl]]];

        $res = $this->sendMessage($text, $buttons);
        return $res['ok'] ? ($res['message_id'] ?? null) : null;
    }

    /** تعديل رسالة الطلب الموجودة (محاولة مع/بدون أزرار) — نسخة واحدة فقط */
    public function editOrderMessage($order): bool
    {
        $messageId = (int) ($order->telegram_message_id ?? 0);
        if (!$messageId) return false;

        $text = $this->formatOrderMessage($order);
        $adminUrl = rtrim(config('app.url', env('APP_URL', '')), '/') . '/admin/orders/' . $order->id;
        $buttons = [[['text' => '👀 فتح الطلب بلوحة الإدارة', 'url' => $adminUrl]]];

        // المحاولة الأولى: مع الأزرار
        $ok = $this->editMessageText($messageId, $text, $buttons);
        if ($ok) return true;

        // المحاولة الثانية: بدون أزرار
        $ok2 = $this->editMessageText($messageId, $text, null);
        if (!$ok2) {
            Log::warning('Telegram editOrderMessage failed twice (with & without keyboard)', [
                'order_id'   => $order->id,
                'message_id' => $messageId,
            ]);
        }
        return $ok2;
    }

    /** إرسال سريع (بدون إرجاع id) كخطة بديلة */
    public function sendOrder($order): bool
    {
        $text = $this->formatOrderMessage($order);
        $adminUrl = rtrim(config('app.url', env('APP_URL', '')), '/') . '/admin/orders/' . $order->id;
        $buttons = [[['text' => '👀 فتح الطلب بلوحة الإدارة', 'url' => $adminUrl]]];

        $res = $this->sendMessage($text, $buttons);
        return $res['ok'] === true;
    }
}
