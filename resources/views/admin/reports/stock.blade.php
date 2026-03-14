@extends('admin.layout')

@section('title', 'تقارير المخزون')

@push('styles')
<style>
    .stat-card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        border: 0;
        border-radius: 1rem !important;
        overflow: hidden;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05) !important;
    }

    .bg-warning-custom {
        background-color: #fff3cd !important;
    }

    .bg-danger-custom {
        background-color: #f8d7da !important;
    }

    .card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-bottom: 1px solid #dee2e6;
    }

    .card-body {
        background: linear-gradient(135deg, #ffffff 0%, #fdfdfd 100%);
    }

    .badge {
        font-weight: 600;
        font-size: 0.9rem;
        border-radius: 0.5rem;
        padding: 0.4em 0.7em;
    }

    .list-group-item {
        border: none;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding: 0.75rem 1rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    {{-- فلتر الشهر والسنة --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 fw-bold">تقارير المخزون</h1>
        <form method="GET" action="{{ route('admin.reports.stock') }}" class="d-flex gap-2 align-items-center bg-white p-2 rounded-3 shadow-sm">
            <select name="month" class="form-select form-select-sm">
                @for ($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ ($month ?? now()->month) == $m ? 'selected' : '' }}>
                        {{ date('F', mktime(0, 0, 0, $m, 10)) }}
                    </option>
                @endfor
            </select>
            <select name="year" class="form-select form-select-sm">
                @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                    <option value="{{ $y }}" {{ ($year ?? now()->year) == $y ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                @endfor
            </select>
            <button type="submit" class="btn btn-sm btn-primary">تطبيق</button>
        </form>
    </div>

    <div class="row g-4">
        {{-- المنتجات على وشك النفاد --}}
        <div class="col-lg-6">
            <div class="card stat-card bg-warning-custom shadow-sm h-100">
                <div class="card-header">
                    <h5 class="mb-0 text-warning"><i class="bi bi-exclamation-triangle-fill me-2"></i>منتجات على وشك النفاد</h5>
                </div>
                <div class="card-body">
                    @if($lowStockProducts->isEmpty())
                        <p class="text-center text-muted mt-3">لا توجد منتجات على وشك النفاد حاليًا.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach($lowStockProducts as $product)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <a href="{{ route('admin.products.edit', $product->id) }}">{{ $product->name_ar }}</a>
                                    <span class="badge bg-warning text-dark">{{ $product->stock_quantity }} قطعة متبقية</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        {{-- المنتجات النافدة --}}
        <div class="col-lg-6">
            <div class="card stat-card bg-danger-custom shadow-sm h-100">
                <div class="card-header">
                    <h5 class="mb-0 text-danger"><i class="bi bi-x-octagon-fill me-2"></i>منتجات نفدت من المخزون</h5>
                </div>
                <div class="card-body">
                    @if($outOfStockProducts->isEmpty())
                        <p class="text-center text-muted mt-3">لا توجد منتجات نافدة حاليًا.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach($outOfStockProducts as $product)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <a href="{{ route('admin.products.edit', $product->id) }}">{{ $product->name_ar }}</a>
                                    <span class="badge bg-danger">نفدت الكمية</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        {{-- المنتجات الأكثر مبيعًا --}}
        <div class="col-12">
            <div class="card stat-card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-trophy-fill me-2"></i>المنتجات الأكثر مبيعًا (آخر 30 يوم)</h5>
                </div>
                <div class="card-body">
                    @if($topSellingProducts->isEmpty())
                        <p class="text-center text-muted mt-3">لا توجد بيانات مبيعات كافية.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>المنتج</th>
                                        <th class="text-center">عدد مرات البيع</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topSellingProducts as $product)
                                        <tr>
                                            <td>{{ $product->name_ar }}</td>
                                            <td class="text-center fw-bold">{{ $product->order_items_count }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
