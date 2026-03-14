@props(['category', 'currentCategory', 'level' => 1])

@php
    $slug   = $category->slug ?? null;
    $id     = $category->id   ?? null;
    $name   = $category->name_ar ?? $category->name ?? 'قسم';
    $img    = $category->image
                ? asset('storage/' . $category->image)
                : ((isset($category->icon) && $category->icon) ? asset('storage/' . $category->icon) : null);
    $count  = $category->total_products_count ?? $category->products_count ?? null;

    $nodeKey = $slug ? (string)$slug : ('id-' . (string)$id);
    $isActive = $slug && $slug === $currentCategory;

    $hasChildren = method_exists($category, 'children') && $category->children && $category->children->isNotEmpty();
@endphp

<div
    x-data="{ open: false }"
    x-init="open = window.FilterState.isCatOpen('{{ $nodeKey }}', false)"
>
    <div class="flex items-center justify-between" style="padding-right: {{ max(0, ($level - 1) * 1.0) }}rem;">
        <button type="button"
                @click="
                    const next = (selectedCategoryKey === '{{ $nodeKey }}') ? null : '{{ $nodeKey }}';
                    selectedCategoryKey  = next;
                    selectedCategorySlug = next ? @js($slug) : null;
                    selectedCategoryId   = next ? @js($id)   : null;
                "
                @class([
                    'filter-category-item flex-grow text-right',
                    'is-child' => $level > 1,
                    'active' => $isActive
                ])
                :class="{'active': selectedCategoryKey === '{{ $nodeKey }}'}"
        >
            <div class="cat-thumb">
                @if($img)
                    <img src="{{ $img }}" alt="{{ $name }}">
                @else
                    <div class="ph">🧴</div>
                @endif
            </div>
            <div class="cat-texts">
                <div class="cat-name">{{ $name }}</div>
                <div class="cat-meta">
                    {{-- ✅ [تصحيح] تم إضافة كلمة "رئيسي" وتصغير حجم الخط لجميع العناصر --}}
                    @if($level === 1)
                    @elseif($level === 2)
                        <span class="cat-chip text-xs"><i class="bi bi-diagram-3"></i> فرعي</span>
                    @else
                        <span class="cat-chip text-xs"><i class="bi bi-diagram-3"></i> مستوى {{ $level }}</span>
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
                        $nextTick(()=>window.FilterState.toggleCat('{{ $nodeKey }}', open));
                    "
                    class="cat-toggle-btn"
                    :aria-expanded="open.toString()">
                <i class="bi transition-transform duration-300 text-xs" :class="open ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
            </button>
        @endif
    </div>

    @if($hasChildren)
        <div x-show="open"
             x-collapse.duration.300ms
             class="mt-1 space-y-1 smooth-collapse"
             x-transition:enter="transition"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-1">
            @foreach($category->children as $child)
                @include('frontend.partials._filter_category_item', [
                    'category' => $child,
                    'currentCategory' => $currentCategory,
                    'level' => $level + 1
                ])
            @endforeach
        </div>
    @endif
</div>