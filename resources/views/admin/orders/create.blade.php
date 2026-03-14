@extends('admin.layout')

@section('title', 'إضافة طلب يدوي جديد')

@section('content')
<form action="{{ route('admin.orders.store') }}" method="POST" id="create-order-form">
    @csrf
    <div class="card shadow-sm">
        <div class="card-header"><h4 class="mb-0">إنشاء طلب جديد</h4></div>
        <div class="card-body">
            @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error) <p class="mb-0">{{ $error }}</p> @endforeach
                </div>
            @endif

            {{-- Customer Selection --}}
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="customer_id" class="form-label">اختر العميل</label>
                    <select name="customer_id" id="customer_id" class="form-select" required>
                        <option value="">-- اختر العميل --</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" data-banned="{{ $customer->user?->banned_at ? 'true' : 'false' }}" @selected(old('customer_id') == $customer->id)>
                                {{ $customer->name }} - {{ $customer->phone_number }}
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="saved_address_id" id="saved_address_id" value="{{ old('saved_address_id') }}">
                    <div id="banned-customer-warning" class="text-danger fw-bold mt-2" style="display: none;"><i class="bi bi-exclamation-triangle-fill"></i> هذا العميل محظور!</div>
                </div>
            </div>

            {{-- Shipping Address --}}
            <h5 class="mt-3">عنوان التوصيل</h5>
            <div id="saved_addresses_wrapper" class="alert alert-light border mb-3" style="display: none;">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <strong class="mb-0">عناوين العميل المحفوظة</strong>
                    <span class="text-muted small">اختر أحد العناوين ليتم تعبئة الحقول تلقائياً</span>
                </div>
                <div id="saved_addresses_list" class="list-group mt-3"></div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="governorate" class="form-label">المحافظة</label>
                    <select name="governorate" id="governorate" class="form-select" required>
                        <option value="">-- اختر المحافظة --</option>
                        @foreach(['بغداد','نينوى','البصرة','صلاح الدين','دهوك','أربيل','السليمانية','ديالى','واسط','ميسان','ذي قار','المثنى','بابل','كربلاء','النجف','الانبار','الديوانية','كركوك','حلبجة'] as $gov)
                            <option value="{{ $gov }}">{{ $gov }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3"><label for="city" class="form-label">المدينة / القضاء</label><input type="text" class="form-control" id="city" name="city" value="{{ old('city') }}" required></div>
                <div class="col-md-4 mb-3"><label for="nearest_landmark" class="form-label">أقرب نقطة دالة</label><input type="text" class="form-control" id="nearest_landmark" name="nearest_landmark" value="{{ old('nearest_landmark') }}" required></div>
            </div>

            <div class="mb-3">
                <label for="address_details" class="form-label">تفاصيل العنوان</label>
                <textarea class="form-control" id="address_details" name="address_details" rows="3" required>{{ old('address_details') }}</textarea>
            </div>

            {{-- ===== START: تم تعديل هذا القسم ===== --}}
            <div class="mb-3">
                <label for="notes" class="form-label">ملاحظات الطلب (اختياري)</label>
                <textarea name="notes" id="notes" class="form-control" rows="4">{{ old('notes') }}</textarea>
            </div>
            {{-- ===== END: تم تعديل هذا القسم ===== --}}

            <hr>
            <h5 class="mb-3">المنتجات المطلوبة</h5>
            <div class="row g-3 align-items-end mb-3">
                <div class="col-md-6">
                    <label for="product_selector" class="form-label">اختر منتج</label>
                    <select id="product_selector" class="form-select">
                        <option value="">-- اختر المنتج --</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-stock="{{ $product->stock_quantity }}" data-name="{{ $product->name_ar }}" data-category="{{ $product->category_id }}">
                                {{ $product->name_ar }} (المتاح: {{ $product->stock_quantity }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2"><label for="quantity_selector" class="form-label">الكمية</label><input type="number" id="quantity_selector" class="form-control" value="1" min="1"></div>
                <div class="col-md-2"><label for="price_selector" class="form-label">السعر</label><input type="number" id="price_selector" class="form-control" step="any" placeholder="سعر البيع"></div>
                <div class="col-md-2 d-grid"><button type="button" class="btn btn-success" id="add_product_btn"><i class="bi bi-plus-lg"></i></button></div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light"><tr><th>المنتج</th><th style="width: 20%;">سعر البيع</th><th style="width: 15%;">الكمية</th><th>الإجمالي</th><th style="width: 80px;">حذف</th></tr></thead>
                    <tbody id="order_items_table"></tbody>
                </table>
            </div>

            <div class="row justify-content-end mt-4">
                <div class="col-md-6">
                    <h5 class="mb-3">ملخص الطلب</h5>
                    <table class="table table-bordered">
                        <tbody>
                            <tr><th class="w-50">المجموع الفرعي</th><td id="subtotal_amount">0 د.ع</td></tr>
                            <tr><th>كود الخصم</th><td><div class="input-group"><input type="text" id="discount_code_input" name="discount_code" class="form-control form-control-sm" placeholder="أدخل الكود"><button class="btn btn-sm btn-outline-secondary" type="button" id="apply_discount_btn">تطبيق</button></div><input type="hidden" name="discount_amount" id="discount_amount_hidden" value="0"><small id="discount_feedback" class="d-block mt-1"></small></td></tr>
                            <tr><th>قيمة الخصم</th><td id="discount_amount_display">0 د.ع</td></tr>
                            <tr><th>الشحن</th><td><div class="d-flex justify-content-between align-items-center"><span id="shipping_cost_display">{{ number_format($defaultShippingCost ?? 0, 0) }} د.ع</span><div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="free_shipping_switch" name="free_shipping"><label class="form-check-label" for="free_shipping_switch">شحن مجاني</label></div></div></td></tr>
                        </tbody>
                        <tfoot class="table-light"><tr class="fw-bold fs-5"><td>الإجمالي النهائي</td><td id="total_amount">5,000 د.ع</td></tr></tfoot>
                    </table>
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary">حفظ الطلب</button>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">إلغاء</a>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
{{-- ===== START: تم حذف سكربتات TinyMCE ===== --}}
{{-- ===== END: تم حذف سكربتات TinyMCE ===== --}}
<script>
$(document).ready(function() {
    $('#customer_id, #product_selector').select2({
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
    const discountFeedback = $('#discount_feedback');
    const governorateSelect = $('#governorate');
    const cityInput = $('#city');
    const addressDetailsInput = $('#address_details');
    const nearestLandmarkInput = $('#nearest_landmark');
    const savedAddressesWrapper = $('#saved_addresses_wrapper');
    const savedAddressesList = $('#saved_addresses_list');
    const savedAddressInput = $('#saved_address_id');
    const bannedCustomerWarning = $('#banned-customer-warning');
    const addressEndpointTemplate = @json(route('admin.users.getAddress', ['id' => '__ID__']));
    const hasAddressDetailsOld = @json(!empty(old('address_details')));

    let subtotal = 0;
    const defaultShippingCost = {{ (float) ($defaultShippingCost ?? 0) }};
    let shippingCost = defaultShippingCost;
    let discountAmount = 0;

    function formatNumber(num) {
        return parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    function ensureGovernorateOption(value) {
        if (!value) {
            governorateSelect.val('');
            return;
        }

        if (!governorateSelect.find(`option[value="${value}"]`).length) {
            governorateSelect.append(new Option(value, value));
        }

        governorateSelect.val(value);
    }

    function fillAddressFields(payload = {}) {
        ensureGovernorateOption(payload.governorate || '');
        cityInput.val(payload.city || '');
        addressDetailsInput.val(payload.address_details || '');
        nearestLandmarkInput.val(payload.nearest_landmark || '');
    }

    function applyAddressFromPayload(payload = {}) {
        fillAddressFields(payload);
        savedAddressInput.val(payload.id ? payload.id : '');
    }

    function resetAddressSelection() {
        savedAddressesList.empty();
        savedAddressesWrapper.hide();
        applyAddressFromPayload({});
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

    $('#product_selector').on('select2:select', function (e) {
        const selectedOption = $(e.params.data.element);
        $('#price_selector').val(selectedOption.data('price'));
    });

    savedAddressesList.on('change', 'input[name="selected_address_option"]', function () {
        const payload = $(this).data('payload');
        if (payload) {
            applyAddressFromPayload(payload);
        }
    });

    function renderAddressOptions(addresses) {
        savedAddressesList.empty();

        if (!Array.isArray(addresses) || addresses.length === 0) {
            savedAddressesWrapper.hide();
            return;
        }

        if (addresses.length === 1) {
            savedAddressesWrapper.hide();
            applyAddressFromPayload(addresses[0]);
            return;
        }

        savedAddressesWrapper.show();

        addresses.forEach(function (address) {
            const item = $('<label class="list-group-item list-group-item-action d-flex align-items-start gap-3"></label>');
            const radio = $('<input type="radio" class="form-check-input mt-1" name="selected_address_option">');
            radio.val(address.id || '').data('payload', address);

            const content = $('<div class="text-start flex-grow-1"></div>');

            if (address.is_default) {
                content.append('<span class="badge bg-primary ms-1 mb-1">افتراضي</span>');
            }

            content.append($('<div class="fw-bold"></div>').text(address.address_details || 'بدون تفاصيل'));

            if (address.city || address.governorate) {
                content.append(
                    $('<div class="small text-muted"></div>').text([address.city, address.governorate].filter(Boolean).join(' - '))
                );
            }

            if (address.nearest_landmark) {
                content.append(
                    $('<div class="small text-muted"></div>').text('نقطة دالة: ' + address.nearest_landmark)
                );
            }

            item.append(radio).append(content);
            savedAddressesList.append(item);
        });

        const preferred = addresses.find(address => address.is_default) || addresses[0];
        if (preferred) {
            applyAddressFromPayload(preferred);
            savedAddressesList.find(`input[value="${preferred.id}"]`).prop('checked', true);
        }
    }

    function customerAddressesUrl(customerId) {
        return addressEndpointTemplate.replace('__ID__', customerId);
    }

    function fetchCustomerAddresses(customerId) {
        if (!customerId) {
            resetAddressSelection();
            return;
        }

        savedAddressInput.val('');
        savedAddressesWrapper.hide();
        savedAddressesList.empty();

        $.get(customerAddressesUrl(customerId))
            .done(function (response) {
                const addresses = response && Array.isArray(response.addresses) ? response.addresses : [];
                const fallback = response && response.fallback ? response.fallback : {};

                if (!addresses.length) {
                    applyAddressFromPayload(fallback);
                    savedAddressesWrapper.hide();
                    return;
                }

                renderAddressOptions(addresses);
            })
            .fail(function () {
                resetAddressSelection();
            });
    }

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
        const category = selectedOption.data('category');
        const quantity = parseInt($('#quantity_selector').val());

        const newRow = `
            <tr class="item-row" data-stock="${stock}" data-category="${category}">
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
            discountFeedback.text('يرجى إدخال كود الخصم.').removeClass('text-success').addClass('text-danger');
            return;
        }

        const items = [];
        orderItemsTable.find('tr.item-row').each(function() {
            items.push({
                product_id: $(this).find("input[name*='[id]']").val(),
                category_id: $(this).data('category'),
                price: parseFloat($(this).find('.item-price').val()) || 0,
                quantity: parseInt($(this).find('.item-quantity').val()) || 0
            });
        });

        $.ajax({
            url: "{{ route('admin.orders.applyDiscount') }}",
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', code: code, items: items },
            success: function(response) {
                if (response.success) {
                    discountAmount = response.discount_amount;
                    discountAmountHidden.val(discountAmount);
                    discountAmountDisplay.text(formatNumber(discountAmount) + ' د.ع');
                    discountFeedback.text(response.message).removeClass('text-danger').addClass('text-success');
                    updateTotals();
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON.message || 'حدث خطأ ما.';
                discountAmount = 0;
                discountAmountHidden.val(0);
                discountAmountDisplay.text('0 د.ع');
                discountFeedback.text(errorMsg).removeClass('text-success').addClass('text-danger');
                updateTotals();
            }
        });
    });

    $('#customer_id').on('change', function() {
        const selectedOption = $(this).find(':selected');
        const customerId = $(this).val();
        const isBanned = selectedOption.data('banned');
        bannedCustomerWarning.toggle(!!isBanned);
        fetchCustomerAddresses(customerId);
    });

    const initialCustomerId = $('#customer_id').val();
    if (initialCustomerId) {
        if (!hasAddressDetailsOld) {
            $('#customer_id').trigger('change');
        } else {
            const initialOption = $('#customer_id').find(':selected');
            bannedCustomerWarning.toggle(!!initialOption.data('banned'));
        }
    }
});
</script>
@endpush
