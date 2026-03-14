<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use App\Support\Currency;
use Illuminate\Support\Str;

class InvoiceTemplateService
{
    public function render(Order $order): string
    {
        $template = (string) Setting::getValue('orders_invoice_template', '');

        if (trim($template) === '') {
            $template = $this->defaultTemplate();
        }

        $order->loadMissing('items.product', 'customer', 'salesperson');

        $replacements = $this->placeholders($order);

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    public function defaultTemplate(): string
    {
        return <<<'HTML'
<section class="invoice-layout">
    <style>
        .invoice-layout {
            font-family: 'Tajawal', Arial, sans-serif;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(74, 74, 74, 0.15);
            padding: 40px 36px;
            color: #2f2a2a;
        }

        .invoice-layout h1,
        .invoice-layout h2,
        .invoice-layout h3,
        .invoice-layout h4 {
            font-weight: 700;
        }

        .invoice-layout .invoice-brand {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 3px solid #FF5722;
            padding-bottom: 24px;
            margin-bottom: 32px;
        }

        .invoice-layout .invoice-brand .brand-text {
            text-align: end;
        }

        .invoice-layout .invoice-brand .brand-text h1 {
            font-size: 2.5rem;
            margin: 0;
            color: #FF5722;
        }

        .invoice-layout .invoice-brand .brand-text span {
            display: block;
            color: #888;
            font-size: 0.9rem;
        }

        .invoice-layout .invoice-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }

        .invoice-layout .invoice-meta .meta-box {
            background: rgba(129, 212, 250, 0.25);
            border-radius: 12px;
            padding: 18px 20px;
            border: 1px dashed rgba(74, 74, 74, 0.35);
        }

        .invoice-layout .meta-title {
            font-size: 0.95rem;
            letter-spacing: 0.02em;
            color: #FF5722;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .invoice-layout .meta-value {
            font-size: 1.15rem;
            font-weight: 600;
            color: #2f2a2a;
        }

        .invoice-layout table.invoice-items {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            overflow: hidden;
            border-radius: 14px;
            margin-bottom: 30px;
        }

        .invoice-layout table.invoice-items thead {
            background: #FF5722;
            color: #fff;
        }

        .invoice-layout table.invoice-items th,
        .invoice-layout table.invoice-items td {
            padding: 16px 18px;
        }

        .invoice-layout table.invoice-items tbody tr:nth-child(even) {
            background: #F0F2F5;
        }

        .invoice-layout .totals-card {
            background: linear-gradient(135deg, rgba(74, 74, 74, 0.08), rgba(240, 242, 245, 0.45));
            border-radius: 14px;
            padding: 20px 24px;
        }

        .invoice-layout .totals-card .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 1.05rem;
        }

        .invoice-layout .totals-card .totals-row.grand-total {
            font-size: 1.3rem;
            font-weight: 700;
            color: #FF5722;
        }

        .invoice-layout .notes-box {
            margin-top: 28px;
            background: rgba(129, 212, 250, 0.25);
            border-left: 4px solid #FF5722;
            padding: 18px 20px;
            border-radius: 12px;
            color: #FF5722;
        }

        .invoice-layout .footer-note {
            margin-top: 40px;
            text-align: center;
            color: #FF5722;
            font-size: 0.95rem;
        }

        @media print {
            .invoice-layout {
                box-shadow: none;
                padding: 0;
            }
        }
    </style>

    <div class="invoice-brand">
        <div class="brand-logo">[LOGO_IMAGE]</div>
        <div class="brand-text">
            <h1>{{ config('app.name', 'Tofof') }}</h1>
            <span>فاتورة طلب #[ORDER_ID]</span>
        </div>
    </div>

    <div class="invoice-meta">
        <div class="meta-box">
            <div class="meta-title">العميل</div>
            <div class="meta-value">[CUSTOMER_NAME]</div>
            <div class="text-muted small">الهاتف: [CUSTOMER_PHONE]</div>
            <div class="text-muted small">الهاتف الاحتياطي: [CUSTOMER_SECONDARY_PHONE]</div>
        </div>
        <div class="meta-box">
            <div class="meta-title">بيانات الطلب</div>
            <div class="meta-value">[ORDER_DATE]</div>
            <div class="text-muted small">نوع الطلب: [SALE_TYPE]</div>
            <div class="text-muted small">الحالة: [ORDER_STATUS]</div>
        </div>
        <div class="meta-box">
            <div class="meta-title">العنوان</div>
            <div class="meta-value">[CUSTOMER_ADDRESS]</div>
            <div class="text-muted small">[CUSTOMER_ADDRESS_NOTES]</div>
        </div>
    </div>

    [ITEMS_TABLE]

    <div class="totals-card">
        <div class="totals-row"><span>الإجمالي قبل الخصم</span><span>[SUBTOTAL]</span></div>
        <div class="totals-row"><span>الخصومات</span><span>[DISCOUNT_AMOUNT]</span></div>
        <div class="totals-row"><span>رسوم التوصيل</span><span>[SHIPPING_AMOUNT]</span></div>
        <div class="totals-row grand-total"><span>المبلغ المستحق</span><span>[GRAND_TOTAL]</span></div>
    </div>

    <div class="notes-box">
        <strong>ملاحظات الطلب:</strong>
        <div>[ORDER_NOTES]</div>
    </div>

