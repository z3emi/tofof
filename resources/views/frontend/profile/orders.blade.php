@extends('frontend.profile.layout')
@section('title', 'طلباتي')

@push('styles')
<style>
  :root{
    --brand:#cd8985;
    --brand-dark:#be6661;
    --brand-bg:#f9f5f1;
    --soft:#efe4da;
    --hair:#f6efe9;
    --text:#4a3f3f;
    --muted:#7a6e6e;
    --surface:#ffffff;
    --bg:#fffaf9;
    --border:#eadbcd;
  }
  html.dark{
    --brand:#d1a3a4;
    --brand-dark:#f0b0ad;
    --brand-bg:#0b0f14;
    --soft:#2a3443;
    --hair:#1f2937;
    --text:#e5e7eb;
    --muted:#cbd5e1;
    --surface:#0f172a;
    --bg:#0b0f14;
    --border:#1f2937;
  }

  /* سطح الصفحة */
  .surface{
    background:transparent;
    border-radius:16px;
    padding:.25rem;
  }
  @media (min-width:768px){ .surface{ padding:.5rem; } }

  .page-head h2{ color:var(--text); font-weight:800; }
  .page-head p{ color:var(--muted); }

  /* بطاقة الطلب – بارزة مثل تفاصيل الطلب */
  .order-card{
    position:relative;
    border-radius:16px;
    background:var(--surface);
    box-shadow:
      0 14px 28px rgba(205,137,133,.12),
      0 6px 12px rgba(0,0,0,.06),
      inset 0 0 0 1px var(--hair);
    transition: box-shadow .2s ease, transform .12s ease;
    padding: 1rem;
    overflow:hidden;
  }
  html.dark .order-card{
    box-shadow:
      0 14px 28px rgba(0,0,0,.35),
      0 6px 12px rgba(0,0,0,.20),
      inset 0 0 0 1px var(--border);
  }

  /* طبقة لمسة ضوء خفيفة */
  .order-card::before{
    content:"";
    position:absolute; inset:0;
    background: radial-gradient(120% 60% at 110% -10%, rgba(205,137,133,.06), transparent 50%),
                radial-gradient(100% 50% at -10% 0%, rgba(255,255,255,.8), transparent 40%);
    pointer-events:none;
  }
  html.dark .order-card::before{
    background: radial-gradient(120% 60% at 110% -10%, rgba(240,176,173,.08), transparent 55%),
                radial-gradient(100% 50% at -10% 0%, rgba(255,255,255,.04), transparent 45%);
  }

  /* شريط تمييز جانبي */
  .order-card::after{
    content:"";
    position:absolute; top:0; bottom:0; right:-6px; width:6px; border-radius:12px 0 0 12px;
    background: linear-gradient(180deg, var(--brand), var(--brand-dark));
    opacity:0; transform: translateX(6px);
    transition: opacity .2s ease, transform .2s ease;
  }
  .order-card:hover{
    box-shadow:
      0 18px 36px rgba(205,137,133,.18),
      0 8px 16px rgba(0,0,0,.08),
      inset 0 0 0 1px var(--soft);
    transform: translateY(-1px);
  }
  html.dark .order-card:hover{
    box-shadow:
      0 18px 36px rgba(0,0,0,.45),
      0 8px 16px rgba(0,0,0,.30),
      inset 0 0 0 1px var(--border);
  }
  .order-card:hover::after{ opacity:1; transform: translateX(0); }

  /* فواصل رقيقة */
  .order-header{
    display:flex; align-items:center; justify-content:space-between; gap:.75rem;
    padding-bottom:.65rem; border-bottom:1px solid var(--hair);
  }
  .order-footer{
    display:flex; align-items:center; justify-content:space-between; gap:.75rem;
    padding-top:.65rem; border-top:1px solid var(--hair);
  }

  .order-title{ color:var(--text); font-weight:800; }
  .order-meta{ color:var(--muted); font-size:.85rem; letter-spacing:.1px; }
  .order-total{ color:var(--text); font-size:.95rem; }
  .order-total b{ font-weight:800; }

  /* شارة الحالة */
  .status-badge{
    display:inline-flex; align-items:center; gap:.35rem;
    font-size:.75rem; font-weight:800; padding:.35rem .65rem; border-radius:999px;
    white-space:nowrap; border:1px solid transparent;
    box-shadow: inset 0 0 0 1px rgba(255,255,255,.65);
  }
  .status-pending   { background:#FEF8E7; color:#8A5B10; border-color:#FBEEC5; }
  .status-processing{ background:#ECF3FF; color:#1E40AF; border-color:#DCE8FF; }
  .status-shipped   { background:#EEF0FF; color:#3730A3; border-color:#E1E5FF; }
  .status-delivered { background:#EAFBF0; color:#166534; border-color:#CFF6DD; }
  .status-cancelled { background:#F6F7F9; color:#374151; border-color:#ECEFF3; }
  .status-returned  { background:#FFECEF; color:#8F1D1D; border-color:#FFD5DA; }

  /* شارات الحالة في الوضع الليلي */
  html.dark .status-pending   { background:rgba(250,204,21,.14); color:#facc15; border-color:rgba(250,204,21,.28); box-shadow:none; }
  html.dark .status-processing{ background:rgba(59,130,246,.14); color:#93c5fd; border-color:rgba(59,130,246,.28); box-shadow:none; }
  html.dark .status-shipped   { background:rgba(99,102,241,.14); color:#a5b4fc; border-color:rgba(99,102,241,.28); box-shadow:none; }
  html.dark .status-delivered { background:rgba(34,197,94,.14); color:#86efac; border-color:rgba(34,197,94,.28); box-shadow:none; }
  html.dark .status-cancelled { background:rgba(148,163,184,.12); color:#cbd5e1; border-color:rgba(148,163,184,.24); box-shadow:none; }
  html.dark .status-returned  { background:rgba(244,63,94,.14); color:#fda4af; border-color:rgba(244,63,94,.28); box-shadow:none; }

  /* معرض الصور */
  .thumbs{ display:flex; gap:.55rem; overflow-x:auto; padding:.7rem 0 .25rem; -ms-overflow-style:none; scrollbar-width:thin; }
  .thumbs::-webkit-scrollbar{ height:6px; }
  .thumbs::-webkit-scrollbar-thumb{ background:var(--soft); border-radius:999px; }
  .thumb{
    width:50px; height:50px; border-radius:12px; object-fit:cover; flex:0 0 50px;
    background:var(--surface); box-shadow: inset 0 0 0 1px var(--hair);
  }
  .thumb-more{
    width:50px; height:50px; border-radius:12px; background:var(--brand-bg);
    color:var(--brand); display:grid; place-items:center; font-weight:900;
    box-shadow: inset 0 0 0 1px var(--hair);
    flex:0 0 50px;
  }

  /* زر التفاصيل – ستايل براند */
  .btn-outline-brand{
    display:inline-flex; align-items:center; gap:.4rem;
    color:var(--brand); padding:.5rem .85rem; border-radius:12px;
    font-weight:900; font-size:.85rem; background:var(--surface);
    box-shadow: inset 0 0 0 1.5px var(--brand);
    transition:.18s ease;
  }
  .btn-outline-brand:hover{
    background:var(--brand); color:#fff; box-shadow:none;
  }
  .btn-outline-brand i{ font-size:1rem; }
  html.dark .btn-outline-brand{ background:transparent; box-shadow: inset 0 0 0 1.5px var(--brand); }

  /* الحالة الفارغة */
  .empty-state{
    text-align:center; padding:2rem 1rem; background:var(--surface); border-radius:16px;
    box-shadow: 0 12px 30px rgba(205,137,133,.10), inset 0 0 0 1px var(--hair);
  }
  html.dark .empty-state{
    box-shadow: 0 12px 30px rgba(0,0,0,.36), inset 0 0 0 1px var(--border);
  }
  .empty-state .icon{ font-size:3rem; color:#ddcfc2; }
  html.dark .empty-state .icon{ color:#3b3b3b; }

  .btn-brand{
    display:inline-block; background:var(--brand-dark); color:#fff; font-weight:900;
    padding:.6rem 1.05rem; border-radius:12px; transition:.2s ease;
  }
  .btn-brand:hover{ background:#a8514d; }

  /* الباجينيشن */
  .pagination{
    display:flex; justify-content:center; gap:.35rem; margin-top:1.1rem; flex-wrap:wrap;
  }
  .pagination .page-item .page-link{
    background:var(--brand-bg)!important; color:var(--brand-dark)!important;
    border:none!important; box-shadow: inset 0 0 0 1px var(--soft);
    font-weight:800; border-radius:12px; padding:.5rem .85rem; font-size:.875rem; min-width:2.5rem; transition:.18s ease;
  }
  .pagination .page-item .page-link:hover{
    background:#dcaca9!important; color:#fff!important; box-shadow:none;
  }
  .pagination .page-item.active .page-link{
    background:var(--brand-dark)!important; color:#fff!important; box-shadow:none;
  }
  .pagination .page-item.disabled .page-link{
    color:#c7c7c7!important; background:var(--brand-bg)!important; box-shadow: inset 0 0 0 1px #eee;
  }
  html.dark .pagination .page-item .page-link{
    background:#0f172a!important; color:var(--brand)!important; box-shadow: inset 0 0 0 1px var(--border);
  }
  html.dark .pagination .page-item .page-link:hover{
    background:#1f2937!important; color:#fff!important; box-shadow:none;
  }
  html.dark .pagination .page-item.active .page-link{
    background:var(--brand)!important; color:#111827!important;
  }
  html.dark .pagination .page-item.disabled .page-link{
    color:#475569!important; background:#0b0f14!important; box-shadow: inset 0 0 0 1px var(--border);
  }

  /* موبايل */
  @media (max-width: 640px){
    .surface{ padding:0; }
    .order-card{ padding:.9rem; }
    .order-header{ flex-direction:column; align-items:flex-start; }
    .order-footer{ flex-direction:column; align-items:flex-start; gap:.6rem; }
    .btn-outline-brand{ width:100%; justify-content:center; }
  }
  /* إخفاء أزرار السابق / التالي / الأول / الأخير */
.pagination .page-item:first-child,
.pagination .page-item:last-child {
  display: none !important;
}

</style>
@endpush

@section('profile-content')
<div class="surface">
  <div class="page-head mb-4 md:mb-6">
    <h2 class="text-xl md:text-2xl"><br>سجل طلباتي</h2>
    <p class="text-sm md:text-base mt-1">هنا يمكنك تتبّع جميع طلباتك الحالية والسابقة</p>
  </div>

  @if($orders->isEmpty())
    <div class="empty-state">
      <i class="bi bi-receipt icon"></i>
      <p class="mt-3" style="color:var(--muted)">لم تقومي بأي طلبات بعد</p>
      <a href="{{ route('shop') }}" class="btn-brand mt-3">ابدأ التسوق الآن</a>
    </div>
  @else
    <div class="space-y-4 md:space-y-5">
      @foreach($orders as $order)
        @php
          $statusKey = $order->status;
          $statusText = match($statusKey){
            'pending'    => 'قيد الانتظار',
            'processing' => 'قيد المعالجة',
            'shipped'    => 'تم الشحن',
            'delivered'  => 'تم التوصيل',
            'cancelled'  => 'ملغي',
            'returned'   => 'مرتجع',
            default      => $statusKey,
          };
        @endphp

        <div class="order-card">
          {{-- رأس البطاقة --}}
          <div class="order-header">
            <div>
              <div class="order-title text-sm md:text-base">طلب رقم #{{ $order->id }}</div>
              <div class="order-meta">تاريخ الطلب: {{ $order->created_at->format('Y-m-d') }}</div>
            </div>
            <div>
              <span class="status-badge status-{{ $statusKey }}">
                <i class="bi bi-circle-fill" style="font-size:.55rem"></i>
                {{ $statusText }}
              </span>
            </div>
          </div>

          {{-- صور المنتجات --}}
          <div class="thumbs">
            @foreach($order->items->take(8) as $item)
              @if($item->product)
                <img
                  src="{{ $item->product->firstImage ? asset('storage/' . $item->product->firstImage->image_path) : 'https://placehold.co/96x96/f9f5f1/cd8985?text=Img' }}"
                  alt="{{ $item->product->name_translated }}"
                  class="thumb"
                >
              @endif
            @endforeach
            @if($order->items->count() > 8)
              <div class="thumb-more">+{{ $order->items->count() - 8 }}</div>
            @endif
          </div>

          {{-- تذييل البطاقة --}}
          <div class="order-footer">
            <p class="order-total">
              الإجمالي: <b>{{ number_format($order->total_amount, 0) }} د.ع</b>
            </p>
            <a href="{{ route('profile.orders.show', $order->id) }}" class="btn-outline-brand">
              عرض التفاصيل <i class="bi bi-chevron-left"></i>
            </a>
          </div>
        </div>
      @endforeach
    </div>

    {{-- Pagination --}}
    <div class="mt-6 md:mt-8">
      {{ $orders->onEachSide(1)->links() }}
    </div>
  @endif
</div>
@endsection
