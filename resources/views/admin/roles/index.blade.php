@extends('admin.layout')

@section('title', 'إدارة الأدوار والصلاحيات')

@php
    $roleTranslations = [
        'Super-Admin' => 'مدير النظام (كامل الصلاحيات)',
        'admin' => 'مسؤول (Admin)',
        'manager' => 'مدير فرع / قسم',
        'editor' => 'محرر محتوى',
        'sub-admin' => 'مساعد مدير',
    ];
@endphp

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
            <h2 class="mb-2 fw-bold text-white"><i class="bi bi-shield-lock-fill me-2"></i> إدارة الأدوار والصلاحيات</h2>
            <p class="mb-0 opacity-75 fs-6 text-white small">تعريف المجموعات الوظيفية وتحديد مستوى الوصول لكل منها في النظام.</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <div class="col-toggle-place"></div>
            @can('create-roles')
                <a href="{{ route('admin.roles.create') }}" class="btn btn-light px-4 fw-bold text-brand"><i class="bi bi-plus-circle me-1"></i> إضافة دور جديد</a>
            @endcan
        </div>
    </div>
    
    <div class="p-4 p-lg-5">
        <div class="row g-3 mb-4 p-4 bg-light rounded-4 border align-items-end">
            <div class="col-md-9">
                <label class="small fw-bold text-muted mb-2">بحث باسم الدور</label>
                <input type="text" id="roleSearch" class="form-control search-input" placeholder="أدخل اسم الدور الوظيفي للبحث السريع...">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn text-white w-100 py-3 fw-bold" style="background:var(--primary-dark); border-radius:12px">بحث وتطبيق</button>
            </div>
        </div>

        <div class="table-container shadow-sm border overflow-hidden">
            <table class="table mb-0 align-middle text-center" id="roles_table">
                <thead class="bg-light border-bottom">
                    <tr class="text-muted small fw-bold">
                        <th class="py-3" width="50" data-column-id="seq">#</th>
                        <th class="py-3" width="70" data-hide="true" data-column-id="id">{!! \App\Support\Sort::link('id', 'ID') !!}</th>
                        <th class="py-3 text-start" data-column-id="name">{!! \App\Support\Sort::link('name', 'مسمى الدور الوظيفي') !!}</th>
                        <th class="py-3" data-column-id="guard">نظام الوصول (Guard)</th>
                        <th class="py-3" data-column-id="permissions">عدد الصلاحيات</th>
                        <th class="py-3" width="120" data-column-id="actions">الإجراءات</th>
                    </tr>
                </thead>
                <tbody id="rolesTableBody">
                    @forelse ($roles as $role)
                        <tr>
                            <td class="small text-muted">{{ $loop->iteration + ($roles->perPage() * ($roles->currentPage() - 1)) }}</td>
                            <td class="small text-muted">#{{ $role->id }}</td>
                            <td class="text-start">
                                <div class="fw-bold text-dark">{{ $roleTranslations[$role->name] ?? $role->name }}</div>
                                <div class="small text-muted">{{ $role->name }}</div>
                            </td>
                            <td><span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1">{{ $role->guard_name }}</span></td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 fw-bold">
                                    <i class="bi bi-shield-lock me-1"></i> {{ $role->permissions->count() }} صلاحية
                                </span>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    @if($role->name !== 'Super-Admin')
                                        <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-sm btn-outline-primary rounded-3 px-2 py-1" title="تعديل"><i class="bi bi-pencil"></i></a>
                                        @can('delete-roles')
                                            <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" onsubmit="return confirm('حذف هذا الدور؟')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-3 px-2 py-1" title="حذف"><i class="bi bi-trash"></i></button>
                                            </form>
                                        @endcan
                                    @else
                                        <span class="text-muted small">دور محمي</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-5 text-muted">لا يوجد أدوار معرفة حالياً.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($roles->hasPages())
            <div class="mt-4 d-flex justify-content-center">{{ $roles->links() }}</div>
        @endif
    </div>
</div>

<script>
    document.getElementById('roleSearch').addEventListener('input', function() {
        const query = this.value.toLowerCase();
        document.querySelectorAll('#rolesTableBody tr').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(query) ? '' : 'none';
        });
    });
</script>
@endsection