@extends('admin.layout')

@section('title', 'لوحة تحكم التقارير')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 fw-bold">لوحة تحكم التقارير</h1>
    <p class="text-muted mb-4">اختر التقرير الذي تود عرضه من القائمة أدناه.</p>

    <div class="row g-4">
        {{-- التقرير المالي --}}
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="bi bi-graph-up-arrow fs-1 text-success"></i>
                    </div>
                    <h5 class="card-title">التقارير المالية</h5>
                    <p class="card-text text-muted small">عرض ملخص المبيعات، المشتريات، المصاريف، والأرباح.</p>
                    <a href="{{ route('admin.reports.financial') }}" class="btn btn-primary btn-sm">عرض التقرير</a>
                </div>
            </div>
        </div>

        {{-- تقارير المخزون (مثال لم يتم إنشاؤه بعد) --}}
<div class="col-md-6 col-lg-4">
    <div class="card h-100 shadow-sm">
        <div class="card-body text-center">
            <div class="mb-3">
                <i class="bi bi-boxes fs-1 text-primary"></i>
            </div>
            <h5 class="card-title">تقارير المخزون</h5>
            <p class="card-text text-muted small">متابعة المنتجات التي على وشك النفاد والأكثر مبيعًا.</p>
            {{-- **THE CHANGE**: Update the link and remove the 'disabled' class --}}
            <a href="{{ route('admin.reports.inventory') }}" class="btn btn-primary btn-sm">عرض التقرير</a>
        </div>
    </div>
</div>

        {{-- تقارير العملاء (مثال لم يتم إنشاؤه بعد) --}}
<div class="col-md-6 col-lg-4">
    <div class="card h-100 shadow-sm">
        <div class="card-body text-center">
            <div class="mb-3">
                <i class="bi bi-people-fill fs-1 text-info"></i>
            </div>
            <h5 class="card-title">تقارير العملاء</h5>
            <p class="card-text text-muted small">تحليل أفضل العملاء والعملاء غير النشطين.</p>
            {{-- **THE CHANGE**: Update the link and remove the 'disabled' class --}}
            <a href="{{ route('admin.reports.customers') }}" class="btn btn-primary btn-sm">عرض التقرير</a>
        </div>
    </div>
</div>
        
        {{-- يمكنك إضافة المزيد من بطاقات التقارير هنا --}}

    </div>
</div>
@endsection
