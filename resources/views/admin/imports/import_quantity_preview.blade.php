@extends('admin.layout')
@section('title', 'تعيين الأعمدة - تحديث كميات المنتجات')

@push('styles')
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem; color: white; border-radius: 0 !important; }
    .table-container { border-radius: 15px; border: 1px solid #f1f5f9; overflow: hidden; background: #fff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    .section-title { font-size: 1.1rem; font-weight: 700; color: var(--primary-dark); margin-bottom: 1rem; display: flex; align-items: center; gap: 10px; }
    .section-title::after { content: ''; flex-grow: 1; height: 2px; background: #f1f5f9; }

    .import-alert { border-radius: .75rem; border: 1px solid #e2e8f0; }
    .table thead th { white-space: nowrap; }
    .column-select {
        width: 100%;
        min-width: 0;
        border-radius: .5rem;
        font-size: .8rem;
        border-color: #d7dfea;
    }

    @media (max-width: 991.98px) {
        .form-card-header { padding: 1.4rem 1rem; }
        .form-card-header h2 { font-size: 1.15rem; }
        .table { font-size: .8rem; }
        .column-select { font-size: .74rem; }
    }
</style>
@endpush

@section('content')
<div class="form-card">
    <div class="form-card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2 class="mb-2 fw-bold text-white"><i class="bi bi-file-earmark-spreadsheet me-2"></i> تعيين الأعمدة (تحديث الكميات)</h2>
            <p class="mb-0 opacity-75 fs-6 text-white small">اختيار أعمدة SKU والكمية من الملف قبل تنفيذ التحديث الفعلي.</p>
        </div>
    </div>

    <div class="p-4 p-lg-5">
    @if(session('error'))
        <div class="alert alert-danger import-alert">{{ session('error') }}</div>
    @endif
    
    <form method="POST" action="{{ route('admin.products.import_quantity.store') }}" id="importForm">
        @csrf
        <input type="hidden" name="path" value="{{ $path }}">
        <input type="hidden" name="sku_col" id="sku_col_hidden" value="">
        <input type="hidden" name="qty_col" id="qty_col_hidden" value="">

        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" role="switch" id="ignoreHeader" name="ignore_header" checked>
            <label class="form-check-label" for="ignoreHeader">تجاهل أول صف (Header)</label>
        </div>

        <div class="alert alert-info import-alert mb-4">
            <i class="bi bi-info-circle me-2"></i>
            اختر العمود الخاص بـ <b>SKU (كود المنتج)</b> وعمود <b>الكمية (Quantity)</b> من القوائم المنسدلة في أعلى الجدول.
        </div>

        <div class="section-title text-muted mb-4">
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2">معاينة الملف</span>
            <span class="small fw-bold">{{ count($headers) }} عمود</span>
        </div>

        <div class="table-container shadow-sm border overflow-hidden">
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
        </div>

        <div class="mt-4 d-flex justify-content-end gap-2 flex-wrap">
            <button type="button" class="btn btn-success rounded-3 px-4 py-2 fw-bold" onclick="submitForm()">
                <i class="bi bi-upload me-1"></i> استيراد وتحديث الكميات
            </button>
            <a href="{{ route('admin.products.import_quantity') }}" class="btn btn-outline-secondary rounded-3 px-4 py-2 fw-bold">إلغاء</a>
        </div>
    </form>
</div>
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