    <div class="footer-note">
        نشكركم على اختياركم لنا. لأي استفسار يرجى التواصل على [COMPANY_PHONE].
    </div>
</section>
HTML;
    }

    protected function placeholders(Order $order): array
    {
        $currency = $order->currency ?: Currency::IQD;
        $subtotal = $order->items->reduce(function ($carry, $item) use ($currency) {
            $price = Currency::roundForCurrency($item->price, $currency);
            return $carry + ($price * $item->quantity);
        }, 0.0);

        $discountAmount = (float) $order->discount_amount;
        $shipping = (float) $order->shipping_cost;
        $total = (float) $order->total_amount;
        $symbol = Currency::symbolFor($currency);

        $itemsTable = $this->buildItemsTable($order, $currency, $symbol);
        $itemsRows = $this->buildItemRows($order, $currency, $symbol);

        $customer = $order->customer;
        $customerName = $customer?->display_name ?? __('عميل غير مسجل');
        $customerPhone = $customer?->phone_number ?? __('غير متوفر');
        $customerSecondary = $customer?->secondary_phone ?? __('غير متوفر');
        $governorate = $order->governorate ?: ($customer?->governorate ?? '');
        $city = $order->city ?: ($customer?->city ?? '');
        $address = trim(implode(' - ', array_filter([$governorate, $city])));
        $addressNotes = $order->nearest_landmark ?: ($customer?->address ?? '');
        $customerNotes = $order->notes ?: __('لا توجد ملاحظات إضافية');
        $salesperson = $order->salesperson?->name ?? __('غير محدد');
        $companyPhone = Setting::getValue('company_phone', '');

        $statusLabels = [
            'pending' => __('قيد الانتظار'),
            'processing' => __('قيد المعالجة'),
            'shipped' => __('تم الشحن'),
            'delivered' => __('تم التوصيل'),
            'returned' => __('مرتجع'),
            'cancelled' => __('ملغي'),
        ];

        $saleTypeLabels = Order::saleTypeLabels();

        return [
            '[ORDER_ID]' => (string) $order->id,
            '[ORDER_NUMBER]' => '#' . $order->id,
            '[ORDER_DATE]' => optional($order->created_at)->format('Y-m-d H:i') ?? '',
            '[ORDER_STATUS]' => $statusLabels[$order->status] ?? $order->status,
            '[SALE_TYPE]' => $saleTypeLabels[$order->sale_type] ?? $order->sale_type,
            '[CUSTOMER_NAME]' => $this->escape($customerName),
            '[CUSTOMER_PHONE]' => $this->escape($customerPhone),
            '[CUSTOMER_SECONDARY_PHONE]' => $this->escape($customerSecondary),
            '[CUSTOMER_ADDRESS]' => $this->escape($address ?: __('غير متوفر')),
            '[CUSTOMER_ADDRESS_NOTES]' => $this->escape($addressNotes ?: __('لا يوجد')), 
            '[ORDER_NOTES]' => nl2br($this->escape($customerNotes)),
            '[ITEMS_TABLE]' => $itemsTable,
            '[ITEM_ROWS]' => $itemsRows,
            '[SUBTOTAL]' => $this->formatMoney($subtotal, $currency),
            '[DISCOUNT_AMOUNT]' => $this->formatMoney($discountAmount, $currency),
            '[SHIPPING_AMOUNT]' => $shipping > 0
                ? $this->formatMoney($shipping, $currency)
                : __('مجاني'),
            '[GRAND_TOTAL]' => $this->formatMoney($total, $currency),
            '[CURRENCY_CODE]' => $currency,
            '[CURRENCY_SYMBOL]' => $symbol,
            '[ITEMS_COUNT]' => (string) $order->items->count(),
            '[SALESPERSON_NAME]' => $this->escape($salesperson),
            '[COMPANY_NAME]' => $this->escape(config('app.name', 'Tofof')),
            '[COMPANY_PHONE]' => $this->escape($companyPhone !== '' ? $companyPhone : __('غير متوفر')),
            '[COMPANY_LOGO_URL]' => $this->escape($this->logoUrl()),
            '[LOGO_IMAGE]' => '<img src="' . $this->escape($this->logoUrl()) . '" alt="' . $this->escape(config('app.name', 'Tofof')) . '" style="max-height: 90px;">',
        ];
    }

    protected function formatMoney(float $amount, string $currency): string
    {
        return Currency::formatForCurrency($amount, $currency);
    }

    protected function buildItemsTable(Order $order, string $currency, string $symbol): string
    {
        $rows = $this->buildItemRows($order, $currency, $symbol);

        return '<table class="table invoice-items">'
            . '<thead>'
            . '<tr>'
            . '<th>' . __('المنتج') . '</th>'
            . '<th class="text-center">' . __('السعر') . '</th>'
            . '<th class="text-center">' . __('الكمية') . '</th>'
            . '<th class="text-end">' . __('الإجمالي') . '</th>'
            . '</tr>'
            . '</thead>'
            . '<tbody>' . $rows . '</tbody>'
            . '</table>';
    }

    protected function buildItemRows(Order $order, string $currency, string $symbol): string
    {
        $rows = [];

        foreach ($order->items as $item) {
            $name = $this->escape($item->product->name_ar ?? $item->product->name ?? __('منتج محذوف'));
            $price = Currency::roundForCurrency($item->price, $currency);
            $total = $price * $item->quantity;

            $rows[] = '<tr>'
                . '<td>' . $name . '</td>'
                . '<td class="text-center">' . $this->formatMoney($price, $currency) . '</td>'
                . '<td class="text-center">' . $item->quantity . '</td>'
                . '<td class="text-end">' . $this->formatMoney($total, $currency) . '</td>'
                . '</tr>';
        }

        return implode('', $rows);
    }

    protected function escape(string $value): string
    {
        return e($value);
    }

    protected function logoUrl(): string
    {
        $stored = Setting::getValue('company_logo_url');

        if ($stored) {
            return $stored;
        }

        $candidate = asset('logo.png');

        if (Str::contains($candidate, '://')) {
            return $candidate;
        }

        return url('/logo.png');
    }
}
