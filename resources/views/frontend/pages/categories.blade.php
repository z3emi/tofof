@extends('layouts.app')

@section('title', 'تصفح الفئات والبراندات')

@push('styles')
<style>
  :root {
    --brand: #cd8985;
    --brand-dark: #be6661;
    --line: #eadbcd;
    --soft: #f9f5f1;
    --text: #34282C;
    --text-light: #7a6e6e;
    --bg-light: #fefefe;
    --border: #f0f0f0;
    --shadow: rgba(0, 0, 0, 0.08);
    --transition: all 0.3s ease;
  }
  
  html.dark {
    --line: #374151;
    --soft: #1f2937;
    --text: #e5e7eb;
    --text-light: #9ca3af;
    --bg-light: #111827;
    --border: #374151;
    --shadow: rgba(0, 0, 0, 0.2);
  }
  
  .category-tree-page * {
    font-family: "Cairo", sans-serif !important;
  }
  
  .category-tree { position: relative; }
  .category-node { margin-bottom: 1.5rem; position: relative; }
  
  /* كرت الفئة الرئيسية */
  .category-card {
    background: var(--bg-light); border-radius: 14px;
    box-shadow: 0 8px 18px var(--shadow); transition: var(--transition);
    border: 1px solid var(--border); display: flex;
    align-items: center; position: relative;
  }
  .category-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 22px rgba(0, 0, 0, 0.12);
  }
  html.dark .category-card { background: #1f2937; }
  
  .category-card::before {
    content: ''; position: absolute; right: 0; top: 0; bottom: 0; width: 6px;
    background: linear-gradient(180deg, var(--brand) 0%, #e8b8b6 100%);
    border-top-right-radius: 14px; border-bottom-right-radius: 14px;
  }
  html.dark .category-card::before { background: var(--brand); }
  
  .category-link {
    display: flex; align-items: center; padding: 1.25rem;
    flex: 1; text-decoration: none; color: inherit;
  }
  
  .category-icon {
    width: 60px; height: 60px; border-radius: 12px; overflow: hidden;
    flex-shrink: 0; margin-left: 1rem; background: var(--soft);
    display: flex; align-items: center; justify-content: center;
    border: 1px solid var(--line);
  }
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
    height: 28px; padding: 0 0.65rem; background: var(--soft);
    border-radius: 999px; font-size: 0.85rem;
    color: var(--brand-dark); font-weight: 600; border: 1px solid var(--line);
  }
  
  .category-actions { padding: 0 1rem; }
  
  .expand-btn {
    width: 38px; height: 38px; border-radius: 999px; background: var(--bg-light);
    border: 1px solid var(--line); color: var(--brand);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: var(--transition);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
  }
  html.dark .expand-btn { background: #374151; }
  .expand-btn:hover { background: #fff3f3; border-color: #f3c6c3; color: var(--brand-dark); }
  html.dark .expand-btn:hover { background: rgba(205, 137, 133, 0.1); }
  .expand-btn i { transition: transform 0.3s ease; }
  .rotate-180 { transform: rotate(180deg); }
  
  /* الفئات الفرعية (المستوى الثاني) */
  .subcategories { margin-top: 1rem; margin-right: 1.5rem; position: relative; }
  .subcategory-list { list-style: none; padding: 0; margin: 0; }
  .subcategory-node { margin-bottom: 1rem; position: relative; }
  
  .subcategory-card {
    background: var(--bg-light); border-radius: 12px;
    box-shadow: 0 6px 14px var(--shadow); transition: var(--transition);
    border: 1px solid var(--border); display: flex; align-items: center;
    position: relative;
  }
  html.dark .subcategory-card { background: #2d3748; }
  
  .subcategory-indicator {
    position: absolute; right: -1.5rem; top: 0;
    width: 2px; height: 50%; background-color: var(--line);
  }
  .subcategory-indicator::after {
    content: ''; position: absolute; right: 0; top: 100%;
    width: 0.75rem; height: 2px; background-color: var(--line);
  }
  .subcategory-link { padding: 1rem; display: flex; align-items: center; flex: 1; text-decoration: none; color: inherit; }
  .subcategory-icon { width: 50px; height: 50px; border-radius: 10px; flex-shrink: 0; margin-left: 0.75rem; background: var(--soft); display: flex; align-items: center; justify-content: center; border: 1px solid var(--line); }
  .subcategory-name { font-size: 1.1rem; margin: 0 0 0.25rem 0; font-weight: 700; color: var(--text); line-height: 1.4; }
  .subcategory-actions { padding: 0 0.75rem; }
  
  /* البراندات (المستوى الثالث) */
  .sub-subcategories { margin-top: 0.75rem; margin-right: 2.5rem; }
  .sub-subcategory-list { list-style: none; padding: 0; margin: 0; }
  .sub-subcategory-node { margin-bottom: 0.75rem; }
  .sub-subcategory-card {
    background: var(--bg-light); border-radius: 10px; box-shadow: 0 4px 10px var(--shadow);
    border: 1px solid var(--border); display: flex; align-items: center; position: relative;
  }
  html.dark .sub-subcategory-card { background: #374151; }
  .sub-subcategory-link { padding: 0.75rem; display: flex; align-items: center; flex: 1; text-decoration: none; color: inherit; }
  .sub-subcategory-icon { width: 40px; height: 40px; border-radius: 8px; flex-shrink: 0; margin-left: 0.5rem; background: var(--soft); display: flex; align-items: center; justify-content: center; border: 1px solid var(--line); }
  .sub-subcategory-name { font-size: 1rem; margin: 0; font-weight: 700; color: var(--text); line-height: 1.4; }
</style>
@endpush

@section('content')
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
             <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-bold"
                  style="background:#f9f5f1;color:#be6661;border:1px solid #eadbcd">
                <i class="bi bi-stars"></i> اكتشفي عالمنا
            </div>
            <h1 class="text-3xl md:text-4xl font-extrabold mt-3" style="color:#4a3f3f">
                تصفح حسب الفئات والبراندات
            </h1>
            <p class="mt-2" style="color:#7a6e6e">
                كل ما تبحثين عنه من منتجات الجمال، مُنظم حسب الفئة ليسهل عليكِ رحلة التسوق.
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
                                @if($img) <img src="{{ asset('storage/' . $img) }}" alt="{{ $fia->name_ar }}" class="icon-image">
                                @else <div class="icon-placeholder">🏷️</div> @endif
                            </div>
                            <div class="category-details">
                                <h3 class="category-name">{{ $fia->name_ar }}</h3>
                                <div class="category-meta">
                                    <span class="meta-item"><i class="bi bi-grid-3x3-gap-fill"></i> فئة رئيسية</span>
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
                                                    @if($subFia->image) <img src="{{ asset('storage/' . $subFia->image) }}" alt="{{ $subFia->name_ar }}" class="icon-image">
                                                    @else <div class="icon-placeholder">🏷️</div> @endif
                                                </div>
                                                <div class="subcategory-details">
                                                    <h4 class="subcategory-name">{{ $subFia->name_ar }}</h4>
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
                                                                        @if($brand->image) <img src="{{ asset('storage/' . $brand->image) }}" alt="{{ $brand->name_ar }}" class="icon-image">
                                                                        @else <div class="icon-placeholder">🧴</div> @endif
                                                                    </div>
                                                                    <div class="sub-subcategory-details">
                                                                        <h5 class="sub-subcategory-name">{{ $brand->name_ar }}</h5>
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
                    لا توجد فئات لعرضها حالياً.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection