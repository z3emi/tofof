@extends('admin.layout')
@section('title', 'إدارة الصلاحيات: ' . $role->name)

@php
    $translations = [
        'view-admin-panel' => 'الوصول إلى لوحة التحكم',
        'view-activity-log' => 'عرض سجل النشاطات (Log)',
        'edit-settings' => 'تعديل إعدادات المتجر العامة',
        'view-products' => 'عرض المنتجات',
        'create-products' => 'إضافة منتج جديد',
        'edit-products' => 'تعديل تفاصيل الساعات',
        'delete-products' => 'حذف المنتجات',
        'view-categories' => 'عرض الأقسام والبراندات',
        'create-categories' => 'إضافة قسم أو براند جديد',
        'edit-categories' => 'تعديل الأقسام والبراندات',
        'delete-categories' => 'حذف الأقسام والبراندات',
        'view-orders' => 'عرض طلبات العملاء',
        'create-orders' => 'إضافة طلب يدوي',
        'edit-orders' => 'تحديث حالة الطلب',
        'delete-orders' => 'حذف الطلبات',
        'view-trashed-orders' => 'عرض الطلبات المحذوفة',
        'restore-orders' => 'استعادة الطلبات',
        'force-delete-orders' => 'حذف الطلبات نهائياً',
        'view-users' => 'إدارة المستخدمين والمدراء',
        'create-users' => 'إضافة مستخدم جديد',
        'edit-users' => 'تعديل بيانات المستخدم',
        'delete-users' => 'حذف المستخدمين',
        'ban-users' => 'حظر/إلغاء حظر المستخدمين',
        'view-roles' => 'عرض الأدوار والصلاحيات',
        'create-roles' => 'إضافة دور جديد',
        'edit-roles' => 'تعديل صلاحيات الأدوار',
        'delete-roles' => 'حذف الأدوار',
        'view-customers' => 'عرض بيانات العملاء',
        'create-customers' => 'إضافة عميل جديد',
        'edit-customers' => 'تعديل بيانات العميل',
        'delete-customers' => 'حذف العملاء',
        'ban-customers' => 'حظر العملاء',
        'manage-wallet' => 'إدارة محفظة العميل',
        'view-discount-codes' => 'إدارة الكوبونات والعروض',
        'create-discount-codes' => 'إضافة كود خصم',
        'edit-discount-codes' => 'تعديل كود خصم',
        'delete-discount-codes' => 'حذف كود خصم',
        'view-reports' => 'الوصول للتقارير',
        'view-reports-customers' => 'تقارير العملاء',
        'manage-backups' => 'إدارة النسخ الاحتياطي',
        'manage-imports' => 'تحديث النسخة البرمجية',
        'view-blog' => 'عرض مقالات المدونة',
        'create-blog' => 'إضافة مقال جديد',
        'edit-blog' => 'تعديل المقالات',
        'delete-blog' => 'حذف المقالات',
        'manage-barcodes' => 'إدارة الباركود و QR',
        'manage-customer-tiers' => 'إعدادات فئات العملاء',
        'manage-reviews' => 'إدارة تقييمات المنتج',
    ];

    function permissionGroupName($permissionName) {
        if (str_contains($permissionName, 'product')) return 'المنتجات (Products)';
        if (str_contains($permissionName, 'blog')) return 'المدونة (Blog)';
        if (str_contains($permissionName, 'category')) return 'التصنيفات (Categories)';
        if (str_contains($permissionName, 'order')) return 'الطلبات (Orders)';
        if (str_contains($permissionName, 'user') || str_contains($permissionName, 'role')) return 'المستخدمون (Users)';
        if (str_contains($permissionName, 'customer')) return 'العملاء (Customers)';
        if (str_contains($permissionName, 'discount')) return 'الكوبونات (Coupons)';
        if (str_contains($permissionName, 'report') || str_contains($permissionName, 'expense')) return 'التقارير (Reports)';
        if (str_contains($permissionName, 'setting') || str_contains($permissionName, 'backup') || str_contains($permissionName, 'import')) return 'النظام (System)';
        return 'عام (General)';
    }

    $groupedPermissions = $permissions->groupBy(fn($p) => permissionGroupName($p->name));
    
    $icons = [
        'المنتجات (Products)' => 'bi-watch', 'المدونة (Blog)' => 'bi-journal-richtext', 'التصنيفات (Categories)' => 'bi-tags',
        'الطلبات (Orders)' => 'bi-cart-check', 'المستخدمون (Users)' => 'bi-people', 'العملاء (Customers)' => 'bi-person-badge',
        'الكوبونات (Coupons)' => 'bi-percent', 'التقارير (Reports)' => 'bi-graph-up-arrow', 'النظام (System)' => 'bi-gear-wide-connected', 'عام (General)' => 'bi-shield-check',
    ];
@endphp

