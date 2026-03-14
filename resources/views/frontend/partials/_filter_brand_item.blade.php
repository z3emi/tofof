@props(['brand', 'currentBrand', 'level' => 1])

@php
    $slug   = $brand->slug ?? null;
    $name   = $brand->name_ar ?? $brand->name ?? 'براند';
    $img    = $brand->image
                ? asset('storage/' . $brand->image)
                : ((isset($brand->icon) && $brand->icon) ? asset('storage/' . $brand->icon) : null);
    
    // ✅ [تصحيح] منطق جديد وأكثر قوة لحساب عدد المنتجات
    $count = null;
    if (isset($brand->total_products_count)) {
        // استخدام العدد الإجمالي المحسوب مسبقاً إذا كان موجوداً
        $count = $brand->total_products_count;
    } elseif (isset($brand->products_count)) {
        // إذا لم يكن موجوداً، استخدم العدد الخاص بالعنصر نفسه
        $count = $brand->products_count;
    } elseif ($brand->relationLoaded('products')) {
        // كحل أخير، قم بعد المنتجات إذا كانت العلاقة محملة بالفعل
        $count = $brand->products->count();
    }

    $hasChildren = isset($brand->children) && $brand->children && $brand->children->isNotEmpty();

    $isActive = $slug && $slug === $currentBrand;
    if (!$isActive && $hasChildren) {
        foreach ($brand->children as $child) {
            if (($child->slug ?? null) === $currentBrand) { $isActive = true; break; }
            if (isset($child->children) && $child->children->isNotEmpty() && $child->children->pluck('slug')->contains($currentBrand)) { $isActive = true; break; }
        }
    }
@endphp

<div
    x-data="{ open: false }"
    x-init="
        open = window.FilterState.isCatOpen('brand:{{ $slug ?? 'null' }}', {{ $isActive ? 'true' : 'false' }});
    "
>
    <div class="flex items-center justify-between" style="padding-right: {{ max(0, ($level - 1) * 1.0) }}rem;">
        <button type="button"
                @click="selectedBrand = (selectedBrand === '{{ $slug }}') ? null : '{{ $slug }}'"
                @class(['filter-category-item flex-grow', 'is-child' => $level > 1, 'active' => $isActive])
                :class="{'active': selectedBrand === '{{ $slug }}'}"
        >
            <div class="cat-thumb">
                @if($img)<img src="{{ $img }}" alt="{{ $name }}">@else<div class="ph">🏷️</div>@endif
            </div>
            <div class="cat-texts">
                <div class="cat-name">{{ $name }}</div>
                <div class="cat-meta">
                    @if($level === 1)<span class="cat-chip text-xs"><i class="bi bi-diagram-3"></i> رئيسي</span>
                    @elseif($level === 2)<span class="cat-chip text-xs"><i class="bi bi-diagram-3"></i> فرعي</span>
                    @else<span class="cat-chip text-xs"><i class="bi bi-diagram-3"></i> مستوى {{ $level }}</span>
                    @endif
                    
                    @if(!is_null($count) && $count > 0)
                        <span class="cat-chip text-xs"><i class="bi bi-box-seam"></i> {{ $count }} منتج</span>
                    @endif
                </div>
            </div>
        </button>

        @if($hasChildren)
            <button type="button"
                    @click="
                        open = !open;
                        $nextTick(()=>window.FilterState.toggleCat('brand:{{ $slug ?? 'null' }}', open));
                    "
                    class="cat-toggle-btn"
                    :aria-expanded="open.toString()">
                <i class="bi transition-transform duration-300 text-xs" :class="open ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
            </button>
        @endif
    </div>

    @if($hasChildren)
        <div x-show="open" x-collapse.duration.300ms class="mt-1 space-y-1 smooth-collapse">
            @foreach($brand->children as $child)
                @include('frontend.partials._filter_brand_item', ['brand' => $child, 'currentBrand' => $currentBrand, 'level' => $level + 1])
            @endforeach
        </div>
    @endif
</div>