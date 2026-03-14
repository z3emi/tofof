<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\User;
use App\Notifications\NewContactMessageNotification;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ContactController extends Controller
{
    public function __construct(private TelegramService $telegram)
    {
    }

    /**
     * تخزين رسالة اتصل بنا + إرسال إشعار للتليجرام + إشعار للأدمِن
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            // الإيميل اختياري لكن إذا تم إدخاله يجب أن يكون صحيحاً
            'email'       => 'nullable|email|max:255',
            'phone'       => 'required|string|max:30',
            'description' => 'required|string',
        ]);

        // إنشاء السجل
        $message = ContactMessage::create($validated + ['status' => 'new']);

        // إشعار الأدمِن داخل لوحة التحكم (نفس رول الاستشارات حتى يكون أسهل)
        try {
            $adminRoleNames = ['Super-Admin', 'Order-Manager'];
            $admins = User::role($adminRoleNames)->get();

            if ($admins->isNotEmpty()) {
                Notification::send($admins, new NewContactMessageNotification($message));
            }
        } catch (\Throwable $e) {
            Log::error('Failed to send contact message notification: ' . $e->getMessage());
        }

        // إشعار التليجرام
        try {
            $this->telegram->notifyContact($message);
        } catch (\Throwable $e) {
            Log::warning('Telegram contact notification failed: ' . $e->getMessage(), [
                'contact_message_id' => $message->id,
            ]);
        }

        return redirect()
            ->route('contact.success')
            ->with('success', 'تم استلام رسالتج بنجاح، راح يتواصل وياج فريق الدعم بأقرب وقت 🤍');
    }

    /**
     * صفحة نجاح الإرسال
     */
    public function success()
    {
        return view('frontend.pages.contact-success');
    }
}
