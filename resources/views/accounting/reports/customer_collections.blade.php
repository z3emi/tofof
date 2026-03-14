@extends('admin.layout')

@section('title', 'تقرير تحصيلات العملاء')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h4 mb-1">تقرير تحصيلات العملاء</h1>
        <p class="text-muted small mb-0">تابع السندات المحصلة من العملاء سواء نقداً أو عن طريق المندوبين.</p>
    </div>
    <a href="{{ request()->fullUrlWithQuery(['export' => 1]) }}" class="btn btn-success" @cannot('export-excel') disabled @endcannot>
        <i class="bi bi-file-earmark-excel"></i> Excel
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form class="row g-3">
            <div class="col-md-4">
                <label class="form-label">العميل</label>
                <select name="customer_id" class="form-select">
                    <option value="">جميع العملاء</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" @selected($selectedCustomer == $customer->id)>
                            {{ $customer->display_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">المسؤول / المندوب</label>
                <select name="manager_id" class="form-select">
                    <option value="">الكل</option>
                    @foreach($managers as $manager)
                        <option value="{{ $manager->id }}" @selected($selectedManager == $manager->id)>
                            {{ $manager->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">من تاريخ</label>
                <input type="date" name="from" value="{{ $from ?? '' }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">إلى تاريخ</label>
                <input type="date" name="to" value="{{ $to ?? '' }}" class="form-control">
            </div>
            <div class="col-12 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">تطبيق الفلاتر</button>
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
                        <th>رقم السند</th>
                        <th>العميل</th>
                        <th>نوع الاستلام</th>
                        <th>المسؤول</th>
                        <th>المستلم</th>
                        <th>المبلغ (IQD)</th>
                        <th>المبلغ بالعملة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>{{ $row['date'] }}</td>
                            <td>{{ $row['number'] }}</td>
                            <td>{{ $row['customer'] ?? '—' }}</td>
                            <td>
                                @if($row['receiver_type'] === 'sales_rep')
                                    <span class="badge bg-info text-dark">مندوب</span>
                                @else
                                    <span class="badge bg-secondary">مباشر</span>
                                @endif
                            </td>
                            <td>{{ $row['manager'] ?? '—' }}</td>
                            <td>{{ $row['collector'] ?? $row['cash_account'] ?? '—' }}</td>
                            <td>{{ number_format($row['amount'], 2) }}</td>
                            <td>{{ number_format($row['currency_amount'], 2) }} {{ $row['currency_code'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">لا توجد تحصيلات ضمن المعايير الحالية.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="6" class="text-end">إجمالي المبالغ</th>
                        <th>{{ number_format($totals['amount'], 2) }} IQD</th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
