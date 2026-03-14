@extends('admin.layout')

@section('title', 'تعديل وصفة تصنيع')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">تعديل وصفة {{ $bom->product?->name_ar ?? '' }}</h4>
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
        <form action="{{ route('admin.manufacturing.boms.update', $bom) }}" method="POST" id="bom-form">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">المنتج <span class="text-danger">*</span></label>
                    <select name="product_id" class="form-select" required>
                        <option value="" disabled>اختر منتجًا...</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" @selected(old('product_id', $bom->product_id) == $product->id)>{{ $product->name_ar }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">اسم المتغير</label>
                    <input type="text" name="variant_name" class="form-control" value="{{ old('variant_name', $bom->variant_name) }}">
                </div>
                <div class="col-md-12">
                    <label class="form-label">ملاحظات</label>
                    <textarea name="notes" rows="3" class="form-control">{{ old('notes', $bom->notes) }}</textarea>
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
                        @php $items = old('items', $bom->items->map(fn($item) => ['material_id' => $item->material_id, 'quantity' => $item->quantity])->toArray()); @endphp
                        @forelse($items as $index => $item)
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
                <button type="submit" class="btn btn-primary">تحديث الوصفة</button>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.querySelector('#bom-items-table tbody');
    const addRowBtn = document.getElementById('add-material-row');
    let rowIndex = {{ count(old('items', $bom->items)) }};

    const renderPlaceholder = () => {
        if (!tableBody.querySelector('tr')) {
            const row = document.createElement('tr');
            row.classList.add('placeholder');
            row.innerHTML = '<td colspan="3" class="text-center text-muted">أضف المواد المطلوبة للوصفة.</td>';
            tableBody.appendChild(row);
        }
    };

    if (!tableBody.querySelector('tr')) {
        renderPlaceholder();
    }

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
