@extends('admin.layout')

@section('title', 'تعديل كود الخصم')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--multiple {
        border: 1px solid #ced4da;
        min-height: 38px;
        padding-top: .25rem;
        padding-bottom: .25rem;
    }
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #86b7fe;
        box-shadow: 0 0 0 .25rem rgba(13,110,253,.25);
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        padding: .2rem .5rem;
        margin-top: .15rem;
    }
</style>
@endpush

@section('content')
<div class="card shadow-sm">
    <div class="card-header">
        <h4 class="mb-0">تعديل كود الخصم: {{ $discount_code->code }}</h4>
    </div>

    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('admin.discount-codes.update', $discount_code->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="code" class="form-label">الكود <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="code" name="code" value="{{ old('code', $discount_code->code) }}" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="type" class="form-label">نوع الخصم <span class="text-danger">*</span></label>
                    <select class="form-select" id="type" name="type" required>
                        <option value="fixed" {{ old('type', $discount_code->type) === 'fixed' ? 'selected' : '' }}>مبلغ ثابت</option>
                        <option value="percentage" {{ old('type', $discount_code->type) === 'percentage' ? 'selected' : '' }}>نسبة مئوية</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="value" class="form-label">قيمة الخصم <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control" id="value" name="value" value="{{ old('value', $discount_code->value) }}" required>
                </div>

                {{-- يظهر فقط عندما النوع نسبة مئوية --}}
                <div class="col-md-6 mb-3" id="max_discount_amount_wrapper" style="display:none;">
                    <label for="max_discount_amount" class="form-label">الحد الأقصى لمبلغ الخصم (د.ع)</label>
                    <input type="number" step="0.01" class="form-control" id="max_discount_amount" name="max_discount_amount" value="{{ old('max_discount_amount', $discount_code->max_discount_amount) }}">
                    <small class="text-muted">يُطبق فقط عند اختيار “نسبة مئوية”.</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="max_uses" class="form-label">أقصى عدد للاستخدام (اختياري)</label>
                    <input type="number" class="form-control" id="max_uses" name="max_uses" value="{{ old('max_uses', $discount_code->max_uses) }}" placeholder="اتركه فارغاً للاستخدام غير المحدود">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="max_uses_per_user" class="form-label">أقصى عدد للاستخدام لكل مستخدم (اختياري)</label>
                    <input type="number" class="form-control" id="max_uses_per_user" name="max_uses_per_user" value="{{ old('max_uses_per_user', $discount_code->max_uses_per_user) }}" placeholder="اتركه فارغاً للاستخدام غير المحدود">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="expires_at" class="form-label">تاريخ الانتهاء (اختياري)</label>
                    <input type="datetime-local" class="form-control" id="expires_at" name="expires_at" value="{{ old('expires_at', $discount_code->expires_at ? $discount_code->expires_at->format('Y-m-d\TH:i') : '') }}" placeholder="اتركه فارغاً ليبقى صالحاً دائماً">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="categories" class="form-label">الأقسام المسموح لها (اختياري)</label>
                    <div class="d-flex gap-2 mb-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="select_all_categories">تحديد الكل</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="clear_all_categories">إلغاء الكل</button>
                    </div>
                    <select multiple class="form-select" id="categories" name="categories[]">
                        @php
                            $selectedCategories = old('categories', $discount_code->categories->pluck('id')->toArray());
                        @endphp
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(in_array($category->id, $selectedCategories))>
                                {{ $category->name_ar }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">اتركها فارغة لتكون صالحة لكل الأقسام (إلا إذا قيّدتها بالمنتجات).</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="products" class="form-label">المنتجات المسموح لها (اختياري)</label>
                    <div class="d-flex gap-2 mb-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="select_all_products">تحديد الكل</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="clear_all_products">إلغاء الكل</button>
                    </div>
                    <select multiple class="form-select" id="products" name="products[]">
                        @php
                            $selectedProducts = old('products', $discount_code->products->pluck('id')->toArray());
                        @endphp
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" @selected(in_array($product->id, $selectedProducts))>
                                {{ $product->name_ar }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">اتركها فارغة لتكون صالحة لكل المنتجات (إلا إذا قيّدتها بالأقسام).</small>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.getElementById('type');
    const maxWrapper = document.getElementById('max_discount_amount_wrapper');

    function toggleMaxField() {
        if (typeSelect.value === 'percentage') {
            maxWrapper.style.display = 'block';
        } else {
            maxWrapper.style.display = 'none';
            // في صفحة التعديل: لا نفرّغ القيمة تلقائياً إلا إذا رغبتِ بذلك
        }
    }

    function initSelect2(selector, placeholder) {
        $(selector).select2({
            width: '100%',
            placeholder,
            allowClear: true,
            closeOnSelect: false,
            dir: 'rtl'
        });
    }
    initSelect2('#categories', 'اختر أقساماً…');
    initSelect2('#products',   'اختر منتجات…');

    // أزرار تحديد/إلغاء الكل
    document.getElementById('select_all_categories')?.addEventListener('click', function(){
        const $el = $('#categories');
        const all = $el.find('option').map(function(){ return this.value; }).get();
        $el.val(all).trigger('change');
    });
    document.getElementById('clear_all_categories')?.addEventListener('click', function(){
        $('#categories').val(null).trigger('change');
    });

    document.getElementById('select_all_products')?.addEventListener('click', function(){
        const $el = $('#products');
        const all = $el.find('option').map(function(){ return this.value; }).get();
        $el.val(all).trigger('change');
    });
    document.getElementById('clear_all_products')?.addEventListener('click', function(){
        $('#products').val(null).trigger('change');
    });

    typeSelect.addEventListener('change', toggleMaxField);
    toggleMaxField();
});
</script>
@endpush
