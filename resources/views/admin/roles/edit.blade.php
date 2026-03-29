@extends('admin.layout')

@section('title', 'تعديل الصلاحيات: ' . $role->name)

@php
    $translations = [
        'view-admin-panel' => 'الوصول إلى لوحة التحكم', 'view-activity-log' => 'عرض سجل النشاطات (Log)', 
        'edit-settings' => 'إعدادات المتجر (عام)', 'edit-settings-frontend' => 'إعدادات واجهة الموقع', 'edit-settings-seo' => 'إعدادات SEO',
        'view-products' => 'عرض المنتجات', 'create-products' => 'إضافة منتج جديد', 'edit-products' => 'تعديل تفاصيل الساعات', 'delete-products' => 'حذف المنتجات',
        'view-categories' => 'عرض الأقسام والبراندات', 'create-categories' => 'إضافة قسم أو براند جديد', 'edit-categories' => 'تعديل الأقسام والبراندات', 'delete-categories' => 'حذف الأقسام والبراندات',
        'view-orders' => 'عرض طلبات العملاء', 'create-orders' => 'إضافة طلب يدوي', 'edit-orders' => 'تحديث حالة الطلب', 'delete-orders' => 'حذف الطلبات',
        'view-trashed-orders' => 'عرض الطلبات المحذوفة', 'restore-orders' => 'استعادة الطلبات', 'force-delete-orders' => 'حذف الطلبات نهائياً',
        'view-users' => 'إدارة المستخدمين والمدراء', 'create-users' => 'إضافة مستخدم جديد', 'edit-users' => 'تعديل بيانات المستخدم', 'delete-users' => 'حذف المستخدمين', 'ban-users' => 'حظر/إلغاء حظر المستخدمين',
        'view-roles' => 'عرض الأدوار والصلاحيات', 'create-roles' => 'إضافة دور جديد', 'edit-roles' => 'تعديل صلاحيات الأدوار', 'delete-roles' => 'حذف الأدوار',
        'view-customers' => 'عرض بيانات العملاء', 'create-customers' => 'إضافة عميل جديد', 'edit-customers' => 'تعديل بيانات العميل', 'delete-customers' => 'حذف العملاء', 'ban-customers' => 'حظر العملاء', 'manage-wallet' => 'إدارة محفظة العميل',
        'view-discount-codes' => 'إدارة الكوبونات والعروض', 'create-discount-codes' => 'إضافة كود خصم', 'edit-discount-codes' => 'تعديل كود خصم', 'delete-discount-codes' => 'حذف كود خصم',
        'view-reports' => 'الوصول للتقارير', 'view-reports-financial' => 'تقارير المبيعات', 'view-reports-inventory' => 'تقارير المخزون', 'view-reports-customers' => 'تقارير العملاء',
        'manage-backups' => 'إدارة النسخ الاحتياطي', 'manage-imports' => 'إدارة الاسترداد والتحديث', 'manage-whatsapp' => 'إدارة الواتساب', 'manage-slides' => 'إدارة السلايدرات',
        'manage-barcodes' => 'إدارة الباركود و QR', 'manage-customer-tiers' => 'إعدادات فئات العملاء', 'manage-reviews' => 'إدارة تقييمات المنتج', 'manage-expenses' => 'إدارة المصاريف', 'manage-suppliers' => 'إدارة الموردين',
    ];

    function permGroupName($name) {
        if (str_contains($name, 'product')) return 'المنتجات (Products)';
        if (str_contains($name, 'blog')) return 'المدونة (Blog)';
        if (str_contains($name, 'category')) return 'التصنيفات (Categories)';
        if (str_contains($name, 'order')) return 'الطلبات (Orders)';
        if (str_contains($name, 'user') || str_contains($name, 'role') || str_contains($name, 'manager')) return 'المستخدمون (Users)';
        if (str_contains($name, 'customer')) return 'العملاء (Customers)';
        if (str_contains($name, 'discount')) return 'الكوبونات (Coupons)';
        if (str_contains($name, 'report') || str_contains($name, 'expense')) return 'التقارير (Reports)';
        if (str_contains($name, 'setting') || str_contains($name, 'backup') || str_contains($name, 'import') || str_contains($name, 'whatsapp') || str_contains($name, 'slide')) return 'النظام (System)';
        return 'عام (General)';
    }

    $groupedPerms = $permissions->groupBy(fn($p) => permGroupName($p->name));
    $icons = [
        'المنتجات (Products)' => 'bi-watch', 'المدونة (Blog)' => 'bi-journal-richtext', 'التصنيفات (Categories)' => 'bi-tags',
        'الطلبات (Orders)' => 'bi-cart-check', 'المستخدمون (Users)' => 'bi-people', 'العملاء (Customers)' => 'bi-person-badge',
        'الكوبونات (Coupons)' => 'bi-percent', 'التقارير (Reports)' => 'bi-graph-up-arrow', 'النظام (System)' => 'bi-gear-wide-connected', 'عام (General)' => 'bi-shield-check',
    ];
@endphp

