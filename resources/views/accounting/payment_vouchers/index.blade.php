@extends('admin.layout')

@section('title', 'سندات الصرف')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 mb-0">سندات الصرف</h1>
    @can('create-payment-voucher')
    <a href="{{ route('admin.accounting.payment-vouchers.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> سند جديد
    </a>
    @endcan
</div>

@if (session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>الرقم</th>
                        <th>التاريخ</th>
                        <th>الحساب النقدي</th>
                        <th>حساب المصروف</th>
                        <th>المبلغ</th>
                        <th>القيمة بالدينار</th>
                        <th>المسؤول</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vouchers as $voucher)
                        <tr>
                            <td>{{ $voucher->number }}</td>
                            <td>{{ $voucher->voucher_date->format('Y-m-d') }}</td>
                            <td>{{ $voucher->cashAccount->name }}</td>
                            <td>{{ $voucher->expenseAccount->name }}</td>
                            <td>{{ number_format($voucher->currency_amount, 2) }} {{ $voucher->currency_code }}</td>
                            <td>{{ number_format($voucher->amount, 2) }} IQD</td>
                            <td>{{ optional($voucher->manager)->name }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">لا توجد سندات</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $vouchers->links() }}
    </div>
</div>
@endsection
