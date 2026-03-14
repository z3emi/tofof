@extends('admin.layout')

@section('title', 'إضافة فاتورة شراء')

@section('content')
<div class="card shadow-sm">
    <div class="card-header">
        <h4 class="mb-0">إضافة فاتورة شراء جديدة</h4>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.purchases.store') }}" method="POST">
            @csrf
            {{-- معلومات الفاتورة الأساسية --}}
            <div class="row border-bottom pb-3 mb-3">
                <div class="col-md-4 mb-3">
                    <label for="supplier_id" class="form-label">المورد <span class="text-danger">*</span></label>
                    <select class="form-select" id="supplier_id" name="supplier_id" required>
                        <option value="" disabled selected>اختر مورد...</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="invoice_date" class="form-label">تاريخ الفاتورة <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="invoice_date" name="invoice_date" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="invoice_number" class="form-label">رقم الفاتورة (اختياري)</label>
                    <input type="text" class="form-control" id="invoice_number" name="invoice_number">
                </div>
            </div>

            {{-- قسم إضافة المنتجات --}}
            <h5 class="mb-3">بنود الفاتورة</h5>
            <div class="row align-items-end p-3 mb-3 bg-light rounded">
                <div class="col-md-5">
                    <label for="product_search" class="form-label">اختر منتج</label>
                    <select id="product_search" class="form-select">
                         <option value="" disabled selected>ابحث أو اختر منتج...</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" data-price="{{ $product->price }}">{{ $product->name_ar }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="quantity" class="form-label">الكمية</label>
                    <input type="number" id="quantity" class="form-control" value="1" min="1">
                </div>
                 <div class="col-md-3">
                    <label for="purchase_price" class="form-label">سعر الشراء للقطعة</label>
                    <input type="number" id="purchase_price" step="0.01" class="form-control" placeholder="0.00">
                </div>
                <div class="col-md-2">
                    <button type="button" id="add_item_btn" class="btn btn-success w-100">إضافة</button>
                </div>
            </div>

            {{-- جدول بنود الفاتورة --}}
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>المنتج</th>
                            <th>الكمية</th>
                            <th>سعر الشراء</th>
                            <th>الإجمالي</th>
                            <th>حذف</th>
                        </tr>
                    </thead>
                    <tbody id="invoice_items_body">
                        {{-- سيتم إضافة الصفوف هنا عبر JavaScript --}}
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">حفظ الفاتورة</button>
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
    let itemIndex = 0;

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

        const total = quantity * price;

        const row = `
            <tr>
                <td>
                    <input type="hidden" name="items[${itemIndex}][product_id]" value="${productId}">
                    ${productName}
                </td>
                <td>
                    <input type="hidden" name="items[${itemIndex}][quantity]" value="${quantity}">
                    ${quantity}
                </td>
                <td>
                    <input type="hidden" name="items[${itemIndex}][purchase_price]" value="${price}">
                    ${price.toFixed(2)}
                </td>
                <td>${total.toFixed(2)}</td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-item-btn">X</button>
                </td>
            </tr>
        `;

        itemsBody.insertAdjacentHTML('beforeend', row);
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
});
</script>
@endsection
