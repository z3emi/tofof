@extends('admin.layout')

@section('title', 'تعديل كود الخصم: ' . $discount_code->code)

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem; color: white; border-radius: 0 !important; }
    .form-section-title { font-weight: 700; color: var(--primary-dark); border-right: 4px solid var(--accent-gold); padding-right: 15px; margin-bottom: 2rem; }
    .select2-container--default .select2-selection--multiple { border-radius: 12px; border: 1px solid #e0e0e0; padding: 0.3rem; }
    .submit-btn { background: var(--primary-dark); padding: 1rem 3rem; border-radius: 10px; font-weight: 700; color: white; border: none; }
</style>
@endpush

@section('content')
<div class="form-card">
    <div class="form-card-header">
        <h2 class="mb-2 fw-bold text-white"><i class="bi bi-percent me-2"></i> تعديل كود الخصم: {{ $discount_code->code }}</h2>
        <p class="mb-0 opacity-75 fs-6 text-white small">قم بتحديث شروط الاستخدام أو القيمة المالية للكوبون المختار.</p>
    </div>
    
    <div class="p-4 p-lg-5">
        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.discount-codes.update', $discount_code->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-5">
                <h5 class="form-section-title">إعدادات الكود الأساسية</h5>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="code" class="form-label fw-bold small">رمز كود الخصم <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" style="border-radius:12px; padding:0.8rem" id="code" name="code" value="{{ old('code', $discount_code->code) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="type" class="form-label fw-bold small">نوع الخصم <span class="text-danger">*</span></label>
                        <select class="form-select" style="border-radius:12px; padding:0.8rem" id="type" name="type" required>
                            <option value="fixed" @selected(old('type', $discount_code->type) == 'fixed')>مبلغ ثابت (د.ع)</option>
                            <option value="percentage" @selected(old('type', $discount_code->type) == 'percentage')>نسبة مئوية (%)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="value" class="form-label fw-bold small">قيمة الخصم <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" max="99999999.99" class="form-control" style="border-radius:12px; padding:0.8rem" id="value" name="value" value="{{ old('value', $discount_code->value) }}" required>
                    </div>
                    <div class="col-md-6" id="max_discount_amount_wrapper" style="display:none;">
                        <label for="max_discount_amount" class="form-label fw-bold small">الحد الأقصى لمبلغ الخصم (د.ع)</label>
                        <input type="number" step="0.01" min="0" max="99999999.99" class="form-control" style="border-radius:12px; padding:0.8rem" id="max_discount_amount" name="max_discount_amount" value="{{ old('max_discount_amount', $discount_code->max_discount_amount) }}">
                    </div>
                </div>
            </div>

            <div class="mb-5">
                <h5 class="form-section-title">قيود الاستخدام</h5>
                <div class="row g-4">
                    <div class="col-md-4">
                        <label for="max_uses" class="form-label fw-bold small">أقصى عدد للاستخدام (كلياً)</label>
                        <input type="number" class="form-control" style="border-radius:12px; padding:0.8rem" id="max_uses" name="max_uses" value="{{ old('max_uses', $discount_code->max_uses) }}">
                    </div>
                    <div class="col-md-4">
                        <label for="max_uses_per_user" class="form-label fw-bold small">أقصى استخدام للمستخدم الواحد</label>
                        <input type="number" class="form-control" style="border-radius:12px; padding:0.8rem" id="max_uses_per_user" name="max_uses_per_user" value="{{ old('max_uses_per_user', $discount_code->max_uses_per_user) }}">
                    </div>
                    <div class="col-md-4">
                        <label for="expires_at" class="form-label fw-bold small">تاريخ ووقت الانتهاء (اختياري)</label>
                        <input type="datetime-local" class="form-control" style="border-radius:12px; padding:0.8rem" id="expires_at" name="expires_at" value="{{ old('expires_at', $discount_code->expires_at ? $discount_code->expires_at->format('Y-m-d\TH:i') : '') }}">
                    </div>
                </div>
            </div>

            <div class="mb-5">
                <h5 class="form-section-title">الاستهداف وشروط الأهلية</h5>
                <div class="row g-4">
                    <div class="col-md-4">
                        <label for="audience_mode" class="form-label fw-bold small">طريقة اختيار المستهدفين</label>
                        <select class="form-select" style="border-radius:12px; padding:0.8rem" id="audience_mode" name="audience_mode">
                            <option value="all" @selected(old('audience_mode', $discount_code->audience_mode ?? 'all') === 'all')>جميع المستخدمين</option>
                            <option value="eligible" @selected(old('audience_mode', $discount_code->audience_mode) === 'eligible')>المستخدمون المطابقون للشروط</option>
                            <option value="selected" @selected(old('audience_mode', $discount_code->audience_mode) === 'selected')>مستخدمون محددون فقط</option>
                        </select>
                    </div>

                    <div class="col-md-4" id="order_count_condition_wrapper">
                        <label for="order_count_operator" class="form-label fw-bold small">شرط عدد الطلبات الموصلة</label>
                        <div class="input-group">
                            <select class="form-select" id="order_count_operator" name="order_count_operator">
                                <option value="">بدون شرط</option>
                                <option value="gte" @selected(old('order_count_operator', $discount_code->order_count_operator) === 'gte')>أكبر من أو يساوي</option>
                                <option value="lte" @selected(old('order_count_operator', $discount_code->order_count_operator) === 'lte')>أقل من أو يساوي</option>
                            </select>
                            <input type="number" min="0" class="form-control" id="order_count_threshold" name="order_count_threshold" value="{{ old('order_count_threshold', $discount_code->order_count_threshold) }}" placeholder="عدد الطلبات">
                        </div>
                    </div>

                    <div class="col-md-4" id="amount_condition_wrapper">
                        <label for="amount_operator" class="form-label fw-bold small">شرط إجمالي مبلغ الطلبات الموصلة</label>
                        <div class="input-group">
                            <select class="form-select" id="amount_operator" name="amount_operator">
                                <option value="">بدون شرط</option>
                                <option value="gte" @selected(old('amount_operator', $discount_code->amount_operator) === 'gte')>أكبر من أو يساوي</option>
                                <option value="lte" @selected(old('amount_operator', $discount_code->amount_operator) === 'lte')>أقل من أو يساوي</option>
                            </select>
                            <input type="number" min="0" step="0.01" class="form-control" id="amount_threshold" name="amount_threshold" value="{{ old('amount_threshold', $discount_code->amount_threshold) }}" placeholder="المبلغ">
                        </div>
                    </div>

                    <div class="col-md-6" id="target_users_wrapper">
                        @php $selUsers = old('users', $discount_code->targetUsers->pluck('id')->toArray()); @endphp
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label for="users" class="form-label fw-bold small mb-0">مستخدمون محددون (الكود يعمل لهم فقط)</label>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary" id="select_all_users">الكل</button>
                                <button type="button" class="btn btn-outline-secondary" id="clear_all_users">مسح</button>
                            </div>
                        </div>
                        <select multiple class="form-select select2-users" id="users" name="users[]">
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" @selected(in_array($user->id, $selUsers))>
                                    {{ $user->name }}{{ $user->phone_number ? ' - ' . $user->phone_number : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>
            </div>

            <div class="mb-5">
                <h5 class="form-section-title">قنوات الإشعار عند الإرسال</h5>
                <div class="row g-4">
                    <div class="col-md-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="notify_via_bell" name="notify_via_bell" value="1" @checked(old('notify_via_bell', $discount_code->notify_via_bell ?? true))>
                            <label class="form-check-label fw-bold" for="notify_via_bell">إشعار الجرس (داخل الموقع)</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="notify_via_push" name="notify_via_push" value="1" @checked(old('notify_via_push', $discount_code->notify_via_push ?? true))>
                            <label class="form-check-label fw-bold" for="notify_via_push">Web Push</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-5">
                <h5 class="form-section-title">الشمولية</h5>
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label for="categories" class="form-label fw-bold small mb-0">الأقسام المشمولة</label>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary" id="select_all_categories">الكل</button>
                                <button type="button" class="btn btn-outline-secondary" id="clear_all_categories">مسح</button>
                            </div>
                        </div>
                        <select multiple class="form-select select2-categories" id="categories" name="categories[]">
                            @php $selCats = old('categories', $discount_code->categories->pluck('id')->toArray()); @endphp
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(in_array($category->id, $selCats))>
                                    {{ $category->name_ar }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label for="products" class="form-label fw-bold small mb-0">منتجات محددة</label>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary" id="select_all_products">الكل</button>
                                <button type="button" class="btn btn-outline-secondary" id="clear_all_products">مسح</button>
                            </div>
                        </div>
                        <select multiple class="form-select select2-products" id="products" name="products[]">
                            @php $selProds = old('products', $discount_code->products->pluck('id')->toArray()); @endphp
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" @selected(in_array($product->id, $selProds))>
                                    {{ $product->name_ar }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        @php $selPrimary = old('primary_categories', $discount_code->targetPrimaryCategories->pluck('id')->toArray()); @endphp
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label for="primary_categories" class="form-label fw-bold small mb-0">البراندات المشمولة (PrimaryCategory)</label>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary" id="select_all_primary_categories">الكل</button>
                                <button type="button" class="btn btn-outline-secondary" id="clear_all_primary_categories">مسح</button>
                            </div>
                        </div>
                        <select multiple class="form-select select2-primary-categories" id="primary_categories" name="primary_categories[]">
                            @foreach($primaryCategories as $primaryCategory)
                                <option value="{{ $primaryCategory->id }}" @selected(in_array($primaryCategory->id, $selPrimary))>
                                    {{ $primaryCategory->name_ar }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-3 pt-5 border-top">
                <a href="{{ route('admin.discount-codes.index') }}" class="btn btn-light px-5 py-3 rounded-3 fw-bold">إلغاء</a>
                <button type="submit" class="submit-btn shadow-sm py-3 px-5">تحديث وحفظ الكود</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.getElementById('type');
    const toggleField = () => document.getElementById('max_discount_amount_wrapper').style.display = (typeSelect.value === 'percentage' ? 'block' : 'none');
    typeSelect.addEventListener('change', toggleField); toggleField();

    $('.select2-categories').select2({ width: '100%', dir: 'rtl', placeholder: 'اختر الأقسام...' });
    $('.select2-products').select2({ width: '100%', dir: 'rtl', placeholder: 'اختر المنتجات...' });
    $('.select2-users').select2({ width: '100%', dir: 'rtl', placeholder: 'اختر المستخدمين...' });
    $('.select2-primary-categories').select2({ width: '100%', dir: 'rtl', placeholder: 'اختر البراندات...' });

    const audienceMode = document.getElementById('audience_mode');
    const usersWrapper = document.getElementById('target_users_wrapper');
    const orderCountConditionWrapper = document.getElementById('order_count_condition_wrapper');
    const amountConditionWrapper = document.getElementById('amount_condition_wrapper');
    const toggleUsersSection = () => {
        usersWrapper.style.display = (audienceMode.value === 'selected' || $('#users').val()?.length) ? 'block' : 'none';
    };

    const toggleEligibilityConditions = () => {
        const shouldHide = audienceMode.value === 'all';
        orderCountConditionWrapper.style.display = shouldHide ? 'none' : '';
        amountConditionWrapper.style.display = shouldHide ? 'none' : '';
    };

    audienceMode.addEventListener('change', () => {
        toggleUsersSection();
        toggleEligibilityConditions();
    });

    toggleUsersSection();
    toggleEligibilityConditions();

    $('#select_all_categories').on('click', () => { $('#categories').val($('#categories option').map(function(){ return this.value; }).get()).trigger('change'); });
    $('#clear_all_categories').on('click', () => { $('#categories').val(null).trigger('change'); });
    $('#select_all_products').on('click', () => { $('#products').val($('#products option').map(function(){ return this.value; }).get()).trigger('change'); });
    $('#clear_all_products').on('click', () => { $('#products').val(null).trigger('change'); });
    $('#select_all_users').on('click', () => { $('#users').val($('#users option').map(function(){ return this.value; }).get()).trigger('change'); toggleUsersSection(); });
    $('#clear_all_users').on('click', () => { $('#users').val(null).trigger('change'); toggleUsersSection(); });
    $('#select_all_primary_categories').on('click', () => { $('#primary_categories').val($('#primary_categories option').map(function(){ return this.value; }).get()).trigger('change'); });
    $('#clear_all_primary_categories').on('click', () => { $('#primary_categories').val(null).trigger('change'); });
});
</script>
@endpush
@endsection
