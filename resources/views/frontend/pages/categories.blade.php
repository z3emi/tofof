@extends('layouts.app')

@section('title', __('pages.categories_title'))

@section('content')
<style>
  :root {
    --brand: #6d0e16;
    --brand-dark: #500a10;
    --line: #f3f4f6;
    --soft: #fdf2f2;
    --text: #1a1a1a;
    --text-light: #4b5563;
    --bg-light: #ffffff;
    --border: #eef0f2;
    --shadow: rgba(109, 14, 22, 0.05);
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }
  
  html.dark .category-tree-page {
    --line: #1f2937;
    --soft: #111827;
    --text: #f9fafb;
    --text-light: #9ca3af;
    --bg-light: #0f172a;
    --border: #1f2937;
    --shadow: rgba(0, 0, 0, 0.3);
  }
  
  .category-tree-page * {
    font-family: "Cairo", sans-serif !important;
  }
  
  .category-tree { position: relative; }
  .category-node { margin-bottom: 1.5rem; position: relative; }
  
  /* كرت الفئة الرئيسية */
  .category-card {
    background: var(--bg-light); border-radius: 20px;
    box-shadow: 0 4px 15px var(--shadow); transition: var(--transition);
    border: 1px solid var(--border); display: flex;
    align-items: center; position: relative; overflow: hidden;
  }
  .category-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 25px rgba(109, 14, 22, 0.12);
    border-color: rgba(109, 14, 22, 0.2);
  }
  html.dark .category-card { background: #111827; border-color: #1e293b; }
  
  .category-card::before {
    content: ''; position: absolute; right: 0; top: 0; bottom: 0; width: 5px;
    background: linear-gradient(180deg, var(--brand) 0%, #8b131a 100%);
  }
  html.dark .category-card::before { background: var(--brand); }
  
  .category-link {
    display: flex; align-items: center; padding: 1.25rem;
    flex: 1; text-decoration: none; color: inherit;
  }
  
  .category-icon {
    width: 64px; height: 64px; border-radius: 16px; overflow: hidden;
    flex-shrink: 0; margin-left: 1.25rem; background: #ffffff;
    display: flex; align-items: center; justify-content: center;
    border: 2px solid #ffffff; box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    transition: var(--transition);
  }
  .category-link:hover .category-icon {
    transform: scale(1.08) rotate(-2deg);
    box-shadow: 0 8px 15px rgba(109, 14, 22, 0.15);
  }
  html.dark .category-icon { background: #1e293b; border-color: #334155; }
  .icon-image { width: 100%; height: 100%; object-fit: cover; }
  .icon-placeholder { font-size: 1.5rem; color: var(--brand); }
  
  .category-name {
    margin: 0 0 0.5rem 0; font-size: 1.25rem; font-weight: 700;
    color: var(--text); line-height: 1.4;
  }
  
  .category-meta, .subcategory-meta, .sub-subcategory-meta {
    display: flex; flex-wrap: wrap; gap: 0.35rem;
  }
  
  .meta-item {
    display: inline-flex; align-items: center; gap: 0.35rem;
    height: 26px; padding: 0 0.75rem; background: var(--soft);
    border-radius: 999px; font-size: 0.8rem;
    color: var(--brand); font-weight: 700; border: 1px solid rgba(109, 14, 22, 0.1);
    transition: var(--transition);
  }
  .meta-item:hover { background: var(--brand); color: #ffffff; }
  
  .category-actions { padding: 0 1rem; }
  
  .expand-btn {
    width: 36px; height: 36px; border-radius: 50%; background: var(--soft);
    border: 1px solid var(--border); color: var(--brand);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: var(--transition);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  }
  html.dark .expand-btn { background: #1f2937; border-color: #374151; }
  .expand-btn:hover { background: var(--brand); border-color: var(--brand); color: #ffffff; transform: scale(1.1); }
  .expand-btn i { transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
  .rotate-180 { transform: rotate(180deg); }
  
  /* الفئات الفرعية (المستوى الثاني) */
  .subcategories { margin-top: 1rem; margin-right: 1.5rem; position: relative; }
  .subcategory-list { list-style: none; padding: 0; margin: 0; }
  .subcategory-node { margin-bottom: 1rem; position: relative; }
  
  .subcategory-card {
    background: var(--bg-light); border-radius: 16px;
    box-shadow: 0 4px 12px var(--shadow); transition: var(--transition);
    border: 1px solid var(--border); display: flex; align-items: center;
    position: relative;
  }
  html.dark .subcategory-card { background: #1e293b; border-color: #334155; }
  
  .subcategory-indicator {
    position: absolute; right: -1.25rem; top: -1.75rem;
    width: 2px; height: calc(100% + 1rem); background-color: var(--line);
  }
  .subcategory-indicator::after {
    content: ''; position: absolute; right: 0; top: 50%;
    width: 1.25rem; height: 2px; background-color: var(--line);
  }
  .subcategory-link { padding: 1rem; display: flex; align-items: center; flex: 1; text-decoration: none; color: inherit; }
  .subcategory-icon { 
    width: 54px; height: 54px; border-radius: 14px; flex-shrink: 0; 
    margin-left: 1rem; background: #ffffff; display: flex; 
    align-items: center; justify-content: center; border: 2px solid #ffffff; 
    box-shadow: 0 3px 8px rgba(0,0,0,0.06); transition: var(--transition);
  }
  .subcategory-link:hover .subcategory-icon { transform: scale(1.05); }
  html.dark .subcategory-icon { background: #1e293b; border-color: #334155; }
  .subcategory-name { font-size: 1.15rem; margin: 0 0 0.25rem 0; font-weight: 800; color: var(--text); line-height: 1.4; }
  .subcategory-actions { padding: 0 1rem; }
  
  /* البراندات (المستوى الثالث) */
  .sub-subcategories { margin-top: 0.75rem; margin-right: 2.5rem; }
  .sub-subcategory-list { list-style: none; padding: 0; margin: 0; }
  .sub-subcategory-node { margin-bottom: 0.75rem; }
  .sub-subcategory-card {
    background: var(--bg-light); border-radius: 12px; box-shadow: 0 3px 8px var(--shadow);
    border: 1px solid var(--border); display: flex; align-items: center; position: relative;
    transition: var(--transition);
  }
  .sub-subcategory-card:hover { border-color: var(--brand); transform: translateX(-5px); }
  html.dark .sub-subcategory-card { background: #334155; border-color: #475569; }
  .sub-subcategory-link { padding: 0.75rem; display: flex; align-items: center; flex: 1; text-decoration: none; color: inherit; }
  .sub-subcategory-icon { width: 40px; height: 40px; border-radius: 8px; flex-shrink: 0; margin-left: 0.5rem; background: var(--soft); display: flex; align-items: center; justify-content: center; border: 1px solid var(--line); }
  .sub-subcategory-name { font-size: 0.95rem; margin: 0; font-weight: 700; color: var(--text); line-height: 1.4; }
  
  .category-tree-page {
    background: linear-gradient(180deg, #ffffff 0%, #f7f7f7 100%);
    min-height: 100vh;
  }
  html.dark .category-tree-page {
    background: linear-gradient(180deg, #0a0a0a 0%, #0f172a 100%);
  }
</style>

@php
    // ✅ [تصحيح] هذا هو المنطق الجديد الذي يدمج البراندات داخل الفئات
    use App\Models\PrimaryCategory; // هذه هي "الفئات"
    use App\Models\Category;       // هذه هي "البراندات"
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Cache;

    $fiatTree = Cache::remember('unified_categories_brands_tree', now()->addHours(6), function () {
        // الخطوة 1: جلب كل الفئات الرئيسية وأبنائها (الفئات الفرعية)
        $fiat = PrimaryCategory::whereNull('parent_id')
            ->with(['children' => function ($query) {
                $query->orderBy('name_ar', 'asc');
            }])
            ->orderBy('name_ar', 'asc')
            ->get();

        // الخطوة 2: لكل فئة فرعية، ابحث عن البراندات المرتبطة بها
        foreach ($fiat as $fia) {
            if ($fia->children->isNotEmpty()) {
                foreach ($fia->children as $subFia) {
                    try {
                        // أ) ابحث في الجدول الوسيط عن كل المنتجات التابعة لهذه الفئة الفرعية
                        $product_ids = DB::table('primary_category_product')
                                         ->where('primary_category_id', $subFia->id)
                                         ->pluck('product_id');

                        // ب) من قائمة المنتجات هذه، استخرج أرقام البراندات بدون تكرار
                        $brand_ids = DB::table('products')
                                       ->whereIn('id', $product_ids)
                                       ->whereNotNull('category_id')
                                       ->distinct()
                                       ->pluck('category_id');
                        
                        // ج) اجلب معلومات البراندات وقم بإضافتها إلى الفئة الفرعية
                        if ($brand_ids->isNotEmpty()) {
                            $subFia->brands = Category::whereIn('id', $brand_ids)->orderBy('name_ar', 'asc')->get();
                        } else {
                            $subFia->brands = collect();
                        }
                    } catch (\Exception $e) {
                        // في حال حدوث أي خطأ، تأكد من أن القائمة فارغة لتجنب توقف الصفحة
                        $subFia->brands = collect();
                    }
                }
            }
        }
        return $fiat;
    });
@endphp

<div class="py-12 category-tree-page" dir="rtl">
    <div class="container mx-auto px-4">
        {{-- رأس الصفحة --}}
        <div class="mb-12 text-center max-w-3xl mx-auto">
             <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider"
                  style="background:rgba(109, 14, 22, 0.08);color:#6d0e16;border:1px solid rgba(109, 14, 22, 0.15)">
                <i class="bi bi-stars"></i> {{ __('pages.discover') }}
            </div>
            <h1 class="text-3xl md:text-5xl font-extrabold mt-4" style="color:#1a1a1a">
                <span class="dark:text-white">{{ __('pages.categories_browse') }}</span> <span style="color:#6d0e16">{{ __('pages.categories_and_brands') }}</span>
            </h1>
            <p class="mt-4 text-lg opacity-80" style="color:#4b5563">
                <span class="dark:text-gray-400">{{ __('pages.categories_intro') }}</span>
            </p>
        </div>
        
        <div class="max-w-4xl mx-auto category-tree">
            @forelse ($fiatTree as $fia)
                @php
                    $hasChildren = $fia->children && $fia->children->isNotEmpty();
                @endphp
                <div class="category-node" x-data="{ open: false }">
                    {{-- المستوى الأول: الفئة الرئيسية (مثل: العناية بالبشرة) --}}
                    <div class="category-card">
                        <a href="{{ route('shop', ['brand' => $fia->slug]) }}" class="category-link">
                            <div class="category-icon">
                                @php $img = $fia->image ?: $fia->icon; @endphp
                                @if($img) <img src="{{ asset('storage/' . $img) }}" alt="{{ $fia->name_translated }}" class="icon-image">
                                @else <div class="icon-placeholder">🏷️</div> @endif
                            </div>
                            <div class="category-details">
                                <h3 class="category-name">{{ $fia->name_translated }}</h3>
                                <div class="category-meta">
                                    <span class="meta-item"><i class="bi bi-grid-3x3-gap-fill"></i> {{ __('pages.main_category') }}</span>
                                </div>
                            </div>
                        </a>
                        @if($hasChildren)
                            <div class="category-actions">
                                <button class="expand-btn" @click="open = !open"><i class="bi bi-chevron-down" :class="{'rotate-180': open}"></i></button>
                            </div>
                        @endif
                    </div>
                    
                    {{-- المستوى الثاني: الفئات الفرعية (مثل: سيرومات، غسول) --}}
                    @if($hasChildren)
                        <div class="subcategories" x-show="open" x-collapse style="display: none;">
                            <ul class="subcategory-list">
                                @foreach($fia->children as $subFia)
                                    @php
                                        $hasBrands = $subFia->brands && $subFia->brands->isNotEmpty();
                                    @endphp
                                    <li class="subcategory-node" x-data="{ open: false }">
                                        <div class="subcategory-card">
                                            <div class="subcategory-indicator"></div>
                                            <a href="{{ route('shop', ['brand' => $subFia->slug]) }}" class="subcategory-link">
                                                <div class="subcategory-icon">
                                                    @if($subFia->image) <img src="{{ asset('storage/' . $subFia->image) }}" alt="{{ $subFia->name_translated }}" class="icon-image">
                                                    @else <div class="icon-placeholder">🏷️</div> @endif
                                                </div>
                                                <div class="subcategory-details">
                                                    <h4 class="subcategory-name">{{ $subFia->name_translated }}</h4>
                                                </div>
                                            </a>
                                            @if($hasBrands)
                                                <div class="subcategory-actions">
                                                    <button class="expand-btn" @click="open = !open"><i class="bi bi-chevron-down" :class="{'rotate-180': open}"></i></button>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        {{-- المستوى الثالث: البراندات (مثل: CeraVe, The Ordinary) --}}
                                        @if($hasBrands)
                                            <div class="sub-subcategories" x-show="open" x-collapse style="display: none;">
                                                <ul class="sub-subcategory-list">
                                                    @foreach($subFia->brands as $brand)
                                                        <li class="sub-subcategory-node">
                                                            <div class="sub-subcategory-card">
                                                                <a href="{{ route('shop', ['category' => $brand->slug]) }}" class="sub-subcategory-link">
                                                                    <div class="sub-subcategory-icon">
                                                                        @if($brand->image) <img src="{{ asset('storage/' . $brand->image) }}" alt="{{ $brand->name_translated }}" class="icon-image">
                                                                        @else <div class="icon-placeholder">🧴</div> @endif
                                                                    </div>
                                                                    <div class="sub-subcategory-details">
                                                                        <h5 class="sub-subcategory-name">{{ $brand->name_translated }}</h5>
                                                                    </div>
                                                                </a>
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-6" style="color:#7a6e6e">
                    {{ __('pages.no_categories') }}
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection