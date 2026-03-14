@extends('admin.layout')

@section('title', 'تعديل شحنة تصنيع')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">تعديل شحنة أمر {{ $shipment->order?->reference }}</h4>
    <a href="{{ route('admin.manufacturing.shipments.index') }}" class="btn btn-outline-secondary">عودة للشحنات</a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card shadow-sm">
    <div class="card-body">
        <form action="{{ route('admin.manufacturing.shipments.update', $shipment) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">أمر التصنيع <span class="text-danger">*</span></label>
                    <select name="order_id" class="form-select" required>
                        <option value="" disabled>اختر أمراً...</option>
                        @foreach($orders as $order)
                            <option value="{{ $order->id }}" @selected(old('order_id', $shipment->order_id) == $order->id)>{{ $order->reference }} - {{ $order->product?->name_ar }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">المخزن <span class="text-danger">*</span></label>
                    <select name="warehouse_id" class="form-select" required>
                        <option value="" disabled>اختر مخزناً...</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected(old('warehouse_id', $shipment->warehouse_id) == $warehouse->id)>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">رقم التتبع</label>
                    <input type="text" name="tracking_number" class="form-control" value="{{ old('tracking_number', $shipment->tracking_number) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">الكمية المشحونة <span class="text-danger">*</span></label>
                    <input type="number" min="0" name="shipped_quantity" class="form-control" value="{{ old('shipped_quantity', $shipment->shipped_quantity) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">تاريخ الشحن <span class="text-danger">*</span></label>
                    <input type="date" name="shipped_at" class="form-control" value="{{ old('shipped_at', optional($shipment->shipped_at)->format('Y-m-d')) }}" required>
                </div>
                <div class="col-12">
                    <label class="form-label">ملاحظات</label>
                    <textarea name="notes" rows="3" class="form-control">{{ old('notes', $shipment->notes) }}</textarea>
                </div>
            </div>
            <div class="mt-4 d-flex justify-content-end gap-2">
                <a href="{{ route('admin.manufacturing.shipments.index') }}" class="btn btn-light">إلغاء</a>
                <button type="submit" class="btn btn-primary">تحديث الشحنة</button>
            </div>
        </form>
    </div>
</div>
@endsection
