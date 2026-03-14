@extends('admin.layout')
@section('title', 'تعديل الدور: ' . $role->name)

@php
    // نفس دوال المساعدة من ملف إنشاء الدور
    function permissionGroupName($permissionName) {
        if (str_contains($permissionName, 'product')) return 'المنتجات';
        if (str_contains($permissionName, 'blog')) return 'المدونة';
        if (str_contains($permissionName, 'category')) return 'التصنيفات';
        if (str_contains($permissionName, 'order')) return 'الطلبات';
        if (str_contains($permissionName, 'user') || str_contains($permissionName, 'role')) return 'المستخدمون والأدوار';
        if (str_contains($permissionName, 'customer')) return 'العملاء';
        if (str_contains($permissionName, 'supplier') || str_contains($permissionName, 'purchase')) return 'الموردون والمشتريات';
        if (str_contains($permissionName, 'expense') || str_contains($permissionName, 'inventory')) return 'الشؤون المالية والمخزون';
        if (str_contains($permissionName, 'discount')) return 'أكواد الخصم';
        if (str_contains($permissionName, 'report')) return 'التقارير';
        if (str_contains($permissionName, 'setting') || str_contains($permissionName, 'backup') || str_contains($permissionName, 'import')) return 'إعدادات النظام';
        return 'صلاحيات عامة';
    }

    $groupedPermissions = $permissions->groupBy(fn($p) => permissionGroupName($p->name));
@endphp

@section('content')
<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">تعديل الدور: {{ $role->name }}</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="name" class="form-label">اسم الدور</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $role->name) }}" required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <h5 class="mt-4">الصلاحيات</h5>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="select_all_permissions">
                    <label class="form-check-label fw-bold" for="select_all_permissions">تحديد الكل</label>
                </div>
                <hr>
                <div class="row gy-4">
                    @foreach($groupedPermissions as $groupName => $group)
                        <div class="col-md-6 col-lg-4">
                            <div class="p-3 border rounded shadow-sm h-100">
                                <h6 class="text-primary fw-bold border-bottom pb-2 mb-3">{{ $groupName }}</h6>
                                @foreach($group as $permission)
                                    <div class="form-check">
                                        <input class="form-check-input permission-checkbox" 
                                               type="checkbox" 
                                               name="permissions[]" 
                                               value="{{ $permission->id }}" 
                                               id="perm_{{ $permission->id }}"
                                               @if($role->permissions->contains($permission->id)) checked @endif>
                                        <label class="form-check-label" for="perm_{{ $permission->id }}">{{ $permission->name }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="btn btn-primary">تحديث الدور</button>
            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">إلغاء</a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('select_all_permissions');
        selectAll.addEventListener('click', function(event) {
            document.querySelectorAll('.permission-checkbox').forEach(checkbox => checkbox.checked = event.target.checked);
        });

        // Optional: Check "Select All" if all permissions are already checked on page load
        function checkSelectAll() {
            const allCheckboxes = document.querySelectorAll('.permission-checkbox');
            const allChecked = Array.from(allCheckboxes).every(cb => cb.checked);
            selectAll.checked = allChecked;
        }
        checkSelectAll();
        document.querySelectorAll('.permission-checkbox').forEach(cb => cb.addEventListener('change', checkSelectAll));
    });
</script>
@endpush