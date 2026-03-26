@php
    $favoriteProductIds = $favoriteProductIds ?? [];
@endphp

@extends('layouts.app')

@section('title', $pageTitle ?? __('shop.title'))

@section('content')
<style>
    :root{
        --primary-color:#6d0e16; --primary-hover:#5a0b12;
        --secondary-color:#D4AF37; --dark-color:#111827; --light-color:#FFFFFF;
    }
    /* === تصحيح الانزياح الأفقي (اليسار) === */
    html, body {
        overflow-x: clip; /* clip على الاثنين بدون hidden حتى لا يصير scroll context منفصل لـ body */
        width: 100%;
    }
    /* ===================================== */
    
    body{ background:#fff; }
    .dark body { background-color: #111827; }

    /* Layout */
    @media(min-width:1024px){
      .layout{display:grid; grid-template-columns:1fr 340px; gap:24px;}
      .filter-dock{position:sticky; top:7rem;}
    }
    @media(max-width:1023px){ .layout{display:block} }
    /* ===== فلتر ثابت مع سكرول داخلي صريح (يمنع ازدواجية السكرول) ===== */
    @media(min-width:1024px){
      .filter-dock{
        max-height: calc(100vh - 9rem);
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: rgba(109,14,22,.25) transparent;
      }
      .filter-dock::-webkit-scrollbar{ width: 4px; }
      .filter-dock::-webkit-scrollbar-track{ background: transparent; }
      .filter-dock::-webkit-scrollbar-thumb{ background: rgba(109,14,22,.25); border-radius:99px; }
    }
    /* Dock Card (Desktop Filters Container) */
    .dock-card{
        border:1px solid rgba(109, 14, 22, 0.2); background:#fff; border-radius:18px;
        box-shadow: 0 18px 28px rgba(109, 14, 22, 0.05), 0 6px 12px rgba(0,0,0,.04);
        padding:16px;
    }
    .dock-card:before{
        content:""; display:block; height:10px; border-radius:999px;
        background:#6d0e16; margin:-16px -16px 16px;
        border-bottom:1px solid #5a0b12;
    }

    /* Toolbar */
    .toolbar{
      background:#fff; border:1px solid #efe7df; border-radius:14px;
      padding:.6rem 1rem; display:flex; justify-content:space-between; align-items:center;
      box-shadow:0 6px 14px rgba(0,0,0,.04);
    }
    .toolbar form { display: flex; align-items: center; gap: 0.75rem; }
    .toolbar-label { font-size: 0.9rem; color: #6b7280; font-weight: 600; }
    .sort-select-wrap {
        position: relative; min-width: 220px;
    }
    .sort-select {
        width: 100%; appearance: none; -webkit-appearance: none; -moz-appearance: none;
        background: #fcfbfa; border: 1px solid #e7ddd4; border-radius: 16px;
        padding: 0.8rem 2.75rem 0.8rem 1rem; color: #2d2a2a; font-size: 0.95rem; font-weight: 600;
        line-height: 1.2; box-shadow: inset 0 1px 2px rgba(0,0,0,.03); transition: border-color .2s ease, box-shadow .2s ease, background-color .2s ease;
    }
    .sort-select:focus {
        outline: none; border-color: rgba(109, 14, 22, 0.45); box-shadow: 0 0 0 4px rgba(109, 14, 22, 0.08);
    }
    .sort-select-icon {
        position: absolute; top: 50%; left: 1rem; transform: translateY(-50%);
        color: #6b7280; font-size: 1rem; pointer-events: none;
    }
    @media(max-width:640px){
        .toolbar { gap: 0.75rem; align-items: stretch; flex-direction: column; }
        .toolbar form { width: 100%; justify-content: space-between; }
        .sort-select-wrap { min-width: 0; flex: 1; }
    }
    .toolbar > div:first-child {
        font-size: 0.75rem !important;
    }

    /* Products Grid */
    .products-grid{ display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:1.2rem;}
    @media(max-width:1280px){ .products-grid{grid-template-columns:repeat(4,1fr); gap:1rem;} }
    @media(max-width:1024px){ .products-grid{grid-template-columns:repeat(3,1fr); gap:1rem;} }
    @media(max-width:640px){  .products-grid{grid-template-columns:repeat(2,1fr); gap:1rem;}  }

    /* Product Card */
    .product-card{ background:#fff; border:2px solid transparent; border-radius:14px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,.05); transition:.3s; position:relative; display:flex; flex-direction:column; }
    .product-card:hover{ transform:translateY(-6px); box-shadow:0 16px 30px rgba(0,0,0,.10); }
    .product-content-link { display: flex; flex-direction: column; flex-grow: 1; text-decoration: none; color: inherit; }
    .product-image-container{ aspect-ratio:1/1; position:relative; overflow:hidden; }
    .product-image-slider{ display:flex; width:200%; height:100%; transition:transform .3s ease; }
    .product-image-slider img{ width:50%; height:100%; object-fit:cover; }
    .product-dots{ position:absolute; bottom:8px; left:50%; transform:translateX(-50%); display:flex; gap:6px; z-index:10;}
    .product-dot{ width:8px; height:8px; background:rgba(255,255,255,.6); border-radius:999px;}
    .product-dot.active{ background:var(--primary-color); }
    /* شارة نسبة الخصم داخل كارت المتجر */
    .product-sale-badge{
        position:absolute;
        top:.75rem;
        right:.75rem;
        z-index:15;
        background:var(--primary-color);
        color:#fff;
        font-weight:800;
        font-size:.75rem;
        padding:.35rem .7rem;
        border-radius:999px;
        box-shadow:0 6px 14px rgba(0,0,0,.12);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    
    .product-info{ padding: 10px; display: flex; flex-direction: column; gap: 5px; text-align: center; flex-grow: 1; }
    
    .product-title{ font-weight:700; color:#2d2a2a; line-height:1.35; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; min-height:2.6em; }
    .price{ color:#000; font-weight:800; font-size:1rem; }
    .old{ text-decoration:line-through; color:#dc2626; font-size:.85rem; }
    .product-actions { display: flex; gap: 8px; padding: 0 10px 10px; position: relative; z-index: 2; }
    .btn-primary{ background:var(--primary-color); color:#fff; border-radius:10px; font-weight:700; transition: .2s; }
    .btn-primary:hover{ background:var(--primary-hover); }
    .product-actions .btn-primary { flex-grow: 1; flex-shrink: 1; min-width: 0; overflow: hidden; height: 44px; display: inline-flex; align-items: center; justify-content: center; padding: 0 .75rem; white-space: nowrap; font-size: 0.9rem; }
    .product-actions .btn-primary:only-child { flex-grow: unset; width: 100%; }
    .btn-fav { width: 44px; height: 44px; border-radius: 999px; background-color: #e5e7eb; color: #4b5563; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; transition: .2s; font-size: 1.1rem; border: none; cursor: pointer; }
    .btn-fav:hover { background-color: #d1d5db; }
    .btn-fav.favorited { background-color: #fee2e2; color: #ef4444; }
    .no-products-message { background-color: #fff; border: 1px solid #f3f4f6; }

    @media (max-width: 640px) {
        .product-card .price { 
            font-size: 1rem; 
        }
        .product-card .old { 
            font-size: 0.8rem;
        }
        .product-actions .btn-primary { 
            height: 40px; 
            font-size: 0.85rem; 
        }
        .btn-fav {
            width: 40px; 
            height: 40px;
            font-size: 1rem;
        }
    }
    @media (max-width: 390px) { 
        .product-info{
            padding: 8px; 
        }
        .product-actions .btn-primary { 
            font-size: 0.75rem; 
            padding: 0 0.4rem; 
        } 
    }
    
    /* === FILTER STYLES (بقية الكود CSS) === */
    .filter-section { border-bottom: 1px solid #f3f4f6; padding-bottom: 1.5rem; margin-bottom: 1.5rem; }
    .filter-section:last-of-type { border-bottom: none; padding-bottom: 0; }
    .filter-title { display: flex; justify-content: space-between; align-items: center; width: 100%; font-size: 1.125rem; font-weight: 700; color: #6d0e16; transition: color 0.2s ease-in-out; }
    .filter-title:hover { color: var(--primary-color); }
    .filter-category-item { display: block; padding: 0.5rem 0.75rem; font-size: 0.875rem; color: #4b5563; border-radius: 0.5rem; transition: all 0.2s ease-in-out; text-decoration: none; }
    .filter-category-item:hover { background-color: #f9fafb; color: var(--primary-color); }
    .filter-category-item.is-child { font-size: 0.825rem; padding-top: 0.4rem; padding-bottom: 0.4rem; }
    .filter-category-item.active { background-color: #fef2f2; color: var(--primary-color); font-weight: 600; }
    .cat-toggle-btn { padding: 0.5rem; color: #9ca3af; border-radius: 99px; }
    .cat-toggle-btn:hover { background-color: #f9fafb; color: var(--primary-color); }
    .price-slider { position: relative; height: 20px; }
    .price-slider-track { position: absolute; top: 50%; transform: translateY(-50%); height: 5px; width: 100%; background: #e5e7eb; border-radius: 99px; }
    .price-slider-range { position: absolute; top: 50%; transform: translateY(-50%); height: 5px; background: var(--primary-color); border-radius: 99px; }
    .price-slider-input { position: absolute; width: 100%; top: 0; height: 20px; -webkit-appearance: none; background: transparent; pointer-events: none; margin: 0; }
    .price-slider-input::-webkit-slider-thumb { -webkit-appearance: none; pointer-events: auto; width: 20px; height: 20px; background: white; border-radius: 50%; border: 3px solid var(--primary-color); cursor: pointer; box-shadow: 0 0 0 2px white; }
    .price-display { padding: 0.25rem 0.75rem; background-color: #f3f4f6; font-size: 0.8rem; border-radius: 99px; color: #4b5563; font-weight: 500; }
    .sale-checkbox { height: 1.125rem; width: 1.125rem; border-radius: 0.25rem; color: var(--primary-color); border-color: #d1d5db; transition: all 0.2s; flex-shrink: 0; focus:ring-offset-0 focus:ring-2 focus:ring-brand-primary; }
    .filter-submit-btn { width: 100%; background: var(--primary-color); color: white; padding: 0.65rem; border-radius: 99px; font-weight: 600; transition: background-color 0.2s ease-in-out; }
    .filter-submit-btn:hover { background: var(--primary-hover); }
    .filter-clear-btn { width: 100%; display: flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.65rem; color: #6b7280; font-weight: 500; }
    .filter-clear-btn:hover { color: #1f2937; }

    /* === DARK MODE STYLES === */
    .dark .toolbar { background-color: #1f2937; color: #d1d5db; border-color: #4b5563; }
    .dark .toolbar-label { color: #d1d5db; }
    .dark .sort-select { background-color: #374151; color: #f9fafb; border-color: #4b5563; box-shadow: none; }
    .dark .sort-select:focus { border-color: #f0b0ad; box-shadow: 0 0 0 4px rgba(240, 176, 173, 0.12); }
    .dark .sort-select-icon { color: #d1d5db; }
    .dark .dock-card { background: #1f2937; color: #d1d5db; border-color: #4b5563; }
    .dark .dock-card:before { background: linear-gradient(90deg, #1f2937, #2c3a4f); border-bottom-color: #4b5563; }
    .dark .mobile-filter-sheet { background-color: #111827; color: #d1d5db; border-top-color: #4b5563; }
    .dark .mobile-filter-sheet header { background-color: rgba(31, 41, 55, 0.9); backdrop-filter: blur(4px); border-bottom-color: #4b5563; }
    .dark .mobile-filter-sheet header h3, .dark .mobile-filter-sheet header .text-gray-500 { color: #f9fafb; }
    .dark .mobile-filter-trigger { background-color: #1f2937; border-color: #4b5563; box-shadow: none; }
    .dark .mobile-filter-trigger span, .dark .mobile-filter-trigger i { color: #d1d5db; }
    .dark .no-products-message { background-color: #1f2937; border-color: #4b5563; color: #9ca3af; }
    .dark .product-card { background-color: #1f2937; }
    .dark .product-title { color: #f9fafb; }
    .dark .price { color: #fff; }
    .dark .old { color: #ef4444; }
    .dark .btn-fav { background-color: #374151; color: #9ca3af; }
    .dark .btn-fav:hover { background-color: #4b5563; }
    .dark .btn-fav.favorited { background-color: rgba(15, 42, 68, 0.2); color: #D4AF37; }
    .dark .filter-section { border-color: #374151; }
    .dark .filter-title { color: #f9fafb; }
    .dark .filter-category-item { color: #d1d5db; }
    .dark .filter-category-item:hover { background-color: #374151; }
    .dark .filter-category-item.active { background-color: rgba(205, 137, 133, 0.1); color: #f9a8d4 !important; }
    .dark .cat-toggle-btn:hover { background-color: #374151; }
    .dark .price-slider-track { background: #4b5563; }
    .dark .price-slider-range { background: #f0b0ad; }
    .dark .price-slider-input::-webkit-slider-thumb { background: #374151; border-color: #f0b0ad; box-shadow: 0 0 0 2px #374151; }
    .dark .price-display { background-color: #374151; color: #d1d5db; }
    .dark .sale-checkbox { border-color: #6b7280; background-color: #374151; }
    .dark .filter-clear-btn { color: #9ca3af; }
    .dark .filter-clear-btn:hover { color: white; }
    
    /* Search Hero */
    .search-hero{
      display:flex; align-items:center; justify-content:center;
      padding:2rem 1rem; margin-bottom:1rem;
      background:#fff; border:1px solid #efe7df; border-radius:14px;
      box-shadow:0 6px 14px rgba(0,0,0,.04);
    }
    .search-hero-title{ text-align:center; font-weight:800; font-size:1.125rem; color:#2d2a2a; }
    .search-hero-title span{ color:var(--primary-color); }
    .dark .search-hero{ background:#1f2937; border-color:#4b5563; color:#d1d5db; }
    .dark .search-hero-title{ color:#f9fafb; }
    .dark .search-hero-title span{ color:#f0b0ad; }

    /* Category Hero */
    .category-hero {
        position: relative;
        border-radius: 14px;
        overflow: hidden;
        margin-bottom: 2rem;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }
    .category-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image: var(--bg-image);
        background-size: cover;
        background-position: center;
        filter: blur(20px) brightness(0.8);
        transform: scale(1.1);
        z-index: 1;
    }
    html.dark .category-hero::before {
        filter: blur(20px) brightness(0.6);
    }
    .category-hero-content {
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }
    .category-hero-image {
        width: 80px;
        height: 80px;
        border-radius: 9999px;
        object-fit: cover;
        border: 4px solid rgba(255, 255, 255, 0.5);
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        flex-shrink: 0;
    }
    .category-hero-text {
        text-align: right;
        color: white;
    }
    .category-hero-subtitle {
        font-weight: 500;
        opacity: 0.9;
    }
    .category-hero-title {
        font-size: 2rem;
        font-weight: 800;
        line-height: 1.2;
        text-shadow: 0 2px 6px rgba(0,0,0,0.5);
    }
    @media(max-width: 640px) {
        .category-hero-title { font-size: 1.5rem; }
        .category-hero-subtitle { font-size: 0.9rem; }
        .category-hero-image { width: 64px; height: 64px; border-width: 3px; }
    }
</style>

{{-- ⭐⭐⭐ كود التنسيق النهائي لترقيم الصفحات ⭐⭐⭐ --}}
<style>
    .pagination-wrapper {
        display: flex;
        justify-content: flex-start; /* For RTL: Puts items on the RIGHT */
    }
    @media (max-width: 640px) {
        .pagination-wrapper {
            justify-content: center; /* Center on mobile */
        }
    }
    .pagination-nav {
        display: flex;
        align-items: center;
        flex-wrap: nowrap;
        gap: 0.5rem;
    }
    .pagination-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 44px;
        height: 44px;
        padding: 0 0.5rem;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.9rem;
        text-decoration: none;
        color: #34282C;
        background-color: #fff;
        border: 2px solid #f0e5da;
        transition: all 0.2s ease-in-out;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05), 0 2px 4px -2px rgb(0 0 0 / 0.05);
    }
    .pagination-link:hover {
        border-color: var(--primary-color);
        color: var(--primary-color);
        transform: translateY(-2px);
    }
    /* Active page style */
    .pagination-link.active {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
        border-color: transparent;
        color: #fff;
        cursor: default;
        transform: none;
        box-shadow: 0 8px 20px rgba(109, 14, 22, 0.3);
    }
    /* Disabled link style */
    .pagination-link.disabled {
        opacity: 0.6;
        cursor: not-allowed;
        background-color: #f9fafb;
    }

    /* Mobile view adjustments */
    @media (max-width: 640px) {
        .pagination-link {
              min-width: 40px;
              height: 40px;
              font-size: 0.8rem;
              border-radius: 10px;
        }
        .pagination-nav {
            gap: 0.35rem;
        }
    }

    /* Dark Mode */
    html.dark .pagination-link {
        background-color: #1f2937;
        border-color: #374151;
        color: #d1d5db;
        box-shadow: none;
    }
    html.dark .pagination-link:hover {
        border-color: var(--primary-hover);
        color: var(--primary-hover);
    }
    html.dark .pagination-link.active {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
        border-color: transparent;
        box-shadow: none;
    }
    html.dark .pagination-link.disabled {
        background-color: #374151;
        border-color: #4b5563;
    }
</style>

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12"
      x-data="{ mobileFiltersOpen:false }"
      x-effect="document.body.classList.toggle('overflow-hidden', mobileFiltersOpen)">

    @php
        $searchTerm = null;
        $currentCategory = null;

        $searchKeys = ['q', 'query', 'search', 'keyword', 'term', 's', 'searchTerm'];
        foreach ($searchKeys as $key) {
            if (request()->filled($key) && is_string(request($key))) {
                $searchTerm = trim(request($key));
                break;
            }
        }

        if (request()->filled('category')) {
            $categorySlug = request('category');
            if(isset($categories)) {
                $findCategory = function($categoryList, $slug) use (&$findCategory, &$currentCategory) {
                    foreach ($categoryList as $category) {
                        if ($category->slug === $slug) {
                            $currentCategory = $category;
                            return;
                        }
                        if ($currentCategory === null && $category->children && $category->children->isNotEmpty()) {
                            $findCategory($category->children, $slug);
                        }
                    }
                };
                $findCategory($categories, $categorySlug);
            }
        }
    @endphp

    {{-- Banners with Search Priority --}}
    @if ($searchTerm)
        <div class="search-hero">
            <div class="search-hero-content">
                <h1 class="search-hero-title">
                    {{ __('shop.search_results_for') }} <span>"{{ e($searchTerm) }}"</span>
                    @if($currentCategory)
                        {{ __('shop.in_section') }} "{{ $currentCategory->name_translated }}"
                    @endif
                </h1>
            </div>
        </div>
    @elseif ($currentCategory)
        <div class="category-hero" style="--bg-image: url('{{ $currentCategory->image ? asset('storage/' . $currentCategory->image) : 'https://placehold.co/1200x300/f3e5e3/be6661?text=Tofof' }}')">
            <div class="category-hero-content">
                @if ($currentCategory->image)
                    <img src="{{ asset('storage/' . $currentCategory->image) }}" alt="{{ $currentCategory->name_translated }}" class="category-hero-image">
                @endif
                <div class="category-hero-text">
                    <p class="category-hero-subtitle">{{ __('shop.browsing_section') }}</p>
                    <h1 class="category-hero-title">{{ $currentCategory->name_translated }}</h1>
                </div>
            </div>
        </div>
    @endif
    
    {{-- Toolbar --}}
    @isset($products)
    <div class="toolbar mb-6">
        {{-- تم إزالة style="font-size: 10px;" والاعتماد على تعديل الـ CSS --}}
        <div class="text-gray-600">
            @php
                $from = ($products->currentPage()-1)*$products->perPage()+1;
                $to    = min($products->currentPage()*$products->perPage(), $products->total());
            @endphp
            {{ __('shop.showing_range', ['from'=>$from, 'to'=>$to, 'total'=>$products->total()]) }}
        </div>
        <form method="GET">
            @foreach(request()->except('sort') as $k=>$v)
                @if(is_array($v))
                    @foreach($v as $vv)<input type="hidden" name="{{ $k }}[]" value="{{ $vv }}">@endforeach
                @else <input type="hidden" name="{{ $k }}" value="{{ $v }}"> @endif
            @endforeach
            @php $sort = request('sort'); @endphp
            <label class="toolbar-label">{{ __('shop.sort_by') }}</label>
            <div class="sort-select-wrap">
                <select name="sort" class="sort-select" onchange="this.form.submit()">
                    <option value="" {{ $sort===''||$sort===null ? 'selected' : '' }}>{{ __('shop.newest') }}</option>
                    <option value="price_asc"  {{ $sort==='price_asc'  ? 'selected' : '' }}>{{ __('shop.price_low_to_high') }}</option>
                    <option value="price_desc" {{ $sort==='price_desc' ? 'selected' : '' }}>{{ __('shop.price_high_to_low') }}</option>
                    <option value="rating_desc"{{ $sort==='rating_desc'? 'selected' : '' }}>{{ __('shop.highest_rated') }}</option>
                    <option value="bestseller" {{ $sort==='bestseller' ? 'selected' : '' }}>{{ __('shop.best_selling') }}</option>
                </select>
                <i class="bi bi-chevron-down sort-select-icon"></i>
            </div>
        </form>
    </div>
    @endisset

    {{-- Mobile Filter Button --}}
    <div class="lg:hidden mb-6">
        <button @click="mobileFiltersOpen = true"
                class="mobile-filter-trigger w-full bg-white p-3 rounded-lg shadow-md border border-gray-200 flex justify-between items-center">
            <span class="font-semibold text-brand-dark"><i class="bi bi-funnel-fill mr-2"></i> {{ __('shop.show_filters') }}</span>
            <i class="bi bi-chevron-down"></i>
        </button>
    </div>

    {{-- Mobile Bottom Sheet --}}
    <template x-teleport="body">
        <div x-show="mobileFiltersOpen" style="display:none" class="fixed inset-0 z-[100] lg-hidden">
            <button class="absolute inset-0 bg-black/50" @click="mobileFiltersOpen=false"></button>
            <div x-show="mobileFiltersOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-y-0"
                 x-transition:leave-end="translate-y-full"
                 class="mobile-filter-sheet fixed bottom-0 left-0 right-0 bg-white rounded-t-2xl shadow-2xl border-t max-h-[85vh] overflow-y-auto" style="overscroll-behavior: contain; -webkit-overflow-scrolling: touch;">
                <header class="sticky top-0 z-10 bg-white/90 backdrop-blur border-b px-4 py-3 flex items-center justify-between">
                    <button @click="mobileFiltersOpen=false" class="text-gray-500 text-2xl leading-none">&times;</button>
                    <h3 class="text-base font-bold">{{ __('shop.filters') }}</h3>
                    <button form="filter-form-mobile"
                            class="text-sm font-semibold px-3 py-1.5 rounded-full bg-[var(--primary-color)] text-white hover:opacity-90">
                        {{ __('common.apply') }}
                    </button>
                </header>
                <div class="p-4">
                    @include('frontend.partials._filters', ['categories'=>$categories, 'isMobile'=>true])
                </div>
            </div>
        </div>
    </template>

    {{-- GRID + FILTER DOCK --}}
    <div class="layout">
        {{-- GRID --}}
        <div>
            {{-- Divided Search Results: Brands and Categories --}}
            @if(request()->filled('query'))
                {{-- Matched Brands Section --}}
                @if(isset($matchedBrands) && $matchedBrands->isNotEmpty())
                    <div class="mb-10">
                        <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-100 dark:border-gray-800">
                            <h2 class="text-xl font-extrabold text-[#6d0e16] dark:text-[#f0b0ad] flex items-center gap-2">
                                <i class="bi bi-tags"></i>
                                {{ __('shop.matching_brands') }}
                            </h2>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                            @foreach($matchedBrands as $brand)
                                <a href="{{ route('shop', ['brand' => $brand->slug]) }}" 
                                   class="group flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-[#6d0e16]/30 transition-all duration-300">
                                    <div class="w-16 h-16 md:w-20 md:h-20 rounded-full overflow-hidden bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-700 mb-3 flex items-center justify-center">
                                        @if($brand->image)
                                            <img src="{{ asset('storage/' . $brand->image) }}" alt="{{ $brand->name_translated }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                        @else
                                            <i class="bi bi-tag text-2xl text-[#6d0e16]/40"></i>
                                        @endif
                                    </div>
                                    <span class="text-sm font-bold text-gray-800 dark:text-gray-200 text-center group-hover:text-[#6d0e16] transition-colors line-clamp-1">
                                        {{ $brand->name_translated }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Matched Categories Section --}}
                @if(isset($matchedCategories) && $matchedCategories->isNotEmpty())
                    <div class="mb-10">
                        <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-100 dark:border-gray-800">
                            <h2 class="text-xl font-extrabold text-[#6d0e16] dark:text-[#f0b0ad] flex items-center gap-2">
                                <i class="bi bi-grid"></i>
                                {{ __('shop.matching_sections') }}
                            </h2>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                            @foreach($matchedCategories as $cat)
                                <a href="{{ route('shop', ['category' => $cat->slug]) }}" 
                                   class="group flex flex-col items-center p-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md hover:border-[#6d0e16]/30 transition-all duration-300">
                                    <div class="w-16 h-16 md:w-20 md:h-20 rounded-xl overflow-hidden bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-700 mb-3 flex items-center justify-center">
                                        @if($cat->image)
                                            <img src="{{ asset('storage/' . $cat->image) }}" alt="{{ $cat->name_translated }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                        @else
                                            <i class="bi bi-grid text-2xl text-[#6d0e16]/40"></i>
                                        @endif
                                    </div>
                                    <span class="text-sm font-bold text-gray-800 dark:text-gray-200 text-center group-hover:text-[#6d0e16] transition-colors line-clamp-1">
                                        {{ $cat->name_translated }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($products->total() > 0)
                    <div class="flex items-center justify-between mb-6 pb-2 border-b border-gray-100 dark:border-gray-800">
                        <h2 class="text-xl font-extrabold text-[#6d0e16] dark:text-[#f0b0ad] flex items-center gap-2">
                            <i class="bi bi-box-seam"></i>
                            {{ __('shop.products') }}
                        </h2>
                    </div>
                @endif
            @endif

            <div class="products-grid">
                @forelse($products as $product)
                    <div class="product-card"
                         x-data="{
                             showAlt:false,
                             hasTwoImages: {{ optional($product->images)->count() > 1 ? 'true' : 'false' }},
                             rtl: document.documentElement.dir === 'rtl',
                             added:false, loadingAdd:false,
                             isFavorite: {{ in_array($product->id, $favoriteProductIds) ? 'true' : 'false' }},
                             loadingFav:false,
                             touchStartX:0,touchStartY:0,isSwiping:false,gestureDetermined:false,
                             handleTouchStart(e){ this.touchStartX=e.touches[0].clientX; this.touchStartY=e.touches[0].clientY; this.isSwiping=false; this.gestureDetermined=false; },
                             handleTouchMove(e){ if(this.gestureDetermined)return; const dx=Math.abs(e.touches[0].clientX-this.touchStartX); const dy=Math.abs(e.touches[0].clientY-this.touchStartY); if(dx>10||dy>10){ if(dx>dy){ this.isSwiping=true; e.preventDefault(); } this.gestureDetermined=true; } },
                             handleTouchEnd(e,linkEl){ if(this.isSwiping){ if(this.hasTwoImages){ this.showAlt=!this.showAlt; } } else { const dx=Math.abs(e.changedTouches[0].clientX-this.touchStartX); const dy=Math.abs(e.changedTouches[0].clientY-this.touchStartY); if(dx<10&&dy<10){ window.location.href=linkEl.href; } } },
                          toggleWishlist() {
                              if(this.loadingFav) return;
                              this.loadingFav=true;
                              fetch('{{ route('wishlist.toggle.async', $product->id) }}', {
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
                                  body:JSON.stringify({product_id:{{ $product->id }},quantity:1})
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
                      @mouseover="hasTwoImages ? showAlt=true : null"
                      @mouseout="hasTwoImages ? showAlt=false : null">

                     <a href="{{ route('product.detail', $product) }}"
                        class="product-content-link"
                        @touchstart="handleTouchStart($event)"
                        @touchmove="handleTouchMove($event)"
                        @touchend="handleTouchEnd($event, $el)">

                         <div class="product-image-container">
                             {{-- شارة منتهي الكمية + شارة نسبة الخصم --}}
                             @php
                                 $isAvailable = ($product->stock_qty ?? $product->stock_quantity ?? 0) > 0;
                                 $isOnSale = $product->isOnSale();
                                 $discountPercentage = ($isOnSale && $product->price > 0)
                                     ? round((($product->price - $product->sale_price) / $product->price) * 100)
                                     : null;
                                 $secondImage = optional($product->images->get(1))->image_path;
                             @endphp

                             {{-- منتهي الكمية --}}
                             @if(!$isAvailable)
                                 <div class="absolute inset-0 bg-black/60 z-10 flex items-center justify-center pointer-events-none">
                                     <span class="text-white font-bold tracking-wider text-sm border border-white/50 rounded-full px-3 py-1">
                                         {{ __('common.out_of_stock') }}
                                     </span>
                                 </div>
                             @endif

                             {{-- شارة نسبة الخصم --}}
                             @if($isOnSale && $isAvailable && $discountPercentage > 0)
                                 <div class="product-sale-badge">-{{ $discountPercentage }}%</div>
                             @endif

                             {{-- ✅ سلايد بدون أي شريط جانبي --}}
                             <div class="product-image-slider">
                                 {{-- الصورة الأولى --}}
                                 @if ($product->firstImage)
                                     <img src="{{ asset('storage/'.$product->firstImage->image_path) }}"
                                          alt="{{ $product->name_translated }}" loading="lazy" width="600" height="600"
                                          :style="{
                                             transform: showAlt && hasTwoImages
                                                 ? (rtl ? 'translateX(105%)' : 'translateX(-105%)')
                                                 : 'translateX(0)',
                                             transition: 'transform 0.35s ease'
                                          }">
                                 @else
                                     <img src="https://placehold.co/600x600?text=No+Image"
                                          alt="No image" loading="lazy" width="600" height="600"
                                          :style="{
                                             transform: showAlt && hasTwoImages
                                                 ? (rtl ? 'translateX(105%)' : 'translateX(-105%)')
                                                 : 'translateX(0)',
                                             transition: 'transform 0.35s ease'
                                          }">
                                 @endif

                                 {{-- الصورة الثانية (تعكس الاتجاه) --}}
                                 @if ($secondImage)
                                     <img src="{{ asset('storage/'.$secondImage) }}"
                                          alt="{{ $product->name_translated }} (alt)" loading="lazy" width="600" height="600"
                                          :style="{
                                             transform: showAlt && hasTwoImages
                                                 ? 'translateX(0)'
                                                 : (rtl ? 'translateX(-105%)' : 'translateX(105%)'),
                                             transition: 'transform 0.35s ease'
                                          }">
                                 @elseif ($product->firstImage)
                                     <img src="{{ asset('storage/' . $product->firstImage->image_path) }}"
                                          alt="{{ $product->name_translated }}" loading="lazy" width="600" height="600"
                                          :style="{
                                             transform: showAlt && hasTwoImages
                                                 ? 'translateX(0)'
                                                 : (rtl ? 'translateX(-105%)' : 'translateX(105%)'),
                                             transition: 'transform 0.35s ease'
                                          }">
                                 @else
                                     <img src="https://placehold.co/600x600?text=No+Image"
                                          alt="No image" loading="lazy" width="600" height="600"
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
                             <h3 class="product-title">{{ $product->name_translated }}</h3>

                             @php
                                 $avg = round((float) ($product->average_rating ?? 0), 1);
                                 $count = (int) ($product->reviews_count ?? 0);
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
                                 @if($product->isOnSale())
                                     <div class="price">{{ number_format($product->sale_price,0) }} {{ __('common.currency') }}</div>
                                     <div class="old">{{ number_format($product->price,0) }} {{ __('common.currency') }}</div>
                                 @else
                                     <div class="price">{{ number_format($product->price,0) }} {{ __('common.currency') }}</div>
                                 @endif
                             </div>
                         </div>
                     </a>
                             <div class="product-actions">
                                 @auth
                                 <button @click.stop="toggleWishlist()"
                                         @touchend.stop
                                         class="btn-fav"
                                         :class="{'favorited':isFavorite, 'opacity-50 pointer-events-none': loadingFav}"
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
                                 <button class="btn-primary bg-gray-400 hover:bg-gray-400 cursor-not-allowed w-full" disabled>
                                     {{ __('common.out_of_stock') }}
                                 </button>
                                 @endif
                             </div>
                    </div>
                @empty
                    <div class="no-products-message col-span-full text-center py-12 rounded-2xl">
                        @if($searchTerm)
                            {{ __('shop.no_results_for_term') }} <strong>"{{ e($searchTerm) }}"</strong>.
                        @else
                            {{ __('shop.no_results_currently') }}
                        @endif
                    </div>
                @endforelse
            </div>

            {{-- ⭐⭐⭐ حاوية الترقيم الجديدة + الكود اليدوي ⭐⭐⭐ --}}
            @if ($products->hasPages())
                <div class="mt-10 pagination-wrapper">
                    <nav class="pagination-nav">
                        {{-- Previous Page Link --}}
                        <a href="{{ $products->previousPageUrl() }}"
                           class="pagination-link {{ $products->onFirstPage() ? 'disabled' : '' }}">
                            <i class="bi bi-chevron-right"></i>
                        </a>

                        {{-- Pagination Elements --}}
                        @php
                            $window = \Illuminate\Pagination\UrlWindow::make($products);
                            $elements = array_filter([
                                $window['first'],
                                is_array($window['slider']) ? '...' : null,
                                $window['slider'],
                                is_array($window['last']) ? '...' : null,
                                $window['last'],
                            ]);
                        @endphp

                        @foreach ($elements as $element)
                            {{-- "Three Dots" Separator --}}
                            @if (is_string($element))
                                <span class="pagination-link disabled">{{ $element }}</span>
                            @endif

                            {{-- Array Of Links --}}
                            @if (is_array($element))
                                @foreach ($element as $page => $url)
                                    <a href="{{ $url }}"
                                       class="pagination-link {{ $page == $products->currentPage() ? 'active' : '' }}">
                                        {{ $page }}
                                    </a>
                                @endforeach
                            @endif
                        @endforeach

                        {{-- Next Page Link --}}
                        <a href="{{ $products->nextPageUrl() }}"
                           class="pagination-link {{ !$products->hasMorePages() ? 'disabled' : '' }}">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </nav>
                </div>
            @endif

        </div>

        {{-- FILTER DOCK (hidden on mobile) --}}
        <aside class="filter-dock hidden lg:block">
            <div class="dock-card">
                @include('frontend.partials._filters', ['categories'=>$categories, 'isMobile'=>false])
            </div>
        </aside>
    </div>
</div>
{{-- ==== Request Unavailable Product – CTA ==== --}}
<div class="mt-14">
  <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm dark:bg-gray-800 dark:border-gray-700">
    <div class="flex items-start gap-4">
      <div class="text-3xl">🛍️</div>
      <div class="flex-1">
        <h3 class="text-lg font-extrabold text-gray-900 dark:text-gray-100">{{ __('shop.cant_find_product') }}</h3>
        <p class="text-gray-600 dark:text-gray-300 mt-1">
          {{ __('shop.request_product_desc') }}
        </p>
        <button
          class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl font-bold text-white"
          style="background: var(--primary-color);"
          @click="$dispatch('open-request-modal')">
          <i class="bi bi-plus-circle"></i> {{ __('shop.request_product') }}
        </button>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js" defer></script>
@endpush