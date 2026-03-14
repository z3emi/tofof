@extends('admin.layout')

@section('title', 'تحويلات داخلية')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">التحويلات الداخلية</h1>
        <p class="text-muted small mb-0">سجل تحركات الأموال بين القاصات والمندوبين المعتمدين.</p>
    </div>
    @can('create-internal-transfer')
        <a href="{{ route('admin.accounting.internal-transfers.create') }}" class="btn btn-primary">
            <i class="bi bi-arrow-left-right"></i> تحويل جديد
        </a>
    @endcan
</div>

@if (session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

<div class="card">
    <div class="card-body">
        <div id="internalTransfersToolbar" class="d-flex align-items-center justify-content-end mb-3"></div>
        <div class="table-responsive">
            <table class="table table-striped align-middle" data-table-toolbar data-toolbar-target="#internalTransfersToolbar">
                <thead>
                    <tr>
                        <th>المرجع</th>
                        <th>التاريخ</th>
                        <th>الجهة المحولة</th>
                        <th>الجهة المستلمة</th>
                        <th>المبلغ</th>
                        <th data-default-hidden="true">ما يعادلها بالنظام</th>
                        <th data-default-hidden="true">المسؤول</th>
                        <th data-default-hidden="true">أنشئ بواسطة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transfers as $transfer)
                        <tr>
                            <td class="fw-semibold">
                                {{ $transfer->reference }}
                                @if($transfer->journalEntry)
                                    <div class="text-muted small">{{ __('قيد :ref', ['ref' => $transfer->journalEntry->reference ?? '—']) }}</div>
                                @endif
                            </td>
                            <td>{{ optional($transfer->transfer_date)->format('Y-m-d') }}</td>
                            <td>
                                <span class="badge bg-secondary-subtle text-body me-2">{{ $transfer->source_type_label }}</span>
                                {{ $transfer->source_label }}
                            </td>
                            <td>
                                <span class="badge bg-success-subtle text-body me-2">{{ $transfer->destination_type_label }}</span>
                                {{ $transfer->destination_label }}
                            </td>
                            <td>{{ $transfer->currency_amount_formatted }}</td>
                            <td>{{ $transfer->system_amount_formatted }}</td>
                            <td>{{ optional($transfer->manager)->name ?? '—' }}</td>
                            <td>{{ optional($transfer->creator)->name ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">لا توجد تحويلات مسجلة بعد.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $transfers->links() }}
    </div>
</div>
@endsection
