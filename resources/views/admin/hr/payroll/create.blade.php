@extends('admin.layout')

@section('title', 'تشغيل مسير الرواتب')

@section('content')
<h4 class="mb-3">تشغيل مسير الرواتب الشهري (إدخال يدوي)</h4>
<p class="text-muted">قم بتعبئة مبالغ الرواتب والبدلات والخصومات لكل موظف قبل تشغيل المسير. يمكن ترك الحقول بصفر للموظفين غير المشمولين.</p>

<form action="{{ route('admin.hr.payroll.store') }}" method="POST" class="card shadow-sm">
    @csrf
    <div class="card-body">
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label">الشهر</label>
                <input type="month" name="period_month" class="form-control" value="{{ old('period_month', $defaultMonth ?? now()->format('Y-m')) }}" required>
                @error('period_month')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">عملة المسير</label>
                <select name="currency" id="payroll_currency" class="form-select">
                    @foreach($currencyOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('currency', $systemCurrency) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('currency')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">سعر الصرف (د.ع لكل 1 $)</label>
                <input type="number" step="0.01" min="0.01" name="exchange_rate" id="payroll_exchange_rate" class="form-control" value="{{ old('exchange_rate', $exchangeRate) }}">
                <small class="text-muted">يستخدم للتحويل عند اختيار الدولار الأمريكي.</small>
                @error('exchange_rate')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label">ملاحظات (اختياري)</label>
                <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                @error('notes')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
        </div>

        @error('employees')<div class="alert alert-danger">{{ $message }}</div>@enderror

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>الموظف</th>
                        <th class="text-end">الراتب الأساسي</th>
                        <th class="text-end">البدلات</th>
                        <th class="text-end">العمولات</th>
                        <th class="text-end">أقساط السلف</th>
                        <th class="text-end">خصومات إضافية</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $employee->name }}</div>
                                <div class="text-muted small">{{ $employee->phone_number }}</div>
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0" name="employees[{{ $employee->id }}][base_salary]" value="{{ old("employees.{$employee->id}.base_salary", $employee->base_salary) }}" class="form-control text-end">
                                @error("employees.{$employee->id}.base_salary")<div class="text-danger small">{{ $message }}</div>@enderror
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0" name="employees[{{ $employee->id }}][allowances]" value="{{ old("employees.{$employee->id}.allowances", $employee->allowances) }}" class="form-control text-end">
                                @error("employees.{$employee->id}.allowances")<div class="text-danger small">{{ $message }}</div>@enderror
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0" name="employees[{{ $employee->id }}][commissions]" value="{{ old("employees.{$employee->id}.commissions", 0) }}" class="form-control text-end">
                                @error("employees.{$employee->id}.commissions")<div class="text-danger small">{{ $message }}</div>@enderror
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0" name="employees[{{ $employee->id }}][loan_installments]" value="{{ old("employees.{$employee->id}.loan_installments", 0) }}" class="form-control text-end">
                                @error("employees.{$employee->id}.loan_installments")<div class="text-danger small">{{ $message }}</div>@enderror
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0" name="employees[{{ $employee->id }}][deductions]" value="{{ old("employees.{$employee->id}.deductions", 0) }}" class="form-control text-end">
                                @error("employees.{$employee->id}.deductions")<div class="text-danger small">{{ $message }}</div>@enderror
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">لا يوجد موظفون مسجلون حالياً.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-end">
        <button type="submit" class="btn btn-primary">تشغيل المسير</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const currencySelect = document.getElementById('payroll_currency');
        const exchangeRateInput = document.getElementById('payroll_exchange_rate');

        function toggleExchangeRate() {
            const isUsd = currencySelect.value === 'USD';
            exchangeRateInput.disabled = !isUsd;
            exchangeRateInput.classList.toggle('opacity-75', !isUsd);
        }

        currencySelect?.addEventListener('change', toggleExchangeRate);
        toggleExchangeRate();
    });
</script>
@endpush
