@extends('admin.layout')

@section('title', 'كشف حساب الصندوق الرئيسي')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">كشف حساب الصندوق الرئيسي</h1>
        <p class="text-muted small mb-0">عرض تفصيلي لحركات الصندوق الرئيسية مع الرصيد التراكمي.</p>
    </div>
    <a href="{{ request()->fullUrlWithQuery(['export' => 1]) }}" class="btn btn-success" @cannot('export-excel') disabled @endcannot>
        <i class="bi bi-file-earmark-excel"></i> Excel
    </a>
</div>

@if(!$account)
    <div class="alert alert-warning">
        لم يتم تعيين حساب نقدي افتراضي بعد. يرجى ربط حساب الصندوق من إعدادات النظام أولاً.
    </div>
@else
    <div class="card mb-4">
        <div class="card-body">
            <form class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">من تاريخ</label>
                    <input type="date" name="from" value="{{ $from ?? '' }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">إلى تاريخ</label>
                    <input type="date" name="to" value="{{ $to ?? '' }}" class="form-control">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">تصفية</button>
                    <span class="text-muted">الحساب: {{ $account->code }} - {{ $account->name }}</span>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>التاريخ</th>
                            <th>المرجع</th>
                            <th>الوصف</th>
                            <th>المدين</th>
                            <th>الدائن</th>
                            <th>الرصيد</th>
                            <th>المسؤول</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td>{{ $row['date'] }}</td>
                                <td>{{ $row['reference'] }}</td>
                                <td>{{ $row['description'] }}</td>
                                <td>{{ number_format($row['debit'], 2) }}</td>
                                <td>{{ number_format($row['credit'], 2) }}</td>
                                <td class="fw-semibold">{{ number_format($row['balance'], 2) }}</td>
                                <td>{{ $row['manager'] ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">لا توجد حركات ضمن الفترة المحددة.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-end">الرصيد الختامي</th>
                            <th>{{ number_format($balance, 2) }} IQD</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endif
@endsection
