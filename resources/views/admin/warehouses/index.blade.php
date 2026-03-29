@extends('admin.layout')

@section('title', 'إدارة المخازن')

@push('styles')
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem; color: white; border-radius: 0 !important; }
    .table-container { border-radius: 15px; border: 1px solid #f1f5f9; overflow: hidden; background: #fff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    .stat-card { background: #f8fafc; border-radius: 20px; border: 1px solid #e2e8f0; padding: 1.5rem; transition: all 0.3s; }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
</style>
@endpush

@section('content')
<div class="form-card">
    <div class="form-card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2 class="mb-2 fw-bold text-white"><i class="bi bi-house-door-fill me-2"></i> إدارة مراكز التخزين</h2>
            <p class="mb-0 opacity-75 fs-6 text-white small">مراقبة المخازن، سعة التخزين، والقيمة المالية للبضائع المودعة.</p>
        </div>
        <div>
            @can('manage-inventory')
                <a href="{{ route('admin.warehouses.create') }}" class="btn btn-light px-4 fw-bold text-brand"><i class="bi bi-plus-circle me-1"></i> إضافة مخزن جديد</a>
            @endcan
        </div>
    </div>
    
    <div class="p-4 p-lg-5">
        <div class="row g-4 mb-5 text-center">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="text-muted small fw-bold mb-2">إجمالي المخازن</div>
                    <h3 class="fw-bold mb-0 text-dark">{{ $warehouses->count() }}</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="text-muted small fw-bold mb-2">إجمالي البضاعة المخزنة</div>
                    <h3 class="fw-bold mb-0 text-primary">{{ number_format($grandTotals['quantity'], 0) }} وحدة</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="text-muted small fw-bold mb-2">القيمة التقديرية للمخزون</div>
                    <h3 class="fw-bold mb-0 text-success">{{ number_format($grandTotals['value'], 0) }} د.ع</h3>
                </div>
            </div>
        </div>

        <div class="table-container shadow-sm border overflow-hidden">
            <table class="table mb-0 align-middle text-center">
                <thead class="bg-light border-bottom">
                    <tr class="text-muted small fw-bold">
                        <th class="py-3 text-start ps-4">المخزن</th>
                        <th class="py-3">الكود</th>
                        <th class="py-3 text-start">الموقع</th>
                        <th class="py-3">عدد الأصناف</th>
                        <th class="py-3">إجمالي الكمية</th>
                        <th class="py-3">قيمة المخزون</th>
                        <th class="py-3 ps-4" width="220">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($warehouses as $warehouse)
                        <tr>
                            <td class="text-start ps-4">
                                <div class="fw-bold text-dark">{{ $warehouse->name }}</div>
                                @if($warehouse->notes) <div class="small text-muted">{{ Str::limit($warehouse->notes, 40) }}</div> @endif
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $warehouse->code ?: '—' }}</span></td>
                            <td class="text-start small">{{ $warehouse->location ?: '—' }}</td>
                            <td><span class="fw-bold">{{ $warehouse->distinct_products }}</span></td>
                            <td><span class="badge bg-primary bg-opacity-10 text-primary px-3">{{ number_format($warehouse->total_quantity, 0) }}</span></td>
                            <td><span class="fw-bold text-success">{{ number_format($warehouse->total_value, 0) }} د.ع</span></td>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('admin.warehouses.show', $warehouse) }}" class="btn btn-sm btn-outline-secondary rounded-3 px-3 py-1 fw-bold"><i class="bi bi-clipboard-data me-1"></i> كشف</a>
                                    @can('manage-inventory')
                                        <a href="{{ route('admin.warehouses.edit', $warehouse->id) }}" class="btn btn-sm btn-outline-primary rounded-3 px-2 py-1"><i class="bi bi-pencil"></i></a>
                                        <form action="{{ route('admin.warehouses.destroy', $warehouse) }}" method="POST" onsubmit="return confirm('حذف المخزن؟')">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-danger rounded-3 px-2 py-1"><i class="bi bi-trash"></i></button></form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-5 text-muted">لا يوجد مخازن مسجلة حالياً.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
