<?php

namespace App\Notifications;

use App\Models\DiscountCode;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class DiscountCodeAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public DiscountCode $discountCode,
        public bool $sendDatabase = true,
        public bool $sendPush = true
    )
    {
    }

    public function via(object $notifiable): array
    {
        $channels = [];

        if ($this->sendDatabase) {
            $channels[] = 'database';
        }

        if ($this->sendPush) {
            $channels[] = WebPushChannel::class;
        }

        return $channels;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'discount_code',
            'icon' => 'bi-ticket-perforated',
            'url' => route('profile.discounts'),
            'discount_code_id' => $this->discountCode->id,
            'code' => $this->discountCode->code,
            'message' => 'تم إرسال كود خصم جديد لك: ' . $this->discountCode->code,
            'expires_at' => optional($this->discountCode->expires_at)?->toDateTimeString(),
        ];
    }

    public function toWebPush(object $notifiable, ?object $notification = null): WebPushMessage
    {
        return (new WebPushMessage)
            ->title('كود خصم جديد')
            ->icon('/icons/icon-192.png')
            ->body('تمت إضافة كود خصم لحسابك: ' . $this->discountCode->code)
            ->data([
                'url' => route('profile.discounts'),
                'discount_code_id' => $this->discountCode->id,
                'type' => 'discount_code',
            ]);
    }
}
