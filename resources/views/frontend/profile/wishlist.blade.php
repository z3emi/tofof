@extends('frontend.profile.layout')
@section('title', __('profile.wishlist_title'))

@push('styles')
<style>
:root{
  /* Brand palette */
  --primary-color:#0F2A44; --primary-hover:#0A1D2F; --secondary-color:#E6E6E6; --accent-color:#eadbcd;

  /* Base (Light) */
  --bg:#FFFFFF; --bg-soft:#EFF6FF; --surface:#ffffff; --card-bg:#EFF6FF;
  --text:#111827; --text-soft:#555; --muted:#999; --border:#DBEAFE;

  /* Gradients (light) */
  --hero-start:rgba(239,246,255,.6); --hero-end:rgba(219,234,254,.6);
  --cat-grad-from:#FFFFFF; --cat-grad-to:#EFF6FF;

  /* Slider overlay */
  --slider-overlay-from:rgba(59,130,246,.8); --slider-overlay-to:rgba(37,99,235,.8);

  /* soft tint */
  --soft:#E6E6E6;
}
html.dark{
  --bg:#0b0f14; --bg-soft:#0f172a; --surface:#0f172a; --card-bg:#111827;
  --text:#e5e7eb; --text-soft:#cbd5e1; --muted:#94a3b8; --border:#1f2937;

  --hero-start:rgba(15,23,42,.6); --hero-end:rgba(17,24,39,.6);
  --cat-grad-from:#0b0f14; --cat-grad-to:#0f172a;

  --slider-overlay-from:rgba(59,130,246,.75); --slider-overlay-to:rgba(37,99,235,.75);
  --soft:rgba(55,65,81,.35);
}

/* سطح موحد خفيف */
.surface{ background:transparent; border-radius:16px; }

/* رأس الصفحة */
.page-head h2{ color:var(--text); font-weight:800; }
.page-head p{ color:var(--text-soft); }

/* زر رئيسي */
.btn-brand{
  display:inline-flex; align-items:center; gap:.45rem;
  background:var(--primary-color); color:var(--surface); font-weight:800;
  padding:.55rem 1rem; border-radius:12px; border:0; transition:.18s ease; text-decoration:none;
}
.btn-brand:hover{ background:var(--primary-hover); color:var(--surface); }

/* ===== بطاقة المتجر (مطابقة) ===== */
.shop-card{
  position:relative; background:var(--surface); border-radius:16px; overflow:hidden;
  border:1px solid var(--border);
  box-shadow: 0 6px 18px rgba(0,0,0,.06);
  transition: transform .18s ease, box-shadow .2s ease, border-color .2s ease;
}
.shop-card:hover{
  transform: translateY(-2px);
  box-shadow: 0 12px 28px rgba(0,0,0,.10);
  border-color: var(--soft);
}
.shop-thumb-wrap{
  position:relative; aspect-ratio:1/1; overflow:hidden; background:var(--card-bg);
}
.shop-thumb{ width:100%; height:100%; object-fit:cover; transition: transform .35s ease; }
.shop-card:hover .shop-thumb{ transform: scale(1.05); }

.shop-sale-badge{
  position:absolute; top:.6rem; right:.6rem; z-index:2;
  background:#0F2A44; color:#D4AF37; font-weight:800; font-size:.75rem;
  padding:.35rem .55rem; border-radius:999px; border: 1px solid #D4AF37;
}
/* بادج منتهي الكمية */
.shop-out-badge{
  position:absolute; top:.6rem; right:.6rem; z-index:2;
  background:#9CA3AF; color:#fff; font-weight:800; font-size:.75rem;
  padding:.35rem .55rem; border-radius:999px;
}
.shop-wish{
  position:absolute; top:.6rem; left:.6rem; z-index:2;
  width:36px; height:36px; display:flex; align-items:center; justify-content:center;
  border-radius:999px;
  background:rgba(255,255,255,.9);
  transition: background .2s ease, transform .15s ease;
}
html.dark .shop-wish{ background:rgba(17,24,39,.85); }
.shop-wish:hover{ transform: scale(1.05); }

.shop-body{ padding:.85rem .9rem 1rem; text-align:right; }
.shop-title{
  color:var(--text); font-weight:800; font-size:.98rem; line-height:1.35;
  display:-webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow:hidden;
}
.shop-meta{ color:var(--muted); font-size:.8rem; margin-top:.2rem; }

