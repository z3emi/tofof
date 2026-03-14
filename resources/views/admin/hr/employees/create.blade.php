@extends('admin.layout')

@section('title', 'إضافة موظف جديد')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">إضافة موظف جديد</h4>
    <a href="{{ route('admin.hr.employees.index') }}" class="btn btn-secondary">عودة للقائمة</a>
</div>

<form method="POST" action="{{ route('admin.hr.employees.store') }}" enctype="multipart/form-data" class="card shadow-sm">
    @csrf
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12">
                <h5 class="border-bottom pb-2 mb-3">البيانات الشخصية والتواصل</h5>
            </div>
            <div class="col-md-4">
                <label class="form-label">الاسم الكامل</label>
                <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">البريد الإلكتروني</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control">
                @error('email')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">الجنسية</label>
                <input type="text" name="nationality" value="{{ old('nationality') }}" class="form-control">
                @error('nationality')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">رقم الهاتف الأساسي</label>
                <input type="text" name="phone_number" value="{{ old('phone_number') }}" class="form-control" required>
                @error('phone_number')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">رقم هاتف احتياطي (اختياري)</label>
                <input type="text" name="secondary_phone_number" value="{{ old('secondary_phone_number') }}" class="form-control">
                @error('secondary_phone_number')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">عنوان السكن</label>
                <input type="text" name="address" value="{{ old('address') }}" class="form-control">
                @error('address')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">رمز PIN لتطبيق التتبع</label>
                <input type="text" name="tracking_pin" value="{{ old('tracking_pin') }}" class="form-control" inputmode="numeric" pattern="\d{4,8}" placeholder="مثال: 1234">
                <small class="text-muted">يتكون من 4 إلى 8 أرقام ويُستخدم للدخول من أجهزة التتبع الرسمية.</small>
                @error('tracking_pin')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">الصورة الشخصية</label>
                <input type="file" name="photo" class="form-control" accept="image/*">
                @error('photo')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">صورة بطاقة السكن</label>
                <input type="file" name="housing_card" class="form-control" accept="image/*">
                @error('housing_card')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">صورة الجنسية</label>
                <input type="file" name="nationality_card" class="form-control" accept="image/*">
                @error('nationality_card')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="col-12 pt-3">
                <h5 class="border-bottom pb-2 mb-3">تفاصيل الوظيفة والرواتب</h5>
            </div>
            <div class="col-md-4">
                <label class="form-label">عملة الراتب</label>
                <select name="salary_currency" id="salary_currency" class="form-select">
                    @foreach($salaryCurrencyOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('salary_currency', $salaryCurrency) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('salary_currency')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">الراتب الأساسي</label>
                <div class="input-group">
                    <input type="number" step="0.01" min="0" name="base_salary" value="{{ old('base_salary') }}" class="form-control">
                    <span class="input-group-text" id="base_salary_currency_label">{{ old('salary_currency', $salaryCurrency) === 'USD' ? '$' : 'د.ع' }}</span>
                </div>
                @error('base_salary')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">البدلات</label>
                <div class="input-group">
                    <input type="number" step="0.01" min="0" name="allowances" value="{{ old('allowances') }}" class="form-control">
                    <span class="input-group-text" id="allowances_currency_label">{{ old('salary_currency', $salaryCurrency) === 'USD' ? '$' : 'د.ع' }}</span>
                </div>
                @error('allowances')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">نسبة العمولة</label>
                <div class="input-group">
                    <input type="number" step="0.001" min="0" max="1" name="commission_rate" value="{{ old('commission_rate') }}" class="form-control">
                    <span class="input-group-text">من 0 إلى 1 (مثال: 0.05)</span>
                </div>
                @error('commission_rate')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">المدير المباشر</label>
                <select name="manager_id" class="form-select">
                    <option value="">-- بدون مدير --</option>
                    @foreach($managers as $manager)
                        <option value="{{ $manager->id }}" {{ old('manager_id') == $manager->id ? 'selected' : '' }}>{{ $manager->name }}</option>
                    @endforeach
                </select>
                <small class="text-muted">يمكن ترك الحقل فارغاً في حالة الموظفين بدون مشرف مباشر.</small>
                @error('manager_id')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label">تفاصيل الحساب البنكي</label>
                <textarea name="bank_account_details" rows="4" class="form-control" placeholder="اسم البنك، رقم الحساب، IBAN ...">{{ old('bank_account_details') }}</textarea>
                @error('bank_account_details')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-end">
        <button type="submit" class="btn btn-primary">حفظ الموظف</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const currencySelect = document.getElementById('salary_currency');
        const baseLabel = document.getElementById('base_salary_currency_label');
        const allowancesLabel = document.getElementById('allowances_currency_label');

        function updateLabels() {
            const symbol = currencySelect.value === 'USD' ? '$' : 'د.ع';
            baseLabel.textContent = symbol;
            allowancesLabel.textContent = symbol;
        }

        currencySelect?.addEventListener('change', updateLabels);
        updateLabels();
    });
</script>
@endpush
