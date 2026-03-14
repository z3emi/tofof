<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

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
        return ['database'];
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
}