@extends('admin.layout')

@section('title', 'كشف مخزن')

@section('content')
<div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h4 mb-1">كشف مخزن: {{ $warehouse->name }}</h1>
        <p class="text-muted mb-0">تفاصيل المنتجات والكميات المتوفرة في هذا المخزن.</p>
    </div>
    <a href="{{ route('admin.warehouses.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-right"></i>
        الرجوع لقائمة المخازن
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <p class="text-muted mb-1">الموقع</p>
                <h5 class="mb-0">{{ $warehouse->location ?: 'غير محدد' }}</h5>
                @if($warehouse->code)
                    <span class="badge bg-secondary-subtle text-secondary mt-2">الكود: {{ $warehouse->code }}</span>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <p class="text-muted mb-1">إجمالي الكمية المتوفرة</p>
                <h4 class="mb-0">{{ number_format($totals['quantity'], 0) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <p class="text-muted mb-1">قيمة المخزون التقديرية</p>
                <h4 class="mb-0">{{ number_format($totals['value'], 0) }} د.ع</h4>
            </div>
        </div>
    </div>
</div>

@if ($warehouse->notes)
    <div class="alert alert-info">{{ $warehouse->notes }}</div>
@endif

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>المنتج</th>
                        <th>الكود/SKU</th>
                        <th class="text-center">الكمية المتاحة</th>
                        <th class="text-center">قيمة المخزون</th>
                        <th>تفاصيل الدفعات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($productGroups as $group)
                        @php
                            $product = $group['product'];
                            $productName = $product?->name_ar
                                ?? $product?->name_en
                                ?? $product?->name
                                ?? 'منتج محذوف';
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $productName }}</div>
                                @if ($group['variant_name'])
                                    <div class="text-muted small">{{ $group['variant_name'] }}</div>
                                @endif
                            </td>
                            <td>{{ $group['sku'] ?: '—' }}</td>
                            <td class="text-center">{{ number_format($group['total_quantity'], 0) }}</td>
                            <td class="text-center">{{ number_format($group['total_value'], 0) }} د.ع</td>
                            <td>
                                <ul class="list-unstyled mb-0 small text-muted">
                                    @foreach ($group['batches'] as $batch)
                                        <li class="mb-1">
                                            <span class="fw-semibold">دفعة رقم {{ $batch['id'] }}</span>
                                            — كمية: {{ number_format($batch['quantity'], 0) }}
                                            @if ($batch['purchase_price'])
                                                — سعر الشراء: {{ number_format($batch['purchase_price'], 0) }} د.ع
                                            @endif
                                            @if ($batch['batch_number'])
                                                — رقم الدفعة: {{ $batch['batch_number'] }}
                                            @endif
                                            @if ($batch['expires_at'])
                                                — انتهاء: {{ $batch['expires_at'] }}
                                            @endif
                                            @if ($batch['reorder_point'])
                                                — نقطة إعادة الطلب: {{ $batch['reorder_point'] }}
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                لا توجد منتجات مسجلة في هذا المخزن حالياً.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
