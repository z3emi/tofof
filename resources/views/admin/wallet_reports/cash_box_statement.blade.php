@extends('admin.layout')

@section('title', __('كشف حركة الصندوق'))

@section('content')
<h1 class="h3 mb-4">{{ __('كشف حركة الصندوق') }}</h1>

<form method="GET" class="card mb-4">
    <div class="card-body row g-3">
        <div class="col-md-4">
            <label class="form-label">{{ __('الصندوق') }}</label>
            <select name="cash_box_id" class="form-select" required>
                <option value="">-- {{ __('اختر الصندوق') }} --</option>
                @foreach($cashBoxes as $cashBox)
                    <option value="{{ $cashBox->id }}" @selected(request('cash_box_id') == $cashBox->id)>
                        {{ $cashBox->name }} ({{ number_format($cashBox->balance, 2) }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">{{ __('من تاريخ') }}</label>
            <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label">{{ __('إلى تاريخ') }}</label>
            <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control">
        </div>
    </div>
    <div class="card-footer text-end">
        <button class="btn btn-primary">{{ __('عرض التقرير') }}</button>
    </div>
</form>

@if($selectedCashBox)
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <div>
            <h2 class="h5 mb-0">{{ $selectedCashBox->name }}</h2>
            <span class="text-muted">{{ __('الرصيد الحالي') }}: <strong>{{ number_format($selectedCashBox->balance, 2) }}</strong></span>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>{{ __('التاريخ') }}</th>
                    <th>{{ __('الوصف') }}</th>
                    <th>{{ __('نوع الحركة') }}</th>
                    <th>{{ __('إيداع (+)') }}</th>
                    <th>{{ __('سحب (-)') }}</th>
                    <th>{{ __('الرصيد بعد') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $transaction)
                    <tr>
                        <td>{{ optional($transaction->transaction_date)->format('Y-m-d H:i') }}</td>
                        <td>{{ $transaction->description ?? '—' }}</td>
                        <td>
                            @if($transaction->type === 'credit')
                                <span class="badge bg-success">{{ __('زيادة في الصندوق') }}</span>
                            @else
                                <span class="badge bg-danger">{{ __('سحب من الصندوق') }}</span>
                            @endif
                        </td>
                        <td>{{ $transaction->type === 'credit' ? number_format($transaction->amount, 2) : '-' }}</td>
                        <td>{{ $transaction->type === 'debit' ? number_format($transaction->amount, 2) : '-' }}</td>
                        <td>{{ number_format($transaction->balance_after, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">{{ __('لا توجد حركات في الفترة المحددة.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
