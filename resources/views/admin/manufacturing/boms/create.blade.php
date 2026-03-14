@extends('admin.layout')

@section('title', 'إضافة وصفة تصنيع')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">إضافة وصفة تصنيع</h4>
    <a href="{{ route('admin.manufacturing.boms.index') }}" class="btn btn-outline-secondary">عودة للوصفات</a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card shadow-sm">
    <div class="card-body">
        <form action="{{ route('admin.manufacturing.boms.store') }}" method="POST" id="bom-form">
            @csrf
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">المنتج <span class="text-danger">*</span></label>
                    <select name="product_id" class="form-select" required>
                        <option value="" disabled selected>اختر منتجًا...</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>{{ $product->name_ar }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">اسم المتغير</label>
                    <input type="text" name="variant_name" class="form-control" value="{{ old('variant_name') }}" placeholder="مثال: حجم 50 مل">
                </div>
                <div class="col-md-12">
                    <label class="form-label">ملاحظات</label>
                    <textarea name="notes" rows="3" class="form-control" placeholder="تفاصيل إضافية عن الوصفة">{{ old('notes') }}</textarea>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0">مكونات الوصفة</h5>
                <button type="button" class="btn btn-sm btn-primary" id="add-material-row"><i class="bi bi-plus"></i> إضافة مادة</button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="bom-items-table">
                    <thead class="table-light">
                        <tr>
                            <th>المادة الخام</th>
                            <th width="180">الكمية المطلوبة</th>
                            <th width="80" class="text-center">حذف</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $oldItems = old('items', []); @endphp
                        @forelse($oldItems as $index => $item)
                            <tr>
                                <td>
                                    <select name="items[{{ $index }}][material_id]" class="form-select" required>
                                        <option value="" disabled>اختر مادة...</option>
                                        @foreach($materials as $material)
                                            <option value="{{ $material->id }}" @selected($item['material_id'] == $material->id)>{{ $material->name }} ({{ $material->unit ?? 'وحدة' }})</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" min="0" step="0.001" name="items[{{ $index }}][quantity]" class="form-control" value="{{ $item['quantity'] ?? 0 }}" required>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-row">×</button>
                                </td>
                            </tr>
                        @empty
                            <tr class="placeholder">
                                <td colspan="3" class="text-center text-muted">أضف المواد المطلوبة للوصفة.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 d-flex justify-content-end gap-2">
                <a href="{{ route('admin.manufacturing.boms.index') }}" class="btn btn-light">إلغاء</a>
                <button type="submit" class="btn btn-primary">حفظ الوصفة</button>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.querySelector('#bom-items-table tbody');
    const addRowBtn = document.getElementById('add-material-row');
    let rowIndex = {{ count(old('items', [])) }};

    const renderPlaceholder = () => {
        if (!tableBody.querySelector('tr')) {
            const row = document.createElement('tr');
            row.classList.add('placeholder');
            row.innerHTML = '<td colspan="3" class="text-center text-muted">أضف المواد المطلوبة للوصفة.</td>';
            tableBody.appendChild(row);
        }
    };

    renderPlaceholder();

    addRowBtn.addEventListener('click', () => {
        if (tableBody.querySelector('.placeholder')) {
            tableBody.innerHTML = '';
        }

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <select name="items[${rowIndex}][material_id]" class="form-select" required>
                    <option value="" disabled selected>اختر مادة...</option>
                    @foreach($materials as $material)
                        <option value="{{ $material->id }}">{{ $material->name }} ({{ $material->unit ?? 'وحدة' }})</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" min="0" step="0.001" name="items[${rowIndex}][quantity]" class="form-control" value="1" required>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger remove-row">×</button>
            </td>`;
        tableBody.appendChild(row);
        rowIndex++;
    });

    tableBody.addEventListener('click', (event) => {
        if (event.target.classList.contains('remove-row')) {
            event.target.closest('tr').remove();
            if (!tableBody.querySelector('tr')) {
                renderPlaceholder();
            }
        }
    });
});
</script>
@endpush
@endsection
