@extends('admin.layout')

@section('title', 'مسير الرواتب ' . ($payroll->original_period_code ?? $payroll->period_code))

@section('content')
@php(
    $payrollCurrency = $payroll->currency ?? \App\Support\Currency::IQD
)
@php(
    $payrollExchange = $payroll->exchange_rate_used ?? null
)
@php(
    $displayPeriod = $payroll->original_period_code ?? $payroll->period_code
)
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1 d-flex align-items-center gap-2">
            <span>مسير الرواتب لشهر {{ $displayPeriod }}</span>
            @if($payroll->isReverted())
                <span class="badge bg-danger">تم التراجع</span>
            @endif
        </h4>
        <div class="text-muted">تمت المعالجة في {{ $payroll->processed_at->format('Y-m-d H:i') }}</div>
        @if($payroll->isReverted())
            <div class="text-danger small mt-1">
                {{ __('تم التراجع في :time بواسطة :manager', [
                    'time' => optional($payroll->reverted_at)->format('Y-m-d H:i'),
                    'manager' => $payroll->revertor?->name ?? __('غير معروف'),
                ]) }}
            </div>
        @endif
    </div>
    <div class="d-flex flex-column align-items-end gap-2">
        <div class="d-flex gap-2">
            <a href="{{ route('admin.hr.payroll.index') }}" class="btn btn-secondary">عودة للسجلات</a>
            @if(!$payroll->isReverted())
                @can('export-excel')
                    <a href="{{ route('admin.hr.payroll.export', $payroll) }}" class="btn btn-outline-success">تصدير Excel</a>
                @endcan
            @endif
        </div>
        @can('revert_payroll')
            @if(!$payroll->isReverted())
                <form method="POST" action="{{ route('admin.hr.payroll.revert', $payroll) }}" class="d-flex gap-2 align-items-start" data-confirm-message="هل أنت متأكد من التراجع عن مسير الرواتب؟ سيتم حذف القيد المحاسبي المرتبط.">
                    @csrf
                    <textarea name="reason" rows="1" class="form-control form-control-sm" placeholder="سبب التراجع (اختياري)">{{ old('reason') }}</textarea>
                    <button type="submit" class="btn btn-sm btn-danger">تراجع عن المسير</button>
                </form>
            @endif
        @endcan
    </div>
</div>

@if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if($payroll->isReverted())
    <div class="alert alert-warning">
        <strong>تنبيه:</strong> تم التراجع عن هذا المسير ولا يمكن تصديره أو استخدامه في التسويات.
        @if($payroll->revert_reason)
            <div class="mt-2">سبب التراجع: {{ $payroll->revert_reason }}</div>
        @endif
    </div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card text-center shadow-sm">
            <div class="card-body">
                <h6 class="text-muted">إجمالي الرواتب</h6>
                <div class="fs-5 fw-semibold">{{ \App\Support\Currency::formatForCurrency(\App\Support\Currency::convertFromSystem($payroll->total_gross, $payrollCurrency, $payrollExchange), $payrollCurrency) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center shadow-sm">
            <div class="card-body">
                <h6 class="text-muted">إجمالي الخصومات</h6>
                <div class="fs-5 fw-semibold">{{ \App\Support\Currency::formatForCurrency(\App\Support\Currency::convertFromSystem($payroll->total_deductions, $payrollCurrency, $payrollExchange), $payrollCurrency) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center shadow-sm">
            <div class="card-body">
                <h6 class="text-muted">أقساط السلف</h6>
                <div class="fs-5 fw-semibold">{{ \App\Support\Currency::formatForCurrency(\App\Support\Currency::convertFromSystem($payroll->total_loan_installments, $payrollCurrency, $payrollExchange), $payrollCurrency) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center shadow-sm">
            <div class="card-body">
                <h6 class="text-muted">صافي الرواتب</h6>
                <div class="fs-5 fw-semibold text-success">{{ \App\Support\Currency::formatForCurrency(\App\Support\Currency::convertFromSystem($payroll->total_net, $payrollCurrency, $payrollExchange), $payrollCurrency) }}</div>
            </div>
        </div>
    </div>
</div>

@if($payroll->notes)
    <div class="alert alert-info">{{ $payroll->notes }}</div>
@endif

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>الموظف</th>
                    <th class="text-end">الراتب الأساسي</th>
                    <th class="text-end">البدلات</th>
                    <th class="text-end">العمولات</th>
                    <th class="text-end">أقساط السلف</th>
                    <th class="text-end">خصومات إضافية</th>
                    <th class="text-end">صافي الراتب</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payroll->items as $item)
                    @php($baseValue = \App\Support\Currency::convertFromSystem($item->base_salary, $payrollCurrency, $payrollExchange))
                    @php($allowanceValue = \App\Support\Currency::convertFromSystem($item->allowances, $payrollCurrency, $payrollExchange))
                    @php($commissionValue = \App\Support\Currency::convertFromSystem($item->commissions, $payrollCurrency, $payrollExchange))
                    @php($loanValue = \App\Support\Currency::convertFromSystem($item->loan_installments, $payrollCurrency, $payrollExchange))
                    @php($deductionValue = \App\Support\Currency::convertFromSystem($item->deductions, $payrollCurrency, $payrollExchange))
                    @php($netValue = \App\Support\Currency::convertFromSystem($item->net_salary, $payrollCurrency, $payrollExchange))
                    <tr>
                        <td>{{ $item->employee?->name ?? '—' }}</td>
                        <td class="text-end">{{ \App\Support\Currency::formatForCurrency($baseValue, $payrollCurrency) }}</td>
                        <td class="text-end">{{ \App\Support\Currency::formatForCurrency($allowanceValue, $payrollCurrency) }}</td>
                        <td class="text-end">{{ \App\Support\Currency::formatForCurrency($commissionValue, $payrollCurrency) }}</td>
                        <td class="text-end">{{ \App\Support\Currency::formatForCurrency($loanValue, $payrollCurrency) }}</td>
                        <td class="text-end">{{ \App\Support\Currency::formatForCurrency($deductionValue, $payrollCurrency) }}</td>
                        <td class="text-end fw-semibold">{{ \App\Support\Currency::formatForCurrency($netValue, $payrollCurrency) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
