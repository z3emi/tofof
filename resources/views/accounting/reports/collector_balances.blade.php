@extends('admin.layout')

@section('title', 'تقرير أرصدة عهد المندوبين')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">تقرير أرصدة عهد المندوبين</h1>
        <p class="text-muted small mb-0">يوضح إجمالي المبالغ المستلمة من العملاء والموجودة لدى كل مندوب.</p>
    </div>
    <a href="{{ request()->fullUrlWithQuery(['export' => 1]) }}" class="btn btn-success" @cannot('export-excel') disabled @endcannot>
        <i class="bi bi-file-earmark-excel"></i> Excel
    </a>
</div>

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
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">تحديث</button>
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
                        <th>المندوب</th>
                        <th>الحساب المحاسبي</th>
                        <th class="text-end">الرصيد (IQD)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>{{ $row['manager']->name }}</td>
                            <td>
                                @php($account = optional($row['manager']->salesRepresentativeAccount))
                                {{ $account ? $account->code . ' - ' . $account->name : '—' }}
                            </td>
                            <td class="text-end fw-semibold {{ $row['balance'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($row['balance'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">لا توجد حركات ضمن الفترة المحددة.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2" class="text-end">إجمالي العهد</th>
                        <th class="text-end">{{ number_format($total, 2) }} IQD</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
