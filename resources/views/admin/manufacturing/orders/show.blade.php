@extends('admin.layout')

@section('title', 'تفاصيل أمر التصنيع')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">أمر التصنيع #{{ $order->reference }}</h4>
        <p class="text-muted mb-0">متابعة المكونات والشحنات المرتبطة بالأمر.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.manufacturing.orders.edit', $order) }}" class="btn btn-outline-primary">تعديل</a>
        <a href="{{ route('admin.manufacturing.orders.index') }}" class="btn btn-outline-secondary">عودة للقائمة</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted">المنتج</h6>
                <div class="fw-semibold">{{ $order->product?->name_ar ?? 'منتج محذوف' }}</div>
                <div class="text-muted">{{ $order->variant_name ?? 'بدون متغير' }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted">الكمية</h6>
                <div>المخطط: <span class="fw-semibold">{{ number_format($order->planned_quantity, 0) }}</span></div>
                <div>المكتمل: <span class="fw-semibold">{{ number_format($order->completed_quantity, 0) }}</span></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted">التكلفة الإجمالية</h6>
                @php
                    $statusLabels = [
                        'planned' => 'مخطط',
                        'in_progress' => 'قيد التنفيذ',
                        'completed' => 'مكتمل',
                        'cancelled' => 'ملغى',
                    ];
                    $statusClasses = [
                        'planned' => 'bg-secondary',
                        'in_progress' => 'bg-info',
                        'completed' => 'bg-success',
                        'cancelled' => 'bg-danger',
                    ];
                @endphp
                <div class="fw-semibold">{{ \App\Support\Currency::format($order->total_cost) }}</div>
                <div class="mt-2"><span class="badge {{ $statusClasses[$order->status] ?? 'bg-secondary' }}">{{ $statusLabels[$order->status] ?? $order->status }}</span></div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-light">تفاصيل عامة</div>
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">تاريخ البدء</dt>
            <dd class="col-sm-9">{{ optional($order->starts_at)->format('Y-m-d') ?? '—' }}</dd>

            <dt class="col-sm-3">تاريخ التسليم</dt>
            <dd class="col-sm-9">{{ optional($order->due_at)->format('Y-m-d') ?? '—' }}</dd>

            <dt class="col-sm-3">ملاحظات</dt>
            <dd class="col-sm-9">{{ $order->notes ?: '—' }}</dd>
        </dl>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-light">المواد المستخدمة</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th>المادة الخام</th>
                        <th class="text-end">الكمية</th>
                        <th class="text-end">التكلفة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($order->materials as $material)
                        <tr>
                            <td>{{ $material->material?->name ?? '—' }}</td>
                            <td class="text-end">{{ number_format($material->quantity_used, 3) }}</td>
                            <td class="text-end">{{ \App\Support\Currency::format($material->cost) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center py-4 text-muted">لم تُسجل مواد لهذا الأمر.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-light">الشحنات المرتبطة</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th>المخزن</th>
                        <th>رقم التتبع</th>
                        <th class="text-end">الكمية المشحونة</th>
                        <th>تاريخ الشحن</th>
                        <th>ملاحظات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($order->shipments as $shipment)
                        <tr>
                            <td>{{ $shipment->warehouse?->name ?? 'غير محدد' }}</td>
                            <td>{{ $shipment->tracking_number ?? '—' }}</td>
                            <td class="text-end">{{ number_format($shipment->shipped_quantity, 0) }}</td>
                            <td>{{ optional($shipment->shipped_at)->format('Y-m-d') ?? '—' }}</td>
                            <td>{{ $shipment->notes ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-4 text-muted">لا توجد شحنات مسجلة لهذا الأمر.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
