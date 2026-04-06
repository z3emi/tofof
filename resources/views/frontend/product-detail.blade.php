@php
    $favoriteProductIds = $favoriteProductIds ?? [];
@endphp

@extends('layouts.app')

@section('title', $product->name_translated)

@push('styles')
<style>
    /* ========== Tokens خاصة بهذه الصفحة ========== */
    .product-page{
        --brand:#6d0e16;
        --brand-dark:#4f0b12;
        --brand-gold:#d59e06;
        --bg:#F7F7F7;
        --bg-soft:#FFFFFF;
        --surface:#ffffff;
        --text:#111111;
        --text-soft:#999999;
        --border:#E5E5E5;
        --tile-shadow: 0 8px 22px rgba(0,0,0,.06);
        --tile-shadow-lg: 0 15px 30px rgba(0,0,0,.10);
    }
    html.dark .product-page{
        --bg:#0B0B0B;
        --bg-soft:#1F1F1F;
        --surface:#1F1F1F;
        --text:#FFFFFF;
        --text-soft:#BBBBBB;
        --border:#1F1F1F;
        --tile-shadow: 0 10px 26px rgba(0,0,0,.28);
        --tile-shadow-lg: 0 18px 40px rgba(0,0,0,.36);
    }

    /* خلفية الصفحة */
    .product-page{ background: var(--bg); }

    /* ===== Utilities للعلامة التجارية (مستخدمة بالصفحة) ===== */
    .product-page .text-brand-primary{ color: var(--brand) !important; }
    .product-page .text-brand-dark{ color: var(--text) !important; }
    .product-page .text-brand-text{ color: var(--text) !important; }
    .product-page .bg-brand-primary{ background-color: var(--brand) !important; color:#fff; }
    .product-page .bg-brand-accent{ background-color: var(--brand-dark) !important; color:#fff; }

    /* ===== نصوص عامة داخل الصفحة ===== */
    html.dark .product-page .text-gray-700,
    html.dark .product-page .text-brand-dark,
    html.dark .product-page .text-brand-text{ color: var(--text) !important; }
    html.dark .product-page .text-gray-600,
    html.dark .product-page .text-gray-500{ color: var(--text-soft) !important; }

    /* روابط */
    .product-page a:hover{ opacity:.9; }
    html.dark .product-page a{ color: var(--brand); }

    /* ===== حدود وخلفيات عامة ===== */
    html.dark .product-page .bg-white,
    html.dark .product-page .border.rounded-lg.p-4,
    html.dark .product-page .border.rounded.p-4,
    html.dark .product-page .border.rounded{ background: var(--surface) !important; }
    html.dark .product-page .border,
    html.dark .product-page .border-t,
    html.dark .product-page .border-b,
    html.dark .product-page .border-gray-200,
    html.dark .product-page .border-gray-300{ border-color: var(--border) !important; }

    /* ===== معرض الصور ===== */
    .product-gallery-container {
        position: relative;
    }
    .main-image-wrapper {
        background: #fff;
        padding: 10px;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.06);
        border: 1px solid var(--border);
        transition: transform 0.3s ease;
    }
    .image-zoom-container {
        cursor: zoom-in;
        border-radius: 14px;
        background: #fff;
        position: relative;
    }
    .thumbnail-scroll {
        scrollbar-width: thin;
        scrollbar-color: var(--brand) transparent;
    }
    .thumbnail-scroll::-webkit-scrollbar { height: 4px; }
    .thumbnail-scroll::-webkit-scrollbar-thumb { background: var(--brand); border-radius: 10px; }
    
    .thumb-btn {
        width: 76px;
        height: 76px;
        border-radius: 12px;
        border: 2px solid transparent;
        padding: 4px;
        background: #fff;
        box-shadow: 0 4px 12px rgba(0,0,0,0.04);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        flex-shrink: 0;
    }
    .thumb-btn.active {
        border-color: var(--brand);
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(109, 14, 22, 0.15);
    }
    .thumb-btn img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 8px;
    }
    
    .zoom-hint {
        position: absolute;
        bottom: 15px;
        right: 15px;
        background: rgba(255,255,255,0.8);
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text);
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        backdrop-filter: blur(4px);
        z-index: 5;
        transition: 0.3s;
    }
    .image-zoom-container:hover .zoom-hint { transform: scale(1.1); background: #fff; }

    html.dark .main-image-wrapper { background: #1a1a1a; }
    html.dark .image-zoom-container { background: #1a1a1a; }
    html.dark .thumb-btn { background: #1a1a1a; }

    .zoom-modal-overlay {
        background-color: rgba(0, 0, 0, 0.85);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        transition: all 0.4s ease;
    }
    .zoomed-image-card {
        background: transparent;
        padding: 5px;
        border-radius: 12px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }

    /* ===================================================
        تنسيق موحد للأزرار الرئيسية والثانوية
       =================================================== */
    .btn-primary, .btn-secondary {
        display: flex; justify-content: center; align-items: center;
        gap: 0.5rem; width: 100%; padding: 0.75rem 1.5rem;
        border-radius: 0.5rem; font-size: 1.125rem; transition: all 0.3s ease;
    }
    .btn-primary {
        background-color: var(--brand); color: white; border: none; font-weight: 700;
    }
    .btn-primary:hover {
        background-color: var(--brand-dark); transform: translateY(-2px);
    }
    .btn-secondary {
        background-color: var(--surface); color: var(--text);
        border: 1px solid var(--border); font-weight: 600;
    }
    .btn-secondary:hover { background-color: #f9fafb; }
    html.dark .btn-secondary {
        border-color: var(--border); background-color: var(--surface); color: var(--text);
    }
    html.dark .btn-secondary:hover { background-color: #1f2937; }

    /* ===================================================
        تخصيص موحد لزر "منتهي الكمية"
       =================================================== */
    .btn-primary[disabled] {
        background-color: #e5e7eb !important; color: #111827 !important;
        cursor: not-allowed; box-shadow: none; transform: none; opacity: 1;
    }
    html.dark .product-page .btn-primary[disabled] {
        background-color: #374151 !important; color: #9ca3af !important;
    }

    /* ===================================================
        ✅ [تعديل] تنسيق جديد لمتحكم الكمية
       =================================================== */
    .qty-wrap {
        display: inline-flex;
        border: 1px solid var(--border);
        border-radius: 999px; /* Pill shape */
        background: var(--surface);
        box-shadow: 0 4px 12px rgba(0,0,0,0.04);
        overflow: hidden;
    }
    .qty-btn {
        background: transparent;
        border: none;
        padding: 0.5rem 1.25rem;
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--text);
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .qty-btn:hover { background-color: var(--bg-soft); }
    html.dark .qty-btn:hover { background-color: rgba(255,255,255,0.05); }
    .qty-input {
        width: 50px; height: 44px; text-align: center;
        font-weight: 700; border: none;
        border-left: 1px solid var(--border);
        border-right: 1px solid var(--border);
        background: transparent; color: var(--text);
        -moz-appearance: textfield; /* Firefox */
    }
    .qty-input::-webkit-outer-spin-button,
    .qty-input::-webkit-inner-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }
    html.dark .qty-input { border-color: var(--border) !important; }
    
    /* =============================================================
        تنسيقات بطاقة المنتج الجديدة من الصفحة الرئيسية
    ================================================================ */
    .product-page .product-card {
        background:#fff;
        border-radius:14px;
        border:2px solid transparent;
        box-shadow:0 4px 12px rgba(0,0,0,.05);
        transform:translate3d(0,0,0);
        transition: transform .35s cubic-bezier(.22,.61,.36,1), box-shadow .35s ease, border-color .25s ease;
        will-change: transform, box-shadow;
        display: flex;
        flex-direction: column;
    }
    .product-page .product-card:hover {
        transform:translate3d(0,-6px,0);
        box-shadow:0 16px 30px rgba(0,0,0,.10);
    }
    .product-page .product-content-link { display:flex; flex-direction:column; flex-grow:1; text-decoration:none; color:inherit; }
    .product-page .product-info { padding:12px; display:flex; flex-direction:column; gap:8px; text-align:center; flex-grow:1; }
    .product-page .product-title { font-weight:700; color:#2d2a2a; line-height:1.35; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; min-height:2.6em; word-break: break-word; }
    .product-page .price { color:var(--brand); font-weight:800; font-size:1.1rem; }
    .product-page .old { text-decoration:line-through; color:#9ca3af; font-size:.85rem; }
    .product-page .product-actions { display:flex; gap:8px; margin-top:auto; padding: 0 12px 12px; position:relative; z-index:2; }
    .product-page .product-actions .btn-primary { flex-grow:1; flex-shrink:1; min-width:0; overflow:hidden; height:44px; display:inline-flex; align-items:center; justify-content:center; padding:0 .75rem; white-space:nowrap; font-size:.9rem; }
    .product-page .product-actions .btn-primary:only-child { flex-grow:unset; width:100%; }
    .product-page .btn-fav { width:44px; height:44px; border-radius:999px; background-color:#e5e7eb; color:#4b5563; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0; transition:.2s; font-size:1.1rem; border:none; cursor:pointer; }
    .product-page .btn-fav:hover { background-color:#d1d5db; }
    .product-page .btn-fav.favorited { background-color:#fee2e2; color:#ef4444; }
    .product-page .product-image-container { aspect-ratio: 1 / 1; position:relative; overflow:hidden; border-radius:12px 12px 0 0; }
    .product-page .product-image-slider { position: relative; width: 100%; height: 100%; }
    .product-page .product-image-slider img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transition: transform 0.35s ease; }
    .product-page .product-dots { position: absolute; bottom: 8px; left: 50%; transform: translateX(-50%); display: flex; gap: 6px; z-index: 10; }
    .product-page .product-dot { width: 8px; height: 8px; background-color: rgba(255, 255, 255, 0.6); border-radius: 50%; transition: width 0.3s ease, background-color 0.3s ease, transform 0.3s ease; }
    .product-page .product-dot.active { width: 18px; border-radius: 4px; background-color: var(--brand); transform: scale(1.1); }
    html.dark .product-page .product-card { background-color:#1f2937; }
    html.dark .product-page .product-title { color:#f9fafb; }
    html.dark .product-page .btn-fav { background-color:#374151; color:#9ca3af; }
    html.dark .product-page .btn-fav:hover { background-color:#4b5563; }
    html.dark .product-page .btn-fav.favorited { background-color:rgba(205,137,133,.2); color:#f9a8d4; }
    /* شارة الخصم على كروت المنتجات في صفحة المنتج */
    .product-page .product-sale-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 20;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 700;
        color: #ffffff;
        background: #C81D25;
        box-shadow: 0 8px 16px rgba(0,0,0,.18);
        border: 1px solid #D4AF37;
        letter-spacing: 0.03em;
    }

    /* =============================================================
        تنسيق مخصص لشبكة عرض "منتجات مشابهة"
    ================================================================ */
    .product-page .related-products-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }
    @media (min-width: 768px) {
        .product-page .related-products-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1.5rem;
        }
    }
    @media (min-width: 1024px) {
        .product-page .related-products-grid {
            grid-template-columns: repeat(5, minmax(0, 1fr));
        }
    }

    /* عرض عدد محدد: 5 افتراضيا، و6 فقط على الهاتف الصغير */
    .product-page .related-products-grid > .product-card {
        display: none;
    }
    .product-page .related-products-grid > .product-card:nth-child(-n+5) {
        display: flex;
    }
    @media (max-width: 767px) {
        .product-page .related-products-grid > .product-card {
            display: none;
        }
        .product-page .related-products-grid > .product-card:nth-child(-n+6) {
            display: flex;
        }
    }
    /* =============================================================
        تنسيق الزووم (Hover Zoom) للصورة الرئيسية
    ================================================================ */
    .image-zoom-container {
        cursor: crosshair;
        transition: transform 0.1s ease-out;
    }
    .image-zoom-container img {
        transition: transform 0.3s ease-out, transform-origin 0.1s ease-out;
        will-change: transform, transform-origin;
    }

    /* =============================================================
        Reviews UI
    ================================================================ */
    .product-page .reviews-wrap { display: grid; gap: 0.9rem; }
    .product-page .review-form-card {
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 1rem;
        background: linear-gradient(180deg, #ffffff, #fbfbfb);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.04);
    }
    .product-page .review-card {
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 0.95rem 1rem;
        background: linear-gradient(180deg, #ffffff, #fafafa);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.04);
    }
    .product-page .review-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    .product-page .review-user {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        min-width: 0;
    }
    .product-page .review-avatar {
        width: 42px;
        height: 42px;
        border-radius: 999px;
        border: 1px solid var(--border);
        object-fit: cover;
        flex-shrink: 0;
    }
    .product-page .review-user-name {
        font-weight: 800;
        line-height: 1.15;
        color: var(--text);
    }
    .product-page .review-user-time {
        font-size: 0.78rem;
        color: #9ca3af;
        margin-top: 0.2rem;
    }
    .product-page .review-side {
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }
    .product-page .review-stars {
        display: inline-flex;
        align-items: center;
        color: #d59e06;
        gap: 2px;
    }
    .product-page .review-comment {
        margin-top: 0.75rem;
        font-size: 0.96rem;
        line-height: 1.8;
        color: var(--text);
        white-space: pre-line;
    }
    .product-page .review-admin-reply {
        margin-top: 0.7rem;
        border-radius: 12px;
        border: 1px solid #bfdbfe;
        background: #eff6ff;
        padding: 0.65rem 0.8rem;
    }
    .product-page .review-delete {
        color: #dc2626;
        font-size: 0.82rem;
        font-weight: 700;
        border: 0;
        background: transparent;
        padding: 0;
    }
    .product-page .review-delete:hover { text-decoration: underline; }
    .product-page .review-textarea {
        width: 100%;
        border: 1px solid var(--border);
        border-radius: 12px;
        background: #fff;
        padding: 0.62rem 0.72rem;
        color: var(--text);
    }
    .product-page .review-textarea:focus {
        outline: none;
        border-color: rgba(109, 14, 22, 0.45);
        box-shadow: 0 0 0 3px rgba(109, 14, 22, 0.12);
    }
    html.dark .product-page .review-form-card,
    html.dark .product-page .review-card {
        background: linear-gradient(180deg, #1f2937, #18202d);
        border-color: #374151;
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.28);
    }
    html.dark .product-page .review-textarea {
        background: #111827;
        border-color: #374151;
        color: #f3f4f6;
    }
    html.dark .product-page .review-admin-reply {
        background: rgba(30, 64, 175, 0.22);
        border-color: rgba(96, 165, 250, 0.45);
    }
    @media (max-width: 640px) {
        .product-page .review-card { padding: 0.85rem; }
        .product-page .review-avatar { width: 38px; height: 38px; }
        .product-page .review-user-name { font-size: 0.95rem; }
        .product-page .review-comment { font-size: 0.9rem; }
    }
</style>
@endpush

@section('content')
<div class="product-page">
    <div class="container mx-auto px-4 py-12">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-10 lg:gap-16 items-start"
             x-data="{
                 @if($product->images->isNotEmpty())
                     mainImage: '{{ asset('storage/' . $product->images->first()->image_path) }}',
                     defaultImage: '{{ asset('storage/' . $product->images->first()->image_path) }}',
                 @else
                     mainImage: 'https://placehold.co/600x600?text=No+Image',
                     defaultImage: 'https://placehold.co/600x600?text=No+Image',
                 @endif
                 quantity: 1,
                 selectedOptions: {},
                 selectedOptionValueIds: {},
                 options: @js($productOptionsPayload ?? []),
                 combinationImageMap: @js($combinationImageMap ?? []),
                 added: false,
                 loadingAdd: false,
                 isFavorite: {{ $isFavorited ? 'true' : 'false' }},
                 loadingFav: false,
                  imageZoomOpen: false,
                  init() {
                      this.$watch('imageZoomOpen', value => {
                          if (value) {
                              document.body.style.overflow = 'hidden';
                          } else {
                              document.body.style.overflow = '';
                          }
                      });
                  },
                  isZoomed: false,
                  zoomOrigin: 'center',
                  handleZoom(e) {
                      const rect = e.currentTarget.getBoundingClientRect();
                      const x = ((e.clientX - rect.left) / rect.width) * 100;
                      const y = ((e.clientY - rect.top) / rect.height) * 100;
                      this.zoomOrigin = `${x}% ${y}%`;
                      this.isZoomed = true;
                  },
                 selectOption(option, value) {
                     this.selectedOptionValueIds[option.id] = value.id;
                     this.selectedOptions[option.name] = value.label;
                     this.updateImageFromSelection();
                 },
                 optionIsSelected(option, value) {
                     return Number(this.selectedOptionValueIds[option.id] || 0) === Number(value.id);
                 },
                 resolveCombinationKey() {
                     if (!this.options.length) {
                         return null;
                     }

                     const pickedValues = [];
                     for (const option of this.options) {
                         const selectedId = Number(this.selectedOptionValueIds[option.id] || 0);
                         if (!selectedId) {
                             return null;
                         }
                         pickedValues.push(selectedId);
                     }

                     return pickedValues.sort((a, b) => a - b).join('-');
                 },
                 updateImageFromSelection() {
                     const key = this.resolveCombinationKey();
                     if (!key) {
                         this.mainImage = this.defaultImage;
                         return;
                     }

                     const mapped = this.combinationImageMap[key] || null;
                     this.mainImage = mapped || this.defaultImage;
                 }
             }">

            {{-- معرض الصور --}}
            <div class="md:col-span-2 product-gallery-container flex flex-col gap-6">
                <div class="main-image-wrapper">
                    <div class="relative w-full aspect-square rounded-lg overflow-hidden image-zoom-container shadow-inner" 
                         @mousemove="handleZoom($event)"
                         @mouseleave="isZoomed = false"
                         @click="imageZoomOpen = true">
                        
                        {{-- شارة الخصم --}}
                        @if($product->isOnSale())
                            @php
                                $mainDiscount = round(100 - ($product->sale_price / $product->price * 100));
                            @endphp
                            <div class="absolute top-4 left-4 z-10 bg-red-600 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg border border-white/20">
                                {{ __('product.discount_badge', ['percent' => $mainDiscount]) }}
                            </div>
                        @endif

                        <div class="zoom-hint">
                            <i class="bi bi-search text-lg"></i>
                        </div>

                        <img :src="mainImage" 
                             alt="{{ $product->name_translated }}" 
                             class="absolute inset-0 w-full h-full object-contain p-4" 
                             :style="{ 
                                 transform: isZoomed ? 'scale(2.5)' : 'scale(1)', 
                                 transformOrigin: zoomOrigin,
                                 transition: isZoomed ? 'none' : 'transform 0.4s cubic-bezier(0.4, 0, 0.2, 1)'
                             }"
                             loading="eager">
                    </div>
                </div>

                @if($product->images->count() > 1)
                <div class="thumbnail-scroll flex gap-3 overflow-x-auto pb-4 no-scrollbar">
                    @foreach($product->images as $image)
                        <button @click="mainImage = '{{ asset('storage/' . $image->image_path) }}'"
                                class="thumb-btn transition-all"
                                :class="mainImage === '{{ asset('storage/' . $image->image_path) }}' ? 'active' : 'opacity-70 hover:opacity-100 hover:border-gray-200'">
                            <img src="{{ asset('storage/' . $image->image_path) }}" alt="Thumbnail">
                        </button>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- معلومات المنتج --}}
            <div class="md:col-span-3">
                <div class="text-sm text-gray-500 mb-2">
                    <a href="{{ route('shop') }}" class="hover:text-brand-primary">{{ __('product.shop') }}</a>
                    @if($product->category)
                        / <a href="{{ route('shop', ['category' => $product->category->slug]) }}" class="hover:text-brand-primary">{{ $product->category->name_translated }}</a>
                    @endif
                </div>
                <h1 class="text-3xl lg:text-4xl font-bold text-brand-text mb-3">{{ $product->name_translated }}</h1>
                @php
                    $avg = round((float) ($product->average_rating ?? 0), 1);
                    $revCount = (int) ($product->reviews_count ?? 0);
                @endphp
                <div class="flex items-center gap-3 mb-2">
                    <div id="avg-stars" class="flex items-center text-yellow-500">
                        @for($i = 1; $i <= 5; $i++)
                            @php
                                $full = $i <= floor($avg);
                                $half = !$full && ($i - $avg) <= 0.5;
                            @endphp
                            <i class="bi {{ $full ? 'bi-star-fill' : ($half ? 'bi-star-half' : 'bi-star') }} text-xl me-1"></i>
                        @endfor
                    </div>
                    <div id="avg-text" class="text-sm text-gray-600">{{ number_format($avg,1) }} / 5 · <span id="rev-count">{{ $revCount }}</span> {{ __('product.rating') }}</div>
                </div>
                <span class="text-sm text-gray-500 mb-4">SKU: {{ $product->sku ?? 'N/A' }}</span>
                <div class="mb-6">
                    @if($product->isOnSale())
                        <span class="text-brand-primary font-bold text-3xl">{{ number_format($product->sale_price, 0) }} {{ __('common.currency') }}</span>
                        <span class="text-gray-400 line-through text-xl ml-3">{{ number_format($product->price, 0) }} {{ __('common.currency') }}</span>
                    @else
                        <span class="text-brand-primary font-bold text-3xl">{{ number_format($product->price, 0) }} {{ __('common.currency') }}</span>
                    @endif
                </div>
                <div class="border-t border-b border-gray-200 py-4 mb-8 space-y-3">
                    <div class="flex items-center text-sm text-gray-700"><span>{{ __('product.trusted_delivery') }}</span></div>
                    <div class="flex items-center text-sm text-gray-700"><span>{{ __('product.fast_processing') }}</span></div>
                    <div class="flex items-center text-sm text-gray-700"><span>{{ __('product.safe_checkout') }}</span></div>
                    <div class="flex items-center text-sm text-gray-700"><span>{{ __('product.authentic_products') }}</span></div>
                </div>

                <template x-if="options.length">
                    <div class="mb-6 space-y-4">
                        <template x-for="option in options" :key="option.id">
                            <div>
                                <div class="text-sm font-semibold text-gray-700 mb-2" x-text="option.name"></div>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="value in option.values" :key="value.id">
                                        <button type="button"
                                                @click="selectOption(option, value)"
                                                class="px-3 py-2 text-sm rounded-md border transition"
                                                :class="optionIsSelected(option, value) ? 'border-brand-primary text-brand-primary bg-white' : 'border-gray-200 text-gray-700 hover:border-brand-primary'"
                                                x-text="value.label"></button>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
                
                {{-- ✅ [تعديل] تم تحديث هذا البلوك بالكامل --}}
                <div class="space-y-4 mb-8">
                    @php 
                        $stock = $product->stock_qty ?? $product->stock_quantity ?? 0;
                        $isAvailable = $stock > 0;
                    @endphp

                    @if ($isAvailable)
                        <div class="flex items-center gap-4 flex-wrap">
                            <label class="text-lg font-semibold text-gray-700 dark:text-gray-200">{{ __('product.quantity') }}</label>
                            <div class="flex items-center gap-3">
                                <div class="qty-wrap">
                                    <button type="button" @click="quantity > 1 ? quantity-- : 1" class="qty-btn">−</button>
                                    <input type="number" x-model.number="quantity" min="1" max="{{ $stock }}" class="qty-input" aria-label="{{ __('common.quantity') }}" />
                                    <button type="button" @click="quantity < {{ $stock }} ? quantity++ : {{ $stock }}" class="qty-btn">+</button>
                                </div>
                            </div>
                        </div>
                        <button
                            @click.prevent="loadingAdd = true; addToCart({{ $product->id }}, quantity, selectedOptions).then(data => { if(data.success) { added = true; window.dispatchEvent(new CustomEvent('cart-updated', { detail: { cartCount: data.cartCount } })); setTimeout(() => added = false, 2000); } else { alert(data.message || '{{ __('common.connection_error') }}'); } loadingAdd = false; }).catch(() => { alert('{{ __('product.server_unreachable') }}'); loadingAdd = false; });"
                            class="btn-primary shadow" :disabled="loadingAdd || added">
                            <span x-show="!added && !loadingAdd"><i class="bi bi-cart-plus-fill text-xl"></i> {{ __('product.add_to_cart') }}</span>
                            <span x-show="loadingAdd"><i class="bi bi-arrow-repeat animate-spin text-xl"></i></span>
                            <span x-show="added"><i class="bi bi-check-lg text-xl"></i> {{ __('product.added_to_cart') }}</span>
                        </button>
                    @else
                        <button class="btn-primary" disabled><i class="bi bi-x-circle-fill"></i> {{ __('product.out_of_stock') }}</button>
                    @endif

                    @auth
                    <button @click.prevent="loadingFav = true; toggleWishlist({{ $product->id }}).then(data => { if(data.success) { isFavorite = !isFavorite; window.dispatchEvent(new CustomEvent('wishlist-updated', { detail: { count: data.wishlistCount } })); } else { alert('{{ __('common.connection_error') }}'); } loadingFav = false; }).catch(() => { alert('{{ __('product.server_unreachable') }}'); loadingFav = false; });" class="btn-secondary" :disabled="loadingFav">
                        <i class="bi text-xl" :class="isFavorite ? 'bi-heart-fill text-red-500' : 'bi-heart'"></i>
                        <span x-text="isFavorite ? '{{ __('product.remove_from_wishlist') }}' : '{{ __('product.add_to_wishlist') }}'"></span>
                    </button>
                    @endauth
                </div>

                <div class="prose max-w-none leading-relaxed border-t border-gray-200 pt-8">
                    <h3 class="font-bold text-xl mb-4" style="color:var(--text)">{{ __('product.description') }}</h3>
                    {!! $product->description_translated !!}
                </div>
                <div id="reviews" class="mt-10 border-t border-gray-200 pt-8">
                    <h3 class="font-bold text-xl mb-4" style="color:var(--text)">{{ __('product.reviews') }}</h3>
                    <div id="review-success" class="hidden mb-3 text-sm text-green-700 bg-green-100 rounded p-2"></div>
                    <div id="review-errors" class="hidden mb-3 text-sm text-red-700 bg-red-100 rounded p-2"></div>
                    @php
                        $user = auth()->user();
                        $userHasReviewed = $user ? $product->reviews()->where('user_id', $user->id)->exists() : false;
                        $isAdmin = $user && ($user->can('delete-reviews') || (method_exists($user, 'hasRole') && $user->hasRole('super-admin')));
                    @endphp
                    @auth
                        @if(!$userHasReviewed)
                        <div class="review-form-card mb-6">
                            <h4 class="font-semibold mb-3" style="color:var(--text)">{{ __('product.rate_product') }}</h4>
                            <form id="review-form" method="POST" action="{{ url('/products/'.$product->id.'/reviews') }}" data-no-transition="true" x-data="{ rating: 5 }">
                                @csrf
                                <label class="block mb-1" style="color:var(--text)">{{ __('product.your_rating') }}</label>
                                <div class="flex items-center gap-1 mb-2">
                                    @for($i=1; $i<=5; $i++)
                                        <button type="button" @click="rating={{ $i }}" class="text-2xl leading-none">
                                            <i :class="rating >= {{ $i }} ? 'bi-star-fill text-yellow-500' : 'bi-star text-gray-400'" class="bi"></i>
                                        </button>
                                    @endfor
                                </div>
                                <input type="hidden" name="rating" x-model="rating">
                                <textarea name="comment" rows="3" class="review-textarea" placeholder="{{ __('product.comment_placeholder') }}"></textarea>
                                <div class="mt-3 flex items-center gap-2">
                                    <button id="review-submit" class="bg-brand-primary text-white px-4 py-2 rounded hover:bg-brand-accent transition" type="submit">{{ __('product.submit_review') }}</button>
                                </div>
                            </form>
                        </div>
                        @endif
                    @else
                        <p class="text-sm mb-6">
                            {{ __('product.login_to_review') }} <a href="{{ route('login') }}" class="text-brand-primary underline">{{ __('product.login') }}</a>.
                        </p>
                    @endauth
                    @php
                        $reviews = $product->reviews()->with('user')->latest()->paginate(10);
                    @endphp
                    <div id="reviews-list" class="reviews-wrap">
                        @forelse($reviews as $r)
                            <div class="review-card" id="review-card-{{ $r->id }}">
                                <div class="review-card-head">
                                    <div class="review-user">
                                        <img src="{{ $r->user?->avatar_url ?? asset('storage/avatars/default.jpg') }}" alt="avatar" class="review-avatar">
                                        <div>
                                            <div class="review-user-name">{{ $r->user?->name ?? __('product.user') }}</div>
                                            <div class="review-user-time">{{ $r->created_at->diffForHumans() }}</div>
                                        </div>
                                    </div>
                                    <div class="review-side">
                                        <div class="review-stars">
                                            @for($i=1; $i<=5; $i++)
                                                <i class="bi {{ $i <= $r->rating ? 'bi-star-fill' : 'bi-star' }} ms-1"></i>
                                            @endfor
                                        </div>
                                        @auth
                                            @if($isAdmin || $r->user_id === auth()->id())
                                                <button class="review-delete" data-delete-review data-product-id="{{ $product->id }}" data-review-id="{{ $r->id }}">{{ __('product.delete') }}</button>
                                            @endif
                                        @endauth
                                    </div>
                                </div>
                                @if($r->comment)<p class="review-comment">{{ $r->comment }}</p>@endif
                                @if(!empty($r->admin_reply))
                                    <div class="review-admin-reply">
                                        <div class="text-xs font-semibold text-blue-700 mb-1">رد الإدارة</div>
                                        <p class="text-sm text-gray-700 mb-0" style="white-space: pre-line;">{{ $r->admin_reply }}</p>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-gray-600">{{ __('product.no_reviews') }} 🤍</p>
                        @endforelse
                    </div>
                    <div class="mt-3">{{ $reviews->withQueryString()->links() }}</div>
                </div>
            </div>
            <div x-show="imageZoomOpen" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="fixed inset-0 z-[99999] flex items-center md:items-start justify-center p-4 md:pt-[240px] md:pb-20 zoom-modal-overlay" 
                 style="display: none;" 
                 @keydown.escape.window="imageZoomOpen = false">
                
                <div class="relative w-full max-w-5xl flex items-center justify-center pointer-events-none" @click.away="imageZoomOpen = false">
                    <div class="zoomed-image-card pointer-events-auto">
                        <img :src="mainImage" alt="Zoomed Image" class="max-h-[70vh] md:max-h-[calc(100vh-340px)] w-auto object-contain rounded-xl shadow-2xl border border-white/20">
                    </div>
                    <button @click="imageZoomOpen = false" 
                            class="absolute top-0 right-0 md:-top-4 md:-right-4 bg-white text-black rounded-full h-10 w-10 flex items-center justify-center shadow-lg hover:bg-gray-200 transition-all z-[100000] pointer-events-auto">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @php
        $displayRelatedProducts = $relatedProducts;

        // Fallback: if no related products are found, show other active products.
        if ($displayRelatedProducts->isEmpty()) {
            $displayRelatedProducts = \App\Models\Product::query()
                ->where('is_active', true)
                ->where('id', '!=', $product->id)
                ->with(['images', 'firstImage'])
                ->latest('id')
                ->take(6)
                ->get();
        }

        // Keep up to 6 products; CSS shows 5 on desktop.
        $displayRelatedProducts = $displayRelatedProducts->take(6);
    @endphp

    @if($displayRelatedProducts->isNotEmpty())
    <div class="container mx-auto px-4 py-12 border-t border-gray-200 mt-12">
        <h2 class="text-3xl font-bold text-brand-dark mb-8 text-center">{{ __('product.you_may_like') }}</h2>
        <div class="related-products-grid">
            @foreach($displayRelatedProducts as $relatedProduct)
                {{-- ===== Shop-identical Product Card ===== --}}
                <div class="product-card"
                     x-data="{
                         showAlt: false,
                         hasTwoImages: {{ optional($relatedProduct->images)->count() > 1 ? 'true' : 'false' }},
                         rtl: document.documentElement.dir === 'rtl',
                         added: false,
                         loadingAdd: false,
                         isFavorite: {{ in_array($relatedProduct->id, $favoriteProductIds ?? []) ? 'true' : 'false' }},
                         loadingFav: false,
                         touchStartX: 0, touchStartY: 0, isSwiping: false, gestureDetermined: false,
                         handleTouchStart(e) { this.touchStartX = e.touches[0].clientX; this.touchStartY = e.touches[0].clientY; this.isSwiping = false; this.gestureDetermined = false; },
                         handleTouchMove(e) { if (this.gestureDetermined) return; const dx = Math.abs(e.touches[0].clientX - this.touchStartX); const dy = Math.abs(e.touches[0].clientY - this.touchStartY); if (dx > 10 || dy > 10) { if (dx > dy) { this.isSwiping = true; e.preventDefault(); } this.gestureDetermined = true; } },
                         handleTouchEnd(e, linkEl) { if (this.isSwiping) { if (this.hasTwoImages) { this.showAlt = !this.showAlt; } } else { const dx = Math.abs(e.changedTouches[0].clientX - this.touchStartX); const dy = Math.abs(e.changedTouches[0].clientY - this.touchStartY); if (dx < 10 && dy < 10) { window.location.href = linkEl.href; } } },
                         toggleWishlist() {
                             if(this.loadingFav) return;
                             this.loadingFav=true;
                             fetch('{{ route('wishlist.toggle.async', $relatedProduct->id) }}', {
                                 method:'POST',
                                 headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json','X-Requested-With':'XMLHttpRequest'}
                             })
                             .then(r=>r.json())
                             .then(d=>{
                                 if(d.success){
                                     this.isFavorite=!this.isFavorite;
                                     window.dispatchEvent(new CustomEvent('wishlist-updated',{detail:{count:d.wishlistCount}}))
                                 } else { alert(d.message || 'Error'); }
                             })
                             .catch(()=>alert('{{ __('common.connection_error') }}'))
                             .finally(()=>this.loadingFav=false);
                         },
                         addToCart() {
                             if(this.loadingAdd) return;
                             this.loadingAdd=true;
                             fetch('{{ route('cart.store') }}', {
                                 method:'POST',
                                 headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json','Content-Type':'application/json'},
                                 body:JSON.stringify({product_id:{{ $relatedProduct->id }},quantity:1})
                             })
                             .then(r=>r.json())
                             .then(d=>{
                                 if(d.success){
                                     this.added=true;
                                     window.dispatchEvent(new CustomEvent('cart-updated',{detail:{cartCount:d.cartCount}}));
                                     setTimeout(()=>this.added=false,1800);
                                 } else { alert(d.message || 'Error'); }
                             })
                             .catch(()=>alert('{{ __('common.connection_error') }}'))
                             .finally(()=>this.loadingAdd=false);
                         }
                     }"
                     @mouseover="hasTwoImages ? showAlt = true : null"
                     @mouseout="hasTwoImages ? showAlt = false : null">

                    <a href="{{ route('product.detail', $relatedProduct) }}"
                       class="product-content-link"
                       @touchstart="handleTouchStart($event)"
                       @touchmove="handleTouchMove($event)"
                       @touchend="handleTouchEnd($event, $el)">

                        <div class="relative product-image-container">
                            @php
                                $isAvailable = ($relatedProduct->stock_qty ?? $relatedProduct->stock_quantity ?? 0) > 0;
                                $hasSale = $relatedProduct->isOnSale();
                                $discountPercent = ($hasSale && $relatedProduct->price > 0)
                                    ? round(100 - ($relatedProduct->sale_price / $relatedProduct->price * 100))
                                    : 0;
                                $secondImage = optional($relatedProduct->images->get(1))->image_path;
                            @endphp

                            {{-- شارة "منتهي الكمية" --}}
                            @if(!$isAvailable)
                                <div class="absolute inset-0 bg-black/60 z-10 flex items-center justify-center pointer-events-none">
                                    <span class="text-white font-bold tracking-wider text-sm border border-white/50 rounded-full px-3 py-1">{{ __('product.out_of_stock') }}</span>
                                </div>
                            @endif

                            {{-- شارة الخصم --}}
                            @if($hasSale && $discountPercent > 0)
                                <div class="product-sale-badge">
                                    -{{ $discountPercent }}%
                                </div>
                            @endif

                            {{-- سلايد الصور --}}
                            <div class="product-image-slider">
                                {{-- الصورة الأولى --}}
                                @if ($relatedProduct->firstImage)
                                    <img src="{{ asset('storage/'.$relatedProduct->firstImage->image_path) }}" 
                                         alt="{{ $relatedProduct->name_translated }}" loading="lazy" width="600" height="600"
                                         :style="{
                                            transform: showAlt && hasTwoImages
                                                ? (rtl ? 'translateX(105%)' : 'translateX(-105%)')
                                                : 'translateX(0)',
                                            transition: 'transform 0.35s ease'
                                         }">
                                @else
                                    <img src="https://placehold.co/600x600?text=No+Image" alt="No image" loading="lazy" width="600" height="600"
                                         :style="{
                                            transform: showAlt && hasTwoImages
                                                ? (rtl ? 'translateX(105%)' : 'translateX(-105%)')
                                                : 'translateX(0)',
                                            transition: 'transform 0.35s ease'
                                         }">
                                @endif

                                {{-- الصورة الثانية --}}
                                @if ($secondImage)
                                    <img src="{{ asset('storage/'.$secondImage) }}" 
                                         alt="{{ $relatedProduct->name_translated }} (alt)" loading="lazy" width="600" height="600"
                                         :style="{
                                            transform: showAlt && hasTwoImages
                                                ? 'translateX(0)'
                                                : (rtl ? 'translateX(-105%)' : 'translateX(105%)'),
                                            transition: 'transform 0.35s ease'
                                         }">
                                @elseif ($relatedProduct->firstImage)
                                    <img src="{{ asset('storage/' . $relatedProduct->firstImage->image_path) }}" 
                                         alt="{{ $relatedProduct->name_translated }}" loading="lazy" width="600" height="600"
                                         :style="{
                                            transform: showAlt && hasTwoImages
                                                ? 'translateX(0)'
                                                : (rtl ? 'translateX(-105%)' : 'translateX(105%)'),
                                            transition: 'transform 0.35s ease'
                                         }">
                                @endif
                            </div>

                            <template x-if="hasTwoImages">
                                <div class="product-dots">
                                    <div class="product-dot" :class="{ 'active': !showAlt }"></div>
                                    <div class="product-dot" :class="{ 'active': showAlt }"></div>
                                </div>
                            </template>
                        </div>

                        <div class="product-info">
                            <h3 class="product-title">{{ $relatedProduct->name_translated }}</h3>

                            @php
                                $avg = round((float) ($relatedProduct->average_rating ?? 0), 1);
                                $count = (int) ($relatedProduct->reviews_count ?? 0);
                            @endphp
                            <div class="flex items-center justify-center gap-2" title="{{ __('common.rating') }} {{ $avg }}">
                                <div class="flex">
                                    @for($i=1;$i<=5;$i++)
                                        @php $full=$i<=floor($avg); $half=!$full && ($i-$avg)<=0.5; @endphp
                                        <i class="bi {{ $full ? 'bi-star-fill' : ($half ? 'bi-star-half' : 'bi-star') }} text-yellow-500 text-sm"></i>
                                    @endfor
                                </div>
                                @if($count > 0)
                                    <span class="text-xs text-gray-500">({{ $count }})</span>
                                @endif
                            </div>

                            <div class="flex items-baseline justify-center gap-2">
                                @if($relatedProduct->isOnSale())
                                    <div class="price">{{ number_format($relatedProduct->sale_price, 0) }} {{ __('common.currency') }}</div>
                                    <div class="old">{{ number_format($relatedProduct->price, 0) }} {{ __('common.currency') }}</div>
                                @else
                                    <div class="price">{{ number_format($relatedProduct->price, 0) }} {{ __('common.currency') }}</div>
                                @endif
                            </div>
                        </div>
                    </a>
                    <div class="product-actions">
                        @auth
                        <button
                            @click.stop="toggleWishlist()"
                            @touchend.stop
                            class="btn-fav"
                            :class="{ 'favorited': isFavorite, 'opacity-60 pointer-events-none': loadingFav }"
                            :disabled="loadingFav">
                            <i class="bi" :class="isFavorite ? 'bi-heart-fill' : 'bi-heart'"></i>
                        </button>
                        @endauth

                        @if ($isAvailable)
                            <button @click.stop="addToCart()"
                                    @touchend.stop
                                    class="btn-primary">
                                <span x-show="!added && !loadingAdd"><i class="bi bi-cart-plus"></i> {{ __('common.add_to_cart') }}</span>
                                <span x-show="loadingAdd"><i class="bi bi-arrow-repeat animate-spin"></i></span>
                                <span x-show="added"><i class="bi bi-check-lg"></i> {{ __('common.added_to_cart') }}</span>
                            </button>
                        @else
                            <button class="btn-primary" disabled>
                                {{ __('common.out_of_stock') }}
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection


@push('scripts')
<script>
    // ===== بيانات المنتج لاستخدامها مع Meta Pixel =====
    var PRODUCT_ID = {{ $product->id }};
    var PRODUCT_NAME = @json($product->name_translated);
    var PRODUCT_UNIT_PRICE = {{ $product->isOnSale() ? ($product->sale_price ?? $product->price ?? 0) : ($product->price ?? 0) }};
    var PRODUCT_CATEGORY = @json(optional($product->category)->name_translated);

    // ===== حدث ViewContent عند فتح صفحة المنتج =====
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof fbq === 'function') {
            var params = {
                content_ids: [String(PRODUCT_ID)],
                content_name: PRODUCT_NAME,
                content_type: 'product',
                value: PRODUCT_UNIT_PRICE,
                currency: 'IQD'
            };

            if (PRODUCT_CATEGORY) {
                params.content_category = PRODUCT_CATEGORY;
            }

            fbq('track', 'ViewContent', params);
        }
    });

    // سلة + مفضلة
    function addToCart(productId, quantity, selectedOptions = {}) {
        return fetch("{{ route('cart.store') }}", {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}", "Accept": "application/json" },
            body: JSON.stringify({ product_id: productId, quantity: quantity, selected_options: selectedOptions })
        })
        .then(r => r.json())
        .then(function (data) {
            // ===== حدث AddToCart عند الإضافة للسلة (لنفس المنتج الحالي) =====
            if (typeof fbq === 'function' && data && data.success && productId === PRODUCT_ID) {
                fbq('track', 'AddToCart', {
                    content_ids: [String(PRODUCT_ID)],
                    content_name: PRODUCT_NAME,
                    content_type: 'product',
                    value: PRODUCT_UNIT_PRICE * quantity,
                    currency: 'IQD'
                });
            }
            return data;
        });
    }

    function toggleWishlist(productId) {
        return fetch(`{{ url('/wishlist/toggle-async') }}/${productId}`, {
            method: "POST",
            headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}", "Accept": "application/json" }
        }).then(r => r.json());
    }

    // إرسال التقييم AJAX بدون ريفرش
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('review-form');
        const successBox = document.getElementById('review-success');
        const errorsBox  = document.getElementById('review-errors');
        const reviewsList = document.getElementById('reviews-list');

        if (form) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                successBox.classList.add('hidden');
                errorsBox.classList.add('hidden');
                errorsBox.innerHTML = '';
                const fd = new FormData(form);
                try {
                    const resp = await fetch(form.action, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        body: fd
                    });
                    const data = await resp.json();
                    if (!resp.ok || !data.success) {
                        const msgs = [];
                        if (data && data.errors) {
                            Object.values(data.errors).forEach(arr => arr.forEach(m => msgs.push(m)));
                        } else {
                            msgs.push(data.message || '{{ __('product.send_error') }}');
                        }
                        errorsBox.innerHTML = msgs.map(m => `<div>• ${m}</div>`).join('');
                        errorsBox.classList.remove('hidden');
                        return;
                    }
                    if (data.review && data.review.visible) {
                        appendReviewCard(data.review.id, data.review.user_name, data.review.rating, data.review.comment, data.review.created_at_human, true, form.dataset.userAvatar);
                    }
                    if (data.stats) updateStats(data.stats.avg, data.stats.count);
                    successBox.textContent = data.message || '{{ __('product.saved_success') }}';
                    successBox.classList.remove('hidden');
                    form.closest('.border.rounded-lg')?.remove();
                } catch (err) {
                    errorsBox.textContent = '{{ __('product.server_unreachable') }}';
                    errorsBox.classList.remove('hidden');
                }
            });
        }

        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('[data-delete-review]');
            if (!btn) return;
            e.preventDefault();
            if (!confirm('{{ __('product.confirm_delete_comment') }}')) return;
            const productId = btn.dataset.productId;
            const reviewId  = btn.dataset.reviewId;
            try {
                const resp = await fetch(`/products/${productId}/reviews/${reviewId}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await resp.json();
                if (!resp.ok || !data.success) {
                    alert(data.message || '{{ __('product.delete_comment_failed') }}'); return;
                }
                document.getElementById(`review-card-${reviewId}`)?.remove();
                if (data.stats) updateStats(data.stats.avg, data.stats.count);
            } catch (_) {
                alert('{{ __('product.server_unreachable') }}');
            }
        });

        function appendReviewCard(id, name, rating, comment, createdHuman, mine = false, avatarUrl = null) {
            const stars = Array.from({length:5}).map((_,i)=> `<i class="bi ${i+1 <= rating ? 'bi-star-fill' : 'bi-star'}"></i>`).join('');
            const commentHtml = comment ? `<p class="review-comment">${escapeHtml(comment)}</p>` : '';
            const deleteBtn = mine ? `<button class="review-delete" data-delete-review data-product-id="{{ $product->id }}" data-review-id="${id}">{{ __('product.delete') }}</button>` : '';
            const safeAvatar = avatarUrl || '{{ auth()->user()?->avatar_url ?? asset('storage/avatars/default.jpg') }}';
            const header = `<div class="review-card-head"><div class="review-user"><img src="${safeAvatar}" alt="avatar" class="review-avatar"><div><div class="review-user-name">${escapeHtml(name)}</div><div class="review-user-time">${escapeHtml(createdHuman || '')}</div></div></div><div class="review-side"><div class="review-stars">${stars}</div>${deleteBtn}</div></div>`;
            const card = `<div class="review-card" id="review-card-${id}">${header}${commentHtml}</div>`;
            reviewsList.insertAdjacentHTML('afterbegin', card);
        }

        function updateStats(avg, count) {
            const revCountEl = document.getElementById('rev-count');
            const avgText = document.getElementById('avg-text');
            const avgStars = document.getElementById('avg-stars');
            if (revCountEl) revCountEl.textContent = count;
            if (avgText) avgText.innerHTML = `${Number(avg).toFixed(1)} / 5 · <span id="rev-count">${count}</span> {{ __('product.rating') }}`;
            if (avgStars) {
                avgStars.innerHTML = '';
                const a = Number(avg);
                for (let i=1; i<=5; i++) {
                    const full = i <= Math.floor(a);
                    const half = !full && (i - a) <= 0.5;
                    avgStars.insertAdjacentHTML('beforeend', `<i class="bi ${full ? 'bi-star-fill' : (half ? 'bi-star-half' : 'bi-star')} text-xl me-1"></i>`);
                }
            }
        }
        function escapeHtml(str) { return (str || '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m])); }
    });
</script>
@endpush