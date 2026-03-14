@extends('admin.layout')

@section('title', 'وحدة التصنيع وسلسلة التوريد')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">وحدة التصنيع وسلسلة التوريد</h4>
        <p class="text-muted mb-0">تتبع المواد الخام وأوامر الإنتاج والشحنات القادمة من المصانع الخارجية.</p>
    </div>
    <div class="btn-group">
        <a href="{{ route('admin.manufacturing.orders.create') }}" class="btn btn-primary"><i class="bi bi-clipboard-plus"></i> أمر تصنيع جديد</a>
        <a href="{{ route('admin.manufacturing.shipments.create') }}" class="btn btn-outline-primary"><i class="bi bi-truck"></i> تسجيل شحنة</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-circle bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-gear"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-1">أوامر الإنتاج النشطة</p>
                        <h4 class="mb-0">{{ $activeOrders }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-circle bg-success bg-opacity-10 text-success">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-1">أوامر مكتملة</p>
                        <h4 class="mb-0">{{ $completedOrders }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-circle bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-1">التكلفة التراكمية للإنتاج</p>
                        <h4 class="mb-0">{{ \App\Support\Currency::format($totalProductionCost) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-light">المواد الخام</div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>المادة</th>
                            <th>الوحدة</th>
                            <th class="text-end">التكلفة/وحدة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($materials as $material)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $material->name }}</div>
                                    <small class="text-muted">{{ $material->sku ?? 'بدون رمز' }}</small>
                                </td>
                                <td>{{ $material->unit ?? '—' }}</td>
                                <td class="text-end">{{ \App\Support\Currency::format($material->cost_per_unit) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center py-3">لا توجد مواد خام مسجلة.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-light">وصفات التصنيع (BOM)</div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>المنتج</th>
                            <th>عدد المكونات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($boms as $bom)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $bom->product?->name_ar ?? 'منتج' }}</div>
                                    <small class="text-muted">{{ $bom->variant_name ?? 'بدون متغير' }}</small>
                                </td>
                                <td>{{ $bom->items->count() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-center py-3">لا توجد وصفات تصنيع.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-light">آخر الشحنات</div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>الطلب</th>
                            <th>الكمية</th>
                            <th>المخزن</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shipments as $shipment)
                            <tr>
                                <td>{{ $shipment->order?->reference }}</td>
                                <td>{{ $shipment->shipped_quantity }}</td>
                                <td>{{ $shipment->warehouse?->name ?? 'غير محدد' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center py-3">لا توجد شحنات مسجلة.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mt-4">
    <div class="card-header bg-light">أوامر التصنيع</div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>المرجع</th>
                    <th>المنتج</th>
                    <th>الكمية المخطط لها</th>
                    <th>الكمية المكتملة</th>
                    <th>الحالة</th>
                    <th>التكلفة</th>
                    <th>تاريخ البدء</th>
                    <th>تاريخ التسليم</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td class="fw-semibold">{{ $order->reference }}</td>
                        <td>
                            <div>{{ $order->product?->name_ar ?? 'منتج' }}</div>
                            <small class="text-muted">{{ $order->variant_name ?? 'بدون متغير' }}</small>
                        </td>
                        <td>{{ $order->planned_quantity }}</td>
                        <td>{{ $order->completed_quantity }}</td>
                        <td>
                            <span class="badge {{ $order->status === 'completed' ? 'bg-success' : ($order->status === 'in_progress' ? 'bg-info' : 'bg-secondary') }}">{{ $order->status }}</span>
                        </td>
                        <td>{{ \App\Support\Currency::format($order->total_cost) }}</td>
                        <td>{{ optional($order->starts_at)->format('Y-m-d') ?? '—' }}</td>
                        <td>{{ optional($order->due_at)->format('Y-m-d') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center py-4">لا توجد أوامر تصنيع حالياً.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $orders->links() }}
    </div>
</div>
@endsection
