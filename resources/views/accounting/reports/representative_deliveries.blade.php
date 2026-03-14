@extends('admin.layout')

@section('title', 'تقرير تسليمات المندوبين')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">تقرير تسليمات المندوبين للمحاسب</h1>
        <p class="text-muted small mb-0">يعرض التحويلات الداخلية التي تم فيها تسليم النقد من المندوبين إلى الصندوق.</p>
    </div>
    <a href="{{ request()->fullUrlWithQuery(['export' => 1]) }}" class="btn btn-success" @cannot('export-excel') disabled @endcannot>
        <i class="bi bi-file-earmark-excel"></i> Excel
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form class="row g-3">
            <div class="col-md-4">
                <label class="form-label">المندوب</label>
                <select name="manager_id" class="form-select">
                    <option value="">جميع المندوبين</option>
                    @foreach($managers as $manager)
                        <option value="{{ $manager->id }}" @selected($selectedManager == $manager->id)>{{ $manager->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">من تاريخ</label>
                <input type="date" name="from" value="{{ $from ?? '' }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">إلى تاريخ</label>
                <input type="date" name="to" value="{{ $to ?? '' }}" class="form-control">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">تطبيق</button>
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
                        <th>المندوب</th>
                        <th>الصندوق المستقبل</th>
                        <th>المبلغ (IQD)</th>
                        <th>المبلغ بالعملة</th>
                        <th>أنشئ بواسطة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>{{ $row['date'] }}</td>
                            <td>{{ $row['reference'] }}</td>
                            <td>{{ $row['manager'] ?? '—' }}</td>
                            <td>{{ $row['cash_account'] ?? '—' }}</td>
                            <td>{{ number_format($row['amount'], 2) }}</td>
                            <td>{{ number_format($row['currency_amount'], 2) }} {{ $row['currency_code'] }}</td>
                            <td>{{ $row['created_by'] ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">لا توجد تسليمات ضمن المعايير الحالية.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-end">إجمالي التسليمات</th>
                        <th>{{ number_format($total, 2) }} IQD</th>
                        <th colspan="2"></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
