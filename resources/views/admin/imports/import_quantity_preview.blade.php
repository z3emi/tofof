@extends('admin.layout')
@section('title', 'تعيين الأعمدة - تحديث كميات المنتجات')

@section('content')
<div class="container py-4">
    <h3 class="mb-4">📥 تعيين الأعمدة (تحديث الكميات)</h3>
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    
    <form method="POST" action="{{ route('admin.products.import_quantity.store') }}" id="importForm">
        @csrf
        <input type="hidden" name="path" value="{{ $path }}">
        <input type="hidden" name="sku_col" id="sku_col_hidden" value="">
        <input type="hidden" name="qty_col" id="qty_col_hidden" value="">

        <div class="alert alert-info mb-3">
            <i class="bi bi-info-circle me-2"></i>
            اختر العمود الخاص بـ <b>SKU (كود المنتج)</b> وعمود <b>الكمية (Quantity)</b> من القوائم المنسدلة في أعلى الجدول.
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center align-middle">
                <thead class="table-light">
                    <tr>
                        @foreach($headers as $index => $header)
                            <th>
                                <select class="form-select form-select-sm column-select" data-index="{{ $index }}">
                                    <option value="ignore">-- تجاهل --</option>
                                    <option value="sku">SKU (كود المنتج)</option>
                                    <option value="qty">الكمية (Quantity)</option>
                                </select>
                                <div class="mt-1 small text-muted">{{ $header ?: 'عمود #' . ($index+1) }}</div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        <tr>
                            @foreach($row as $cell)
                                <td>{{ $cell }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4 text-end">
            <button type="button" class="btn btn-success" onclick="submitForm()">
                <i class="bi bi-upload me-1"></i> استيراد وتحديث الكميات
            </button>
            <a href="{{ route('admin.products.import_quantity') }}" class="btn btn-secondary">إلغاء</a>
        </div>
    </form>
</div>

<script>
function submitForm() {
    let skuCol = null;
    let qtyCol = null;

    document.querySelectorAll('.column-select').forEach(select => {
        if (select.value === 'sku') {
            skuCol = select.getAttribute('data-index');
        } else if (select.value === 'qty') {
            qtyCol = select.getAttribute('data-index');
        }
    });

    if (skuCol === null || qtyCol === null) {
        alert('الرجاء تعيين كل من عمود كود المنتج (SKU) وعمود (الكمية) من القوائم المنسدلة للمتابعة.');
        return;
    }

    document.getElementById('sku_col_hidden').value = skuCol;
    document.getElementById('qty_col_hidden').value = qtyCol;

    document.getElementById('importForm').submit();
}
</script>
@endsection
