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

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2" style="background-color: var(--primary-dark); color: white;">
        <h4 class="mb-0">جميع أدوار المستخدمين</h4>
        <div>
            @can('create-roles')
                <a href="{{ route('admin.roles.create') }}" class="btn btn-light btn-sm fw-bold">
                    <i class="bi bi-plus-circle me-1"></i> إضافة دور جديد
                </a>
            @endcan
        </div>
    </div>

    <div class="card-body">
        <div class="row g-2 mb-4" dir="rtl">
            <div class="col-md-4">
                <input type="text" id="roleSearch" class="form-control" placeholder="ابحث عن دور وظيفي...">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn text-white w-100 fw-bold" style="background-color: var(--primary-dark);">
                    <i class="bi bi-search me-1"></i> بحث
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle" dir="rtl">
                <thead class="table-light">
                    <tr>
                        <th width="80">#</th>
                        <th>مسمى الدور الوظيفي</th>
                        <th>نظام الوصول (Guard)</th>
                        <th>الصلاحيات الممنوحة</th>
                        <th width="200">العمليات</th>
                    </tr>
                </thead>
                <tbody id="rolesTableBody">
                    @forelse ($roles as $role)
                        <tr>
                            <td class="text-muted fw-bold">#{{ str_pad($role->id, 2, '0', STR_PAD_LEFT) }}</td>
                            <td>
                                <div class="fw-bold">{{ $roleTranslations[$role->name] ?? $role->name }}</div>
                                <small class="text-muted">{{ $role->name }}</small>
                            </td>
                            <td>
                                <span class="badge bg-secondary opacity-75">{{ $role->guard_name }}</span>
                            </td>
                            <td>
                                <span class="badge border text-dark fw-bold" style="background-color: rgba(109, 14, 22, 0.05); border-color: var(--primary-dark) !important;">
                                    <i class="bi bi-shield-lock me-1"></i>
                                    {{ $role->permissions->count() }} صلاحية
                                </span>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    @if($role->name !== 'Super-Admin')
                                        <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-outline-info btn-sm" title="تعديل">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endif
                                    @if($role->name !== 'Super-Admin')
                                        <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="حذف">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-4 text-muted text-center">لا توجد أدوار لعرضها.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($roles->hasPages())
            <div class="mt-4 d-flex justify-content-center">
                {{ $roles->links() }}
            </div>
        @endif
    </div>
</div>

<script>
    document.getElementById('roleSearch').addEventListener('input', function() {
        const query = this.value.toLowerCase();
        const rows = document.querySelectorAll('#rolesTableBody tr');
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });
    });
</script>
@endsection