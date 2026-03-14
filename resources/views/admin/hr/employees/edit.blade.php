@extends('admin.layout')

@section('title', 'تعديل بيانات الموظف ' . $employee->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">تعديل بيانات {{ $employee->name }}</h4>
    <a href="{{ route('admin.hr.employees.index') }}" class="btn btn-secondary">عودة للقائمة</a>
</div>

@if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

@if(!empty($missingFields))
    <div class="alert alert-warning d-flex align-items-start gap-3">
        <i class="bi bi-exclamation-triangle-fill fs-4"></i>
        <div>
            <strong>ملف الموظف غير مكتمل.</strong>
            <div class="small">يرجى استكمال الحقول التالية لضمان اكتمال البيانات:</div>
            <ul class="mb-0 mt-2 small">
                @foreach($missingFields as $field)
                    <li>{{ $field }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<form method="POST" action="{{ route('admin.hr.employees.update', $employee) }}" enctype="multipart/form-data" class="card shadow-sm">
    @csrf
    @method('PUT')
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12">
                <h5 class="border-bottom pb-2 mb-3">البيانات الشخصية والتواصل</h5>
            </div>
            <div class="col-md-4">
                <label class="form-label">الاسم الكامل</label>
                <input type="text" name="name" value="{{ old('name', $employee->name) }}" class="form-control" required>
                @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">البريد الإلكتروني</label>
                <input type="email" name="email" value="{{ old('email', $employee->email) }}" class="form-control">
                @error('email')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">الجنسية</label>
                <input type="text" name="nationality" value="{{ old('nationality', $employee->nationality) }}" class="form-control">
                @error('nationality')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">رقم الهاتف الأساسي</label>
                <input type="text" name="phone_number" value="{{ old('phone_number', $employee->phone_number) }}" class="form-control" required>
                @error('phone_number')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">رقم هاتف احتياطي (اختياري)</label>
                <input type="text" name="secondary_phone_number" value="{{ old('secondary_phone_number', $employee->secondary_phone_number) }}" class="form-control">
                @error('secondary_phone_number')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">عنوان السكن</label>
                <input type="text" name="address" value="{{ old('address', $employee->address) }}" class="form-control">
                @error('address')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">رمز PIN لتطبيق التتبع</label>
                <input type="text" name="tracking_pin" value="{{ old('tracking_pin') }}" class="form-control" inputmode="numeric" pattern="\d{4,8}" placeholder="مثال: 123456">
                <small class="text-muted">أدخل رمزاً جديداً من 4 إلى 8 أرقام لتحديث الوصول من أجهزة التتبع.</small>
                @error('tracking_pin')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">إدارة رمز PIN الحالي</label>
                <div class="form-control bg-light">
                    @if($employee->hasTrackingPin())
                        <span class="text-success">رمز PIN مفعل لهذا الموظف.</span>
                    @else
                        <span class="text-muted">لم يتم تعيين رمز PIN بعد.</span>
                    @endif
                </div>
                <div class="form-check mt-2">
                    <input type="hidden" name="reset_tracking_pin" value="0">
                    <input type="checkbox" name="reset_tracking_pin" value="1" id="reset_tracking_pin" class="form-check-input" {{ old('reset_tracking_pin', '0') === '1' ? 'checked' : '' }}>
                    <label class="form-check-label" for="reset_tracking_pin">إزالة رمز PIN الحالي</label>
                </div>
                <small class="text-muted">حدد الخيار لمسح الرمز الحالي ومنع تسجيل الدخول من الأجهزة حتى يتم تعيين رمز جديد.</small>
            </div>
            <div class="col-md-6">
                <label class="form-label">الصورة الشخصية</label>
                <input type="file" name="photo" class="form-control" accept="image/*">
                @if($employee->profile_photo_url)
                    <div class="mt-2 small">
                        <a href="{{ $employee->profile_photo_url }}" target="_blank" class="link-primary">عرض الصورة الحالية</a>
                    </div>
                @endif
                @error('photo')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">صورة بطاقة السكن</label>
                <input type="file" name="housing_card" class="form-control" accept="image/*">
                @if($employee->housing_card_url)
                    <div class="mt-2 small">
                        <a href="{{ $employee->housing_card_url }}" target="_blank" class="link-primary">عرض البطاقة الحالية</a>
                    </div>
                @endif
                @error('housing_card')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">صورة الجنسية</label>
                <input type="file" name="nationality_card" class="form-control" accept="image/*">
                @if($employee->nationality_card_url)
                    <div class="mt-2 small">
                        <a href="{{ $employee->nationality_card_url }}" target="_blank" class="link-primary">عرض الصورة الحالية</a>
                    </div>
                @endif
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
                    <input type="number" step="0.01" min="0" name="base_salary" value="{{ old('base_salary', number_format($displayBaseSalary, 2, '.', '')) }}" class="form-control">
                    <span class="input-group-text" id="base_salary_currency_label">{{ old('salary_currency', $salaryCurrency) === 'USD' ? '$' : 'د.ع' }}</span>
                </div>
                @error('base_salary')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">البدلات</label>
                <div class="input-group">
                    <input type="number" step="0.01" min="0" name="allowances" value="{{ old('allowances', number_format($displayAllowances, 2, '.', '')) }}" class="form-control">
                    <span class="input-group-text" id="allowances_currency_label">{{ old('salary_currency', $salaryCurrency) === 'USD' ? '$' : 'د.ع' }}</span>
                </div>
                @error('allowances')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">نسبة العمولة</label>
                <div class="input-group">
                    <input type="number" step="0.001" min="0" max="1" name="commission_rate" value="{{ old('commission_rate', $employee->commission_rate) }}" class="form-control">
                    <span class="input-group-text">من 0 إلى 1 (مثال: 0.05)</span>
                </div>
                @error('commission_rate')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">المدير المباشر</label>
                <select name="manager_id" class="form-select">
                    <option value="">-- بدون مدير --</option>
                    @foreach($managers as $manager)
                        <option value="{{ $manager->id }}" {{ old('manager_id', $employee->manager_id) == $manager->id ? 'selected' : '' }}>{{ $manager->name }}</option>
                    @endforeach
                </select>
                @error('manager_id')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label">تفاصيل الحساب البنكي</label>
                <textarea name="bank_account_details" rows="4" class="form-control" placeholder="اسم البنك، رقم الحساب، IBAN ...">{{ old('bank_account_details', $employee->bank_account_details) }}</textarea>
                @error('bank_account_details')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-end">
        <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
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
