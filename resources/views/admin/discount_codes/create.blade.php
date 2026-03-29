@extends('admin.layout')

@section('title', 'إضافة كود خصم')

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
        <h2 class="mb-2 fw-bold text-white"><i class="bi bi-percent me-2"></i> إضافة كود خصم جديد</h2>
        <p class="mb-0 opacity-75 fs-6 text-white small">قم بإنشاء كوبونات ترويجية لتشجيع المبيعات وزيادة الولاء.</p>
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

        <form action="{{ route('admin.discount-codes.store') }}" method="POST">
            @csrf

            <div class="mb-5">
                <h5 class="form-section-title">إعدادات الكود الأساسية</h5>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="code" class="form-label fw-bold small">رمز كود الخصم <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" style="border-radius:12px; padding:0.8rem" id="code" name="code" value="{{ old('code') }}" placeholder="مثال: SAVE20" required>
                    </div>
                    <div class="col-md-6">
                        <label for="type" class="form-label fw-bold small">نوع الخصم <span class="text-danger">*</span></label>
                        <select class="form-select" style="border-radius:12px; padding:0.8rem" id="type" name="type" required>
                            <option value="fixed" @selected(old('type') == 'fixed')>مبلغ ثابت (د.ع)</option>
                            <option value="percentage" @selected(old('type') == 'percentage')>نسبة مئوية (%)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="value" class="form-label fw-bold small">قيمة الخصم <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" style="border-radius:12px; padding:0.8rem" id="value" name="value" value="{{ old('value') }}" placeholder="أدخل القيمة الرقمية" required>
                    </div>
                    <div class="col-md-6" id="max_discount_amount_wrapper" style="display:none;">
                        <label for="max_discount_amount" class="form-label fw-bold small">الحد الأقصى لمبلغ الخصم (د.ع)</label>
                        <input type="number" step="0.01" class="form-control" style="border-radius:12px; padding:0.8rem" id="max_discount_amount" name="max_discount_amount" value="{{ old('max_discount_amount') }}" placeholder="اختياري">
                        <small class="text-muted small">يُطبق فقط عند اختيار "نسبة مئوية".</small>
                    </div>
                </div>
            </div>

            <div class="mb-5">
                <h5 class="form-section-title">قيود الاستخدام والصلاحية</h5>
                <div class="row g-4">
                    <div class="col-md-4">
                        <label for="max_uses" class="form-label fw-bold small">أقصى عدد للاستخدام (كلياً)</label>
                        <input type="number" class="form-control" style="border-radius:12px; padding:0.8rem" id="max_uses" name="max_uses" value="{{ old('max_uses') }}" placeholder="بلا حدود">
                    </div>
                    <div class="col-md-4">
                        <label for="max_uses_per_user" class="form-label fw-bold small">أقصى استخدام للمستخدم الواحد</label>
                        <input type="number" class="form-control" style="border-radius:12px; padding:0.8rem" id="max_uses_per_user" name="max_uses_per_user" value="{{ old('max_uses_per_user') }}" placeholder="بلا حدود">
                    </div>
                    <div class="col-md-4">
                        <label for="expires_at" class="form-label fw-bold small">تاريخ ووقت الانتهاء (اختياري)</label>
                        <input type="datetime-local" class="form-control" style="border-radius:12px; padding:0.8rem" id="expires_at" name="expires_at" value="{{ old('expires_at') }}">
                    </div>
                </div>
            </div>

            <div class="mb-5">
                <h5 class="form-section-title">تخصيص الشمولية</h5>
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label for="categories" class="form-label fw-bold small mb-0">الأقسام المشمولة</label>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary" id="select_all_categories">الكل</button>
                                <button type="button" class="btn btn-outline-secondary" id="clear_all_categories">مسح</button>
                            </div>
                        </div>
                        <select multiple class="form-select select2-categories" id="categories" name="categories[]">
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(in_array($category->id, old('categories', [])))>
                                    {{ $category->name_ar }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label for="products" class="form-label fw-bold small mb-0">منتجات محددة</label>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary" id="select_all_products">الكل</button>
                                <button type="button" class="btn btn-outline-secondary" id="clear_all_products">مسح</button>
                            </div>
                        </div>
                        <select multiple class="form-select select2-products" id="products" name="products[]">
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" @selected(in_array($product->id, old('products', [])))>
                                    {{ $product->name_ar }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-3 pt-5 border-top">
                <a href="{{ route('admin.discount-codes.index') }}" class="btn btn-light px-5 py-3 rounded-3 fw-bold">إلغاء</a>
                <button type="submit" class="submit-btn shadow-sm py-3 px-5">إنشاء وحفظ الكود</button>
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
    const maxWrapper = document.getElementById('max_discount_amount_wrapper');
    const toggleField = () => maxWrapper.style.display = (typeSelect.value === 'percentage' ? 'block' : 'none');
    typeSelect.addEventListener('change', toggleField);
    toggleField();

    $('.select2-categories').select2({ width: '100%', dir: 'rtl', placeholder: 'اختر الأقسام...' });
    $('.select2-products').select2({ width: '100%', dir: 'rtl', placeholder: 'اختر المنتجات...' });

    $('#select_all_categories').on('click', () => { $('#categories').val($('#categories option').map(function(){ return this.value; }).get()).trigger('change'); });
    $('#clear_all_categories').on('click', () => { $('#categories').val(null).trigger('change'); });
    $('#select_all_products').on('click', () => { $('#products').val($('#products option').map(function(){ return this.value; }).get()).trigger('change'); });
    $('#clear_all_products').on('click', () => { $('#products').val(null).trigger('change'); });
});
</script>
@endpush
@endsection
