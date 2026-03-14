@extends('admin.layout')

@section('title', 'تفاصيل فاتورة الشراء #' . $purchase->id)

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">تفاصيل فاتورة الشراء #{{ $purchase->id }}</h4>
        <a href="{{ route('admin.purchases.index') }}" class="btn btn-outline-secondary btn-sm">العودة للفواتير</a>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <strong>المورد:</strong> {{ $purchase->supplier->name ?? 'غير محدد' }}<br>
                <strong>تاريخ الفاتورة:</strong> {{ $purchase->invoice_date->format('d-m-Y') }}<br>
                <strong>رقم الفاتورة:</strong> {{ $purchase->invoice_number ?? '-' }}
            </div>
            <div class="col-md-6 text-md-end">
                <h4>الإجمالي: {{ number_format($purchase->total_amount, 2) }} د.ع</h4>
            </div>
        </div>

        <h5>بنود الفاتورة:</h5>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>المنتج</th>
                        <th>الكمية</th>
                        <th>سعر الشراء للقطعة</th>
                        <th>الإجمالي</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchase->items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->product->name_ar ?? 'منتج محذوف' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->purchase_price, 2) }} د.ع</td>
                        <td>{{ number_format($item->quantity * $item->purchase_price, 2) }} د.ع</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($purchase->notes)
        <div class="mt-4">
            <strong>ملاحظات:</strong>
            <p class="mt-2 p-3 bg-light rounded">{{ $purchase->notes }}</p>
        </div>
        @endif
    </div>
</div>
@endsection
