@extends('admin.layout')

@section('title', 'تفاصيل الطلب #' . $order->id)

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    :root {
        --primary-dark: #be6661;
        --primary-medium: #cd8985;
        --text-dark: #3a3a3a;
    }
    .panel {
        background-color: #ffffff;
        border-radius: 10px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        padding: 1.25rem;
        margin-bottom: 1.5rem;
    }
    .panel-header {
        font-weight: bold;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid #f0f0f0;
        color: var(--primary-dark);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .panel-header .header-text {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    #orderLocationMap { height: 300px; z-index: 1; border-radius: 8px; }
    .small-muted { font-size: .85rem; color: #6c757d; }
</style>
@endpush

@section('content')
@php
    $statusTexts = [
        'pending' => ['text' => 'قيد الانتظار', 'color' => 'warning'],
        'processing' => ['text' => 'قيد المعالجة', 'color' => 'info'],
        'shipped' => ['text' => 'تم الشحن', 'color' => 'primary'],
        'delivered' => ['text' => 'تم التوصيل', 'color' => 'success'],
        'returned' => ['text' => 'مرتجع', 'color' => 'danger'],
        'cancelled' => ['text' => 'ملغى', 'color' => 'secondary']
    ];
    $statusInfo = $statusTexts[$order->status] ?? ['text' => $order->status, 'color' => 'dark'];

    // ✅ [تصحيح] حساب المبلغ المدفوع من المحفظة والإجمالي قبل استخدامها مباشرة هنا
    $totalBeforeWallet = $subtotal - $order->discount_amount + $order->shipping_cost;
    $walletPaidAmount = $totalBeforeWallet - $order->total_amount;
    // نتأكد أن القيمة ليست سالبة في حال وجود خطأ بالحسابات
    $walletPaidAmount = max(0, $walletPaidAmount);
@endphp

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>تفاصيل الطلب <span class="text-primary">#{{ $order->id }}</span></h3>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.orders.invoice', $order->id) }}" class="btn btn-sm btn-dark"><i class="bi bi-printer-fill me-1"></i> طباعة</a>
            <a href="{{ route('admin.orders.edit', $order->id) }}" class="btn btn-sm btn-info"><i class="bi bi-pencil-fill me-1"></i> تعديل</a>
            @can('delete-orders')
                @if(in_array($order->status, ['cancelled', 'returned']))
                    <form action="{{ route('admin.orders.destroy', $order->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('هل أنت متأكد من نقل الطلب إلى سلة المحذوفات؟');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash-fill me-1"></i> حذف
                        </button>
                    </form>
                @else
                    <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="يمكن حذف الطلب فقط إذا كانت حالته ملغي أو مرتجع">
                        <i class="bi bi-trash-fill me-1"></i> حذف
                    </button>
                @endif
            @endcan
        </div>
    </div>

    <div class="row g-4">
        {{-- العمود الأيمن --}}
        <div class="col-lg-4">

            {{-- 1) تغيير حالة الطلب (تم نقله للأعلى) --}}
            <div class="panel">
                <div class="panel-header"><div class="header-text"><i class="bi bi-arrow-repeat"></i><span>تغيير حالة الطلب</span></div></div>
                <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST">
                    @csrf
                    <div class="input-group">
                        <select name="status" class="form-select">
                            @foreach($statusTexts as $key => $info)
                                <option value="{{ $key }}" @selected($order->status == $key)>{{ $info['text'] }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary">حفظ</button>
                    </div>
                </form>
            </div>

            {{-- 2) معلومات العميل --}}
            <div class="panel">
                <div class="panel-header">
                    <div class="header-text">
                        <i class="bi bi-person-circle"></i>
                        <span>معلومات المستخدم</span>
                    </div>
                    @if($order->user_id)
                    <a href="{{ route('admin.users.show', $order->user_id) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-person-lines-fill"></i> عرض الملف
                    </a>
                    @endif
                </div>
                <p class="mb-1"><strong>الاسم:</strong> {{ $order->customer->name ?? 'مستخدم محذوف' }}</p>
                <p><strong>الهاتف:</strong> <a href="tel:{{ $order->customer->phone_number ?? '' }}">{{ $order->customer->phone_number ?? 'N/A' }}</a></p>
                <hr class="my-2">
                @if($order->is_gift)
                    <p class="mb-1"><strong>عنوان التوصيل المعتمد:</strong> {{ $order->gift_recipient_address_details ?: $addressDetails ?: 'غير محدد' }}</p>
                    @if($order->gift_recipient_name)
                        <p class="mb-1"><strong>المستلم:</strong> {{ $order->gift_recipient_name }}</p>
                    @endif
                @else
                    <p class="mb-1"><strong>المحافظة:</strong> {{ $resolvedGovernorate ?? 'غير محدد' }}</p>
                    <p class="mb-1"><strong>المدينة:</strong> {{ $resolvedCity ?? 'غير محددة' }}</p>
                    @if($addressDetails)
                        <p class="mb-1"><strong>تفاصيل العنوان:</strong> {{ $addressDetails }}</p>
                    @endif
                    @if($nearestLandmark)
                        <p class="mb-1"><strong>أقرب نقطة دالة:</strong> {{ $nearestLandmark }}</p>
                    @endif
                @endif
                @if($addressNotes)
                    <p class="text-muted small mb-0">ملاحظات: {{ $addressNotes }}</p>
                @endif
            </div>

            @if($order->is_gift)
            <div class="panel">
                <div class="panel-header">
                    <div class="header-text">
                        <i class="bi bi-gift-fill"></i>
                        <span>بيانات الهدية</span>
                    </div>
                </div>
                <p class="mb-1"><strong>حالة الطلب:</strong> <span class="badge bg-danger">هدية</span></p>
                <p class="mb-1"><strong>اسم المستلم:</strong> {{ $order->gift_recipient_name }}</p>
                <p class="mb-1"><strong>هاتف المستلم:</strong> <a href="tel:{{ $order->gift_recipient_phone }}">{{ $order->gift_recipient_phone }}</a></p>
                <p class="mb-1"><strong>عنوان المستلم:</strong> {{ $order->gift_recipient_address_details }}</p>
                @if($order->gift_message)
                    <p class="mb-0"><strong>رسالة الهدية:</strong> {{ $order->gift_message }}</p>
                @endif
            </div>
            @endif

            {{-- 3) ملخص الطلب --}}
            <div class="panel">
                <div class="panel-header">
                    <div class="header-text"><i class="bi bi-file-earmark-text"></i><span>ملخص الطلب</span></div>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span>الحالة:</span>
                        <span class="badge bg-{{ $statusInfo['color'] }}">{{ $statusInfo['text'] }}</span>
                    </li>

                    {{-- المجموع الفرعي من عناصر الطلب (ممرر من الكنترولر) --}}
                    <li class="list-group-item d-flex justify-content-between">
                        <span>المجموع الفرعي:</span>
                        <span>{{ number_format($subtotal, 0) }} د.ع</span>
                    </li>

                    {{-- كود الخصم المستخدم إن وجد --}}
                    <li class="list-group-item d-flex justify-content-between">
                        <span>كود الخصم:</span>
                        <span>
                            @if(!empty($appliedDiscountCode))
                                <span class="badge bg-light text-dark border">{{ $appliedDiscountCode }}</span>
                            @else
                                <span class="text-muted">لم يُستخدم</span>
                            @endif
                        </span>
                    </li>

                    {{-- قيمة الخصم --}}
                    <li class="list-group-item d-flex justify-content-between">
                        <span>الخصم:</span>
                        <span class="text-success">- {{ number_format($order->discount_amount, 0) }} د.ع</span>
                    </li>

                    {{-- الشحن --}}
                    @if($order->shipping_cost > 0 || \App\Models\Setting::isShippingEnabled())
                    <li class="list-group-item d-flex justify-content-between">
                        <span>الشحن:</span>
                        <span>{{ $order->shipping_cost > 0 ? number_format($order->shipping_cost, 0) . ' د.ع' : 'مجاني' }}</span>
                    </li>
                    @endif

                    {{-- ✅ [تصحيح] يتم عرض هذا الحقل فقط اذا كان هناك مبلغ مدفوع من المحفظة --}}
                    @if ($walletPaidAmount > 0)
                        {{-- المبلغ المدفوع من المحفظة --}}
                        <li class="list-group-item d-flex justify-content-between">
                            <span>مدفوع من المحفظة:</span>
                            {{-- ✅ [تصحيح] تم استخدام المتغير المحسوب حديثاً --}}
                            <span class="text-primary">- {{ number_format($walletPaidAmount, 0) }} د.ع</span>
                        </li>

                        {{-- الإجمالي قبل المحفظة (اختياري) --}}
                        <li class="list-group-item d-flex justify-content-between small-muted">
                            <span>الإجمالي قبل المحفظة:</span>
                            {{-- ✅ [تصحيح] تم استخدام المتغير المحسوب حديثاً --}}
                            <span>{{ number_format($totalBeforeWallet, 0) }} د.ع</span>
                        </li>
                    @endif

                    {{-- المتبقي للدفع (الإجمالي الحالي) --}}
                    <li class="list-group-item d-flex justify-content-between fw-bold fs-5">
                        <span>المتبقي للدفع:</span>
                        <span class="text-primary">{{ number_format($order->total_amount, 0) }} د.ع</span>
                    </li>
                </ul>
            </div>
        </div>

        {{-- العمود الأيسر --}}
        <div class="col-lg-8">
            <div class="panel">
                <div class="panel-header"><div class="header-text"><i class="bi bi-cart-check"></i><span>المنتجات المطلوبة</span></div></div>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>المنتج</th>
                                <th class="text-center">السعر</th>
                                <th class="text-center">الكمية</th>
                                <th class="text-end">الإجمالي</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $item->product?->firstImage ? asset('storage/' . $item->product->firstImage->image_path) : 'https://placehold.co/50x50?text=Img' }}"
                                             width="50" height="50" class="rounded me-2" style="object-fit:cover;">
                                        <div>
                                            <div>{{ $item->product->name_ar ?? 'منتج محذوف' }}</div>
                                            @if(!empty($item->option_selections))
                                                <div class="small text-muted mt-1">
                                                    @foreach($item->option_selections as $label => $value)
                                                        <div>{{ $label }}: {{ is_array($value) ? implode(', ', $value) : $value }}</div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">{{ number_format($item->price, 0) }} د.ع</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end">{{ number_format($item->price * $item->quantity, 0) }} د.ع</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel">
                <div class="panel-header"><div class="header-text"><i class="bi bi-geo-alt-fill"></i><span>موقع التوصيل</span></div></div>
                @if($primaryAddress?->latitude && $primaryAddress?->longitude)
                    <div id="orderLocationMap"></div>
                @else
                    <p class="text-muted text-center my-4">لم يحدد المستخدم الموقع على الخريطة.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if($primaryAddress?->latitude && $primaryAddress?->longitude)
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const lat = {{ $primaryAddress->latitude }};
            const lng = {{ $primaryAddress->longitude }};
            const map = L.map('orderLocationMap').setView([lat, lng], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
            L.marker([lat, lng]).addTo(map).bindPopup('موقع توصيل الطلب.').openPopup();
        });
    </script>
@endif
@endpush