@section('content')
@can('edit-roles')
@push('styles')
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem; color: white; border-radius: 0 !important; }
    .form-section-title { font-weight: 700; color: var(--primary-dark); border-right: 4px solid var(--accent-gold); padding-right: 15px; margin-bottom: 2rem; }
    .permission-group-card { border: 1px solid #f1f5f9; border-radius: 15px; padding: 1.5rem; background: #fafbff; height: 100%; transition: all 0.3s ease; }
    .permission-item { background: #fff; border: 1px solid #f1f5f9; border-radius: 12px; padding: 0.8rem 1.2rem; margin-bottom: 0.8rem; display: flex; justify-content: space-between; align-items: center; transition: all 0.2s ease; }
    .ios-switch { position: relative; display: inline-block; width: 44px; height: 24px; cursor: pointer; }
    .ios-switch input { opacity: 0; width: 0; height: 0; }
    .ios-slider { position: absolute; inset: 0; background-color: #e9e9ea; transition: .3s; border-radius: 34px; }
    .ios-slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 2px; bottom: 2px; background-color: white; transition: .3s; border-radius: 50%; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    input:checked + .ios-slider { background-color: #34c759; }
    input:checked + .ios-slider:before { transform: translateX(20px); }
    .submit-btn { background: var(--primary-dark); padding: 1rem 3rem; border-radius: 10px; font-weight: 700; color: white; border: none; }
</style>
@endpush

<div class="form-card">
    <div class="form-card-header d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-2 fw-bold text-white"><i class="bi bi-shield-lock-fill me-2"></i> تعديل صلاحيات الدور: {{ $role->name }}</h2>
            <p class="mb-0 opacity-75 fs-6 text-white small">يمكنك تعديل صلاحيات الوصول لهذا الدور من خلال المبدلات أدناه.</p>
        </div>
        <div>
            <span class="badge bg-white text-dark py-2 px-4 rounded-pill fw-bold shadow-sm" id="perm-count">{{ $role->permissions->count() }} صلاحيات مفعلة</span>
        </div>
    </div>
    
    <div class="p-4 p-lg-5">
        <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-5">
                <h5 class="form-section-title">معلومات الدور</h5>
                <div class="row">
                    <div class="col-md-6">
                        <label for="name" class="form-label fw-bold">اسم الدور الوظيفي (بالإنجليزي)</label>
                        <input type="text" class="form-control" style="border-radius:12px; padding:0.8rem" id="name" name="name" value="{{ old('name', $role->name) }}" placeholder="مثال: support-team" required>
                        <small class="text-muted mt-2 d-block">استخدم حروف إنجليزية صغيرة وواصلات فقط.</small>
                    </div>
                </div>
            </div>

            <div class="mb-5">
                <h5 class="form-section-title">تخصيص الصلاحيات</h5>
                <div class="row g-4">
                    @foreach($groupedPerms as $groupName => $group)
                        <div class="col-md-6 col-xl-4">
                            <div class="permission-group-card shadow-xs">
                                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                                    <h6 class="fw-bold mb-0 text-brand">
                                        <i class="bi {{ $icons[$groupName] ?? 'bi-grid' }} me-2"></i>{{ $groupName }}
                                    </h6>
                                    <div class="form-check form-switch p-0 m-0">
                                        <input class="form-check-input select-all-group scale-110 ms-0" type="checkbox" style="cursor:pointer">
                                    </div>
                                </div>
                                @foreach($group as $permission)
                                    <div class="permission-item">
                                        <div>
                                            <div class="fw-bold small">{{ $translations[$permission->name] ?? $permission->name }}</div>
                                            <div class="text-muted" style="font-size:0.65rem">{{ $permission->name }}</div>
                                        </div>
                                        <label class="ios-switch">
                                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" 
                                                   class="permission-checkbox" @checked($role->permissions->contains($permission->id))>
                                            <span class="ios-slider"></span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="d-flex justify-content-end gap-3 pt-5 border-top">
                <a href="{{ route('admin.roles.index') }}" class="btn btn-light px-5 py-3 rounded-3 fw-bold">إلغاء</a>
                <button type="submit" class="submit-btn shadow-sm py-3 px-5">حفظ التعديلات</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const countDisplay = document.getElementById('perm-count');
        const update = () => countDisplay.textContent = `${document.querySelectorAll('.permission-checkbox:checked').length} صلاحيات مفعلة`;
        document.querySelectorAll('.permission-checkbox').forEach(cb => cb.addEventListener('change', update));
        document.querySelectorAll('.select-all-group').forEach(sw => sw.addEventListener('change', function() {
            this.closest('.permission-group-card').querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = this.checked);
            update();
        }));
    });
</script>
@else
<div class="p-5 text-center">
    <div class="alert alert-danger rounded-4 shadow-sm p-5 d-inline-block">
        <i class="bi bi-shield-exclamation display-1 mb-3"></i>
        <h4>ليس لديك صلاحية لتعديل الأدوار.</h4>
    </div>
</div>
@endcan
@endsection