@extends('frontend.profile.layout')
@section('title', __('profile.wishlist_title'))

@push('styles')
<style>
    [x-cloak] { display: none !important; }

    /* Scope for wishlist - Precise Homepage Colors & Card Design */
    .wishlist-scope {
        --primary-color: #c32126;
        --primary-hover: #a61c20;
        --card-bg: #ffffff;
        --text: #111111;
        --text-soft: #333333;
        --border: #e5e5e5;
    }

    html.dark .wishlist-scope {
        --card-bg: #1f2937;
        --text: #ffffff;
        --text-soft: #e5e5e5;
        --border: #262626;
    }

    .wishlist-scope .page-head h2 { color: var(--text); font-weight: 800; }

    /* Products Grid Matching Homepage */
    .wishlist-scope .products-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1.5rem;
    }
    @media (max-width: 1280px) { .wishlist-scope .products-grid { grid-template-columns: repeat(4, 1fr); gap: 1rem; } }
    @media (max-width: 1024px) { .wishlist-scope .products-grid { grid-template-columns: repeat(3, 1fr); gap: 1rem; } }
    @media (max-width: 768px) {  .wishlist-scope .products-grid { grid-template-columns: repeat(2, 1fr); gap: 1rem; } }

    /* Premium Product Card - Replicating Home/Shop precisely */
    .wishlist-scope .product-card {
        background: var(--card-bg);
        border-radius: 14px;
        border: 2px solid transparent;
        box-shadow: 0 4px 12px rgba(0,0,0,.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        position: relative;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .wishlist-scope .product-card:hover { transform: translateY(-6px); box-shadow: 0 16px 30px rgba(0,0,0,.12); }

    .wishlist-scope .product-image-container {
        aspect-ratio: 1/1;
        position: relative;
        overflow: hidden;
        background: #f9f9f9;
        border-radius: 12px 12px 0 0;
    }
    html.dark .wishlist-scope .product-image-container { background: #111827; }

    .wishlist-scope .product-image-slider { position: relative; width: 100%; height: 100%; }
    .wishlist-scope .product-image-slider img {
        position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transition: transform 0.35s ease;
    }

    .wishlist-scope .product-info { padding: 12px; display: flex; flex-direction: column; gap: 6px; text-align: center; flex-grow: 1; }
    .wishlist-scope .product-title {
        font-weight: 700; color: var(--text); line-height: 1.35; font-size: 0.95rem;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 2.6em;
    }
    .wishlist-scope .price { color: var(--text); font-weight: 800; font-size: 1.05rem; }
    .wishlist-scope .old { text-decoration: line-through; color: #ef4444; font-size: .85rem; margin-right: 4px; }

    .wishlist-scope .product-actions { display: flex; gap: 8px; padding: 0 10px 10px; margin-top: auto; }

    /* Button styles - Shared with Home/Shop */
    .wishlist-scope .btn-primary {
        flex-grow: 1; height: 44px; background: #6d0e16 !important; color: #ffffff !important;
        border-radius: 10px; font-weight: 700; border: none; cursor: pointer;
        display: inline-flex; align-items: center; justify-content: center;
        padding: 0 0.75rem; white-space: nowrap; font-size: 0.9rem; transition: 0.2s;
    }
    .wishlist-scope .btn-primary:hover { background: #500a10 !important; }
    .wishlist-scope .btn-primary:disabled { background-color: #9ca3af !important; cursor: not-allowed; }

    .wishlist-scope .btn-fav {
        width: 44px; height: 44px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;
        flex-shrink: 0; border: none; cursor: pointer; transition: 0.2s; font-size: 1.15rem;
        background-color: #fee2e2; color: #ef4444;
    }
    .wishlist-scope .btn-fav:hover { background-color: #fecaca; }

    .wishlist-scope .product-sale-badge {
        position: absolute; top: .6rem; right: .6rem; z-index: 15;
        background: var(--primary-color); color: #fff; font-weight: 800; font-size: .7rem;
        padding: .3rem .6rem; border-radius: 999px; box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }

    .wishlist-scope .out-of-stock-overlay {
        position: absolute; inset: 0; background: rgba(0,0,0,0.6); z-index: 10;
        display: flex; align-items: center; justify-content: center; pointer-events: none;
    }
    .wishlist-scope .out-of-stock-badge {
        color: white; font-weight: 700; border: 1px solid rgba(255,255,255,0.4);
        border-radius: 999px; padding: 0.25rem 0.6rem; font-size: 0.75rem; text-transform: uppercase;
    }

    .wishlist-scope .empty-state { text-align: center; padding: 5rem 2rem; }
    .wishlist-scope .empty-state > i { font-size: 3.5rem; color: #d1d5db; margin-bottom: 1rem; display: block; }
    .wishlist-scope .btn-browse {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        height: 44px;
        background: #6d0e16;
        color: #ffffff;
        padding: 0 0.75rem;
        border-radius: 10px;
        border: none;
        font-weight: 700;
        font-size: 0.9rem;
        white-space: nowrap;
        text-decoration: none;
        transition: 0.2s;
        box-shadow: 0 8px 16px rgba(109, 14, 22, 0.15);
    }
    .wishlist-scope .btn-browse:hover { background: #500a10 !important; }
    .wishlist-scope .btn-browse i {
        font-size: 1rem;
        line-height: 1;
        margin: 0;
        display: inline-block;
    }
    .wishlist-scope .btn-browse:focus-visible {
        outline: none;
        box-shadow: 0 0 0 4px rgba(109, 14, 22, 0.2), 0 10px 20px rgba(109, 14, 22, 0.18);
    }
    @media (max-width: 640px) {
        .wishlist-scope .btn-browse {
            width: 100%;
        }
    }

    /* Alpine Transition Fixes */
    .wishlist-scope [x-cloak] { display: none !important; }
</style>
@endpush

@section('profile-content')
<div class="wishlist-scope">
    <div class="page-head mb-8">
        <h2 class="text-2xl font-extrabold">{{ __('profile.wishlist_title') }}</h2>
        <p class="text-gray-500 mt-1">{{ __('profile.wishlist_subheading') }}</p>
    </div>

    @if ($favorites->isEmpty())
        <div class="empty-state">
            <i class="bi bi-heart"></i>
            <h3 class="text-xl font-bold mb-3">{{ __('profile.no_favorites') }}</h3>
            <p class="text-gray-500 mb-6">{{ __('profile.wishlist_empty_desc') }}</p>
            <a href="{{ Route::has('shop') ? route('shop') : url('/shop') }}" class="btn-browse" data-fast-nav="true" aria-label="{{ __('profile.browse_store') }}">
                <i class="bi bi-bag-plus"></i> {{ __('profile.browse_store') }}
            </a>
        </div>
    @else
        <div class="products-grid">
            @foreach ($favorites as $favorite)
                @if ($product = $favorite->product)
                @php
                    $isAvailable = ($product->stock_qty ?? $product->stock_quantity ?? 0) > 0;
                    $isOnSale = $product->isOnSale();
                    $discountPct = ($isOnSale && $product->price > 0) ? round((($product->price - $product->sale_price) / $product->price) * 100) : 0;
                    $firstImg = $product->firstImage ? asset('storage/'.$product->firstImage->image_path) : 'https://placehold.co/600x600?text=No+Image';
                    $secondImg = optional($product->images->get(1))->image_path ? asset('storage/'.$product->images->get(1)->image_path) : null;
                @endphp

                <div class="product-card"
                     x-data="{
                         showAlt: false, activeState: 'normal',
                         loadingFav: false, isFavorite: true,
                         toggleWishlist() {
                             if(this.loadingFav) return;
                             this.loadingFav = true;
                             fetch('{{ route('wishlist.toggle.async', $product->id) }}', {
                                 method:'POST',
                                 headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json','X-Requested-With':'XMLHttpRequest'}
                             })
                             .then(r => r.json())
                             .then(d => { if(d.success) { this.isFavorite = false; window.dispatchEvent(new CustomEvent('wishlist-updated',{detail:{count:d.wishlistCount}})); } })
                             .finally(() => this.loadingFav = false);
                         },
                         addToCart() {
                             if(this.activeState !== 'normal') return;
                             this.activeState = 'loading';
                             fetch('{{ route('cart.store') }}', {
                                 method:'POST',
                                 headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json','Content-Type':'application/json'},
                                 body:JSON.stringify({product_id:{{ $product->id }}, quantity:1})
                             })
                             .then(r => r.json())
                             .then(d => {
                                 if(d.success){
                                     this.activeState = 'added';
                                     window.dispatchEvent(new CustomEvent('cart-updated',{detail:{cartCount:d.cartCount}}));
                                     setTimeout(() => this.activeState = 'normal', 2500);
                                 } else { alert(d.message || 'Error'); this.activeState = 'normal'; }
                             })
                             .catch(() => { this.activeState = 'normal'; });
                         }
                     }"
                     x-show="isFavorite"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-90"
                     @mouseover="secondImg ? showAlt=true : null"
                     @mouseout="secondImg ? showAlt=false : null">

                    <a href="{{ route('product.detail', $product) }}" class="block">
                        <div class="product-image-container">
                            @if(!$isAvailable)
                                <div class="out-of-stock-overlay">
                                    <span class="out-of-stock-badge">{{ __('common.out_of_stock') }}</span>
                                </div>
                            @endif

                            @if($isOnSale && $isAvailable && $discountPct > 0)
                                <div class="product-sale-badge">-{{ $discountPct }}%</div>
                            @endif

                            <div class="product-image-slider">
                                <img src="{{ $firstImg }}" alt="{{ $product->name_translated }}" loading="lazy"
                                     :style="{ transform: showAlt ? (document.documentElement.dir === 'rtl' ? 'translateX(105%)' : 'translateX(-105%)') : 'translateX(0)', transition: 'transform 0.4s ease' }">
                                @if($secondImg)
                                    <img src="{{ $secondImg }}" alt="{{ $product->name_translated }}" loading="lazy"
                                         :style="{ transform: showAlt ? 'translateX(0)' : (document.documentElement.dir === 'rtl' ? 'translateX(-105%)' : 'translateX(105%)'), transition: 'transform 0.4s ease' }">
                                @endif
                            </div>
                        </div>
                    </a>

                    <div class="product-info">
                        <h3 class="product-title">{{ $product->name_translated }}</h3>
                        <div class="flex items-baseline justify-center gap-1">
                            @if($isOnSale && $isAvailable)
                                <span class="price">{{ number_format($product->sale_price, 0) }} {{ __('common.currency') }}</span>
                                <span class="old">{{ number_format($product->price, 0) }} {{ __('common.currency') }}</span>
                            @else
                                <span class="price">{{ number_format($product->price, 0) }} {{ __('common.currency') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="product-actions" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                        {{-- Wishlist Toggle --}}
                        <button @click.stop="toggleWishlist()" class="btn-fav" :disabled="loadingFav" title="{{ __('common.remove_from_wishlist') }}">
                            <template x-if="!loadingFav"><i class="bi bi-heart-fill"></i></template>
                            <template x-if="loadingFav"><i class="bi bi-arrow-repeat animate-spin"></i></template>
                        </button>

                        {{-- Add to Cart --}}
                        @if ($isAvailable)
                        <button @click.stop="addToCart()" class="btn-primary" :disabled="activeState !== 'normal'">
                            <span x-show="activeState === 'normal'" class="flex items-center gap-2">
                                <i class="bi bi-cart-plus"></i> {{ __('common.add_to_cart') }}
                            </span>
                            <span x-show="activeState === 'loading'" x-cloak>
                                <i class="bi bi-arrow-repeat animate-spin"></i>
                            </span>
                            <span x-show="activeState === 'added'" x-cloak class="flex items-center gap-2">
                                <i class="bi bi-check-lg"></i> {{ __('common.added_to_cart') }}
                            </span>
                        </button>
                        @else
                        <button class="btn-primary !bg-gray-400 !cursor-not-allowed" disabled>
                            {{ __('common.out_of_stock') }}
                        </button>
                        @endif
                    </div>
                </div>
                @endif
            @endforeach
        </div>
    @endif
</div>
@endsection

