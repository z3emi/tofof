{{-- _filters.blade.php – تبديل: البراندات بدل الفئات + تطبيق عند الضغط على زر "تطبيق" فقط --}}

@once
    @push('styles')
    <style>
        :root {
            --brand-primary: #6d0e16;
            --brand-hover: #5a0b12;
            --ease-smooth: cubic-bezier(.22,.61,.36,1);
        }

        .filter-section { border-bottom: 1px solid #f3f4f6; padding-bottom: 1.5rem; margin-bottom: 1.5rem; }
        .dark .filter-section { border-color: #374151; }
        .filter-section:last-of-type { border-bottom: none; padding-bottom: 0; }

        .filter-title {
            display: flex; justify-content: space-between; align-items: center; width: 100%;
            font-size: 1.125rem; font-weight: 700; color: #6d0e16; transition: color 0.2s ease-in-out;
        }
        .dark .filter-title { color: #f9fafb; }
        .filter-title:hover { color: var(--brand-primary); }

        .filter-category-item {
            display: flex; align-items: center; gap: .65rem;
            padding: .55rem .65rem; border-radius: .75rem; text-decoration: none;
            transition: background .2s, color .2s; color: #4b5563;
        }
        .dark .filter-category-item { color: #d1d5db; }
        .filter-category-item:hover { background: #f9fafb; color: var(--brand-primary); }
        .dark .filter-category-item:hover { background: #374151; }
        .filter-category-item.is-child { padding-top: .45rem; padding-bottom: .45rem; }
        .filter-category-item.active { background: #f4e3e3; color: var(--brand-primary); font-weight: 600; }
        .dark .filter-category-item.active { background: rgba(109,14,22,.08); color: #f0b0ad; }

        .cat-toggle-btn { padding: .4rem; color: #9ca3af; border-radius: 999px; }
        .cat-toggle-btn:hover { background: #f9fafb; color: var(--brand-primary); }
        .dark .cat-toggle-btn:hover { background: #374151; }

        .cat-thumb{
            width:36px;height:36px;border-radius:.6rem;overflow:hidden;flex-shrink:0;
            background:#f4e3e3;border:1px solid #ead6d6;display:flex;align-items:center;justify-content:center
        }
        .cat-thumb img{ width:100%;height:100%;object-fit:cover }
        .cat-thumb .ph{ font-size:1rem;color:var(--brand-primary) }

        .cat-texts{ display:flex;flex-direction:column; gap:.1rem; min-width:0 }
        .cat-name{ font-weight:700; font-size:.93rem; line-height:1.2; color:#374151; white-space:nowrap; overflow:hidden; text-overflow:ellipsis }
        .dark .cat-name{ color:#f3f4f6 }
        .cat-meta{ display:flex;align-items:center;gap:.35rem; font-size:.78rem; color:#6b7280 }
        .dark .cat-meta{ color:#a1a1aa }
        .cat-chip{
            display:inline-flex;align-items:center;gap:.3rem; padding:0 .5rem; height:22px;
            border-radius:999px; background:#f4e3e3; border:1px solid #ead6d6; color:#6d0e16; font-weight:600
        }

        .price-slider { position: relative; height: 20px; }
        .price-slider-track { position: absolute; top: 50%; transform: translateY(-50%); height: 5px; width: 100%; background: #e5e7eb; border-radius: 999px; }
        .dark .price-slider-track { background: #4b5563; }
        .price-slider-range { position: absolute; top: 50%; transform: translateY(-50%); height: 5px; background: var(--brand-primary); border-radius: 999px; }
        .dark .price-slider-range { background: #f0b0ad; }
        .price-slider-input {
            position: absolute; width: 100%; top: 0; height: 20px; -webkit-appearance: none; background: transparent;
            pointer-events: none; margin: 0;
        }
        .price-slider-input::-webkit-slider-thumb {
            -webkit-appearance: none; pointer-events: auto; width: 20px; height: 20px;
            background: white; border-radius: 50%; border: 3px solid var(--brand-primary);
            cursor: pointer; box-shadow: 0 0 0 2px white;
        }
        .dark .price-slider-input::-webkit-slider-thumb {
            background: #374151; border-color: #f0b0ad; box-shadow: 0 0 0 2px #374151;
        }

        .price-display { padding: 0.25rem 0.75rem; background-color: #f3f4f6; font-size: 0.8rem; border-radius: 999px; color: #4b5563; font-weight: 500; }
        .dark .price-display { background-color: #374151; color: #d1d5db; }

        .sale-checkbox { height: 1.125rem; width: 1.125rem; border-radius: 0.25rem; color: var(--brand-primary); border-color: #d1d5db; transition: .2s; flex-shrink: 0; }
        .dark .sale-checkbox { border-color: #6b7280; background-color: #374151; }

        .filter-submit-btn { width: 100%; background: var(--brand-primary); color: white; padding: 0.65rem; border-radius: 999px; font-weight: 700; transition: background-color 0.2s ease-in-out; }
        .filter-submit-btn:hover { background: var(--brand-hover); }
        .filter-clear-btn { width: 100%; display: flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.65rem; color: #6b7280; font-weight: 500; }
        .dark .filter-clear-btn { color: #9ca3af; }
        .filter-clear-btn:hover { color: #1f2937; }
        .dark .filter-clear-btn:hover { color: white; }

        .smooth-collapse{ overflow: hidden; }
        .transition{ transition: transform .28s var(--ease-smooth), opacity .28s var(--ease-smooth); }
    </style>
    @endpush
@endonce


<form method="GET" action="{{ route('shop') }}"
      id="{{ ($isMobile ?? false) ? 'filter-form-mobile' : 'filter-form' }}"
      dir="rtl"
      x-data="filters({
          initialMin: {{ (int) request('min_price', 0) }},
          initialMax: {{ (int) request('max_price', 500000) }},
          initialBrand: @js(request('brand')),
          initialCategory: @js(request('category')),
      })"
>

    {{-- حقول خفية تُرسل عند الضغط على "تطبيق الفلتر" --}}
    <input type="hidden" name="brand"     :value="selectedBrand ?? ''">
    <input type="hidden" name="category"  :value="selectedCategorySlug ?? ''">
    <input type="hidden" name="min_price" :value="minPrice">
    <input type="hidden" name="max_price" :value="maxPrice">

    <div class="space-y-6">

        {{-- (بدل الفئات) ← البراندات – اعرض الجذور فقط، والأطفال داخل include --}}
        <div x-data="{ open: false }"
             x-init="open = sessionStorage.getItem('flt_section_brands_open') === 'true'"
             class="filter-section">
            <button type="button"
                    @click="open = !open; sessionStorage.setItem('flt_section_brands_open', open)"
                    class="filter-title">
                <span class="flex items-center gap-3">
                    <i class="bi bi-tags-fill text-lg"></i>
                    <span>الفئات</span>
                </span>
                <i class="bi bi-chevron-down transform transition-transform duration-300" :class="{ 'rotate-180': open }"></i>
            </button>

            <div x-show="open"
                 x-collapse.duration.300ms
                 class="mt-4 pr-2 space-y-1 smooth-collapse"
                 x-transition:enter="transition"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-1">

                @php
                    $currentBrand = request('brand');

                    // جهّز كولكشن للبراندات ثم اعزل الجذور فقط
                    $brandsSrc = $brandsTree ?? $brands ?? [];
                    if ($brandsSrc instanceof \Illuminate\Pagination\LengthAwarePaginator
                        || $brandsSrc instanceof \Illuminate\Pagination\Paginator) {
                        $brandsSrc = collect($brandsSrc->items());
                    } elseif (is_array($brandsSrc)) {
                        $brandsSrc = collect($brandsSrc);
                    }
                    $brandsSrc = $brandsSrc instanceof \Illuminate\Support\Collection ? $brandsSrc : collect();

                    $rootBrands = $brandsSrc->filter(function($b){
                        if (isset($b->parent) && !is_null($b->parent)) return false;
                        $pid = $b->parent_id ?? $b->parentId ?? null;
                        return is_null($pid);
                    });
                @endphp

                {{-- الكل --}}
                <button type="button"
                        class="filter-category-item"
                        :class="{'active': !selectedBrand}"
                        @click="selectedBrand = null">
                    <div class="cat-thumb"><div class="ph">All</div></div>
                    <div class="cat-texts"><div class="cat-name">الكل</div></div>
                </button>

                {{-- ✅ نعرض براندات الجذر فقط؛ الأطفال داخل _filter_brand_item --}}
                @forelse($rootBrands as $brand)
                    @include('frontend.partials._filter_brand_item', [
                        'brand' => $brand,
                        'currentBrand' => $currentBrand,
                        'level' => 1
                    ])
                @empty
                    <div class="text-sm text-gray-500">لا توجد براندات متاحة.</div>
                @endforelse
            </div>
        </div>

        {{-- الفئات (اعرض الجذور فقط؛ الأطفال عبر include) --}}
        <div x-data="{ open: false }"
             x-init="open = sessionStorage.getItem('flt_section_categories_open') === 'true'"
             class="filter-section">
            <button type="button"
                    @click="open = !open; sessionStorage.setItem('flt_section_categories_open', open)"
                    class="filter-title">
                <span class="flex items-center gap-3">
                    <i class="bi bi-tag-fill text-lg"></i>
                    <span>البراندات</span>
                </span>
                <i class="bi bi-chevron-down transform transition-transform duration-300" :class="{ 'rotate-180': open }"></i>
            </button>

            <div x-show="open"
                 x-collapse.duration.300ms
                 class="mt-4 pr-2 space-y-1 smooth-collapse"
                 x-transition:enter="transition"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-1">

                @php
                    $currentCategory = request('category');

                    // جهّز كولكشن للفئات ثم اعزل الجذور فقط
                    $cats = $categories ?? collect();
                    if ($cats instanceof \Illuminate\Pagination\LengthAwarePaginator || $cats instanceof \Illuminate\Pagination\Paginator) {
                        $cats = collect($cats->items());
                    } elseif (is_array($cats)) {
                        $cats = collect($cats);
                    }
                    $cats = $cats instanceof \Illuminate\Support\Collection ? $cats : collect();

                    $rootCategories = $cats->filter(function($c){
                        if (isset($c->parent) && !is_null($c->parent)) return false;
                        $pid = $c->parent_id ?? $c->parentId ?? null;
                        return is_null($pid);
                    });
                @endphp

                {{-- الكل --}}
                <a href="{{ route('shop', request()->except(['category','page'])) }}"
                   @class(['filter-category-item','active' => !$currentCategory])>
                    <div class="cat-thumb"><div class="ph">All</div></div>
                    <div class="cat-texts">
                        <div class="cat-name">الكل</div>
                        @isset($all_products_count)
                            <div class="cat-meta">
                                <span class="cat-chip"><i class="bi bi-box-seam"></i> {{ $all_products_count }} منتج</span>
                            </div>
                        @endisset
                    </div>
                </a>

                {{-- ✅ نعرض فئات الجذر فقط؛ الأطفال داخل _filter_category_item --}}
                @forelse ($rootCategories as $category)
                    @include('frontend.partials._filter_category_item', [
                        'category' => $category,
                        'currentCategory' => $currentCategory,
                        'level' => 1
                    ])
                @empty
                    <div class="text-sm text-gray-500">لا توجد فئات متاحة.</div>
                @endforelse
            </div>
        </div>

        {{-- السعر --}}
        <div x-data="{ open: false }"
             x-init="open = sessionStorage.getItem('flt_section_price_open') === 'true'"
             class="filter-section">
            <button type="button"
                    @click="open = !open; sessionStorage.setItem('flt_section_price_open', open)"
                    class="filter-title">
                <span class="flex items-center gap-3">
                     <i class="bi bi-currency-dollar text-lg"></i>
                    <span>السعر</span>
                </span>
                <i class="bi bi-chevron-down transform transition-transform duration-300" :class="{ 'rotate-180': open }"></i>
            </button>

            <div x-show="open"
                 x-collapse.duration.300ms
                 class="mt-4 px-2 smooth-collapse"
                 x-transition:enter="transition"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-1">
                <div class="price-slider">
                    <div class="price-slider-track"></div>
                    <div class="price-slider-range" :style="`right: ${startPercent}%; left: ${endPercentFromLeft}%`"></div>
                    <input type="range" :min="minLimit" :max="maxLimit" x-model.number="minPrice" step="1000" class="price-slider-input">
                    <input type="range" :min="minLimit" :max="maxLimit" x-model.number="maxPrice" step="1000" class="price-slider-input">
                </div>
                <div class="flex justify_between items-center mt-3">
                    <div class="price-display" x-text="formatPrice(minPrice)"></div>
                    <div class="price-display" x-text="formatPrice(maxPrice)"></div>
                </div>
            </div>
        </div>

        {{-- العروض --}}
        <div x-data="{ open: false }"
             x-init="open = sessionStorage.getItem('flt_section_sale_open') === 'true'"
             class="filter-section">
            <button type="button"
                    @click="open = !open; sessionStorage.setItem('flt_section_sale_open', open)"
                    class="filter-title">
                <span class="flex items-center gap-3">
                    <i class="bi bi-star-fill text-lg"></i>
                    <span>العروض</span>
                </span>
                <i class="bi bi-chevron-down transform transition-transform duration-300" :class="{ 'rotate-180': open }"></i>
            </button>

            <div x-show="open"
                 x-collapse.duration.300ms
                 class="mt-4 pl-1 smooth-collapse"
                 x-transition:enter="transition"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-1">
                 <label class="inline-flex items-center gap-3 cursor-pointer group">
                    <input type="checkbox" name="on_sale" value="true" class="sale-checkbox" {{ request('on_sale') ? 'checked' : '' }}>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-brand-primary dark:group-hover:text-rose-300 transition-colors">
                        عرض المنتجات المخفضة فقط
                    </span>
                </label>
            </div>
        </div>

        {{-- الأزرار --}}
        <div class="space-y-3 pt-4">
            <button type="submit" class="filter-submit-btn">تطبيق الفلتر</button>
            @if(request()->hasAny(['brand','category','min_price','max_price','on_sale']))
                <a href="{{ route('shop', request()->except(['brand','category','min_price','max_price','on_sale', 'page'])) }}" class="filter-clear-btn">
                    <i class="bi bi-eraser"></i> مسح الفلتر
                </a>
            @endif
        </div>
    </div>
</form>

@once
    @push('scripts')
    <script>
        (function(){
            const w = window;
            if (w.FilterState) return;
            w.FilterState = {
                _getSet(key){
                    try { return new Set(JSON.parse(sessionStorage.getItem(key) ?? '[]')); }
                    catch(e){ return new Set(); }
                },
                _saveSet(key, set){
                    try { sessionStorage.setItem(key, JSON.stringify(Array.from(set))); } catch(e){}
                },
                isCatOpen(key, def=false){
                    const set = this._getSet('flt_open_cats');
                    return set.has(key) ? true : !!def;
                },
                toggleCat(key, isOpen){
                    const set = this._getSet('flt_open_cats');
                    if(isOpen){ set.add(key); } else { set.delete(key); }
                    this._saveSet('flt_open_cats', set);
                }
            };
        })();

        document.addEventListener('alpine:initializing', () => {
            Alpine.data('filters', (opts) => ({
                selectedBrand: opts.initialBrand || null,
                selectedCategoryKey: null,
                selectedCategorySlug: opts.initialCategory || null,
                selectedCategoryId: null,

                minPrice: opts.initialMin ?? 0,
                maxPrice: opts.initialMax ?? 500000,
                minLimit: 0,
                maxLimit: 500000,
                startPercent: 0,
                endPercentFromLeft: 0,

                init() {
                    if (this.selectedCategorySlug) this.selectedCategoryKey = String(this.selectedCategorySlug);
                    this.updateThumbs();
                    this.$watch('minPrice', v => {
                        v=parseInt(v);
                        if (v>=this.maxPrice) this.minPrice=this.maxPrice-1000;
                        if (v<this.minLimit) this.minPrice=this.minLimit;
                        this.updateThumbs();
                    });
                    this.$watch('maxPrice', v => {
                        v=parseInt(v);
                        if (v<=this.minPrice) this.maxPrice=this.minPrice+1000;
                        if (v>this.maxLimit) this.maxPrice=this.maxLimit;
                        this.updateThumbs();
                    });
                },
                updateThumbs(){
                    this.startPercent = ((this.minPrice - this.minLimit) / (this.maxLimit - this.minLimit)) * 100;
                    this.endPercentFromLeft = 100 - (((this.maxPrice - this.minLimit) / (this.maxLimit - this.minLimit)) * 100);
                },
                formatPrice(price){ return new Intl.NumberFormat('en-US').format(price) + ' د.ع'; }
            }))
        })
    </script>
    @endpush
@endonce
