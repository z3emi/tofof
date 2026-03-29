@extends('admin.layout')

@section('title', 'إدارة الموردين')

@push('styles')
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem; color: white; border-radius: 0 !important; }
    .table-container { border-radius: 15px; border: 1px solid #f1f5f9; overflow: hidden; background: #fff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    .search-input { border-radius: 12px; border: 1px solid #e2e8f0; padding: 0.8rem 1.2rem; background: #fafbff; }
</style>
@endpush

@section('content')
<div class="form-card">
    <div class="form-card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2 class="mb-2 fw-bold text-white"><i class="bi bi-truck-flatbed me-2"></i> إدارة الموردين والشركات</h2>
            <p class="mb-0 opacity-75 fs-6 text-white small">إدارة جهات التوريد، أرقام التواصل، وعناوين العمل الخاصة بشركاء المخزون.</p>
        </div>
        <div>
            @can('create-suppliers')
                <a href="{{ route('admin.suppliers.create') }}" class="btn btn-light px-4 fw-bold text-brand"><i class="bi bi-plus-circle me-1"></i> إضافة مورد جديد</a>
            @endcan
        </div>
    </div>
    
    <div class="p-4 p-lg-5">
        <form method="GET" action="{{ route('admin.suppliers.index') }}" class="row g-3 mb-4 p-4 bg-light rounded-4 border align-items-end">
            <div class="col-md-8">
                <label class="small fw-bold text-muted mb-2">بحث سريع في الموردين</label>
                <input type="text" name="search" class="form-control search-input" placeholder="اسم المورد، رقم الهاتف، أو البريد..." value="{{ request('search') }}">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn text-white px-4 py-3 fw-bold flex-grow-1" style="background:var(--primary-dark); border-radius:12px">بحث</button>
                <button type="button" class="btn btn-outline-dark d-flex align-items-center justify-content-center" style="width:58px; height:58px; border-radius:12px" data-bs-toggle="modal" data-bs-target="#filtersModal" title="فلاتر إضافية">
                    <i class="bi bi-funnel fs-4"></i>
                </button>
                @if(request()->anyFilled(['search','date_from','date_to']))
                    <a href="{{ route('admin.suppliers.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" style="width:58px; height:58px; border-radius:12px" title="تصفير">
                        <i class="bi bi-arrow-counterclockwise fs-4"></i>
                    </a>
                @endif
            </div>
        </form>

        <div class="table-container shadow-sm border overflow-hidden">
            <table class="table mb-0 align-middle text-center">
                <thead class="bg-light border-bottom">
                    <tr class="text-muted small fw-bold">
                        <th class="py-3" width="80">ID</th>
                        <th class="py-3 text-start">الاسم / الشركة</th>
                        <th class="py-3">رقم الهاتف</th>
                        <th class="py-3">البريد الإلكتروني</th>
                        <th class="py-3" width="120">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($suppliers as $supplier)
                        <tr>
                            <td class="small text-muted">#{{ $supplier->id }}</td>
                            <td class="text-start fw-bold text-dark">{{ $supplier->name }}</td>
                            <td><span class="small fw-medium">{{ $supplier->phone ?? $supplier->phone_number ?? '-' }}</span></td>
                            <td class="small text-muted">{{ $supplier->email ?? '-' }}</td>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('admin.suppliers.edit', $supplier->id) }}" class="btn btn-sm btn-outline-primary rounded-3 px-2 py-1"><i class="bi bi-pencil"></i></a>
                                    @can('delete-suppliers')
                                        <form action="{{ route('admin.suppliers.destroy', $supplier->id) }}" method="POST" onsubmit="return confirm('حذف المورد؟')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-3 px-2 py-1"><i class="bi bi-trash"></i></button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-5 text-muted">لا يوجد موردين معرفين في النظام.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($suppliers->hasPages())
            <div class="mt-4 d-flex justify-content-center">{{ $suppliers->links() }}</div>
        @endif
    </div>
</div>

{{-- Filters Modal --}}
<div class="modal fade" id="filtersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:20px">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold mb-0"><i class="bi bi-funnel me-2"></i>تصفية الموردين المتقدمة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form method="GET" action="{{ route('admin.suppliers.index') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted mb-2">تاريخ تسجيل المورد (من)</label>
                            <input type="date" name="date_from" class="form-control search-input" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted mb-2">تاريخ تسجيل المورد (إلى)</label>
                            <input type="date" name="date_to" class="form-control search-input" value="{{ request('date_to') }}">
                        </div>
                    </div>
                    <div class="mt-5 d-flex gap-2">
                        <button type="submit" class="btn text-white w-100 py-3 fw-bold" style="background:var(--primary-dark); border-radius:12px">تطبيق الفلاتر</button>
                        <a href="{{ route('admin.suppliers.index') }}" class="btn btn-outline-secondary px-4 py-3" style="border-radius:12px">تصفير الكل</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
