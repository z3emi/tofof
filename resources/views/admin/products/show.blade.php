@extends('admin.layout')

@section('title', 'تفاصيل المنتج #' . $product->id)

@push('styles')
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem; color: white; border-radius: 0 !important; }
    .panel {
        background-color: #ffffff;
        border-radius: 14px;
        border: 1px solid #edf2f7;
        box-shadow: 0 8px 22px rgba(15,23,42,0.05);
        padding: 1.35rem;
        margin-bottom: 1.5rem;
    }
    .panel-header {
        font-weight: bold;
        margin-bottom: 1rem;
        padding-bottom: 0.9rem;
        border-bottom: 1px solid #eef2f7;
        color: var(--primary-dark);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .75rem;
    }
    .info-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: .65rem .8rem;
    }
    .desc-block {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #f8fafc;
        padding: .9rem 1rem;
    }
    .desc-title {
        font-weight: 700;
        color: #334155;
        margin-bottom: .5rem;
    }
    .rich-description {
        color: #0f172a;
        line-height: 1.8;
        word-break: break-word;
    }
    .rich-description p:last-child { margin-bottom: 0; }
    .rich-description ul,
    .rich-description ol { padding-inline-start: 1.2rem; }
    .thumb {
        width: 90px;
        height: 90px;
        border-radius: 12px;
        object-fit: cover;
        border: 1px solid #e2e8f0;
        background: #fff;
        cursor: zoom-in;
        transition: transform .2s ease;
    }
    .thumb:hover { transform: scale(1.04); }
    .status-badge { border-radius: 8px; padding: 0.4rem 0.8rem; font-weight: 700; font-size: 0.8rem; color: #fff; }
    .bg-active { background: #198754; }
    .bg-inactive { background: #6c757d; }
    .price-chip {
        display: inline-block;
        border-radius: 10px;
        border: 1px solid #dbeafe;
        background: #eff6ff;
        color: #1e3a8a;
        font-weight: 700;
        padding: .35rem .65rem;
    }
    @media (max-width: 992px) {
        .info-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="form-card">
    <div class="form-card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2 class="mb-2 fw-bold text-white"><i class="bi bi-box-seam me-2"></i> تفاصيل المنتج #{{ $product->id }}</h2>
            <p class="mb-0 opacity-75 fs-6 text-white small">عرض بيانات المنتج الكاملة، الفئات، الصور، الخيارات والمتغيرات.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-light">
                <i class="bi bi-arrow-right-short"></i> رجوع
            </a>
            @can('edit-products')
            <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-outline-light">
                <i class="bi bi-pencil-fill me-1"></i> تعديل
            </a>
            @endcan
        </div>
    </div>

    <div class="p-4 p-lg-5">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="panel">
                    <div class="panel-header">
                        <span><i class="bi bi-card-text me-1"></i> بيانات أساسية</span>
                    </div>
                    <div class="info-grid">
                        <div class="info-item"><strong>ID:</strong> {{ $product->id }}</div>
                        <div class="info-item"><strong>SKU:</strong> {{ $product->sku ?: '—' }}</div>
                        <div class="info-item"><strong>الحالة:</strong>
                            @if($product->is_active)
                                <span class="status-badge bg-active">فعال</span>
                            @else
                                <span class="status-badge bg-inactive">غير فعال</span>
                            @endif
                        </div>
                        <div class="info-item"><strong>المخزون:</strong> {{ (int) ($product->available_quantity ?? 0) }}</div>
                        <div class="info-item"><strong>اسم البراند:</strong> {{ $product->category?->name_ar ?: '—' }}</div>
                        <div class="info-item"><strong>السعر:</strong> <span class="price-chip">{{ number_format($product->price, 0) }} د.ع</span></div>
                        <div class="info-item"><strong>سعر الخصم:</strong> {{ $product->sale_price ? number_format($product->sale_price, 0) . ' د.ع' : '—' }}</div>
                        <div class="info-item"><strong>الإضافة:</strong> {{ optional($product->created_at)->format('Y-m-d H:i') }}</div>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-header">
                        <span><i class="bi bi-diagram-3 me-1"></i> الفئات الرئيسية</span>
                    </div>
                    @if($product->primaryCategories->isNotEmpty())
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($product->primaryCategories as $pc)
                                <span class="badge bg-light text-dark border">{{ $pc->name_ar }}@if($pc->parent) / {{ $pc->parent->name_ar }}@endif</span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">لا توجد فئات رئيسية مرتبطة.</p>
                    @endif
                </div>
            </div>

            <div class="col-lg-8">
                <div class="panel">
                    <div class="panel-header">
                        <span><i class="bi bi-images me-1"></i> صور المنتج</span>
                    </div>
                    @if($product->images->isNotEmpty())
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($product->images as $img)
                                <button type="button"
                                        class="btn p-0 border-0 bg-transparent product-image-trigger"
                                        data-image-url="{{ asset('storage/' . $img->image_path) }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#productImageModal"
                                        title="عرض الصورة">
                                    <img src="{{ asset('storage/' . $img->image_path) }}" alt="Product image" class="thumb">
                                </button>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">لا توجد صور لهذا المنتج.</p>
                    @endif
                </div>

                <div class="panel">
                    <div class="panel-header">
                        <span><i class="bi bi-type me-1"></i> الاسم والوصف</span>
                    </div>
                    <div class="mb-3 desc-block">
                        <div class="fw-bold">الاسم العربي</div>
                        <div>{{ $product->name_ar ?: '—' }}</div>
                    </div>
                    <div class="mb-3 desc-block">
                        <div class="fw-bold">الاسم الإنجليزي</div>
                        <div>{{ $product->name_en ?: '—' }}</div>
                    </div>
                    <div class="mb-3 desc-block">
                        <div class="desc-title">الوصف العربي</div>
                        <div class="rich-description">
                            @if(!empty($product->description_ar))
                                {!! $product->description_ar !!}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </div>
                    </div>
                    <div class="desc-block">
                        <div class="desc-title">الوصف الإنجليزي</div>
                        <div class="rich-description">
                            @if(!empty($product->description_en))
                                {!! $product->description_en !!}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-header">
                        <span><i class="bi bi-sliders me-1"></i> الخيارات والمتغيرات</span>
                    </div>
                    @if($product->options->isNotEmpty())
                        <div class="d-flex flex-column gap-3">
                            @foreach($product->options as $opt)
                                <div class="border rounded-3 p-3 bg-light">
                                    <div class="fw-bold mb-2">{{ $opt->name_ar ?: ($opt->name_en ?: 'Option') }}</div>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($opt->values as $val)
                                            <span class="badge bg-white text-dark border">{{ $val->value_ar ?: ($val->value_en ?: 'Value') }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">لا توجد خيارات مسجلة لهذا المنتج.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="productImageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-dark border-0">
            <div class="modal-header border-0">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 text-center">
                <img id="productImagePreview" src="" alt="Product image preview" style="max-width:100%; max-height:80vh; object-fit:contain;">
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const preview = document.getElementById('productImagePreview');

    document.querySelectorAll('.product-image-trigger').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const imageUrl = btn.dataset.imageUrl || '';
            if (preview) preview.src = imageUrl;
        });
    });
});
</script>
@endpush
