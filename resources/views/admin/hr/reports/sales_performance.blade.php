@extends('admin.layout')

@section('title', 'تقرير أداء المبيعات للموظفين')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">تقرير أداء المبيعات</h4>
    <div>
        @can('export-excel')
            <a href="{{ route('admin.hr.reports.sales-performance', array_merge(request()->all(), ['export' => 1])) }}" class="btn btn-outline-success">تصدير Excel</a>
        @endcan
    </div>
</div>

<form method="GET" class="card shadow-sm mb-4">
    <div class="card-body row g-3">
        <div class="col-md-4">
            <label class="form-label">من تاريخ</label>
            <input type="date" name="from" value="{{ $filters['from'] }}" class="form-control">
        </div>
        <div class="col-md-4">
            <label class="form-label">إلى تاريخ</label>
            <input type="date" name="to" value="{{ $filters['to'] }}" class="form-control">
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button class="btn btn-primary w-100">تحديث التقرير</button>
        </div>
    </div>
</form>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>الموظف</th>
                    <th class="text-end">عدد الطلبات المسلمة</th>
                    <th class="text-end">إجمالي المبيعات</th>
                    <th class="text-end">إجمالي العمولات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>{{ $row['employee']->name ?? '—' }}</td>
                        <td class="text-end">{{ $row['orders_count'] }}</td>
                        <td class="text-end">{{ number_format($row['total_sales'], 2) }} د.ع</td>
                        <td class="text-end">{{ number_format($row['total_commissions'], 2) }} د.ع</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-4">لا توجد نتائج ضمن الفترة المحددة.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
