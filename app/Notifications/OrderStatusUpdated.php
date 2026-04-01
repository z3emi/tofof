<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class OrderStatusUpdated extends Notification
{
    use Queueable;

    public function __construct(public Order $order)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', WebPushChannel::class];
    }

    public function toArray(object $notifiable): array
    {
        $statusLabels = [
            'pending' => 'قيد الانتظار',
            'processing' => 'قيد المعالجة',
            'shipped' => 'تم الشحن',
            'delivered' => 'تم التوصيل',
            'cancelled' => 'ملغي',
            'returned' => 'مرتجع',
        ];

        $status = (string) $this->order->status;
        $statusLabel = $statusLabels[$status] ?? $status;

        return [
            'order_id' => $this->order->id,
            'status' => $status,
            'icon' => 'bi-truck',
            'url' => route('profile.orders.show', $this->order->id),
            'message' => 'تم تحديث حالة طلبك #' . $this->order->id . ' إلى ' . $statusLabel,
        ];
    }

    public function toWebPush(object $notifiable, ?object $notification = null): WebPushMessage
    {
        $statusLabels = [
            'pending' => 'قيد الانتظار',
            'processing' => 'قيد المعالجة',
            'shipped' => 'تم الشحن',
            'delivered' => 'تم التوصيل',
            'cancelled' => 'ملغي',
            'returned' => 'مرتجع',
        ];

        $status = (string) $this->order->status;
        $statusLabel = $statusLabels[$status] ?? $status;

        return (new WebPushMessage)
            ->title('تحديث الطلب #' . $this->order->id)
            ->icon('/icons/icon-192.png')
            ->body('تم تحديث حالة طلبك إلى: ' . $statusLabel)
            ->data([
                'url' => route('profile.orders.show', $this->order->id),
                'order_id' => $this->order->id,
                'type' => 'order_status',
            ]);
    }

}
