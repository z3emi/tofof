@extends('admin.layout')

@section('title', 'مسير الرواتب')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">سجلات مسير الرواتب</h4>
    @can('process_payroll')
        <a href="{{ route('admin.hr.payroll.create') }}" class="btn btn-primary">تشغيل مسير جديد</a>
    @endcan
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>الفترة</th>
                    <th>تاريخ المعالجة</th>
                    <th class="text-end">صافي الرواتب</th>
                    <th class="text-end">عدد الموظفين</th>
                    <th class="text-center">الحالة</th>
                    <th class="text-end">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payrolls as $payroll)
                    @php(
                        $payrollCurrency = $payroll->currency ?? \App\Support\Currency::IQD
                    )
                    @php(
                        $payrollExchange = $payroll->exchange_rate_used ?? null
                    )
                    @php(
                        $displayNet = \App\Support\Currency::convertFromSystem($payroll->total_net, $payrollCurrency, $payrollExchange)
                    )
                    @php(
                        $displayPeriod = $payroll->original_period_code ?? $payroll->period_start->format('Y-m')
                    )
                    <tr data-dblclick-url="{{ route('admin.hr.payroll.show', $payroll) }}"
                        data-dblclick-label="عرض تفاصيل مسير رواتب الفترة {{ $payroll->period_start->format('Y-m') }}">
                        <td>{{ $displayPeriod }}</td>
                        <td>{{ $payroll->processed_at->format('Y-m-d H:i') }}</td>
                        <td class="text-end">{{ \App\Support\Currency::formatForCurrency($displayNet, $payrollCurrency) }}</td>
                        <td class="text-end">{{ $payroll->items_count }}</td>
                        <td class="text-center">
                            @if($payroll->isReverted())
                                <span class="badge bg-danger">تم التراجع</span>
                            @else
                                <span class="badge bg-success">ساري</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.hr.payroll.show', $payroll) }}" class="btn btn-sm btn-outline-primary">عرض</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">لم يتم تشغيل مسير رواتب بعد.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $payrolls->links() }}
    </div>
</div>
@endsection
