<?php

namespace App\Http\Controllers;

use App\Models\ProductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;

class ProductRequestController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_name' => ['required','string','max:255'],
            'brand'        => ['nullable','string','max:255'],
            'link'         => ['nullable','url','max:500'],
            'phone'        => ['required','string','max:50'], // الهاتف صار nullable هنا
            'notes'        => ['nullable','string','max:5000'],
        ]);

        $data['user_id'] = Auth::id();

        // ✅ تعديل مهم: إذا المستخدم ما دخل رقم، نحوله لنص فارغ حتى ما يرسل NULL
        if (empty($data['phone'])) {
            $data['phone'] = '';
        }

        $pr = ProductRequest::create($data);

        // ====== Notifications (اختياري) ======
        // 1) Telegram
        try {
            $botToken = config('services.telegram.bot_token');
            $chatId   = config('services.telegram.chat_id');
            if ($botToken && $chatId) {
                $normalized = preg_replace("/\r\n?/", "\n", trim($chatId));
                $chatIds = preg_split('/[\s,]+/', $normalized) ?: [];
                $chatIds = array_values(array_filter(array_map('trim', $chatIds), static fn ($id) => $id !== ''));

                if (empty($chatIds)) {
                    $chatIds = [$chatId];
                }

                $lines = [];
                $lines[] = "🛍️ *طلب منتج غير متوفر*";
                $lines[] = "• المنتج: *{$pr->product_name}*";
                if (!empty($pr->brand)) $lines[] = "• الماركة: {$pr->brand}";
                if (!empty($pr->link))  $lines[] = "• الرابط: {$pr->link}";
                if (!empty($pr->phone)) $lines[] = "• الهاتف/واتساب: `{$pr->phone}`";
                if (!empty($pr->notes)) $lines[] = "• ملاحظات: {$pr->notes}";
                $lines[] = "— الوقت: ".$pr->created_at->format('Y-m-d H:i');

                $text = implode("\n", $lines);

                foreach ($chatIds as $singleChatId) {
                    Http::asForm()->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                        'chat_id' => $singleChatId,
                        'text'    => $text,
                        'parse_mode' => 'Markdown',
                        'disable_web_page_preview' => true,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Telegram notify failed: '.$e->getMessage());
        }

        // 2) Email (اختياري)
        try {
            $to = config('mail.product_request_to') ?? env('PRODUCT_REQUEST_TO');
            if ($to) {
                $subject = 'طلب منتج غير متوفر - Tofof';
                $body = view('emails.product_request_plain', ['pr' => $pr])->render();
                Mail::raw($body, function($m) use ($to, $subject) {
                    $m->to($to)->subject($subject);
                });
            }
        } catch (\Throwable $e) {
            Log::warning('Email notify failed: '.$e->getMessage());
        }

        return response()->json(['success' => true]);
    }
}