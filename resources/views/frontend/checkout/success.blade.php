@extends('layouts.app')

@section('title', 'تم استلام طلبك بنجاح')

@push('styles')
<style>
  /* ====== Scoped vars (won't leak) ====== */
  .order-success{
    --card-bg:#ffffff;
    --card-border:#e5e7eb;
    --body-bg:#f9fafb;
    --text:#1f2937;
    --muted:#6b7280;
    --brand:#be6661;
    --brand-soft:#f3e5e3;
    --ok:#22c55e;
    --ok-soft:#dcfce7;
    --blue-soft:#eff6ff;
    --blue:#1d4ed8;
  }
  html.dark .order-success{
    --card-bg:#0f172a;
    --card-border:#1f2937;
    --body-bg:#0b0f14;
    --text:#e5e7eb;
    --muted:#9ca3af;
    --brand:#f0b0ad;
    --brand-soft:rgba(240,176,173,.08);
    --ok:#22c55e;
    --ok-soft:rgba(34,197,94,.12);
    --blue-soft:rgba(29,78,216,.12);
    --blue:#93c5fd;
  }

  /* Confetti background (scoped) */
  .order-success .confetti-bg{
    position: relative; overflow: hidden;
    background: var(--body-bg);
  }
  .order-success .confetti-bg::before{
    content:''; position:absolute; inset:0; z-index:0; opacity:.28;
    background-image:
      radial-gradient(circle at 15% 50%, #a8e6cf 2px, transparent 0),
      radial-gradient(circle at 85% 30%, #dcedc1 2px, transparent 0),
      radial-gradient(circle at 25% 90%, #ffd3b6 2px, transparent 0),
      radial-gradient(circle at 75% 70%, #ffaaa5 2px, transparent 0);
    background-size: 50px 50px;
    pointer-events:none;
  }
  html.dark .order-success .confetti-bg::before{
    opacity:.22;
    background-image:
      radial-gradient(circle at 15% 50%, rgba(168,230,207,.75) 2px, transparent 0),
      radial-gradient(circle at 85% 30%, rgba(220,237,193,.75) 2px, transparent 0),
      radial-gradient(circle at 25% 90%, rgba(255,211,182,.75) 2px, transparent 0),
      radial-gradient(circle at 75% 70%, rgba(255,170,165,.75) 2px, transparent 0);
  }

  /* Card */
  .order-success .card{
    background: var(--card-bg);
    border:1px solid var(--card-border);
    color: var(--text);
    border-radius: 18px;
    box-shadow:
      0 12px 28px rgba(0,0,0,.08),
      0 6px 12px rgba(0,0,0,.05),
      inset 0 0 0 1px rgba(255,255,255,.04);
    position: relative;
  }

  /* Icon ring */
  .order-success .ok-ring{
    width: 86px; height: 86px; border-radius: 9999px;
    background: var(--ok-soft);
    display:flex; align-items:center; justify-content:center;
    box-shadow: 0 8px 20px rgba(34,197,94,.15), inset 0 0 0 6px #fff3;
  }
  html.dark .order-success .ok-ring{
    box-shadow: 0 8px 20px rgba(34,197,94,.1), inset 0 0 0 6px rgba(255,255,255,.06);
  }

  /* Info box */
  .order-success .info{
    background: var(--blue-soft);
    border-inline-start: 4px solid var(--blue);
    border-radius: 12px;
  }

  /* Order id box */
  .order-success .order-box{
    background: var(--brand-soft);
    border: 1px dashed var(--card-border);
    border-radius: 14px;
  }

  /* Buttons (keep routes as-is) */
  .order-success .btn{
    display:inline-flex; align-items:center; justify-content:center; gap:.5rem;
    font-weight:800; border-radius: 12px; padding: .8rem 1.2rem;
    transition: .18s ease;
    text-decoration:none;
  }
  .order-success .btn-brand{
    background: var(--brand); color:#fff;
  }
  .order-success .btn-brand:hover{ filter:brightness(.95); }
  .order-success .btn-ghost{
    background: #edf2f7; color:#111827;
  }
  html.dark .order-success .btn-ghost{
    background:#1f2937; color:#e5e7eb;
  }
  .order-success .btn-ghost:hover{ filter:brightness(1.05); }

  /* Subtext colors */
  .order-success .muted{ color: var(--muted); }
</style>
@endpush

@section('content')
<div class="order-success min-h-screen confetti-bg flex items-center justify-center px-4 py-12">
  <div class="content-wrapper max-w-2xl w-full relative z-10">
    <div class="card p-8 md:p-10 text-center">

      {{-- Success Icon --}}
      <div class="ok-ring mx-auto mb-5">
        <i class="bi bi-patch-check-fill text-4xl" style="color:var(--ok)"></i>
      </div>

      <h1 class="text-2xl md:text-3xl font-extrabold mb-2" style="color:var(--text)">شكراً لك، طلبك قيد التنفيذ!</h1>

      {{-- Display the success message from the controller --}}
      @if(session('success'))
        <p class="text-sm md:text-base muted mb-6">{{ session('success') }}</p>
      @else
        <p class="text-sm md:text-base muted mb-6">تم استلام طلبك بنجاح. سنتصل بك قريباً لتأكيد التفاصيل.</p>
      @endif

      {{-- Order ID Section (if provided) --}}
      @if(isset($order))
      <div class="order-box py-4 px-5 my-6">
        <p class="text-xs muted mb-1">رقم الطلب الخاص بك هو:</p>
        <p class="text-2xl font-mono font-extrabold tracking-wider">{{ $order->id }}</p>
      </div>
      @endif

      {{-- What's next --}}
      <div class="info text-right my-8 p-4">
        <h3 class="font-extrabold" style="color:var(--blue)">ماذا سيحدث الآن؟</h3>
        <ul class="list-disc list-inside text-sm mt-2 space-y-1" style="color:var(--blue)">
          <li>ستصلك مكالمة هاتفية من فريقنا خلال 24 ساعة لتأكيد الطلب.</li>
          <li>سيتم تجهيز وشحن طلبك بعد التأكيد.</li>
          <li>يمكنك تتبع حالة الطلب من صفحة "طلباتي".</li>
        </ul>
      </div>

      {{-- Actions (routes kept as provided) --}}
      <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
        <a href="{{ route('shop') }}" class="btn btn-brand w-full sm:w-auto">
          <i class="bi bi-arrow-left"></i> متابعة التسوق
        </a>
        {{-- أبقيته كما هو، لو عندك مسار لطلبات المستخدم بدّله لاحقاً --}}
        <a href="{{ route('profile.orders') }}" class="btn btn-ghost w-full sm:w-auto">
          عرض طلباتي
        </a>
      </div>

    </div>
  </div>
</div>
@endsection
@if(isset($order))
    @php
        // تجهيز بيانات الطلب لاستخدامها مع Meta Pixel Purchase
        $purchaseValue = (float) ($order->total ?? $order->final_total ?? 0);

        $purchaseContentIds = [];
        $purchaseContents   = [];

        foreach ($order->items ?? [] as $item) {
            $purchaseContentIds[] = $item->product_id;
            $purchaseContents[] = [
                'id'       => (string) $item->product_id,
                'quantity' => (int) $item->quantity,
            ];
        }
    @endphp

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof fbq === 'function') {
                    fbq('track', 'Purchase', {
                        value: {{ $purchaseValue }},
                        currency: 'IQD',
                        contents: @json($purchaseContents),
                        content_ids: @json($purchaseContentIds),
                        content_type: 'product',
                        num_items: {{ count($purchaseContentIds) }}
                    });
                }
            });
        </script>
    @endpush
@endif