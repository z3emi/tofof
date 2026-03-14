@extends('admin.layout')

@section('title', 'تعديل فاتورة شراء')

@section('content')
<div class="card shadow-sm">
    <div class="card-header" style="background-color: #f9f5f1;">
        <h4 class="mb-0" style="color: #cd8985;">تعديل فاتورة شراء #{{ $purchase->invoice_number ?? $purchase->id }}</h4>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.purchases.update', $purchase->id) }}" method="POST">
            @csrf
            @method('PATCH')

            {{-- Main Invoice Details (No changes here) --}}
            <div class="row border-bottom pb-3 mb-3">
                <div class="col-md-4 mb-3">
                    <label for="supplier_id" class="form-label">المورد <span class="text-danger">*</span></label>
                    <select class="form-select" id="supplier_id" name="supplier_id" required>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id', $purchase->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="invoice_date" class="form-label">تاريخ الفاتورة <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="invoice_date" name="invoice_date" value="{{ old('invoice_date', $purchase->invoice_date->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="invoice_number" class="form-label">رقم الفاتورة (اختياري)</label>
                    <input type="text" class="form-control" id="invoice_number" name="invoice_number" value="{{ old('invoice_number', $purchase->invoice_number) }}">
                </div>
            </div>

            {{-- Product Adder Section (No changes here) --}}
            <h5 class="mb-3">بنود الفاتورة</h5>
            <div class="row align-items-end p-3 mb-3 bg-light rounded">
                <div class="col-md-5">
                    <label for="product_search" class="form-label">اختر منتج</label>
                    <select id="product_search" class="form-select">
                        <option value="" disabled selected>ابحث أو اختر منتج...</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name_ar }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="quantity" class="form-label">الكمية</label>
                    <input type="number" id="quantity" class="form-control" value="1" min="1">
                </div>
                <div class="col-md-3">
                    <label for="purchase_price" class="form-label">سعر الشراء للقطعة</label>
                    <input type="number" id="purchase_price" step="any" class="form-control" placeholder="0.00">
                </div>
                <div class="col-md-2">
                    <button type="button" id="add_item_btn" class="btn btn-success w-100">إضافة</button>
                </div>
            </div>

            {{-- Invoice Items Table --}}
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>المنتج</th>
                            <th style="width: 15%;">الكمية</th>
                            <th style="width: 20%;">سعر الشراء</th>
                            <th>الإجمالي</th>
                            <th>حذف</th>
                        </tr>
                    </thead>
                    <tbody id="invoice_items_body">
                        {{-- **CHANGE 1**: Existing items now have visible input fields --}}
                        @foreach($purchase->items as $index => $item)
                            <tr>
                                <td>
                                    <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id }}">
                                    {{ $item->product->name_ar ?? 'منتج محذوف' }}
                                </td>
                                <td>
                                    <input type="number" class="form-control item-quantity" name="items[{{ $index }}][quantity]" value="{{ $item->quantity }}" required min="1">
                                </td>
                                <td>
                                    <input type="number" class="form-control item-price" name="items[{{ $index }}][purchase_price]" value="{{ $item->purchase_price }}" step="any" required min="0">
                                </td>
                                <td class="text-center align-middle item-total">
                                    {{ number_format($item->quantity * $item->purchase_price, 2) }}
                                </td>
                                <td class="text-center align-middle">
                                    <button type="button" class="btn btn-danger btn-sm remove-item-btn">X</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                <a href="{{ route('admin.purchases.index') }}" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const addItemBtn = document.getElementById('add_item_btn');
    const productSearch = document.getElementById('product_search');
    const quantityInput = document.getElementById('quantity');
    const priceInput = document.getElementById('purchase_price');
    const itemsBody = document.getElementById('invoice_items_body');
    
    let itemIndex = {{ $purchase->items->count() }};

    function updateRowTotal(row) {
        const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
        const price = parseFloat(row.querySelector('.item-price').value) || 0;
        const totalCell = row.querySelector('.item-total');
        totalCell.textContent = (quantity * price).toFixed(2);
    }

    addItemBtn.addEventListener('click', function () {
        const selectedOption = productSearch.options[productSearch.selectedIndex];
        if (!selectedOption || !selectedOption.value) {
            alert('الرجاء اختيار منتج.');
            return;
        }
        
        const productId = selectedOption.value;
        const productName = selectedOption.text;
        const quantity = parseInt(quantityInput.value);
        const price = parseFloat(priceInput.value);

        if (isNaN(quantity) || quantity <= 0) {
            alert('الرجاء إدخال كمية صحيحة.');
            return;
        }
        if (isNaN(price) || price < 0) {
            alert('الرجاء إدخال سعر شراء صحيح.');
            return;
        }

        // **CHANGE 2**: The new row now also has visible input fields
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td>
                <input type="hidden" name="items[${itemIndex}][product_id]" value="${productId}">
                ${productName}
            </td>
            <td>
                <input type="number" class="form-control item-quantity" name="items[${itemIndex}][quantity]" value="${quantity}" required min="1">
            </td>
            <td>
                <input type="number" class="form-control item-price" name="items[${itemIndex}][purchase_price]" value="${price}" step="any" required min="0">
            </td>
            <td class="text-center align-middle item-total">
                ${(quantity * price).toFixed(2)}
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-danger btn-sm remove-item-btn">X</button>
            </td>
        `;

        itemsBody.appendChild(newRow);
        itemIndex++;

        // Reset inputs
        productSearch.selectedIndex = 0;
        quantityInput.value = 1;
        priceInput.value = '';
    });

    itemsBody.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-item-btn')) {
            e.target.closest('tr').remove();
        }
    });

    // Add event listener to update totals when quantity or price changes
    itemsBody.addEventListener('input', function (e) {
        if (e.target.classList.contains('item-quantity') || e.target.classList.contains('item-price')) {
            updateRowTotal(e.target.closest('tr'));
        }
    });
});
</script>
@endsection