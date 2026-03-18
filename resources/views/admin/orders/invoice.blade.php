<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة طلب #{{ $order->id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-dark: #be6661;
            --primary-medium: #cd8985;
            --secondary-light: #eadbcd;
            --bg-light: #fdfaf7;
            --white: #ffffff;
            --text-dark: #333333;
        }
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f8f9fa;
            color: var(--text-dark);
        }
        .invoice-container {
            max-width: 800px;
            margin: 40px auto;
            background: var(--white);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        .invoice-header {
            padding-bottom: 25px;
            border-bottom: 2px solid var(--primary-dark);
        }
        .brand-logo img {
            max-height: 100px;
            width: auto;
        }
        .invoice-title h2 {
            font-size: 2.8rem;
            font-weight: 700;
            color: var(--primary-dark);
            margin: 0;
        }
        .invoice-details p {
            margin-bottom: 5px;
            font-size: 1rem;
        }
        .table thead {
            background-color: var(--primary-dark);
            color: var(--white);
        }
        .table th, .table td {
            vertical-align: middle;
            padding: 12px 15px;
        }
        .totals-table td {
            padding: 12px 15px;
        }
        .total-row {
            background-color: var(--primary-dark);
            color: var(--white);
            font-weight: 700;
            font-size: 1.2rem;
        }
        .no-print {
            margin-bottom: 20px;
            text-align: center;
        }
        @media print {
            body { background-color: var(--white); }
            .invoice-container { box-shadow: none; margin: 0; padding: 0; max-width: 100%; border: none; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="no-print">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer-fill"></i> طباعة الفاتورة
            </button>
            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-secondary">
                <i class="bi bi-arrow-right-circle-fill"></i> الرجوع للطلب
            </a>
        </div>

        <div class="invoice-container">
            @php
                $subtotal = 0;
                foreach ($order->items as $item) {
                    $subtotal += $item->price * $item->quantity;
                }
                $shippingCost = $order->shipping_cost;
                $discountAmount = $order->discount_amount;
                $finalTotal = ($subtotal - $discountAmount) + $shippingCost;
            @endphp

            <header class="invoice-header row align-items-center mb-5">
                <div class="col-6 brand-logo">
                    <img id="brandLogo" src="" alt="شعار Tofof">
                </div>
                <div class="col-6 text-end invoice-title">
                    <h2>فاتورة</h2>
                </div>
            </header>

            <section class="row mb-5 invoice-details">
                <div class="col-6">
                    <h5 class="fw-bold mb-3">فاتورة إلى:</h5>
                    <p><strong>الاسم:</strong> {{ $order->customer->name ?? 'عميل محذوف' }}</p>
                    <p><strong>الهاتف:</strong> {{ $order->customer->phone_number ?? 'N/A' }}</p>
                    <p><strong>العنوان:</strong> {{ $order->governorate }}, {{ $order->city }}</p>
                    <p class="text-muted"><small>{{ $order->nearest_landmark }}</small></p>
                </div>
                <div class="col-6 text-end">
                    <p><strong>رقم الطلب:</strong> #{{ $order->id }}</p>
                    <p><strong>تاريخ الطلب:</strong> {{ $order->created_at->format('Y/m/d') }}</p>
                </div>
            </section>

            <main>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th scope="col">المنتج</th>
                            <th scope="col" class="text-center">السعر</th>
                            <th scope="col" class="text-center">الكمية</th>
                            <th scope="col" class="text-end">الإجمالي</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td>
                                <div>{{ $item->product->name_ar ?? 'منتج محذوف' }}</div>
                                @if(!empty($item->option_selections))
                                    <div class="small text-muted mt-1">
                                        @foreach($item->option_selections as $label => $value)
                                            <div>{{ $label }}: {{ is_array($value) ? implode(', ', $value) : $value }}</div>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="text-center">{{ number_format($item->price, 0) }} د.ع</td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-end">{{ number_format($item->price * $item->quantity, 0) }} د.ع</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="row justify-content-end mt-4">
                    <div class="col-md-6">
                        <table class="table totals-table">
                            <tbody>
                                <tr>
                                    <td class="text-end">المجموع الفرعي:</td>
                                    <td class="text-end fw-bold">{{ number_format($subtotal, 0) }} د.ع</td>
                                </tr>
                                <tr>
                                    <td class="text-end">الخصم:</td>
                                    <td class="text-end fw-bold text-success">- {{ number_format($discountAmount, 0) }} د.ع</td>
                                </tr>
                                @if($shippingCost > 0 || \App\Models\Setting::isShippingEnabled())
                                <tr>
                                    <td class="text-end">رسوم التوصيل:</td>
                                    <td class="text-end fw-bold">{{ $shippingCost > 0 ? number_format($shippingCost, 0) . ' د.ع' : 'مجاني' }}</td>
                                </tr>
                                @endif
                                <tr class="total-row">
                                    <td class="text-end">المجموع الكلي:</td>
                                    <td class="text-end">{{ number_format($finalTotal, 0) }} د.ع</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>

            <footer class="mt-5 text-center text-muted border-top pt-4">
                <p>شكرًا لثقتكم بنا في طفوف!</p>
                <p class="small">إذا كان لديك أي استفسار بخصوص هذه الفاتورة، يرجى التواصل معنا.</p>
            </footer>
        </div>
    </div>

    <script>
            // نستخدم شعار الموقع الموحد من public/logo.png
      (function () {
        const ts = Date.now(); // لتفادي الكاش
        const candidates = [
                    "{{ asset('logo.png') }}" + "?v=" + ts,
                    "{{ url('/logo.png') }}" + "?v=" + ts
        ];

        const imgEl = document.getElementById('brandLogo');

        let i = 0;
        function tryNext() {
          if (i >= candidates.length) {
            // إذا فشل الكل، نخفي الصورة ببساطة
            imgEl.style.display = 'none';
            return;
          }
          const test = new Image();
          const url = candidates[i++];
          test.onload = function () { imgEl.src = url; };
          test.onerror = tryNext;
          test.src = url;
        }

        tryNext();
      })();
    </script>
</body>
</html>
