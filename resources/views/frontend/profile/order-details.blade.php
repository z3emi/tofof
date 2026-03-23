@extends('frontend.profile.layout')
@section('title', __('profile.order_details_title') . ' #' . $order->id)

@push('styles')
<style>
  :root{
    --brand:#cd8985;
    --brand-dark:#be6661;
    --hair:#f2e9e1;
    --text:#4a3f3f;
    --surface:#ffffff;
    --bg:#fffaf9;
    --muted:#7a6e6e;
    --rail:#eeeeee;
    --progress:#be6661;
    --chip-gray-bg:#F6F7F9;
    --chip-gray-text:#374151;
    --chip-danger-bg:#FFECEF;
    --chip-danger-text:#8F1D1D;
  }
  html.dark{
    --brand:#d1a3a4;
    --brand-dark:#f0b0ad;
    --hair:#1f2937;
    --text:#e5e7eb;
    --surface:#0f172a;
    --bg:#0b0f14;
    --muted:#9ca3af;
    --rail:#263142;
    --progress:#f0b0ad;
    --chip-gray-bg:#0e1624;
    --chip-gray-text:#cbd5e1;
    --chip-danger-bg:rgba(244,63,94,.14);
    --chip-danger-text:#fda4af;
  }
  .surface{ background:transparent; border:none; box-shadow:none; padding:0; }
  @media (min-width:768px){ .surface{ padding:0; } }
  .head h2{ color:var(--text); font-weight:800; }
  .head p{ color:var(--muted); }
  .link-back{ color:var(--brand); font-weight:800; display:inline-flex; align-items:center; gap:.35rem; }
  .link-back:hover{ color:var(--brand-dark); text-decoration:underline; }
  .order-tracker{ position:relative; margin:1.25rem 0; padding:0 .25rem; display:flex; justify-content:space-between; gap:.5rem; }
  .order-tracker .progress-rail{ position:absolute; top:22px; left:8%; width:84%; height:2px; background:var(--rail); z-index:0; }
  .order-tracker .progress-bar{ position:absolute; top:22px; left:8%; height:2px; background:var(--progress); z-index:1; width: calc(84% * (var(--p,0) / 100)); transition:width .25s ease; border-radius:2px; }
  .step{ width:25%; text-align:center; position:relative; z-index:2; }
  .step-circle{ width:2.4rem; height:2.4rem; border-radius:999px; display:grid; place-items:center; background:#f5f5f5; color:#9ca3af; margin:0 auto .45rem; box-shadow: inset 0 0 0 2px #fff, 0 4px 10px rgba(0,0,0,.06); transition:.2s ease; }
  html.dark .step-circle{ background:#0f172a; color:#94a3b8; box-shadow: inset 0 0 0 1px var(--hair), 0 4px 10px rgba(0,0,0,.35); }
  .step-label{ font-size:.78rem; color:#6b7280; font-weight:600; white-space:nowrap; }
  html.dark .step-label{ color:var(--muted); }
  .step.completed .step-circle{ background:#eafbf0; color:#166534; box-shadow: inset 0 0 0 2px #fff, 0 6px 14px rgba(22,101,52,.15); }
  .step.active .step-circle{ background:var(--brand); color:#fff; box-shadow: inset 0 0 0 2px #fff, 0 6px 14px rgba(205,137,133,.25); }
  .step.completed .step-label, .step.active .step-label{ color:var(--text); }
  html.dark .step.completed .step-circle{ background:rgba(34,197,94,.12); color:#86efac; box-shadow: inset 0 0 0 1px var(--hair), 0 6px 14px rgba(0,0,0,.35); }
  html.dark .step.active .step-circle{ background:var(--brand); color:#111827; box-shadow: inset 0 0 0 1px var(--hair), 0 6px 14px rgba(0,0,0,.4); }
  .product-item{ display:flex; gap:.9rem; align-items:center; padding:.8rem .9rem; border-radius:12px; background:var(--surface); box-shadow:0 6px 18px rgba(0,0,0,.05), inset 0 0 0 1px var(--hair); }
  html.dark .product-item{ box-shadow: 0 6px 18px rgba(0,0,0,.35), inset 0 0 0 1px var(--hair); }
  .product-image{ width:72px; height:72px; border-radius:12px; object-fit:cover; box-shadow: inset 0 0 0 1px var(--hair); background:#fff; }
  html.dark .product-image{ background:#0b0f14; }
  .product-name{ color:var(--text); font-weight:700; }
  .product-meta{ color:var(--muted); font-size:.85rem; }
  .summary-panel{ background:var(--surface); border-radius:14px; padding:1rem; box-shadow:0 8px 20px rgba(0,0,0,.05), inset 0 0 0 1px var(--hair); }
  html.dark .summary-panel{ box-shadow: 0 8px 20px rgba(0,0,0,.36), inset 0 0 0 1px var(--hair); }
  .row-line{ display:flex; justify-content:space-between; align-items:center; padding:.35rem 0; }
  .row-line + .row-line{ border-top:1px dashed var(--hair); }
  .row-muted{ color:var(--muted); }
  .row-strong{ color:var(--text); font-weight:800; }
  .pill{ display:inline-flex; align-items:center; gap:.4rem; padding:.35rem .6rem; border-radius:999px; font-weight:700; font-size:.8rem; }
  .pill-danger{ background:var(--chip-danger-bg); color:var(--chip-danger-text); }
  .pill-gray{ background:var(--chip-gray-bg); color:var(--chip-gray-text); }
  @media (max-width:640px){
    .product-item{ padding:.7rem .8rem; gap:.75rem; }
    .product-image{ width:64px; height:64px; }
  }
</style>
@endpush

@section('profile-content')
@php
    // حساب المبالغ
    $recalculatedSubtotal = 0;
    foreach ($order->items as $item) { $recalculatedSubtotal += $item->price * $item->quantity; }
    
    // المبلغ النهائي للدفع (المسجل في الطلب)
    $finalAmountDue = $order->total_amount;

    // حالة الطلب
    $statuses = ['pending','processing','shipped','delivered'];
    $currentStatusIndex = array_search($order->status, $statuses);
    $maxIndex = count($statuses) - 1;
    $progress = ($currentStatusIndex === false) ? 0 : round(($currentStatusIndex * 100) / max($maxIndex,1), 2);
@endphp

<div class="surface">
  {{-- الرأس --}}
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 md:gap-3 head mb-4 md:mb-6">
    <div>
      <h2 class="text-xl md:text-2xl">{{ __('profile.order_details_title') }}</h2>
      <p class="text-xs md:text-sm">{{ __('profile.order_id_label') }} <span class="font-mono">#{{ $order->id }}</span></p>
    </div>
    <a href="{{ route('profile.orders') }}" class="link-back">
      <i class="bi bi-arrow-right"></i> {{ __('profile.back_to_orders') }}
    </a>
  </div>

  {{-- حالة الطلب --}}
  @if($order->status == 'cancelled')
    <div class="summary-panel mb-5 text-center">
      <span class="pill pill-gray"><i class="bi bi-x-circle-fill"></i> {{ __('profile.order_cancelled_msg') }}</span>
    </div>
  @elseif($order->status == 'returned')
    <div class="summary-panel mb-5 text-center">
      <span class="pill pill-danger"><i class="bi bi-arrow-return-left"></i> {{ __('profile.order_returned_msg') }}</span>
    </div>
  @else
    <div class="order-tracker" style="--p: {{ $progress }};">
      <div class="progress-rail"></div>
      <div class="progress-bar"></div>
      @foreach([__('profile.tracker_pending'), __('profile.tracker_processing'), __('profile.tracker_shipped'), __('profile.tracker_delivered')] as $index => $label)
        @php
          $stateClass = ($currentStatusIndex !== false && $currentStatusIndex > $index) ? 'completed'
                       : (($currentStatusIndex !== false && $currentStatusIndex == $index) ? 'active' : 'pending');
        @endphp
        <div class="step {{ $stateClass }}">
          <div class="step-circle">
            <i class="bi {{ ['bi-hourglass-split','bi-box-seam','bi-truck','bi-house-check-fill'][$index] }}"></i>
          </div>
          <div class="step-label">{{ $label }}</div>
        </div>
      @endforeach
    </div>
  @endif

  {{-- المنتجات --}}
  <h3 class="text-lg md:text-xl font-extrabold text-[var(--text)] mb-2 md:mb-3">{{ __('profile.products_section') }}</h3>
  <div class="space-y-2 md:space-y-3">
    @foreach($order->items as $item)
      <div class="product-item">
        <img
          src="{{ $item->product?->firstImage ? asset('storage/' . $item->product->firstImage->image_path) : 'https://placehold.co/80x80/f9f5f1/cd8985?text=Img' }}"
          alt="{{ $item->product->name_translated ?? __('profile.deleted_product') }}"
          class="product-image"
        >
        <div class="flex-grow min-w-0">
          <div class="product-name text-sm md:text-base truncate">{{ $item->product->name_translated ?? __('profile.deleted_product') }}</div>
          <div class="product-meta">{{ __('profile.quantity_label') }} {{ $item->quantity }}</div>
          @if(!empty($item->option_selections))
            <div class="product-meta mt-1">
              @foreach($item->option_selections as $label => $value)
                <div>{{ $label }}: {{ is_array($value) ? implode(', ', $value) : $value }}</div>
              @endforeach
            </div>
          @endif
        </div>
        <div class="text-left">
          <div class="text-[var(--brand-dark)] font-extrabold text-sm md:text-base">
            {{ number_format($item->price * $item->quantity, 0) }} {{ __('profile.currency') }}
          </div>
          <div class="text-[#9ca3af] text-xs">
            ({{ number_format($item->price, 0) }} {{ __('profile.currency') }} {{ __('profile.per_unit') }})
          </div>
        </div>
      </div>
    @endforeach
  </div>

  {{-- الملخص --}}
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mt-5 md:mt-7">
    <div class="summary-panel">
      <h3 class="text-lg md:text-xl font-extrabold text-[var(--text)] mb-2 md:mb-3">{{ __('profile.payment_summary') }}</h3>
      
      {{-- ✅ [تصحيح] تم تحديث منطق عرض الملخص بالكامل --}}
      <div class="row-line"><span class="row-muted">{{ __('profile.subtotal_label') }}</span><span class="row-strong">{{ number_format($recalculatedSubtotal, 0) }} {{ __('profile.currency') }}</span></div>
      
      @if($order->discount_amount > 0)
      <div class="row-line" style="color:#166534"><span>{{ __('profile.discount_label') }}</span><span class="row-strong" style="color:#166534">-{{ number_format($order->discount_amount, 0) }} {{ __('profile.currency') }}</span></div>
      @endif

      @if($order->shipping_cost > 0 || \App\Models\Setting::isShippingEnabled())
      <div class="row-line"><span class="row-muted">{{ __('profile.shipping_label') }}</span><span class="row-strong">{{ $order->shipping_cost > 0 ? number_format($order->shipping_cost, 0) . ' ' . __('profile.currency') : __('profile.free_shipping') }}</span></div>
      @endif

      @if(isset($walletPaidAmount) && $walletPaidAmount > 0)
      <div class="row-line" style="color: #059669;">
        <span class="font-semibold">{{ __('profile.wallet_paid') }}</span>
        <span class="row-strong" style="color: #059669;">-{{ number_format($walletPaidAmount, 0) }} {{ __('profile.currency') }}</span>
      </div>
      @endif

      <div class="row-line" style="border-top:2px solid var(--hair); margin-top:.5rem; padding-top:.75rem;">
        <span class="row-strong text-lg">{{ __('profile.final_amount') }}</span>
        <span class="row-strong text-lg" style="color: var(--brand)">{{ number_format($finalAmountDue, 0) }} {{ __('profile.currency') }}</span>
      </div>
    </div>

    <div class="summary-panel">
      <h3 class="text-lg md:text-xl font-extrabold text-[var(--text)] mb-2 md:mb-3">{{ $order->is_gift ? __('profile.gift_shipping_address') : __('profile.shipping_address') }}</h3>
      <div class="space-y-2 text-sm">
        @if($order->is_gift)
        <div class="row-line" style="padding:.2rem 0;border:none"><span class="row-muted">{{ __('profile.address_label') }}</span><span class="row-strong">{{ $order->gift_recipient_address_details ?: $order->address_details }}</span></div>
        @if($order->gift_recipient_name)
        <div class="row-line" style="padding:.2rem 0;border:none"><span class="row-muted">{{ __('profile.recipient_label') }}</span><span class="row-strong">{{ $order->gift_recipient_name }}</span></div>
        @endif
        @else
        <div class="row-line" style="padding:.2rem 0;border:none"><span class="row-muted">{{ __('profile.governorate_label') }}</span><span class="row-strong">{{ $order->governorate }}</span></div>
        <div class="row-line" style="padding:.2rem 0;border:none"><span class="row-muted">{{ __('profile.city_label') }}</span><span class="row-strong">{{ $order->city }}</span></div>
        <div class="row-line" style="padding:.2rem 0;border:none"><span class="row-muted">{{ __('profile.address_details_label') }}</span><span class="row-strong">{{ $order->address_details }}</span></div>
        @if($order->nearest_landmark)
        <div class="row-line" style="padding:.2rem 0;border:none"><span class="row-muted">{{ __('profile.nearest_landmark') }}</span><span class="row-strong">{{ $order->nearest_landmark }}</span></div>
        @endif
        @endif
      </div>
    </div>

    @if($order->is_gift)
    <div class="summary-panel md:col-span-2">
      <h3 class="text-lg md:text-xl font-extrabold text-[var(--text)] mb-2 md:mb-3">{{ __('profile.gift_data') }}</h3>
      <div class="space-y-2 text-sm">
        <div class="row-line" style="padding:.2rem 0;border:none"><span class="row-muted">{{ __('profile.order_type_label') }}</span><span class="row-strong">{{ __('profile.gift_type') }}</span></div>
        <div class="row-line" style="padding:.2rem 0;border:none"><span class="row-muted">{{ __('profile.recipient_name_label') }}</span><span class="row-strong">{{ $order->gift_recipient_name }}</span></div>
        <div class="row-line" style="padding:.2rem 0;border:none"><span class="row-muted">{{ __('profile.recipient_phone_label') }}</span><span class="row-strong">{{ $order->gift_recipient_phone }}</span></div>
        <div class="row-line" style="padding:.2rem 0;border:none"><span class="row-muted">{{ __('profile.recipient_addr_label') }}</span><span class="row-strong">{{ $order->gift_recipient_address_details }}</span></div>
        @if($order->gift_message)
          <div class="row-line" style="padding:.2rem 0;border:none"><span class="row-muted">{{ __('profile.gift_message_label') }}</span><span class="row-strong">{{ $order->gift_message }}</span></div>
        @endif
      </div>
    </div>
    @endif
  </div>
</div>
@endsection