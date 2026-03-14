<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReferralBonusReceived extends Notification
{
    use Queueable;

    public $bonusAmount;
    public $fromUserName;

    /**
     * Create a new notification instance.
     */
    public function __construct($bonusAmount, $fromUserName)
    {
        $this->bonusAmount = $bonusAmount;
        $this->fromUserName = $fromUserName;
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
        return [
            'bonus_amount' => $this->bonusAmount,
            'from_user_name' => $this->fromUserName,
            'icon' => 'bi-gift-fill',
            'message' => "لقد استلمت مكافأة بقيمة {$this->bonusAmount} د.ع لدعوتك للمستخدم {$this->fromUserName}",
            'url' => route('wallet.index'), // Link to the user's wallet
        ];
    }
}