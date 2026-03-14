@extends('admin.layout')

@section('title', 'إدارة المنتجات')

@push('styles')
<style>
    :root{
        --brand:#cd8985; --brand-dark:#be6661; --muted:#6c757d; --soft:#f9f5f1; --line:#eadbcd;
    }

    /* هيدر البطاقة – أبيض */
    .card-header{
        background:#fff;
        border-bottom:1px solid var(--line);
    }

    /* شريط الفلاتر – أبيض ومضغوط */
    .filters-bar{
        background:#fff;
        border:1px solid var(--line);
        border-radius:.65rem;
        padding:10px;
        box-shadow:0 .25rem .5rem rgba(0,0,0,.03);
        margin-bottom:1rem;
    }
    .filters-bar .form-control,
    .filters-bar .form-select{
        height: 38px;
        padding: .3rem .6rem;
        font-size:.92rem;
    }
    .filters-bar .btn{ padding:.35rem .75rem; }

    /* جدول المنتجات – بدون min-width + تصغير عام */
    .products-table{
        width:100%;
        table-layout:auto;       /* يوزّع العرض حسب الحاجة */
        border-radius:.5rem;
        overflow:hidden;
    }
    .products-table thead th{
        background:#fff;
        position: sticky;
        top: 0;
        z-index: 1;
        border-bottom: 1px solid var(--line) !important;
        padding:.5rem .5rem;
        white-space:nowrap;
    }
    .products-table tbody td{
        padding:.5rem .5rem;
        vertical-align:middle;
    }
    .products-table tbody tr{
        transition: background .15s ease, box-shadow .15s ease;
    }
    .products-table tbody tr:hover{
        background:#fffaf8;
        box-shadow: 0 4px 12px rgba(0,0,0,.03);
    }

    /* خلية المنتج – أصغر */
    .prod-cell .thumb{
        width:44px;height:44px;border-radius:10px;object-fit:cover;border:1px solid #eee;background:#fff;
    }
    .prod-cell .title{
        font-weight:700;margin:0;color:#333;font-size:.95rem;
    }
    .chip{
        display:inline-flex;align-items:center;gap:.35rem;
        background:#fff;border:1px dashed #dcdcdc;color:#666;
        border-radius:999px;padding:.05rem .45rem;font-size:.74rem;font-weight:600;
    }
    .chip-cat{ background:#eadbcd;border-color:#eadbcd;color:#5a4e47 }

    /* التقييم – أصغر شوي */
    .rating .bi { font-size: .92rem; margin-left: 1px; }
    .rating small { color:#6c757d; font-size:.82rem; }

    /* الكمية */
    .qty-badge{ font-weight:700; padding:.3rem .5rem; border-radius:999px; font-size:.9rem }
    .qty-high{ background:#e8f5e9; color:#1b5e20 }
    .qty-mid{ background:#fff3cd; color:#664d03 }
    .qty-low{ background:#fde2e1; color:#a4161a }

    /* الحالة */
    .status-badge{ border-radius:999px; padding:.3rem .65rem; font-weight:700; font-size:.88rem }

    /* عمود العمليات يبقى كامل */
    .col-actions{ width:150px; white-space:nowrap; }

    /* أزرار العمليات – مريحة وواضحة */
    .btn-icon{
        display:inline-flex; align-items:center; justify-content:center;
        width:34px; height:34px; border-radius:8px; padding:0;
    }

    /* تقليل احتمالات لفّ النص داخل المنتج */
    .prod-meta{ gap:.35rem }
    /* إصلاح تداخل سهم الـ select مع النص في RTL */
    html[dir="rtl"] .filters-bar .form-select{
        padding-right: .6rem;       /* مساحة للنص يميناً */
        padding-left: 2.25rem;      /* نوسّع يساراً للسهم */
        background-position: left .65rem center; /* مكان السهم */
        background-size: 12px 12px;  /* حجم السهم */
    }

    /* للي عنده واجهة LTR – فقط للاتساق */
    html[dir="ltr"] .filters-bar .form-select{
        padding-left: .6rem;
        padding-right: 2.25rem;
        background-position: right .65rem center;
        background-size: 12px 12px;
    }
    /* فئة ثانية – نفس شكل البراند لكن بلون مختلف */
    .chip-pc{
        background: var(--pc-bg);
        border-color: var(--pc-bg);
        color: var(--pc-text);
    }
    .chip-pc i{ opacity:.9; }

    :root{
        --brand:#cd8985; --brand-dark:#be6661; --muted:#6c757d; --soft:#f9f5f1; --line:#eadbcd;

        /* ألوان الفئة الثانية (غير البراند) */
        --pc-bg: #e6efff;     /* خلفية فاتحة مزرّقة */
        --pc-text: #2b5ca7;   /* نص أزرق واضح */
    }
</style>

{{-- ⭐⭐⭐ كود التنسيق الجديد لترقيم الصفحات ⭐⭐⭐ --}}
<style>
    .pagination .page-item .page-link {
        color: var(--brand-dark);
        border-radius: 0.5rem;
        margin: 0 2px;
        border-color: var(--line);
        transition: all 0.2s ease-in-out;
    }
    .pagination .page-item .page-link:hover {
        background-color: var(--soft);
        border-color: var(--brand);
        color: var(--brand-dark);
    }
    .pagination .page-item.active .page-link {
        background-color: var(--brand);
        border-color: var(--brand);
        color: #fff;
        box-shadow: 0 4px 10px rgba(205, 137, 133, 0.4);
    }
    .pagination .page-item.disabled .page-link {
        color: #adb5bd;
        background-color: #f8f9fa;
        border-color: #dee2e6;
    }
</style>
@endpush

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h4 class="mb-0">جميع المنتجات</h4>

        <div class="d-flex gap-2">
            @can('view-products')
            <a href="{{ route('admin.products.trash') }}" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-trash me-1"></i> سلة المحذوفات
            </a>
            @endcan
            @can('create-products')
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i> إضافة منتج جديد
            </a>
            @endcan
        </div>
    </div>

    <div class="card-body">
        {{-- فلاتر البحث – خلفية بيضاء --}}
        <form method="GET" action="{{ route('admin.products.index') }}" class="row g-2 filters-bar">
            <div class="col">
                <input type="text" name="search" class="form-control" placeholder="ابحث بالاسم أو SKU" value="{{ request('search') }}">
            </div>
            <div class="col-auto">
                <select name="category_id" class="form-select">
                    <option value="">كل البراندات</option>
                    @foreach(\App\Models\Category::orderBy('name_ar')->get() as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name_ar }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="pc" class="form-select">
                    <option value="">كل الفئات </option>
                    @foreach(($primaryCategories ?? collect()) as $pc)
                        <option value="{{ $pc->id }}" {{ request('pc') == $pc->id ? 'selected' : '' }}>
                            {{ optional($pc->parent)->name_ar ? ($pc->parent->name_ar.' › '.$pc->name_ar) : $pc->name_ar }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="status" class="form-select">
                    <option value="">كل الحالات</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>فعال</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير فعال</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search me-1"></i> بحث
                </button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle products-table">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>المنتج</th>
                        <th>التقييم</th>
                        <th>سعر البيع</th>
                        <th>سعر الخصم</th>
                        <th>الكمية</th>
                        <th>الحالة</th>
                        <th class="col-actions">العمليات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        @php
                            $avg   = isset($product->reviews_avg_rating) ? round($product->reviews_avg_rating, 1) : round(($product->reviews()->avg('rating') ?? 0), 1);
                            $count = isset($product->reviews_count) ? (int)$product->reviews_count : (int)$product->reviews()->count();

                            $qtyAgg = $product->stock_sum_quantity_remaining ?? null;
                            $qty    = !is_null($qtyAgg) ? (int)$qtyAgg : (int)($product->available_quantity ?? 0);

                            $qtyClass = $qty > 10 ? 'qty-high' : ($qty > 0 ? 'qty-mid' : 'qty-low');
                        @endphp

                        <tr @class(['opacity-50' => !$product->is_active])>
                            <td class="fw-bold">{{ $product->id }}</td>

                            {{-- المنتج --}}
                            <td class="text-start prod-cell">
                                <div class="d-flex align-items-center gap-3">
                                    @if ($product->firstImage)
                                        <img src="{{ asset('storage/' . $product->firstImage->image_path) }}" alt="{{ $product->name_ar ?? $product->name }}" class="thumb">
                                    @else
                                        <img src="https://placehold.co/44x44?text=No+Img" alt="No Image" class="thumb">
                                    @endif

                                    <div class="flex-grow-1">
                                        <p class="title mb-1">{{ $product->name_ar ?? $product->name }}</p>
                                        <div class="d-flex align-items-center prod-meta flex-wrap">
                                            <span class="chip">SKU: {{ $product->sku ?? 'N/A' }}</span>
                                            @if(optional($product->category)->name_ar)
                                                <span class="chip chip-cat"><i class="bi bi-tag"></i> {{ $product->category->name_ar }}</span>
                                            @endif
                                            @php
                                                $pc = ($product->primaryCategories && $product->primaryCategories->isNotEmpty())
                                                    ? $product->primaryCategories->first()
                                                    : null;
                                            @endphp
                                            @if($pc)
                                                <span class="chip chip-pc ms-1">
                                                    <i class="bi bi-collection"></i>
                                                    {{ optional($pc->parent)->name_ar ? ($pc->parent->name_ar.' › '.$pc->name_ar) : $pc->name_ar }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- التقييم --}}
                            <td>
                                @if($count > 0)
                                    <div class="d-flex flex-column align-items-center">
                                        <div class="rating d-flex align-items-center">
                                            @for ($i = 1; $i <= 5; $i++)
                                                @if ($avg >= $i)
                                                    <i class="bi bi-star-fill text-warning"></i>
                                                @elseif ($avg >= ($i - 0.5))
                                                    <i class="bi bi-star-half text-warning"></i>
                                                @else
                                                    <i class="bi bi-star text-muted"></i>
                                                @endif
                                            @endfor
                                        </div>
                                        <small class="mt-1">{{ $avg }} من 5 ({{ $count }})</small>
                                    </div>
                                @else
                                    <span class="text-muted">لا تقييمات</span>
                                @endif
                            </td>

                            {{-- السعر --}}
                            <td class="fw-semibold">{{ number_format($product->price, 0) }} د.ع</td>

                            {{-- الخصم --}}
                            <td>
                                @if($product->sale_price)
                                    <span class="text-success fw-bold">{{ number_format($product->sale_price, 0) }} د.ع</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- الكمية --}}
                            <td>
                                <div class="d-flex align-items-center justify-content-center gap-1">
                                    <span class="qty-badge {{ $qtyClass }} stock-display-{{ $product->id }}">{{ $qty }}</span>
                                    @can('edit-products')
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-secondary btn-icon border-0" 
                                            onclick="editStock({{ $product->id }}, {{ $qty }})"
                                            title="تحديث الكمية">
                                        <i class="bi bi-pencil-square" style="font-size: 0.8rem;"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>

                            {{-- الحالة --}}
                            <td>
                                @if($product->is_active)
                                    <span class="badge status-badge bg-success">فعال</span>
                                @else
                                    <span class="badge status-badge bg-secondary">غير فعال</span>
                                @endif
                            </td>

                            {{-- العمليات (تبقى كاملة) --}}
                            <td class="col-actions">
                                @can('edit-products')
                                <a href="{{ route('admin.products.edit', $product->id) }}"
                                   class="btn btn-sm btn-outline-primary btn-icon m-1"
                                   data-bs-toggle="tooltip" title="تعديل">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <form action="{{ route('admin.products.toggleStatus', $product->id) }}" method="POST" class="d-inline-block">
                                    @csrf
                                    <button type="submit"
                                            class="btn btn-sm {{ $product->is_active ? 'btn-outline-warning' : 'btn-outline-success' }} btn-icon m-1"
                                            data-bs-toggle="tooltip"
                                            title="{{ $product->is_active ? 'إيقاف' : 'تفعيل' }}">
                                        <i class="bi {{ $product->is_active ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                    </button>
                                </form>
                                @endcan

                                @can('delete-products')
                                <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('هل أنت متأكد؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger btn-icon m-1"
                                            data-bs-toggle="tooltip" title="حذف">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8">لا توجد منتجات لعرضها.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- التحكم بعدد العناصر + التصفح --}}
        <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
            <form method="GET" action="{{ route('admin.products.index') }}" class="d-flex align-items-center">
                @foreach(request()->except(['per_page', 'page']) as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <label for="per_page" class="me-2">عدد المنتجات:</label>
                <select name="per_page" id="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach([5, 10, 25, 50, 100] as $size)
                        <option value="{{ $size }}" {{ request('per_page', 5) == $size ? 'selected' : '' }}>{{ $size }}</option>
                    @endforeach
                </select>
            </form>

            <div>
                {{ $products->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>

{{-- Stock Update Modal --}}
@can('edit-products')
<div class="modal fade" id="stockUpdateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content" style="border-radius: 15px;">
            <div class="modal-body p-4 text-center">
                <h6 class="mb-3 fw-bold">تحديث الكمية</h6>
                <meta name="csrf-token" content="{{ csrf_token() }}">
                <input type="number" id="stock_input" class="form-control text-center mb-3" min="0" step="1">
                <input type="hidden" id="stock_product_id">
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-sm btn-brand px-4" onclick="submitStock()">حفظ</button>
                    <button type="button" class="btn btn-sm btn-light border px-4" data-bs-dismiss="modal">إلغاء</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endcan
@endsection

@push('scripts')
<script>
    // تفعيل Tooltips لأيقونات العمليات
    document.addEventListener('DOMContentLoaded', function(){
        const triggers = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        triggers.forEach(function (el) { new bootstrap.Tooltip(el); });
    });

    @can('edit-products')
    const stockModal = new bootstrap.Modal(document.getElementById('stockUpdateModal'));
    const stockInput = document.getElementById('stock_input');
    const stockProductId = document.getElementById('stock_product_id');

    function editStock(id, currentQty) {
        stockProductId.value = id;
        stockInput.value = currentQty;
        stockModal.show();
        setTimeout(() => stockInput.focus(), 500);
    }

    function submitStock() {
        const id = stockProductId.value;
        const qty = stockInput.value;
        
        if (qty === '' || qty < 0) {
            alert('يرجى إدخال كمية صحيحة');
            return;
        }

        const url = `{{ route('admin.products.updateStock', ':id') }}`.replace(':id', id);
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ stock_quantity: qty })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update display
                const badge = document.querySelector(`.stock-display-${id}`);
                if (badge) {
                    badge.innerText = data.stock_quantity;
                    // Update class based on thresholds
                    badge.classList.remove('qty-high', 'qty-mid', 'qty-low');
                    const q = parseInt(data.stock_quantity);
                    if (q > 10) badge.classList.add('qty-high');
                    else if (q > 0) badge.classList.add('qty-mid');
                    else badge.classList.add('qty-low');
                }
                stockModal.hide();
                // Optionally show a toast/small notification
            } else {
                alert('حدث خطأ أثناء التحديث');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في الاتصال بالسيرفر');
        });
    }
    @endcan
</script>
@endpush