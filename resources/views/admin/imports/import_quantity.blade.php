@extends('admin.layout')
@section('title', 'تحديث كميات المنتجات')

@section('content')
<div class="card shadow-sm">
    <div class="card-header">
        <h4 class="mb-0">تحديث كميات المنتجات عبر Excel</h4>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            يجب أن يحتوي ملف Excel على عمودين: الأول يحتوي على <strong>SKU</strong> والثاني يحتوي على <strong>الكمية الجديدة</strong>.
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('not_found_skus'))
            <div class="alert alert-warning">
                <h6>منتجات لم يتم العثور عليها:</h6>
                <ul>
                    @foreach(session('not_found_skus') as $sku)
                        <li>{{ $sku }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.products.import_quantity.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="form-label">ملف Excel</label>
                <input type="file" name="file" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">معاينة وتحديد الأعمدة</button>
        </form>
    </div>
</div>
@endsection