@push('styles')
<style>
    .settings-container {
        display: flex;
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.05);
        overflow: hidden;
        min-height: 600px;
        border: 1px solid #f0f0f0;
    }
    
    .settings-sidebar {
        width: 250px;
        background: #fdfdfd;
        border-right: 1px solid #f0f0f0;
        display: flex;
        flex-direction: column;
    }
    .sidebar-header { padding: 1.5rem; border-bottom: 1px solid #f0f0f0; }
    .sidebar-title { font-size: 1.1rem; font-weight: 800; color: #1a1a1a; margin: 0; }
    
    .sidebar-menu { flex: 1; overflow-y: auto; padding: 0.75rem 0; }
    .menu-item {
        padding: 0.75rem 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        color: #555;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.2s;
        border-right: 3px solid transparent;
        text-align: right;
        justify-content: flex-end;
    }
    .menu-item:hover { background: rgba(0,0,0,0.02); color: var(--primary-dark); }
    .menu-item.active {
        background: rgba(109, 14, 22, 0.05);
        color: var(--primary-dark);
        border-right-color: var(--primary-dark);
    }
    .menu-item i { font-size: 1rem; opacity: 0.7; }
    
    .badge-count {
        font-size: 0.6rem;
        background: #eee;
        color: #666;
        padding: 0.1rem 0.35rem;
        border-radius: 50px;
        margin-right: auto;
    }
    .menu-item.active .badge-count { background: var(--primary-dark); color: #fff; }

    .settings-content { flex: 1; display: flex; flex-direction: column; background: #fff; }
    .content-header {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #f0f0f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #fff;
        position: sticky; top: 0; z-index: 10;
    }
    .search-wrapper { position: relative; width: 250px; }
    .search-input {
        width: 100%;
        padding: 0.5rem 2.2rem 0.5rem 1rem;
        border-radius: 50px;
        border: 1px solid #e0e0e0;
        font-size: 0.85rem;
        background: #f9f9f9;
        transition: 0.3s;
    }
    .search-input:focus { background: #fff; border-color: var(--primary-dark); outline: none; }
    .search-icon { position: absolute; right: 0.8rem; top: 50%; transform: translateY(-50%); color: #aaa; font-size: 0.8rem; }
    
    .content-body { padding: 1.5rem; flex: 1; overflow-y: auto; }
    .module-section { display: none; }
    .module-section.active { display: block; }
    
    .section-info { margin-bottom: 1.5rem; border-bottom: 1px solid #f8f8f8; padding-bottom: 1rem; }
    .section-title { font-size: 1.25rem; font-weight: 800; color: #1a1a1a; display: flex; align-items: center; gap: 0.6rem; }
    
    .perm-card {
        background: #fff;
        border: 1px solid #f0f0f0;
        border-radius: 10px;
        padding: 0.85rem 1.25rem;
        margin-bottom: 0.6rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: 0.2s;
    }
    .perm-card:hover { border-color: var(--primary-dark); }
    .perm-info { text-align: right; }
    .perm-title { font-weight: 700; color: #333; font-size: 0.9rem; display: block; }
    .perm-slug { font-size: 0.7rem; color: #aaa; font-family: monospace; }

    .ios-switch {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
    }
    .ios-switch input { opacity: 0; width: 0; height: 0; }
    .ios-slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #e9e9ea;
        transition: .3s;
        border-radius: 34px;
    }
    .ios-slider:before {
        position: absolute;
        content: "";
        height: 20px; width: 20px; left: 2px; bottom: 2px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    input:checked + .ios-slider { background-color: #34c759; }
    input:checked + .ios-slider:before { transform: translateX(20px); }

    .top-action-bar {
        background: var(--primary-dark);
        color: #fff;
        padding: 0.8rem 1.5rem;
        border-radius: 12px;
        margin-bottom: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .role-badge { background: rgba(255,255,255,0.2); padding: 0.25rem 0.75rem; border-radius: 50px; font-size: 0.8rem; font-weight: 700; }
    .btn-save-fixed {
        background: #fff;
        color: var(--primary-dark);
        border: none;
        padding: 0.5rem 1.5rem;
        border-radius: 50px;
        font-weight: 800;
        font-size: 0.85rem;
        transition: 0.2s;
    }
    .btn-save-fixed:hover { transform: scale(1.03); }

    @media (max-width: 992px) {
        .settings-container { flex-direction: column; }
        .settings-sidebar { width: 100%; border-right: none; border-bottom: 1px solid #f0f0f0; }
        .sidebar-menu { display: flex; overflow-x: auto; }
        .menu-item { border-right: none; border-bottom: 2px solid transparent; white-space: nowrap; }
        .menu-item.active { border-bottom-color: var(--primary-dark); background: transparent; }
    }
</style>
@endpush

@section('content')
@can('edit-roles')
<div class="top-action-bar">
    <div class="d-flex align-items-center gap-3">
        <div class="role-badge">{{ $role->name }}</div>
        <div class="small opacity-75">إدارة الصلاحيات والوصول</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.roles.index') }}" class="btn btn-sm btn-outline-light border-0 opacity-75 px-3">إلغاء</a>
        <button form="permissions-form" type="submit" class="btn btn-save-fixed">حفظ التغييرات</button>
    </div>
</div>

<form action="{{ route('admin.roles.update', $role->id) }}" method="POST" id="permissions-form">
    @csrf
    @method('PUT')
    
    <div class="settings-container">
        <div class="settings-sidebar">
            <div class="sidebar-header">
                <p class="sidebar-title">أقسام الصلاحيات</p>
            </div>
            <div class="sidebar-menu" id="module-tabs">
                @foreach($groupedPermissions as $groupName => $group)
                    <div class="menu-item @if($loop->first) active @endif" data-target="section-{{ $loop->index }}">
                        <span class="badge-count">{{ $group->count() }}</span>
                        <span>{{ $groupName }}</span>
                        <i class="bi {{ $icons[$groupName] ?? 'bi-grid' }}"></i>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="settings-content">
            <div class="content-header">
                <div class="search-wrapper">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" id="perm-search" class="search-input" placeholder="بحث...">
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="small text-muted fw-bold" id="enabled-count">{{ $role->permissions->count() }} مفعلة</span>
                    <div class="form-check form-switch p-0 m-0 ms-3">
                        <input class="form-check-input ms-0 me-2 shadow-none" type="checkbox" id="select-all-group" style="cursor:pointer">
                        <label class="small fw-bold text-muted cursor-pointer" for="select-all-group">تحديد الكل</label>
                    </div>
                </div>
            </div>

            <div class="content-body" id="sections-container">
                @foreach($groupedPermissions as $groupName => $group)
                    <div class="module-section @if($loop->first) active @endif" id="section-{{ $loop->index }}">
                        <div class="section-info">
                            <h2 class="section-title">
                                <i class="bi {{ $icons[$groupName] ?? 'bi-grid' }}"></i>
                                {{ $groupName }}
                            </h2>
                        </div>
                        
                        <div class="perms-list">
                            @foreach($group as $permission)
                                <div class="perm-card" data-search="{{ $translations[$permission->name] ?? $permission->name }}">
                                    <div class="perm-info">
                                        <label class="perm-title" for="perm_{{ $permission->id }}" style="cursor:pointer">
                                            {{ $translations[$permission->name] ?? $permission->name }}
                                        </label>
                                        <span class="perm-slug">{{ $permission->name }}</span>
                                    </div>
                                    <label class="ios-switch">
                                        <input type="checkbox" 
                                               name="permissions[]" 
                                               value="{{ $permission->id }}" 
                                               id="perm_{{ $permission->id }}"
                                               class="permission-checkbox"
                                               @if($role->permissions->contains($permission->id)) checked @endif>
                                        <span class="ios-slider"></span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</form>
@else
    <div class="alert alert-danger p-4 rounded-4 shadow-sm text-center">
        <i class="bi bi-exclamation-triangle display-4 text-warning mb-3"></i>
        <h4>عذراً، ليس لديك الصلاحية</h4>
        <p class="text-muted">يرجى التواصل مع مدير النظام للحصول على صلاحية تعديل الأدوار الوظيفية.</p>
    </div>
@endcan
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const menuItems = document.querySelectorAll('.menu-item');
        const sections = document.querySelectorAll('.module-section');
        const searchInput = document.getElementById('perm-search');
        const permCards = document.querySelectorAll('.perm-card');
        const totalEnabledEl = document.getElementById('enabled-count');

        menuItems.forEach(item => {
            item.addEventListener('click', function() {
                const target = this.getAttribute('data-target');
                menuItems.forEach(i => i.classList.remove('active'));
                sections.forEach(s => s.classList.remove('active'));
                this.classList.add('active');
                document.getElementById(target).classList.add('active');
                searchInput.value = '';
                permCards.forEach(c => c.style.display = 'flex');
            });
        });

        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            permCards.forEach(card => {
                const text = card.getAttribute('data-search') || card.innerText.toLowerCase();
                card.style.display = text.includes(query) ? 'flex' : 'none';
            });
            if (query.length > 0) {
                sections.forEach(s => { s.classList.add('active'); s.style.display = 'block'; });
            } else {
                const activeTabId = document.querySelector('.menu-item.active').getAttribute('data-target');
                sections.forEach(s => s.classList.remove('active'));
                document.getElementById(activeTabId).classList.add('active');
            }
        });

        function updateCount() {
            const checked = document.querySelectorAll('.permission-checkbox:checked').length;
            totalEnabledEl.textContent = `${checked} مفعلة`;
        }
        document.querySelectorAll('.permission-checkbox').forEach(cb => {
            cb.addEventListener('change', updateCount);
        });

        document.getElementById('select-all-group').addEventListener('change', function() {
            const activeSection = document.querySelector('.module-section.active');
            if (activeSection) {
                activeSection.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = this.checked);
                updateCount();
            }
        });
    });
</script>
@endpush