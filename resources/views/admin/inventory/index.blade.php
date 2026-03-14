@extends('admin.layout')

@section('title', 'إدارة المخزون')

@push('styles')
<style>
    :root{
        --brand:#cd8985; --brand-dark:#be6661; --muted:#6c757d; --soft:#f9f5f1;
    }
    /* بطاقات الملخص */
    .summary-card{background:#fff;border:1px solid #dee2e6;border-left:5px solid var(--brand);border-radius:.75rem;padding:1rem;text-align:center;margin-bottom:1rem;transition:.2s}
    .summary-card:hover{transform:translateY(-3px);box-shadow:0 6px 18px rgba(0,0,0,.07)}
    .summary-card .icon{font-size:2rem;color:var(--brand)}
    .summary-card h6{color:var(--muted);margin:.5rem 0;font-weight:600}
    .summary-card .value{font-size:1.35rem;font-weight:800;color:#343a40}

    /* رأس المنتج (يشبه فهرس المنتجات) */
    .product-head{
        background:var(--soft);
        border:1px solid #eee;
        border-right:4px solid var(--brand);
        border-radius:.75rem;
        padding:.75rem 1rem;
    }
    .product-head .thumb{
        width:64px;height:64px;border-radius:.5rem;object-fit:cover;border:1px solid #eee;
    }
    .chip{display:inline-flex;align-items:center;gap:.35rem;border-radius:999px;padding:.15rem .6rem;font-size:.8rem;font-weight:600;}
    .chip-sku{background:#fff;border:1px dashed #c9c9c9;color:#666}
    .chip-cat{background:#eadbcd;color:#5a4e47}
    .chip-status{background:#e9f7ef;color:#1e7e34}
    .chip-status.off{background:#f4f4f4;color:#999}
    
    .stock-badge{font-weight:700}
    .stock-high{background:#e8f5e9;color:#1b5e20}
    .stock-mid{background:#fff3cd;color:#664d03}
    .stock-low{background:#fde2e1;color:#a4161a}

    /* إصلاح اتجاه حقل الإدخال */
    .stock-input-wrapper {
        direction: rtl; /* اتجاه الحقل من اليمين لليسار */
    }
    .stock-input-wrapper input {
        direction: ltr; /* اتجاه الأرقام من اليسار لليمين */
        text-align: center;
    }

    /* صف ملخص القيم يمين الجدول */
    .totals-box{
        display:inline-flex;gap:.5rem;flex-wrap:wrap;
    }
    .totals-box .box{
        background:#fff;border:1px solid #eee;border-radius:.5rem;padding:.25rem .5rem;font-size:.85rem
    }
</style>
@endpush

@section('content')

@php
    // ألوان خلفية ناعمة بدوران
    $productColors = ['#fdf0f0','#e6f3f8','#e8f5e9','#fffde7','#f3e5f5','#e0f7fa'];
@endphp

<div class="card shadow-sm">
    <div class="card-header" style="background-color:#f9f5f1;border-bottom:2px solid var(--brand);">
        <h4 class="mb-0" style="color:var(--brand);">نظرة عامة على المخزون</h4>
    </div>
    <div class="card-body">

        {{-- الملخص العام --}}
        <div class="row">
            <div class="col-md-4">
                <div class="summary-card">
                    <div class="icon"><i class="bi bi-wallet2"></i></div>
                    <h6>القيمة الإجمالية للمخزون (حسب الكلفة)</h6>
                    <div class="value">{{ number_format($grandTotalValue, 0) }} د.ع</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="summary-card">
                    <div class="icon"><i class="bi bi-boxes"></i></div>
                    <h6>إجمالي عدد القطع المتوفرة</h6>
                    <div class="value">{{ $grandTotalQuantity }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="summary-card">
                    <div class="icon"><i class="bi bi-tags"></i></div>
                    <h6>عدد المنتجات في النظام</h6>
                    <div class="value">{{ $uniqueProductsCount }}</div>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <h5 class="mb-3">تحديث المخزون</h5>

        <form method="GET" class="mb-3">
            <div class="d-flex gap-2" style="max-width:420px;">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    class="form-control"
                    placeholder="ابحث باسم المنتج">
                <button class="btn btn-primary">بحث</button>

                @if(request('search'))
                    <a href="{{ url()->current() }}" class="btn btn-outline-secondary">مسح</a>
                @endif
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-borderless align-middle mb-0">
                <thead class="table-light text-end">
                    <tr class="border">
                        <th class="text-end pe-3" style="width: 40%">المنتج</th>
                        <th class="text-center">التكلفة (الوحدة)</th>
                        <th class="text-center">القيمة الإجمالية</th>
                        <th class="text-center" style="width:220px">الكمية المتبقية (تعديل)</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($products as $index => $product)
                    @php
                        $totalQty = (int)$product->stock_quantity;
                        $costPrice = $product->price;
                        $totalValue = $totalQty * $costPrice;
                        $color = $productColors[$index % count($productColors)];

                        // تلوين حالة المخزون
                        $stockClass = $totalQty > 10 ? 'stock-high' : ($totalQty > 0 ? 'stock-mid' : 'stock-low');
                    @endphp

                    <tr class="border-top">
                        <td class="product-head text-end" style="background: {{ $color }};">
                            <div class="d-flex align-items-center gap-3">
                                @if ($product->firstImage)
                                    <img class="thumb" src="{{ asset('storage/' . $product->firstImage->image_path) }}" alt="{{ $product->name_ar }}">
                                @else
                                    <img class="thumb" src="https://placehold.co/64x64?text=No+Image" alt="No Image">
                                @endif

                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                        <h6 class="mb-0 fw-bold">{{ $product->name_ar ?? $product->name }}</h6>
                                        @if($product->sku)
                                            <span class="chip chip-sku">SKU: {{ $product->sku }}</span>
                                        @endif
                                        @if($product->category?->name_ar)
                                            <span class="chip chip-cat"><i class="bi bi-tag"></i> {{ $product->category->name_ar }}</span>
                                        @endif
                                        <span class="chip chip-status {{ $product->is_active ? '' : 'off' }}">
                                            <i class="bi {{ $product->is_active ? 'bi-check-circle' : 'bi-x-circle' }}"></i>
                                            {{ $product->is_active ? 'فعال' : 'غير فعال' }}
                                        </span>
                                    </div>

                                    <div class="text-muted small">
                                        سعر البيع الحالي:
                                        <strong>{{ number_format($product->sale_price ?: $product->price, 0) }} د.ع</strong>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="text-center">
                            <strong>{{ number_format($costPrice, 0) }} د.ع</strong>
                        </td>

                        <td class="text-center">
                            <div class="totals-box justify-content-center">
                                <span class="box">المجموع: <strong>{{ number_format($totalValue, 0) }} د.ع</strong></span>
                            </div>
                        </td>

                        <td class="text-center">
                            <form method="POST" action="{{ route('admin.inventory.updateStock', $product->id) }}" class="d-flex align-items-center justify-content-center gap-2 m-0 p-0">
                                @csrf
                                <div class="position-relative stock-input-wrapper">
                                    <input type="number" name="stock_quantity" value="{{ $totalQty }}" class="form-control fw-bold pe-4" style="width: 100px;" min="0">
                                    <span class="position-absolute translate-middle-y end-0 top-50 me-1 p-1 rounded rounded-circle d-flex align-items-center justify-content-center {{ $stockClass }}" style="width: 24px; height: 24px; font-size: 0.75rem;">
                                        <i class="bi bi-box-seam"></i>
                                    </span>
                                </div>
                                <button type="submit" class="btn btn-sm btn-primary py-2 px-3">
                                    حفظ
                                </button>
                            </form>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="4" class="text-center p-4">لا توجد منتجات لعرضها.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $products->links() }}
        </div>
        @endif

    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // منع تغيير الأرقام عند استخدام عجلة الماوس داخل حقول الإدخال
        const numberInputs = document.querySelectorAll('input[type="number"]');
        numberInputs.forEach(input => {
            input.addEventListener('wheel', function(e) {
                e.preventDefault();
            }, { passive: false });
        });
    });
</script>
@endpush