@extends('admin.layout')

@section('title', 'سندات السماح')

@php
    $sortOptions = [
        'route' => 'admin.finance.allowance-vouchers.index',
        'allowed' => $allowedSorts ?? [],
        'default_column' => $defaultSortColumn ?? 'voucher_date',
        'default_direction' => $defaultSortDirection ?? 'desc',
    ];
    $sortClass = \App\Support\Sort::class;
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 mb-0">سندات السماح</h1>
    @can('create-allowance-voucher')
    <a href="{{ route('admin.finance.allowance-vouchers.create') }}" class="btn btn-primary">
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
                        <th>{!! $sortClass::link('number', 'الرقم', $sortOptions) !!}</th>
                        <th>{!! $sortClass::link('voucher_date', 'التاريخ', $sortOptions) !!}</th>
                        <th>العميل</th>
                        <th>{!! $sortClass::link('type', 'نوع السند', $sortOptions) !!}</th>
                        <th class="text-end">{!! $sortClass::link('amount', 'المبلغ', $sortOptions) !!}</th>
                        <th>المسؤول</th>
                        <th>الوصف</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vouchers as $voucher)
                        <tr>
                            <td>{{ $voucher->number }}</td>
                            <td>{{ optional($voucher->voucher_date)->format('Y-m-d H:i') ?? '—' }}</td>
                            <td>{{ optional($voucher->customer)->name }}</td>
                            <td>{{ \App\Models\AllowanceVoucher::typeLabels()[$voucher->type] ?? $voucher->type }}</td>
                            <td class="text-end">{{ number_format($voucher->amount, 2) }}</td>
                            <td>{{ optional($voucher->manager)->name ?? '—' }}</td>
                            <td>{{ $voucher->description ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">لا توجد سندات مسجلة</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $vouchers->links() }}
    </div>
</div>
@endsection
