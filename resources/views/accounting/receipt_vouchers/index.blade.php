@extends('admin.layout')

@section('title', 'سندات القبض')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 mb-0">سندات القبض</h1>
    @can('create-receipt-voucher')
    <a href="{{ route('admin.accounting.receipt-vouchers.create') }}" class="btn btn-primary">
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
                        <th>جهة الاستلام</th>
                        <th>الحساب المقابل</th>
                        <th>العميل</th>
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
                            <td>
                                @if($voucher->receiver_type === 'sales_rep')
                                    <span class="badge bg-info text-dark me-2">مندوب</span>
                                    {{ optional($voucher->collector)->name ?? '—' }}
                                @else
                                    <span class="badge bg-secondary me-2">قاصة/بنك</span>
                                    {{ optional($voucher->cashAccount)->name ?? '—' }}
                                @endif
                            </td>
                            <td>{{ $voucher->account->name }}</td>
                            <td>{{ optional($voucher->customer)->name }}</td>
                            <td>{{ number_format($voucher->currency_amount, 2) }} {{ $voucher->currency_code }}</td>
                            <td>{{ number_format($voucher->amount, 2) }} IQD</td>
                            <td>{{ optional($voucher->manager)->name }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">لا توجد سندات</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $vouchers->links() }}
    </div>
</div>
@endsection
