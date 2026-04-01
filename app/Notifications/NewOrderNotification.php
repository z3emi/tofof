<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class NewOrderNotification extends Notification
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
        $customerName = $this->order->customer->name ?? 'عميل غير مسجل';
        
        return [
            'order_id'      => $this->order->id,
            'customer_name' => $customerName,
            'icon'          => 'bi-cart-plus', // Bootstrap icon for new order
            'message'       => "طلب جديد #{$this->order->id} من العميل: {$customerName}",
        ];
    }

    public function toWebPush(object $notifiable, ?object $notification = null): WebPushMessage
    {
        $customerName = $this->order->customer->name ?? 'عميل غير مسجل';

        return (new WebPushMessage)
            ->title('طلب جديد #' . $this->order->id)
            ->icon('/icons/icon-192.png')
            ->body('تم استلام طلب جديد من: ' . $customerName)
            ->data([
                'url' => url('/admin/orders/' . $this->order->id),
                'order_id' => $this->order->id,
                'type' => 'new_order',
            ]);
    }
}