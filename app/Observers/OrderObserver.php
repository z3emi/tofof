<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class OrderObserver
{
    /** نفّذ بعد الـ Commit (لو الإنشاء داخل ترانزاكشن) */
    public bool $afterCommit = true;

/**
     * عند إنشاء الطلب: أرسل الرسالة وخزّن message_id إن أمكن
     */
    public function created(Order $order): void
    {
        \Illuminate\Support\Facades\Log::info('[OrderObserver "created"] Method called for new order.', ['order_id' => $order->id]);
        try {
            $relations = ['customer', 'user'];
            if (method_exists($order, 'items'))      $relations[] = 'items.product';
            if (method_exists($order, 'orderItems')) $relations[] = 'orderItems.product';
            if ($relations) $order->loadMissing($relations);

            /** @var \App\Services\TelegramService $tg */
            $tg = app(\App\Services\TelegramService::class);

            $canStoreMsgId = \Illuminate\Support\Facades\Schema::hasColumn('orders', 'telegram_message_id');
            
            if ($canStoreMsgId) {
                \Illuminate\Support\Facades\Log::info('[OrderObserver "created"] Attempting to send message and get ID.', ['order_id' => $order->id]);
                $messageId = $tg->sendOrderAndReturnMessageId($order);
                \Illuminate\Support\Facades\Log::info('[OrderObserver "created"] Received response from Telegram.', ['returned_message_id' => $messageId]);

                if ($messageId) {
                    \Illuminate\Support\Facades\Log::info('[OrderObserver "created"] Message ID is valid. Saving to order.', ['message_id' => $messageId]);
                    $order->telegram_message_id = $messageId;
                    $order->saveQuietly();
                    \Illuminate\Support\Facades\Log::info('[OrderObserver "created"] saveQuietly() executed. ID should be saved now.', ['order_id' => $order->id]);
                } else {
                    \Illuminate\Support\Facades\Log::warning('[OrderObserver "created"] Did not receive a valid message_id. Falling back to simple send.', ['order_id' => $order->id]);
                    $tg->sendOrder($order);
                }
            } else {
                \Illuminate\Support\Facades\Log::warning('[OrderObserver "created"] Column "telegram_message_id" does not exist in orders table.', ['order_id' => $order->id]);
                $tg->sendOrder($order);
            }
        } catch (\Throwable $e) {
            report($e);
            \Illuminate\Support\Facades\Log::error('[OrderObserver "created"] An error occurred.', ['error' => $e->getMessage()]);
        }
    }

    /** بعد التحديث */
    public function updated(Order $order): void
    {
        if (!$order->wasChanged('status')) {
            return;
        }

        try {
            Log::info('[OrderObserver] Status was changed. Proceeding to edit Telegram message.', [
                'order_id' => $order->id,
                'old_status' => $order->getOriginal('status'),
                'new_status' => $order->status,
            ]);

            $relations = ['customer', 'user'];
            if (method_exists($order, 'items'))      $relations[] = 'items.product';
            if (method_exists($order, 'orderItems')) $relations[] = 'orderItems.product';
            if ($relations) $order->loadMissing($relations);

            $tg = app(TelegramService::class);
            $messageId = (int) ($order->telegram_message_id ?? 0);
            if ($messageId > 0) {
                $tg->editOrderMessage($order);
            } else {
                Log::warning('[OrderObserver] Order status changed, but no message_id found to edit.', [
                    'order_id' => $order->id
                ]);
            }
        } catch (Throwable $e) {
            report($e);
        }
    }
    /**
     * دالة لتشغيل منطق التحديث بشكل مباشر وإجباري من المتحكم
     * تتجاوز التحقق من دالة wasChanged()
     */
    public function forceRunUpdate(Order $order): void
    {
        try {
            Log::info('[OrderObserver] Force Run: Proceeding to edit Telegram message.', [
                'order_id' => $order->id,
                'current_status' => $order->status,
            ]);

            $relations = ['customer', 'user'];
            if (method_exists($order, 'items'))      $relations[] = 'items.product';
            if (method_exists($order, 'orderItems')) $relations[] = 'orderItems.product';
            if ($relations) $order->loadMissing($relations);

            /** @var \App\Services\TelegramService $tg */
            $tg = app(\App\Services\TelegramService::class);

            $messageId = (int) ($order->telegram_message_id ?? 0);

            if ($messageId > 0) {
                $tg->editOrderMessage($order);
                Log::info('[OrderObserver] Force Run: Edit message request sent.', ['order_id' => $order->id]);
            } else {
                Log::warning('[OrderObserver] Force Run: No message_id found to edit.', [
                    'order_id' => $order->id
                ]);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}