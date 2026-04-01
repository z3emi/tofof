<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class AdminOrderStatusUpdated extends Notification
{
    use Queueable;

    public $order;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', WebPushChannel::class];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'status'   => $this->order->status,
            'icon'     => 'bi-arrow-repeat', // Icon for status update
            'message'  => "تم تحديث حالة الطلب #{$this->order->id} إلى '{$this->order->status}'",
        ];
    }

    public function toWebPush(object $notifiable, ?object $notification = null): WebPushMessage
    {
        return (new WebPushMessage)
            ->title('تحديث حالة الطلب #' . $this->order->id)
            ->icon('/icons/icon-192.png')
            ->body("تم تحديث حالة الطلب إلى: {$this->order->status}")
            ->data([
                'url' => url('/admin/orders/' . $this->order->id),
                'order_id' => $this->order->id,
                'type' => 'admin_order_status',
            ]);
    }

}