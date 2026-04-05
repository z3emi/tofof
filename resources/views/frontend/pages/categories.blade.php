@extends('layouts.app')

@section('title', __('pages.categories_title'))

@section('content')
<style>
  :root {
    --brand-maroon: #6d0e16;
    --brand-soft: #fdf2f2;
    --text-dark: #1a1a1a;
    --text-muted: #64748b;
    --bg-main: #ffffff;
    --bg-alt: #f8fafc;
    --radius-circle: 100px;
  }

  html.dark .icon-grid-categories {
    --bg-main: #0f172a;
    --bg-alt: #1e293b;
    --text-dark: #f8fafc;
    --text-muted: #94a3b8;
    --brand-soft: rgba(109, 14, 22, 0.1);
  }

  .icon-grid-categories {
    min-height: 100vh;
    background: var(--bg-main);
    font-family: 'Cairo', sans-serif !important;
    padding-bottom: 5rem;
  }

  /* Grid of Circles */
  .prime-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem 1rem;
    padding: 2rem 1rem;
  }

  .prime-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    cursor: pointer;
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }
  .prime-item:active { transform: scale(0.9); }

  .prime-circle {
    width: clamp(80px, 20vw, 110px);
    height: clamp(80px, 20vw, 110px);
    border-radius: 50%;
    background: var(--bg-alt);
    margin-bottom: 0.75rem;
    position: relative;
    padding: 3px;
    border: 2px solid var(--brand-soft);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
  }
  .prime-circle img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }

  .prime-name {
    font-size: 0.85rem;
    font-weight: 800;
    color: var(--text-dark);
    line-height: 1.3;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .prime-count { font-size: 0.7rem; color: var(--text-muted); margin-top: 0.1rem; }

  /* Detail View Header */
  .detail-header {
    background: var(--bg-main);
    padding: 1.5rem 1rem;
    border-bottom: 1px solid var(--brand-soft);
    position: sticky;
    top: 60px;
    z-index: 100;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .back-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 800;
    color: var(--brand-maroon);
    background: var(--brand-soft);
    padding: 0.5rem 1rem;
    border-radius: 12px;
    font-size: 0.85rem;
  }

  .browse-btn {
    font-weight: 800;
    color: #fff;
    background: var(--brand-maroon);
    padding: 0.5rem 1rem;
    border-radius: 12px;
    font-size: 0.85rem;
    text-decoration: none;
    box-shadow: 0 4px 10px rgba(109, 14, 22, 0.2);
  }

  /* Detail Content */
  .detail-body { padding: 1.5rem 1rem; }
  
  .sub-sec-title {
    font-size: 1.1rem;
    font-weight: 900;
    color: var(--text-dark);
    margin-bottom: 1rem;
    padding-right: 0.75rem;
    border-right: 4px solid var(--brand-maroon);
  }

  .brands-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-bottom: 2rem;
  }

  .brand-row-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem;
    background: var(--bg-alt);
    border-radius: 18px;
    text-decoration: none;
    border: 1px solid transparent;
    transition: all 0.3s ease;
  }
  .brand-row-item:hover { border-color: var(--brand-maroon); background: #fff; transform: translateX(-5px); }

  .brand-row-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: #fff;
    border: 1px solid var(--brand-soft);
    overflow: hidden;
    flex-shrink: 0;
  }
  .brand-row-icon img { width: 100%; height: 100%; object-fit: contain; }
  .brand-row-name { font-size: 0.95rem; font-weight: 700; color: var(--text-dark); flex: 1; }
  .brand-row-count { font-size: 0.75rem; color: var(--text-muted); }

  /* Animations */
  [x-cloak] { display: none !important; }
</style>

<div class="icon-grid-categories" dir="rtl" x-data="{ view: 'grid', selectedId: null, selectedName: '', selectedSlug: '' }">
    
    {{-- View 1: Main Category Grid --}}
    <div x-show="view == 'grid'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
        {{-- Header Info --}}
            <div class="text-center pt-8 px-4">
                <h1 class="text-2xl font-black text-brand-maroon">الفئات</h1>
            <p class="text-sm text-text-muted mt-1">{{ __('اختر القسم الذي يروق لك لاكتشاف منتجاتنا') }}</p>
        </div>

        <div class="prime-grid">
            @forelse ($fiatTree as $fia)
                <div class="prime-item" @click="view = 'detail'; selectedId = '{{ $fia->id }}'; selectedName = '{{ $fia->name_translated }}'; selectedSlug = '{{ $fia->slug }}'">
                    <div class="prime-circle">
                        @php $img = $fia->image ?: $fia->icon; @endphp
                        @if($img)
                            <img src="{{ asset('storage/' . $img) }}" alt="{{ $fia->name_translated }}">
                        @else
                            <span class="text-2xl">🏷️</span>
                        @endif
                    </div>
                    <span class="prime-name">{{ $fia->name_translated }}</span>
                    <span class="prime-count">{{ $fia->products_count }} {{ __('منتج') }}</span>
                </div>
            @empty
                <div class="col-span-3 py-20 text-center opacity-30">
                    <i class="bi bi-search text-6xl"></i>
                    <p class="mt-4">{{ __('عذراً، لا تتوفر أقسام') }}</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- View 2: Drill-down Detail View --}}
    <div x-show="view == 'detail'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-10" x-transition:enter-end="opacity-100 translate-x-0">
        {{-- Fixed Header for Detail --}}
        <div class="detail-header">
            <button class="back-btn" @click="view = 'grid'">
                <i class="bi bi-arrow-right"></i> {{ __('رجوع') }}
            </button>
            <h2 class="text-lg font-black text-text-dark" x-text="selectedName"></h2>
            <a :href="'/shop?brand=' + selectedSlug" class="browse-btn">
                {{ __('تصفح القسم') }}
            </a>
        </div>

        {{-- Scrollable Detail Content --}}
        <div class="detail-body">
            @foreach ($fiatTree as $fia)
                <div x-show="selectedId == '{{ $fia->id }}'">
                    @if($fia->categories && $fia->categories->isNotEmpty())
                        <h3 class="sub-sec-title">{{ __('الفئات المتاحة') }}</h3>
                        <div class="brands-list">
                            @foreach($fia->categories as $category)
                                <a href="{{ route('shop', ['category' => $category->slug]) }}" class="brand-row-item">
                                    <div class="brand-row-icon">
                                        @if($category->image)
                                            <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name_translated }}">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center bg-gray-50 text-xs">🧴</div>
                                        @endif
                                    </div>
                                    <span class="brand-row-name">{{ $category->name_translated }}</span>
                                    <span class="brand-row-count">{{ $category->products_count }} {{ __('منتج') }}</span>
                                    <i class="bi bi-chevron-left text-text-muted"></i>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="py-20 text-center text-text-muted">
                            <i class="bi bi-box-seam text-6xl"></i>
                            <p class="mt-4">{{ __('لا توجد فئات متاحة حالياً') }}</p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

</div>

<script>
    // Force top scroll when switching views
    window.addEventListener('scroll', () => {}); // No-op, just to trigger Alpine
    document.addEventListener('alpine:init', () => {
        // You could add logic here to scroll to top on view change
    });
</script>
@endsection
