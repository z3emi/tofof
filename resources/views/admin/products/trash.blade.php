@extends('admin.layout')
@section('title', 'سلة محذوفات المنتجات')

@push('styles')
<style>
    :root{ --brand:#cd8985; --line:#eadbcd; }
    .card-header{ background:#fff; border-bottom:1px solid var(--line); }
    .prod-cell .thumb{ width:44px;height:44px;border-radius:10px;object-fit:cover;border:1px solid #eee;background:#fff; }
    .prod-cell .title{ font-weight:700;margin:0;color:#333;font-size:.95rem; }
    .chip{
        display:inline-flex;align-items:center;gap:.35rem;
        background:#fff;border:1px dashed #dcdcdc;color:#666;
        border-radius:999px;padding:.05rem .45rem;font-size:.74rem;font-weight:600;
    }
</style>
@endpush

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h4 class="mb-0 text-danger"><i class="bi bi-trash"></i> سلة محذوفات المنتجات</h4>
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-right me-1"></i> العودة للمنتجات
        </a>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>المنتج</th>
                        <th>السعر</th>
                        <th>تاريخ الحذف</th>
                        <th>العمليات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td class="fw-bold">{{ $product->id }}</td>
                            <td class="text-start prod-cell">
                                <div class="d-flex align-items-center gap-3">
                                    @if ($product->firstImage)
                                        <img src="{{ asset('storage/' . $product->firstImage->image_path) }}" alt="{{ $product->name_ar ?? $product->name }}" class="thumb">
                                    @else
                                        <img src="https://placehold.co/44x44?text=No+Img" alt="No Image" class="thumb">
                                    @endif
                                    <div class="flex-grow-1">
                                        <p class="title mb-1">{{ $product->name_ar ?? $product->name }}</p>
                                        <span class="chip">SKU: {{ $product->sku ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="fw-semibold">{{ number_format($product->price, 0) }} د.ع</td>
                            <td>{{ $product->deleted_at->format('Y-m-d H:i') }}</td>
                            <td>
                                @can('edit-products')
                                <form action="{{ route('admin.products.restore', $product->id) }}" method="POST" class="d-inline-block">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success m-1" title="استرجاع">
                                        <i class="bi bi-arrow-counterclockwise"></i> استرجاع
                                    </button>
                                </form>
                                @endcan

                                @can('edit-products')
                                <form action="{{ route('admin.products.forceDelete', $product->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('تنبيه: سيتم مسح المنتج نهائياً ولا يمكن التراجع. هل أنت متأكد؟')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger m-1" title="حذف نهائي">
                                        <i class="bi bi-trash-fill"></i> حذف نهائي
                                    </button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-4 text-muted">سلة المحذوفات فارغة.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $products->links() }}
        </div>
    </div>
</div>
@endsection
