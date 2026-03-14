@extends('admin.layout')

@section('title', 'تعديل أمر تصنيع')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">تعديل أمر التصنيع {{ $order->reference }}</h4>
    <a href="{{ route('admin.manufacturing.orders.index') }}" class="btn btn-outline-secondary">عودة للأوامر</a>
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

@php
    $bomPayload = $boms->mapWithKeys(function ($bom) {
        return [$bom->id => [
            'product_id' => $bom->product_id,
            'variant_name' => $bom->variant_name,
            'items' => $bom->items->map(fn($item) => [
                'material_id' => $item->material_id,
                'quantity' => (float) $item->quantity,
                'cost_per_unit' => (float) ($item->material?->cost_per_unit ?? 0),
            ])->values(),
        ]];
    });

    $materialPayload = $materials->map(fn($material) => [
        'id' => $material->id,
        'name' => $material->name,
        'unit' => $material->unit,
    ])->values();

    $existingMaterials = $order->materials->map(fn($material) => [
        'material_id' => $material->material_id,
        'quantity_used' => (float) $material->quantity_used,
        'cost' => (float) $material->cost,
    ])->toArray();
@endphp

<div class="card shadow-sm">
    <div class="card-body">
        <form action="{{ route('admin.manufacturing.orders.update', $order) }}" method="POST" id="manufacturing-order-form">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">رقم المرجع <span class="text-danger">*</span></label>
                    <input type="text" name="reference" class="form-control" value="{{ old('reference', $order->reference) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">المنتج <span class="text-danger">*</span></label>
                    <select name="product_id" class="form-select" id="product-select" required>
                        <option value="" disabled>اختر المنتج...</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" @selected(old('product_id', $order->product_id) == $product->id)>{{ $product->name_ar }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">استيراد من وصفة (اختياري)</label>
                    <select id="bom-picker" class="form-select">
                        <option value="">— بدون اختيار —</option>
                        @foreach($boms as $bom)
                            <option value="{{ $bom->id }}">{{ $bom->product?->name_ar }} @if($bom->variant_name) - {{ $bom->variant_name }} @endif</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">اسم المتغير</label>
                    <input type="text" name="variant_name" id="variant-name-input" class="form-control" value="{{ old('variant_name', $order->variant_name) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">الكمية المخطط لها <span class="text-danger">*</span></label>
                    <input type="number" min="1" name="planned_quantity" class="form-control" value="{{ old('planned_quantity', $order->planned_quantity) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">الكمية المكتملة</label>
                    <input type="number" min="0" name="completed_quantity" class="form-control" value="{{ old('completed_quantity', $order->completed_quantity) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">الحالة</label>
                    <select name="status" class="form-select" required>
                        @foreach(['planned' => 'مخطط', 'in_progress' => 'قيد التنفيذ', 'completed' => 'مكتمل', 'cancelled' => 'ملغى'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $order->status) == $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">تاريخ البدء</label>
                    <input type="date" name="starts_at" class="form-control" value="{{ old('starts_at', optional($order->starts_at)->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">تاريخ التسليم المتوقع</label>
                    <input type="date" name="due_at" class="form-control" value="{{ old('due_at', optional($order->due_at)->format('Y-m-d')) }}">
                </div>
                <div class="col-12">
                    <label class="form-label">ملاحظات</label>
                    <textarea name="notes" rows="3" class="form-control">{{ old('notes', $order->notes) }}</textarea>
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0">المواد المستخدمة في الأمر</h5>
                <button type="button" class="btn btn-sm btn-primary" id="add-material-row"><i class="bi bi-plus"></i> إضافة مادة</button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="order-materials-table">
                    <thead class="table-light">
                        <tr>
                            <th>المادة الخام</th>
                            <th width="140">الكمية</th>
                            <th width="160">التكلفة</th>
                            <th width="90" class="text-center">حذف</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $oldMaterials = old('materials', $existingMaterials); @endphp
                        @forelse($oldMaterials as $index => $material)
                            <tr>
                                <td>
                                    <select name="materials[{{ $index }}][material_id]" class="form-select" required>
                                        <option value="" disabled>اختر مادة...</option>
                                        @foreach($materials as $option)
                                            <option value="{{ $option->id }}" @selected(($material['material_id'] ?? null) == $option->id)>{{ $option->name }} ({{ $option->unit ?? 'وحدة' }})</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" min="0" step="0.001" name="materials[{{ $index }}][quantity_used]" class="form-control" value="{{ $material['quantity_used'] ?? 0 }}" required>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <span class="input-group-text">{{ \App\Support\Currency::symbol() }}</span>
                                        <input type="number" min="0" step="0.01" name="materials[{{ $index }}][cost]" class="form-control" value="{{ $material['cost'] ?? 0 }}" required>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-row">×</button>
                                </td>
                            </tr>
                        @empty
                            <tr class="placeholder">
                                <td colspan="4" class="text-center text-muted">أضف المواد وكلفتها للتصنيع.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 d-flex justify-content-end gap-3">
                <div class="text-muted">التكلفة الإجمالية: <span id="order-total-cost">{{ \App\Support\Currency::format(collect($oldMaterials)->sum('cost')) }}</span></div>
                <button type="submit" class="btn btn-primary">تحديث الأمر</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const bomData = @json($bomPayload);
    const materials = @json($materialPayload);
    const tableBody = document.querySelector('#order-materials-table tbody');
    const addRowBtn = document.getElementById('add-material-row');
    const productSelect = document.getElementById('product-select');
    const bomPicker = document.getElementById('bom-picker');
    const variantInput = document.getElementById('variant-name-input');
    const totalCostDisplay = document.getElementById('order-total-cost');
    const currencySymbol = @json(\App\Support\Currency::symbol());
    const precision = currencySymbol === '$' ? 2 : 0;
    let rowIndex = {{ count($oldMaterials) }};

    const renderPlaceholder = () => {
        if (!tableBody.querySelector('tr')) {
            const row = document.createElement('tr');
            row.classList.add('placeholder');
            row.innerHTML = '<td colspan="4" class="text-center text-muted">أضف المواد وكلفتها للتصنيع.</td>';
            tableBody.appendChild(row);
        }
    };

    const updateTotalCost = () => {
        let total = 0;
        tableBody.querySelectorAll('input[name*="[cost]"]').forEach(input => {
            total += parseFloat(input.value || 0);
        });
        totalCostDisplay.textContent = `${currencySymbol} ${total.toFixed(precision)}`;
    };

    const buildOptions = (selectedId = null) => {
        return materials.map(material => {
            const selected = Number(selectedId) === Number(material.id) ? 'selected' : '';
            const unit = material.unit ?? 'وحدة';
            return `<option value="${material.id}" ${selected}>${material.name} (${unit})</option>`;
        }).join('');
    };

    const createRow = (index, data = {}) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <select name="materials[${index}][material_id]" class="form-select" required>
                    <option value="" disabled ${data.material_id ? '' : 'selected'}>اختر مادة...</option>
                    ${buildOptions(data.material_id)}
                </select>
            </td>
            <td>
                <input type="number" min="0" step="0.001" name="materials[${index}][quantity_used]" class="form-control" value="${data.quantity_used ?? 1}" required>
            </td>
            <td>
                <div class="input-group">
                    <span class="input-group-text">${currencySymbol}</span>
                    <input type="number" min="0" step="0.01" name="materials[${index}][cost]" class="form-control" value="${(data.cost ?? 0).toFixed(precision)}" required>
                </div>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger remove-row">×</button>
            </td>`;
        return row;
    };

    renderPlaceholder();
    updateTotalCost();

    addRowBtn.addEventListener('click', () => {
        if (tableBody.querySelector('.placeholder')) {
            tableBody.innerHTML = '';
        }

        const row = createRow(rowIndex);
        tableBody.appendChild(row);
        rowIndex++;
    });

    tableBody.addEventListener('click', event => {
        if (event.target.classList.contains('remove-row')) {
            event.target.closest('tr').remove();
            if (!tableBody.querySelector('tr')) {
                renderPlaceholder();
            }
            updateTotalCost();
        }
    });

    tableBody.addEventListener('input', event => {
        if (event.target.name && event.target.name.includes('[cost]')) {
            updateTotalCost();
        }
    });

    bomPicker.addEventListener('change', event => {
        const bomId = event.target.value;
        if (!bomId || !bomData[bomId]) {
            return;
        }

        const data = bomData[bomId];
        if (!productSelect.value) {
            productSelect.value = data.product_id;
        }
        if (data.variant_name) {
            variantInput.value = data.variant_name;
        }

        tableBody.innerHTML = '';
        data.items.forEach((item, idx) => {
            const row = createRow(idx, {
                material_id: item.material_id,
                quantity_used: item.quantity,
                cost: item.cost_per_unit * item.quantity,
            });
            tableBody.appendChild(row);
        });

        rowIndex = data.items.length;
        if (!rowIndex) {
            renderPlaceholder();
        }
        updateTotalCost();
    });
});
</script>
@endpush
@endsection
