@extends('admin.layout')

@section('title', 'المخازن')

@php
    use Illuminate\Support\Str;
@endphp

@section('content')
<div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h4 mb-1">إدارة المخازن</h1>
        <p class="text-muted mb-0">تابع مواقع التخزين وقيم المخزون لكل مخزن.</p>
    </div>
    @can('manage-inventory')
        <a href="{{ route('admin.warehouses.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>
            إضافة مخزن جديد
        </a>
    @endcan
</div>

@if (session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

@if ($errors->has('warehouse'))
    <div class="alert alert-danger">{{ $errors->first('warehouse') }}</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <p class="text-muted mb-1">عدد المخازن</p>
                <h4 class="mb-0">{{ $warehouses->count() }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <p class="text-muted mb-1">إجمالي الكمية المخزنة</p>
                <h4 class="mb-0">{{ number_format($grandTotals['quantity'], 0) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <p class="text-muted mb-1">القيمة التقديرية</p>
                <h4 class="mb-0">{{ number_format($grandTotals['value'], 0) }} د.ع</h4>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>المخزن</th>
                        <th>الكود</th>
                        <th>الموقع</th>
                        <th class="text-center">عدد المنتجات</th>
                        <th class="text-center">إجمالي الكمية</th>
                        <th class="text-center">قيمة المخزون</th>
                        <th class="text-end">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($warehouses as $warehouse)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $warehouse->name }}</div>
                                @if ($warehouse->notes)
                                    <div class="text-muted small">{{ Str::limit($warehouse->notes, 80) }}</div>
                                @endif
                            </td>
                            <td>{{ $warehouse->code ?: '—' }}</td>
                            <td>{{ $warehouse->location ?: '—' }}</td>
                            <td class="text-center">{{ $warehouse->distinct_products }}</td>
                            <td class="text-center">{{ number_format($warehouse->total_quantity, 0) }}</td>
                            <td class="text-center">{{ number_format($warehouse->total_value, 0) }} د.ع</td>
                            <td class="text-end">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.warehouses.show', $warehouse) }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-clipboard-data"></i>
                                        كشف المخزن
                                    </a>
                                    @can('manage-inventory')
                                        <a href="{{ route('admin.warehouses.edit', $warehouse) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-pencil"></i>
                                            تعديل
                                        </a>
                                        <form action="{{ route('admin.warehouses.destroy', $warehouse) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا المخزن؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="bi bi-trash"></i>
                                                حذف
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                لا توجد مخازن مسجلة حتى الآن.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
