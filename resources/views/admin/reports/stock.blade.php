@extends('admin.layout')

@section('title', 'تقارير المخزون')

@push('styles')
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem; color: white; border-radius: 0 !important; }
    .search-input { border-radius: 12px; border: 1px solid #e2e8f0; padding: 0.8rem 1.2rem; background: #fafbff; }
    
    .stat-card {
        transition: all 0.3s ease-in-out;
        border: 1px solid #e2e8f0;
        border-radius: 15px !important;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }
    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 25px -5px rgba(0,0,0,0.1) !important;
    }

    .bg-warning-custom {
        background: linear-gradient(135deg, #fff3cd 0%, #fff8e1 100%) !important;
        border-bottom: 4px solid #ffc107;
    }

    .bg-danger-custom {
        background: linear-gradient(135deg, #f8d7da 0%, #fde2e2 100%) !important;
        border-bottom: 4px solid #dc3545;
    }

    .card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #eef1f4 100%) !important;
        border-bottom: 2px solid #e2e8f0 !important;
        font-weight: 700;
        color: var(--primary-dark);
    }

    .card-header h5 {
        color: inherit;
    }

    .card-body {
        background: linear-gradient(135deg, #ffffff 0%, #fdfdfd 100%);
    }

    .badge {
        font-weight: 600;
        font-size: 0.9rem;
        border-radius: 0.6rem;
        padding: 0.4em 0.7em;
    }

    .list-group-item {
        border: none;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding: 0.75rem 1rem;
    }

    .table thead th {
        font-weight: 700;
        color: var(--primary-dark);
        border-bottom: 2px solid #e2e8f0;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.02) !important;
    }
</style>
@endpush

@section('content')
<div class="form-card">
    <div class="form-card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2 class="mb-2 fw-bold text-white"><i class="bi bi-inbox me-2"></i> تقارير حالة المخزون</h2>
            <p class="mb-0 opacity-75 fs-6 text-white small">مراقبة المنتجات الراكدة والنافدة، والمنتجات الأكثر حركة في المستودع.</p>
        </div>
        <form method="GET" action="{{ route('admin.reports.stock') }}" class="d-flex gap-2 align-items-center">
            <select name="month" class="form-select form-select-sm search-input" style="width: 150px;">
                @for ($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ ($month ?? now()->month) == $m ? 'selected' : '' }}>
                        {{ date('F', mktime(0, 0, 0, $m, 10)) }}
                    </option>
                @endfor
            </select>
            <select name="year" class="form-select form-select-sm search-input" style="width: 120px;">
                @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                    <option value="{{ $y }}" {{ ($year ?? now()->year) == $y ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                @endfor
            </select>
            <button type="submit" class="btn text-white px-4 py-2 fw-bold" style="background:var(--primary-dark); border-radius:12px; white-space:nowrap;">تطبيق</button>
            <a href="{{ route('admin.reports.stock.export', ['month' => ($month ?? now()->month), 'year' => ($year ?? now()->year)]) }}" class="btn btn-outline-light p-2 d-inline-flex align-items-center justify-content-center" style="width:40px; height:40px; border-radius:10px" title="تصدير إكسل"><i class="bi bi-file-earmark-excel"></i></a>
        </form>
    </div>

    <div class="p-4 p-lg-5">
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
</div>
@endsection
