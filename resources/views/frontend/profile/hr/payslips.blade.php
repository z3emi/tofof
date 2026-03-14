@extends('frontend.profile.layout')
@section('title', 'قسائم الرواتب')

@section('profile-content')
<div class="surface">
    <div class="page-head mb-3">
        <h2 class="text-2xl font-bold">قسائم الرواتب</h2>
        <p class="text-muted">يمكنك الإطلاع على تفاصيل الرواتب الشهرية الخاصة بك.</p>
    </div>

    @if($payslips->isEmpty())
        <div class="alert alert-info">لم يتم إصدار أي قسائم رواتب لك حتى الآن.</div>
    @else
        <div class="card">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>الشهر</th>
                            <th class="text-end">الراتب الأساسي</th>
                            <th class="text-end">البدلات</th>
                            <th class="text-end">العمولات</th>
                            <th class="text-end">أقساط السلف</th>
                            <th class="text-end">خصومات</th>
                            <th class="text-end">الصافي</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payslips as $item)
                            <tr>
                                <td>{{ optional($item->payroll)->period_code }}</td>
                                <td class="text-end">{{ number_format($item->base_salary, 2) }} د.ع</td>
                                <td class="text-end">{{ number_format($item->allowances, 2) }} د.ع</td>
                                <td class="text-end">{{ number_format($item->commissions, 2) }} د.ع</td>
                                <td class="text-end">{{ number_format($item->loan_installments, 2) }} د.ع</td>
                                <td class="text-end">{{ number_format($item->deductions, 2) }} د.ع</td>
                                <td class="text-end fw-semibold">{{ number_format($item->net_salary, 2) }} د.ع</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $payslips->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
