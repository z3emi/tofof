@section('title', 'إضافة طلب يدوي جديد')

@push('styles')
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem; color: white; border-radius: 0 !important; }
    .form-section-title { font-weight: 700; color: var(--primary-dark); border-right: 4px solid var(--accent-gold); padding-right: 15px; margin-bottom: 2rem; }
    .form-control, .form-select { border-radius: 12px; padding: 0.8rem 1.2rem; border: 1px solid #e2e8f0; background-color: #fcfcfc; }
    .product-box { background: #f8fafc; border-radius: 15px; padding: 2rem; border: 1px solid #e2e8f0; margin-bottom: 2rem; }
    .summary-card { background: #fff; border-radius: 15px; border: 1px solid #e2e8f0; overflow: hidden; }
    .summary-item { display: flex; justify-content: space-between; padding: 1rem 1.5rem; border-bottom: 1px solid #f1f5f9; }
    .summary-total { background: #f8fafc; font-size: 1.25rem; font-weight: 800; color: var(--primary-dark); }
    .submit-btn { background: var(--primary-dark); padding: 1rem 3rem; border-radius: 12px; font-weight: 700; color: white; border: none; transition: all 0.3s; }
    .submit-btn:hover { background: var(--primary-medium); transform: translateY(-2px); box-shadow: 0 5px 15px rgba(109,14,22,0.2); }
</style>
@endpush

@section('content')
<div class="form-card">
    <div class="form-card-header">
        <h2 class="mb-2 fw-bold text-white"><i class="bi bi-cart-plus-fill me-2"></i> إنشاء طلب جديد</h2>
        <p class="mb-0 opacity-75 fs-6 text-white small">إضافة طلب يدوي لأحد العملاء في النظام.</p>
    </div>

    <div class="p-4 p-lg-5">
        @if (session('error'))<div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4">{{ session('error') }}</div>@endif
        @if ($errors->any())
            <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4">
                <ul class="mb-0">@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
            </div>
        @endif

        <form action="{{ route('admin.orders.store') }}" method="POST" id="create-order-form">
            @csrf

            <div class="mb-5">
                <h5 class="form-section-title">معلومات العميل والعنوان</h5>
                <div class="row g-4">
                    <div class="col-md-12">
                        <label class="form-label fw-bold">اختر العميل <span class="text-danger">*</span></label>
                        <select name="customer_id" id="customer_id" class="form-select" required>
                            <option value="">-- اختر العميل من القائمة --</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" data-banned="{{ $customer->user?->banned_at ? 'true' : 'false' }}" @selected(old('customer_id') == $customer->id)>
                                    {{ $customer->name }} - {{ $customer->phone_number }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="saved_address_id" id="saved_address_id" value="{{ old('saved_address_id') }}">
                        <div id="banned-customer-warning" class="text-danger fw-bold mt-2 d-none"><i class="bi bi-exclamation-triangle-fill"></i> تنبيه: هذا العميل محظور حالياً!</div>
                    </div>

                    <div class="col-12">
                        <div id="saved_addresses_wrapper" class="alert alert-light border-dashed rounded-4 p-4 mb-0 d-none">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0 fw-bold"><i class="bi bi-geo-alt-fill me-2"></i> العناوين المحفوظة لهذا العميل</h6>
                                <span class="text-muted small">سيتم تعبئة الحقول تلقائياً عند الاختيار</span>
                            </div>
                            <div id="saved_addresses_list" class="row g-3"></div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">المحافظة <span class="text-danger">*</span></label>
                        <select name="governorate" id="governorate" class="form-select" required>
                            <option value="">-- اختر المحافظة --</option>
                            @foreach(['بغداد','نينوى','البصرة','صلاح الدين','دهوك','أربيل','السليمانية','ديالى','واسط','ميسان','ذي قار','المثنى','بابل','كربلاء','النجف','الانبار','الديوانية','كركوك','حلبجة'] as $gov)
                                <option value="{{ $gov }}">{{ $gov }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">المدينة / القضاء <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="city" name="city" value="{{ old('city') }}" placeholder="اسم المنطقة أو القضاء" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">أقرب نقطة دالة <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nearest_landmark" name="nearest_landmark" value="{{ old('nearest_landmark') }}" placeholder="مثال: جامع، مدرسة، محل مشهور" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">تفاصيل العنوان الإضافية</label>
                        <textarea class="form-control" id="address_details" name="address_details" rows="3" placeholder="رقم الزقاق، الدار، أو أي تفاصيل أخرى">{{ old('address_details') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="mb-5">
                <h5 class="form-section-title">خيارات إضافية (هدايا وملاحظات)</h5>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">ملاحظات عامة</label>
                        <textarea name="notes" id="notes" class="form-control" rows="5" placeholder="أي تعليمات خاصة لشركة التوصيل...">{{ old('notes') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <div class="p-4 bg-light rounded-4 border h-100">
                            <div class="form-check form-switch mb-4">
                                <input class="form-check-input scale-125 ms-0 me-3" type="checkbox" id="is_gift" name="is_gift" value="1" @checked(old('is_gift'))>
                                <label class="form-check-label fw-bold text-primary" for="is_gift"><i class="bi bi-gift-fill me-1"></i> هذا الطلب عبارة عن هدية</label>
                            </div>

                            <div id="gift_fields" class="d-none">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="small fw-bold text-muted mb-2">اسم المستلم</label>
                                        <input type="text" class="form-control form-control-sm" id="gift_recipient_name" name="gift_recipient_name" value="{{ old('gift_recipient_name') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small fw-bold text-muted mb-2">رقم هاتف المستلم</label>
                                        <input type="text" class="form-control form-control-sm" id="gift_recipient_phone" name="gift_recipient_phone" value="{{ old('gift_recipient_phone') }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="small fw-bold text-muted mb-2">عنوان الهدية</label>
                                        <input type="text" class="form-control form-control-sm" id="gift_recipient_address_details" name="gift_recipient_address_details" value="{{ old('gift_recipient_address_details') }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="small fw-bold text-muted mb-2">رسالة الهدية</label>
                                        <textarea class="form-control form-control-sm" id="gift_message" name="gift_message" rows="2">{{ old('gift_message') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-5">
                <h5 class="form-section-title">اختيار المنتجات</h5>
                <div class="product-box shadow-sm">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ابحث عن منتج</label>
                            <select id="product_selector" class="form-select">
                                <option value="">-- اكتب اسم المنتج للبحث --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-stock="{{ $product->stock_quantity }}" data-name="{{ $product->name_ar }}" data-category="{{ $product->category_id }}">
                                        {{ $product->name_ar }} (المتوفر: {{ $product->stock_quantity }}) - {{ number_format($product->price, 0) }} د.ع
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">الكمية</label>
                            <input type="number" id="quantity_selector" class="form-control text-center" value="1" min="1">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold">السعر</label>
                            <input type="number" id="price_selector" class="form-control" step="any">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-dark w-100 py-3 fw-bold rounded-3" id="add_product_btn"><i class="bi bi-plus-lg me-1"></i> إضافة</button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive rounded-4 border overflow-hidden shadow-sm mb-4">
                    <table class="table mb-0 align-middle text-center">
                        <thead class="bg-light border-bottom">
                            <tr class="text-muted small fw-bold">
                                <th class="py-3 text-start ps-4">المنتج</th>
                                <th class="py-3" width="180">سعر البيع</th>
                                <th class="py-3" width="120">الكمية</th>
                                <th class="py-3" width="180">الإجمالي</th>
                                <th class="py-3" width="80"></th>
                            </tr>
                        </thead>
                        <tbody id="order_items_table">
                            {{-- Rows added via JS --}}
                        </tbody>
                    </table>
                </div>

                <div class="row justify-content-end">
                    <div class="col-md-5">
                        <div class="summary-card shadow-sm">
                            <div class="summary-item">
                                <span class="text-muted fw-bold">المجموع الفرعي</span>
                                <span id="subtotal_amount" class="fw-bold">0 د.ع</span>
                            </div>
                            <div class="p-3 border-bottom bg-light bg-opacity-50">
                                <label class="small fw-bold text-muted mb-2 d-block">كود الخصم (إن وجد)</label>
                                <div class="input-group">
                                    <input type="text" id="discount_code_input" name="discount_code" class="form-control form-control-sm border-end-0" style="border-radius:10px 0 0 10px" placeholder="أدخل الكود">
                                    <button class="btn btn-outline-dark btn-sm fw-bold px-3" style="border-radius:0 10px 10px 0" type="button" id="apply_discount_btn">تطبيق</button>
                                </div>
                                <input type="hidden" name="discount_amount" id="discount_amount_hidden" value="0">
                                <small id="discount_feedback" class="d-block mt-1 small"></small>
                            </div>
                            <div class="summary-item">
                                <span class="text-muted fw-bold">قيمة الخصم</span>
                                <span id="discount_amount_display" class="text-danger fw-bold">0 د.ع</span>
                            </div>
                            <div class="summary-item align-items-center">
                                <span class="text-muted fw-bold">أجور الشحن</span>
                                <div class="text-end">
                                    <div id="shipping_cost_display" class="fw-bold mb-1">0 د.ع</div>
                                    @if(\App\Models\Setting::isFreeShippingEnabled())
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input" type="checkbox" id="free_shipping_switch" name="free_shipping">
                                            <label class="form-check-label small text-muted" for="free_shipping_switch">شحن مجاني</label>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="summary-item summary-total py-4">
                                <span>الإجمالي النهائي</span>
                                <span id="total_amount">0 د.ع</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-3 pt-5 border-top">
                <a href="{{ route('admin.orders.index') }}" class="btn btn-light px-5 py-3 rounded-3 fw-bold">إلغاء والعودة</a>
                <button type="submit" class="submit-btn shadow-sm py-3 px-5">حفظ الطلب نهائياً</button>
            </div>
        </form>
    </div>
</div>
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
    const isGiftInput = $('#is_gift');
    const giftFields = $('#gift_fields');
    const giftRecipientNameInput = $('#gift_recipient_name');
    const giftRecipientPhoneInput = $('#gift_recipient_phone');
    const giftRecipientAddressInput = $('#gift_recipient_address_details');
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
        savedAddressesWrapper.addClass('d-none');
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

    function toggleGiftFields() {
        const isGift = isGiftInput.is(':checked');
        if (isGift) giftFields.removeClass('d-none'); else giftFields.addClass('d-none');
        giftRecipientNameInput.prop('required', isGift);
        giftRecipientPhoneInput.prop('required', isGift);
        giftRecipientAddressInput.prop('required', isGift);
    }

    $('#product_selector').on('select2:select', function (e) {
        const selectedOption = $(e.params.data.element);
        $('#price_selector').val(selectedOption.data('price'));
    });

    savedAddressesList.on('change', 'input[name="selected_address_option"]', function () {
        const payload = $(this).data('payload');
        savedAddressesList.find('label').removeClass('border-primary bg-primary bg-opacity-5');
        $(this).closest('label').addClass('border-primary bg-primary bg-opacity-5');
        if (payload) {
            applyAddressFromPayload(payload);
        }
    });

    function renderAddressOptions(addresses) {
        savedAddressesList.empty();

        if (!Array.isArray(addresses) || addresses.length === 0) {
            savedAddressesWrapper.addClass('d-none');
            return;
        }

        if (addresses.length === 1) {
            savedAddressesWrapper.addClass('d-none');
            applyAddressFromPayload(addresses[0]);
            return;
        }

        savedAddressesWrapper.removeClass('d-none');

        addresses.forEach(function (address) {
            const col = $('<div class="col-md-6"></div>');
            const item = $('<label class="d-flex align-items-start gap-3 p-3 bg-white border rounded-4 cursor-pointer h-100 transition-all"></label>');
            item.css('cursor', 'pointer');
            
            const radio = $('<input type="radio" class="form-check-input mt-1" name="selected_address_option">');
            radio.val(address.id || '').data('payload', address);

            const content = $('<div class="text-start flex-grow-1"></div>');

            if (address.is_default) {
                content.append('<span class="badge bg-primary ms-1 mb-1" style="font-size:10px">افتراضي</span>');
            }

            content.append($('<div class="fw-bold small text-dark"></div>').text(address.address_details || 'بدون تفاصيل'));

            if (address.city || address.governorate) {
                content.append(
                    $('<div class="small text-muted mt-1" style="font-size:11px"></div>').text([address.city, address.governorate].filter(Boolean).join(' - '))
                );
            }

            item.append(radio).append(content);
            col.append(item);
            savedAddressesList.append(col);
        });

        const preferred = addresses.find(address => address.is_default) || addresses[0];
        if (preferred) {
            applyAddressFromPayload(preferred);
            savedAddressesList.find(`input[value="${preferred.id}"]`).prop('checked', true);
            savedAddressesList.find(`input[value="${preferred.id}"]`).closest('label').addClass('border-primary bg-primary bg-opacity-5');
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
        savedAddressesWrapper.addClass('d-none');
        savedAddressesList.empty();

        $.get(customerAddressesUrl(customerId))
            .done(function (response) {
                const addresses = response && Array.isArray(response.addresses) ? response.addresses : [];
                const fallback = response && response.fallback ? response.fallback : {};

                if (!addresses.length) {
                    applyAddressFromPayload(fallback);
                    savedAddressesWrapper.addClass('d-none');
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
                <td class="text-start ps-4">
                    <input type="hidden" name="products[${productId}][id]" value="${productId}">
                    <div class="fw-bold text-dark">${productName}</div>
                    <div class="small text-muted" style="font-size:11px">المتوفر: ${stock} قطعة</div>
                </td>
                <td><input type="number" name="products[${productId}][price]" class="form-control item-price text-center fw-bold" value="${productPrice}" min="0" step="any" required></td>
                <td><input type="number" name="products[${productId}][quantity]" class="form-control item-quantity text-center fw-bold" value="${quantity}" min="1" required></td>
                <td class="item-subtotal fw-bold text-dark">${formatNumber(productPrice * quantity)} د.ع</td>
                <td><button type="button" class="btn btn-link text-danger p-0 remove-item" title="حذف"><i class="bi bi-x-circle-fill fs-5"></i></button></td>
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
        if (isBanned === true || isBanned === 'true') {
            bannedCustomerWarning.removeClass('d-none');
        } else {
            bannedCustomerWarning.addClass('d-none');
        }
        fetchCustomerAddresses(customerId);
    });

    const initialCustomerId = $('#customer_id').val();
    if (initialCustomerId) {
        if (!hasAddressDetailsOld) {
            $('#customer_id').trigger('change');
        } else {
            const initialOption = $('#customer_id').find(':selected');
            const isBanned = initialOption.data('banned');
            if (isBanned === true || isBanned === 'true') {
                bannedCustomerWarning.removeClass('d-none');
            } else {
                bannedCustomerWarning.addClass('d-none');
            }
        }
    }

    isGiftInput.on('change', toggleGiftFields);
    toggleGiftFields();
});
</script>
@endpush
