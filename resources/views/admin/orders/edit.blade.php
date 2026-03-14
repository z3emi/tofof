@php
$governorates = ['بغداد', 'نينوى', 'البصرة', 'صلاح الدين', 'دهوك', 'أربيل', 'السليمانية', 'ديالى', 'واسط', 'ميسان', 'ذي قار', 'المثنى', 'بابل', 'كربلاء', 'النجف', 'الانبار', 'الديوانية', 'كركوك', 'حلبجة'];
@endphp

@extends('admin.layout')

@section('title', 'تعديل الطلب #' . $order->id)

@section('content')
<form action="{{ route('admin.orders.update', $order->id) }}" method="POST" id="edit-order-form">
    @csrf
    @method('PUT')
    <div class="card shadow-sm">
        <div class="card-header"><h4 class="mb-0">تعديل الطلب #{{ $order->id }}</h4></div>
        <div class="card-body">
            @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error) <p class="mb-0">{{ $error }}</p> @endforeach
                </div>
            @endif

            {{-- Customer and Address Details --}}
            <h5>تفاصيل العميل والعنوان</h5>
            <div class="row bg-light p-3 rounded mb-4">
                <div class="col-md-4 mb-3">
                    <label class="form-label">العميل</label>
                    <input type="text" class="form-control" value="{{ $order->customer->name ?? 'عميل محذوف' }}" readonly style="background-color: #e9ecef;">
                </div>
                 <div class="col-md-4 mb-3">
                    <label class="form-label">رقم الهاتف</label>
                    <input type="text" class="form-control" value="{{ $order->customer->phone_number ?? 'N/A' }}" readonly style="background-color: #e9ecef;">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="governorate" class="form-label">المحافظة</label>
                    <select name="governorate" id="governorate" class="form-select" required>
                        <option value="">-- اختر المحافظة --</option>
                        @foreach($governorates as $governorate)
                            <option value="{{ $governorate }}" @selected(old('governorate', $order->governorate) == $governorate)>{{ $governorate }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3"><label for="city" class="form-label">المدينة</label><input type="text" class="form-control" name="city" id="city" value="{{ old('city', $order->city) }}" required></div>
                <div class="col-md-4 mb-3"><label for="nearest_landmark" class="form-label">أقرب نقطة دالة</label><input type="text" class="form-control" name="nearest_landmark" id="nearest_landmark" value="{{ old('nearest_landmark', $order->nearest_landmark) }}" required></div>
            </div>
            <div class="mb-3">
                <label for="address_details" class="form-label">تفاصيل العنوان</label>
                <textarea name="address_details" id="address_details" class="form-control" rows="3" required>{{ old('address_details', $order->address_details ?? $order->customer?->address_details) }}</textarea>
            </div>
            <hr>
            {{-- ===== START: تم تعديل هذا القسم ===== --}}
            <div class="mb-3">
                <label for="notes" class="form-label">ملاحظات الطلب (اختياري)</label>
                <textarea name="notes" id="notes" class="form-control" rows="4">{{ old('notes', $order->notes) }}</textarea>
            </div>
            {{-- ===== END: تم تعديل هذا القسم ===== --}}
            {{-- Product Adder Section --}}
            <h5 class="mb-3">بنود الطلب</h5>
            <div class="row g-3 align-items-end mb-3 p-3 bg-light rounded">
                <div class="col-md-6"><label for="product_selector" class="form-label">إضافة منتج</label><select id="product_selector" class="form-select"><option value="">-- ابحث أو اختر منتج --</option>@foreach($products as $product)<option value="{{ $product->id }}" data-price="{{ $product->price }}" data-stock="{{ $product->stock_quantity }}" data-name="{{ $product->name_ar }}">{{ $product->name_ar }} (المتاح: {{ $product->stock_quantity }})</option>@endforeach</select></div>
                <div class="col-md-2"><label for="quantity_selector" class="form-label">الكمية</label><input type="number" id="quantity_selector" class="form-control" value="1" min="1"></div>
                <div class="col-md-2"><label for="price_selector" class="form-label">السعر</label><input type="number" id="price_selector" class="form-control" step="any" placeholder="سعر البيع"></div>
                <div class="col-md-2 d-grid"><button type="button" class="btn btn-success" id="add_product_btn"><i class="bi bi-plus-lg"></i> إضافة</button></div>
            </div>

            {{-- Order Items Table --}}
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr><th>المنتج</th><th style="width: 20%;">سعر البيع</th><th style="width: 15%;">الكمية</th><th>الإجمالي</th><th style="width: 80px;">حذف</th></tr>
                    </thead>
                    <tbody id="order_items_table">
                        @foreach($order->items as $item)
                            @if($item->product) {{-- Add this check to prevent errors if product was deleted --}}
                            <tr class="item-row" data-stock="{{ ($item->product->stock_quantity ?? 0) + $item->quantity }}">
                                <td><input type="hidden" name="products[{{ $item->product_id }}][id]" value="{{ $item->product_id }}">{{ $item->product->name_ar ?? 'منتج محذوف' }}</td>
                                <td><input type="number" name="products[{{ $item->product_id }}][price]" class="form-control item-price" value="{{ $item->price }}" min="0" step="any" required></td>
                                <td><input type="number" name="products[{{ $item->product_id }}][quantity]" class="form-control item-quantity" value="{{ $item->quantity }}" min="1" required></td>
                                <td class="item-subtotal">{{ number_format($item->price * $item->quantity, 0) }} د.ع</td>
                                <td><button type="button" class="btn btn-sm btn-danger remove-item"><i class="bi bi-trash"></i></button></td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Order Summary --}}
            <div class="row justify-content-end mt-4">
                <div class="col-md-6">
                    <h5 class="mb-3">ملخص الطلب</h5>
                    <table class="table table-bordered">
                        <tbody>
                            <tr><th class="w-50">المجموع الفرعي</th><td id="subtotal_amount">0 د.ع</td></tr>
                            <tr>
                                <th>كود الخصم</th>
                                <td>
                                    <div class="input-group">
                                        <input type="text" id="discount_code_input" name="discount_code" class="form-control form-control-sm" placeholder="أدخل الكود" value="{{ $order->discountCode?->code }}">
                                        <button class="btn btn-sm btn-outline-secondary" type="button" id="apply_discount_btn">تطبيق</button>
                                    </div>
                                    {{-- ===== START: الحقل المخفي المضاف ===== --}}
                                    <input type="hidden" name="discount_amount" id="discount_amount_hidden" value="{{ $order->discount_amount ?? 0 }}">
                                    {{-- ===== END: الحقل المخفي المضاف ===== --}}
                                    <small id="discount_feedback" class="d-block mt-1"></small>
                                </td>
                            </tr>
                            <tr><th>قيمة الخصم</th><td id="discount_amount_display">{{ number_format($order->discount_amount, 0) }} د.ع</td></tr>
                            <tr><th>الشحن</th><td><div class="d-flex justify-content-between align-items-center"><span id="shipping_cost_display">{{ $order->shipping_cost > 0 ? number_format($order->shipping_cost, 0) . ' د.ع' : 'مجاني' }}</span><div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="free_shipping_switch" name="free_shipping" @checked($order->shipping_cost == 0)><label class="form-check-label" for="free_shipping_switch">شحن مجاني</label></div></div></td></tr>
                        </tbody>
                        <tfoot class="table-light"><tr class="fw-bold fs-5"><td>الإجمالي النهائي</td><td id="total_amount">0 د.ع</td></tr></tfoot>
                    </table>
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-secondary">إلغاء</a>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('#product_selector').select2({
        placeholder: 'ابحث أو اختر...',
        allowClear: true,
        language: { noResults: () => "لا يوجد نتائج مطابقة" }
    });

    const orderItemsTable = $('#order_items_table');
    const subtotalCell = $('#subtotal_amount');
    const totalAmountCell = $('#total_amount');
    const shippingCostCell = $('#shipping_cost_display');
    const freeShippingSwitch = $('#free_shipping_switch');
    const discountCodeInput = $('#discount_code_input');
    const applyDiscountBtn = $('#apply_discount_btn');
    const discountAmountDisplay = $('#discount_amount_display');
    const discountAmountHidden = $('#discount_amount_hidden');

    let subtotal = 0;
    const defaultShippingCost = {{ (float) ($defaultShippingCost ?? 0) }};
    let shippingCost = parseFloat('{{ $order->shipping_cost ?? $defaultShippingCost }}');
    let discountAmount = parseFloat('{{ $order->discount_amount ?? 0 }}');

    function formatNumber(num) {
        return parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    function updateTotals() {
        subtotal = 0;
        orderItemsTable.find('tr.item-row').each(function() {
            const price = parseFloat($(this).find('.item-price').val()) || 0;
            const quantity = parseInt($(this).find('.item-quantity').val()) || 0;
            subtotal += price * quantity;
        });
        
        shippingCost = freeShippingSwitch.is(':checked') ? 0 : defaultShippingCost;
        
        const finalTotal = (subtotal - discountAmount) + shippingCost;

        subtotalCell.text(formatNumber(subtotal) + ' د.ع');
        shippingCostCell.text(shippingCost > 0 ? formatNumber(shippingCost) + ' د.ع' : 'مجاني');
        totalAmountCell.text(formatNumber(finalTotal) + ' د.ع');
    }

    function updateRow(row) {
        const quantity = parseFloat($(row).find('.item-quantity').val()) || 0;
        const price = parseFloat($(row).find('.item-price').val()) || 0;
        $(row).find('.item-subtotal').text(formatNumber(quantity * price) + ' د.ع');
        updateTotals();
    }

    // Initialize totals on page load
    updateTotals();

    $('#product_selector').on('select2:select', function (e) {
        const selectedOption = $(e.params.data.element);
        $('#price_selector').val(selectedOption.data('price'));
    });

    $('#add_product_btn').on('click', function () {
        const selectedOption = $('#product_selector').find(':selected');
        if (!selectedOption.val()) return alert('الرجاء اختيار منتج.');

        const productId = selectedOption.val();
        if ($(`input[name='products[${productId}][id]']`).length > 0) {
            alert('هذا المنتج مضاف مسبقاً.');
            return;
        }

        const productName = selectedOption.data('name');
        const productPrice = parseFloat($('#price_selector').val()) || parseFloat(selectedOption.data('price'));
        const stock = parseFloat(selectedOption.data('stock')) || 0;
        const quantity = parseInt($('#quantity_selector').val());

        const newRow = `
            <tr class="item-row" data-stock="${stock}">
                <td><input type="hidden" name="products[${productId}][id]" value="${productId}">${productName}</td>
                <td><input type="number" name="products[${productId}][price]" class="form-control item-price" value="${productPrice}" min="0" step="any" required></td>
                <td><input type="number" name="products[${productId}][quantity]" class="form-control item-quantity" value="${quantity}" min="1" required></td>
                <td class="item-subtotal">${formatNumber(productPrice * quantity)} د.ع</td>
                <td><button type="button" class="btn btn-sm btn-danger remove-item"><i class="bi bi-trash"></i></button></td>
            </tr>
        `;
        orderItemsTable.append(newRow);
        updateRow(orderItemsTable.find('tr:last-child'));
        $('#product_selector').val('').trigger('change');
        $('#quantity_selector').val(1);
        $('#price_selector').val('');
    });

    orderItemsTable.on('click', '.remove-item', function() {
        $(this).closest('tr').remove();
        updateTotals();
    });

    orderItemsTable.on('input', '.item-price, .item-quantity', function() {
        updateRow($(this).closest('tr'));
    });

    freeShippingSwitch.on('change', function() {
        updateTotals();
    });

    applyDiscountBtn.on('click', function() {
        const code = discountCodeInput.val();
        if (!code) {
            // Reset discount if code is cleared
            discountAmount = 0;
            discountAmountHidden.val(0);
            discountAmountDisplay.text('0 د.ع');
            $('#discount_feedback').text('');
            updateTotals();
            return;
        }

        $.ajax({
            url: "{{ route('admin.orders.applyDiscount') }}",
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', code: code, subtotal: subtotal },
            success: function(response) {
                if (response.success) {
                    discountAmount = response.discount_amount;
                    discountAmountHidden.val(discountAmount);
                    discountAmountDisplay.text(formatNumber(discountAmount) + ' د.ع');
                    $('#discount_feedback').text(response.message).removeClass('text-danger').addClass('text-success');
                    updateTotals();
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON.message || 'حدث خطأ ما.';
                discountAmount = 0;
                discountAmountHidden.val(0);
                discountAmountDisplay.text('0 د.ع');
                $('#discount_feedback').text(errorMsg).removeClass('text-success').addClass('text-danger');
                updateTotals();
            }
        });
    });
});
</script>
@endpush