.shop-price{ display:flex; align-items:center; gap:.4rem; margin-top:.5rem; }
.shop-price .current{ color:#3B82F6; font-weight:900; font-size:1.05rem; }
.shop-price .old{ color:#9ca3af; text-decoration:line-through; font-size:.85rem; }

/* زر إضافة للسلة */
.shop-add{
  margin-top:.75rem; width:100%; padding:.55rem .75rem; border-radius:12px; border:0;
  background:#0F2A44; color:#fff; display:flex; align-items:center; justify-content:center; gap:.5rem;
  font-weight:800; transition: background .18s ease, transform .1s ease;
  border: 1px solid #D4AF37;
}
.shop-add:hover{ background:#0A1D2F; color: #D4AF37; }
.shop-add:disabled{ opacity:.8; cursor:not-allowed; }

/* حالة عدم وجود عناصر */
.empty-state{
  text-align:center; padding:2rem 1rem; background:var(--surface); border-radius:16px;
  border:1px solid var(--border); box-shadow: 0 6px 18px rgba(0,0,0,.06);
}
.empty-state .icon{ font-size:3rem; color:#ddcfc2; }

/* شبكة */
.grid-favs{ display:grid; gap:14px; grid-template-columns:1fr 1fr; }
@media(min-width:768px){ .grid-favs{ gap:18px; grid-template-columns: repeat(3,minmax(0,1fr)); } }
@media(min-width:1280px){ .grid-favs{ gap:20px; grid-template-columns: repeat(4,minmax(0,1fr)); } }

/* Dark tweaks */
html.dark .bg-white{ background-color:var(--surface) !important; }
html.dark [class*="border-[#eadbcd]"]{ border-color:var(--border) !important; }
html.dark .shop-card{ border-color:var(--border); box-shadow: 0 10px 26px rgba(0,0,0,.35); }
</style>
@endpush

@section('profile-content')
<div class="surface">
  <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-3 mb-4 md:mb-6 page-head">
    <div>
      <h2 class="text-xl md:text-2xl"><br>{{ __('profile.wishlist_title') }}</h2>
      <p class="text-sm mt-1">{{ __('profile.wishlist_subheading') }}</p>
    </div>
  </div>

  @if ($favorites->isEmpty())
    <div class="empty-state">
      <i class="bi bi-heart icon"></i>
      <p class="mt-3">{{ __('profile.no_favorites') }}</p>
      <a href="{{ route('shop') }}" class="btn-brand mt-3">
        <i class="bi bi-bag-plus"></i> {{ __('profile.browse_store') }}
      </a>
    </div>
  @else
    <div class="grid-favs">
      @foreach ($favorites as $favorite)
        @if ($product = $favorite->product)
        <!-- ===== بطاقة المنتج (مطابقة المتجر) ===== -->
        @php
          /**
           * كشف نفاد الكمية — شامل وجازم:
           * - يتحقق من عدة أسماء محتملة للمخزون.
           * - يتحقق من فلاق التوفّر.
           * - يتحقق من status النصّي (out_of_stock ...).
           * - لو ماكو أي مؤشر واضح للتوفّر => نعدّه "منتهي" افتراضيًا.
           */
          $qtyFields = [
            $product->quantity ?? null,
            $product->stock ?? null,
            $product->stock_quantity ?? null,
            $product->stock_qty ?? null,
          ];
          $qtyValue = null;
          foreach ($qtyFields as $q) { if (!is_null($q)) { $qtyValue = (int)$q; break; } }

          $flagFalseList = [
            isset($product->in_stock) ? !$product->in_stock : null,
            isset($product->is_in_stock) ? !$product->is_in_stock : null,
            isset($product->available) ? !$product->available : null,
            isset($product->is_available) ? !$product->is_available : null,
          ];
          $hasExplicitFalse = false;
          foreach ($flagFalseList as $f) { if ($f !== null && $f === true) { $hasExplicitFalse = true; break; } }

          $statusRaw = strtolower((string)($product->status ?? $product->stock_status ?? ''));
          $statusOutValues = ['out_of_stock','sold_out','unavailable','inactive','disabled','ended','ended_stock','not_available','na','0'];
          $statusSaysOut = in_array($statusRaw, $statusOutValues, true);

          // اعتبارات إضافية (سعر مفقود/صفر => عادة غير قابل للبيع)
          $priceZeroOrNull = !isset($product->price) || (float)$product->price <= 0;

          if (!is_null($qtyValue)) {
              $isOut = $qtyValue <= 0;
          } elseif ($hasExplicitFalse || $statusSaysOut || $priceZeroOrNull) {
              $isOut = true;
          } else {
              // افتراضيًا: اعتبره منتهي حتى لا يظهر زر الإضافة بالغلط
              $isOut = true;
          }
        @endphp

        <div class="shop-card"
             x-data="{ added:false, loadingAdd:false, isFavorite:true, loadingFav:false }"
             x-show="isFavorite" x-transition:leave="transition ease-in duration-300">
          
          <a href="{{ route('product.detail', $product) }}" class="block shop-thumb-wrap group">
            @if ($product->firstImage)
              <img src="{{ asset('storage/' . $product->firstImage->image_path) }}" class="shop-thumb" loading="lazy" alt="{{ $product->name_translated }}">
            @else
              <img src="https://placehold.co/400x400/f9f5f1/cd8985?text=No+Image" class="shop-thumb" loading="lazy" alt="No image">
            @endif

            @if($isOut)
              <div class="shop-out-badge">{{ __('common.out_of_stock') }}</div>
            @elseif($product->isOnSale())
              @php $discountPercentage = round((($product->price - $product->sale_price) / $product->price) * 100); @endphp
              <div class="shop-sale-badge">-{{ $discountPercentage }}%</div>
            @endif

            <!-- زر المفضلة العائم (نفس المتجر) -->
            <button
              @click.prevent="
                loadingFav = true;
                fetch('{{ url('/wishlist/toggle-async') }}/{{ $product->id }}', {
                  method: 'POST',
                  headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                }).then(res => res.json()).then(data => {
                  if (data.success) {
                    isFavorite = false;
                    window.dispatchEvent(new CustomEvent('wishlist-updated', { detail: { count: data.wishlistCount } }));
                  }
                }).finally(() => loadingFav = false);
              "
              class="shop-wish"
              :disabled="loadingFav"
              title="{{ __('common.remove_from_wishlist') }}"
            >
              <i class="bi bi-heart-fill text-xl" style="color:#D4AF37;"></i>
            </button>
          </a>

          <div class="shop-body">
            <h3 class="shop-title">{{ $product->name_translated }}</h3>
            <div class="shop-meta">{{ $product->brand->name ?? '' }}</div>

            <div class="shop-price">
              @if($product->isOnSale() && !$isOut)
                <span class="current">{{ number_format($product->sale_price, 0) }} {{ __('common.currency') }}</span>
                <span class="old">{{ number_format($product->price, 0) }} {{ __('common.currency') }}</span>
              @else
                <span class="current">{{ number_format($product->price, 0) }} {{ __('common.currency') }}</span>
              @endif
            </div>

            @if($isOut)
              <button class="shop-add" style="background:#9CA3AF;" disabled>
                <i class="bi bi-x-circle"></i> {{ __('common.out_of_stock') }}
              </button>
            @else
              <button
                @click.prevent="
                  loadingAdd = true;
                  fetch('{{ route('cart.store') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ product_id: {{ $product->id }}, quantity: 1 })
                  }).then(res => res.json()).then(data => {
                    if(data.success){
                      added = true;
                      window.dispatchEvent(new CustomEvent('cart-updated', { detail: { cartCount: data.cartCount } }));
                      setTimeout(() => added = false, 2000);
                    } else { alert(data.message || '{{ __('common.connection_error') }}'); }
                  }).finally(() => loadingAdd = false);
                "
                class="shop-add"
                :disabled="loadingAdd || added"
              >
                <span x-show="!added && !loadingAdd"><i class="bi bi-cart-plus"></i> {{ __('common.add_to_cart') }}</span>
                <span x-show="loadingAdd"><i class="bi bi-arrow-repeat animate-spin"></i></span>
                <span x-show="added"><i class="bi bi-check-lg"></i> {{ __('common.added_to_cart') }}</span>
              </button>
            @endif
          </div>
        </div>
        <!-- ===== /بطاقة المنتج ===== -->
        @endif
      @endforeach
    </div>
  @endif
</div>
@endsection
