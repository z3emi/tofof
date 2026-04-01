@extends('admin.layout')

@section('title', 'مركز التقارير والتحليلات')

@push('styles')
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem; color: white; border-radius: 0 !important; }
    .report-card { 
        background: #fff; 
        border-radius: 15px; 
        border: 1px solid #e2e8f0; 
        padding: 2.5rem; 
        transition: all 0.3s ease-in-out; 
        text-align: center; 
        height: 100%; 
        border-bottom: 4px solid transparent;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }
    .report-card:hover { 
        transform: translateY(-8px); 
        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); 
        border-bottom-color: var(--primary-dark);
    }
    .report-icon { 
        width: 70px; 
        height: 70px; 
        border-radius: 20px; 
        display: inline-flex; 
        align-items: center; 
        justify-content: center; 
        margin-bottom: 1.5rem; 
        font-size: 2rem;
        transition: all 0.3s ease;
    }
    .report-card:hover .report-icon {
        transform: scale(1.1);
    }
    .report-card h4 {
        color: var(--primary-dark);
        font-weight: 700;
    }
    .report-card p {
        color: #6b7280;
    }
</style>
@endpush

@section('content')
<div class="form-card">
    <div class="form-card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2 class="mb-2 fw-bold text-white"><i class="bi bi-bar-chart-line-fill me-2"></i> مركز التقارير والذكاء الإداري</h2>
            <p class="mb-0 opacity-75 fs-6 text-white small">تحليل البيانات المالية، حركة المخزون، وسلوك العملاء لاتخاذ قرارات مدروسة.</p>
        </div>
    </div>
    
    <div class="p-4 p-lg-5">
        <div class="row g-4">
            {{-- Financial --}}
            @can('view-reports-financial')
            <div class="col-md-6 col-lg-4">
                <div class="report-card shadow-sm">
                    <div class="report-icon bg-success bg-opacity-10 text-success"><i class="bi bi-graph-up-arrow"></i></div>
                    <h4 class="fw-bold text-dark mb-3">التقارير المالية</h4>
                    <p class="text-muted small mb-4">كشوفات المبيعات، المشتريات، المصاريف التشغيلية وصافي الأرباح بدقة عالية.</p>
                    <a href="{{ route('admin.reports.financial') }}" class="btn text-white w-100 py-3 fw-bold" style="background:var(--primary-dark); border-radius:12px">عرض التقرير المالي</a>
                </div>
            </div>
            @endcan

            {{-- Inventory --}}
            @can('view-reports-inventory')
            <div class="col-md-6 col-lg-4">
                <div class="report-card shadow-sm">
                    <div class="report-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-boxes"></i></div>
                    <h4 class="fw-bold text-dark mb-3">تقارير المخزون</h4>
                    <p class="text-muted small mb-4">تحليل مستويات المخزون، المنتجات الأكثر حركة، والأصناف الراكدة في المستودعات.</p>
                    <a href="{{ route('admin.reports.inventory') }}" class="btn text-white w-100 py-3 fw-bold" style="background:var(--primary-dark); border-radius:12px">عرض تقرير المخزون</a>
                </div>
            </div>
            @endcan

            {{-- Customers --}}
            <div class="col-md-6 col-lg-4">
                <div class="report-card shadow-sm">
                    <div class="report-icon bg-info bg-opacity-10 text-info"><i class="bi bi-people-fill"></i></div>
                    <h4 class="fw-bold text-dark mb-3">تقارير العملاء</h4>
                    <p class="text-muted small mb-4">تحليل قاعدة العملاء، معرفة العملاء الأكثر قيمة، ومراقبة معدلات الولاء والنشاط.</p>
                    <a href="{{ route('admin.reports.customers') }}" class="btn text-white w-100 py-3 fw-bold" style="background:var(--primary-dark); border-radius:12px">عرض تقرير العملاء</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
