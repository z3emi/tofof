<?php

namespace App\Http\Controllers;

use App\Models\ProductRequest;
use App\Services\TelegramService;
use App\Support\RepairsPrimaryKeyAutoIncrement;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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

        try {
            $pr = $this->createProductRequestWithRepair($data);
        } catch (\Throwable $e) {
            Log::warning('Product request save failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => __('layout.request_product_submit_failed_later'),
            ], 500);
        }

        // ====== Notifications (اختياري) ======
        // 1) Telegram
        try {
            $result = app(TelegramService::class)->sendMessage($this->formatTelegramProductRequestMessage($pr));

            if (! (bool) data_get($result, 'ok', false)) {
                Log::warning('Product request Telegram notification not delivered.', [
                    'product_request_id' => $pr->id,
                    'response' => $result,
                ]);
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

    private function createProductRequestWithRepair(array $attributes): ProductRequest
    {
        try {
            return ProductRequest::create($attributes);
        } catch (QueryException $exception) {
            if (! RepairsPrimaryKeyAutoIncrement::isMissingAutoIncrementError($exception, 'product_requests')) {
                throw $exception;
            }

            RepairsPrimaryKeyAutoIncrement::ensure('product_requests');

            return ProductRequest::create($attributes);
        }
    }

    private function formatTelegramProductRequestMessage(ProductRequest $pr): string
    {
        $escape = static fn (?string $value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $lines = [];
        $lines[] = '🛍️ <b>طلب منتج غير متوفر</b>';
        $lines[] = '• المنتج: <b>' . $escape($pr->product_name) . '</b>';

        if (! empty($pr->brand)) {
            $lines[] = '• الماركة: ' . $escape($pr->brand);
        }

        if (! empty($pr->link)) {
            $lines[] = '• الرابط: ' . $escape($pr->link);
        }

        if (! empty($pr->phone)) {
            $lines[] = '• الهاتف/واتساب: <code>' . $escape($pr->phone) . '</code>';
        }

        if (! empty($pr->notes)) {
            $lines[] = '• ملاحظات: ' . $escape($pr->notes);
        }

        $lines[] = '— الوقت: ' . optional($pr->created_at)->format('Y-m-d H:i');

        return implode("\n", $lines);
    }
}
