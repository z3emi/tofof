@extends('admin.layout')

@section('title', 'إدارة فريق العمل')

@push('styles')
<style>
    /* تمييز المدير المحظور باللون الأحمر */
    .table-danger, .table-danger > th, .table-danger > td {
        background-color: #fbe9e7 !important;
        text-decoration: line-through;
        opacity: 0.7;
    }
    /* تمييز المدير غير المفعل باللون الرمادي */
    .table-inactive, .table-inactive > th, .table-inactive > td {
        background-color: #f1f3f5 !important;
        opacity: 0.8;
    }

    /* تخصيص شكل روابط التنقل */
    .pagination {
        justify-content: center !important;
        gap: 0.4rem;
        margin-top: 1rem;
    }
    .pagination .page-item .page-link {
        background-color: #f9f5f1 !important;
        color: #cd8985 !important;
        border-color: #cd8985 !important;
        font-weight: 600;
        border-radius: 0.375rem;
        transition: background-color 0.3s, color 0.3s;
        box-shadow: none;
    }
    .pagination .page-item .page-link:hover {
        background-color: #dcaca9 !important;
        color: #fff !important;
        border-color: #dcaca9 !important;
    }
    .pagination .page-item.active .page-link {
        background-color: #cd8985 !important;
        border-color: #cd8985 !important;
        color: #fff !important;
    }

    /* صفوف قابلة للتحديد */
    .selectable-row { cursor: pointer; transition: background-color .12s ease; }
    .selectable-row.selected td {
        background-color: #f3e5e3 !important;
        --bs-table-accent-bg: #f3e5e3 !important;
    }
    .selectable-row.selected {
        outline: 2px solid #cd8985;
        outline-offset: -2px;
    }
</style>
@endpush

@section('content')
@php
    $adminUser = auth('admin')->user();
    $canCreateManagers = $adminUser?->can('create-managers') ?? false;
    $canViewTrashedManagers = $adminUser?->can('view-trashed-managers') ?? false;
@endphp

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h4 class="mb-0">فريق العمل</h4>
        <div class="d-flex align-items-center flex-wrap gap-2">
            @if($canViewTrashedManagers)
                <a href="{{ route('admin.managers.trash') }}" class="btn btn-outline-danger btn-sm" title="سلة المحذوفات">
                    <i class="bi bi-trash me-1"></i> سلة المحذوفات
                </a>
            @endif
            @if($canCreateManagers)
                <a href="{{ route('admin.managers.create') }}" class="btn btn-primary btn-sm" style="background-color: #cd8985; border-color: #cd8985;">
                    <i class="bi bi-plus-circle me-1"></i> إضافة مدير جديد
                </a>
            @endif
        </div>
    </div>
    <div class="card-body">
        {{-- فورم البحث --}}
        <form method="GET" action="{{ route('admin.managers.index') }}" class="row g-2 mb-4">
            <div class="col">
                <input type="text" name="search" class="form-control" placeholder="ابحث بالاسم، البريد أو رقم الهاتف..." value="{{ request('search') }}">
            </div>
            <div class="col-auto">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">كل الحالات</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>نشط</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>غير مفعل</option>
                    <option value="banned" {{ request('status') == 'banned' ? 'selected' : '' }}>محظور</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary" style="background-color: #cd8985; border-color: #cd8985;">
                    <i class="bi bi-search me-1"></i> بحث
                </button>
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.managers.index') }}" class="btn btn-light border ms-1">
                        إعادة ضبط
                    </a>
                @endif
            </div>
        </form>

        <div class="mt-2 mb-3 ps-1">
            <span class="small text-muted fw-medium">
                <i class="bi bi-info-circle me-1"></i>
                عرض {{ $managers->count() }} من أصل {{ $managers->total() }} مدير
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle" style="min-width: 1000px;">
                <thead class="table-light">
                    <tr>
                        <th width="80">#</th>
                        <th>المدير</th>
                        <th>التواصل</th>
                        <th>الحالة</th>
                        <th>الأدوار</th>
                        <th>تاريخ الانضمام</th>
                        <th width="150">العمليات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($managers as $manager)
                        @php
                            $isSelf = $adminUser && (string)$adminUser->id === (string)$manager->id;
                            $isBanned = $manager->banned_at;
                            $isInactive = is_null($manager->phone_verified_at) && is_null($manager->banned_at);
                        @endphp
                        <tr @class([
                            'selectable-row',
                            'table-danger' => $isBanned,
                            'table-inactive' => $isInactive,
                        ]) data-manager-id="{{ $manager->id }}">
                            <td>{{ $manager->id }}</td>
                            <td>
                                <div class="d-flex align-items-center justify-content-center">
                                    <div class="avatar-circle me-2 bg-light text-dark fw-bold d-flex align-items-center justify-content-center border" style="width: 38px; height: 38px; border-radius: 10px;">
                                        {{ mb_substr($manager->name, 0, 1) }}
                                    </div>
                                    <div class="text-start">
                                        <div class="fw-bold">{{ $manager->name }}</div>
                                        <div class="small text-muted">{{ $manager->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $manager->phone_number ?? '---' }}</td>
                            <td>
                                @if($isBanned)
                                    <span class="badge bg-danger">محظور</span>
                                @elseif($isInactive)
                                    <span class="badge bg-warning text-dark">غير مفعل</span>
                                @else
                                    <span class="badge bg-success">نشط</span>
                                @endif
                            </td>
                            <td>
                                @foreach($manager->roles as $role)
                                    <span class="badge bg-light text-dark border px-2 py-1">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td>{{ $manager->created_at->format('Y-m-d') }}</td>
                            <td>
                                {{-- HIDDEN ACTIONS FOR CONTEXT MENU picked up by the global listener --}}
                                <div class="btn-group">
                                    @can('edit-managers')
                                        <a href="{{ route('admin.managers.edit', $manager->id) }}" class="btn btn-sm btn-outline-info m-1 px-2" title="تعديل">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    @endcan
                                    
                                    @can('ban-managers')
                                        @if($isBanned)
                                            <form action="{{ route('admin.managers.unban', $manager->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success m-1 px-2" title="إلغاء الحظر" onclick="return confirm('تأكيد إلغاء حظر هذا المدير؟')">
                                                    <i class="bi bi-unlock"></i>
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.managers.ban', $manager->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger m-1 px-2" title="حظر" onclick="return confirm('تأكيد حظر هذا المدير؟')">
                                                    <i class="bi bi-slash-circle"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endcan

                                    @if($adminUser->can('delete-managers') && !$isSelf)
                                        <form action="{{ route('admin.managers.destroy', $manager->id) }}" method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger m-1 px-2" title="حذف" onclick="return confirm('هل أنت متأكد من حذف هذا المدير؟ سيتم نقله لسلة المحذوفات.')">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">لا يوجد مدراء لعرضهم حالياً.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
            <form method="GET" action="{{ route('admin.managers.index') }}" class="d-flex align-items-center">
                @foreach(request()->except(['per_page', 'page']) as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <label for="per_page" class="me-2 small text-muted">المدراء لكل صفحة:</label>
                <select name="per_page" id="per_page" class="form-select form-select-sm" onchange="this.form.submit()" style="width: 80px;">
                    @foreach([5, 10, 50, 100, 500] as $size)
                        <option value="{{ $size }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>
                            {{ $size }}
                        </option>
                    @endforeach
                </select>
            </form>

            <div>
                {{ $managers->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let selectedRow = null;
    const rows = document.querySelectorAll('.selectable-row');

    rows.forEach(row => {
        row.addEventListener('click', function(e) {
            if(e.target.closest('a') || e.target.closest('button') || e.target.closest('form')) {
                return;
            }
            if (selectedRow) {
                selectedRow.classList.remove('selected');
            }
            this.classList.add('selected');
            selectedRow = this;
        });
    });
});
</script>
@endpush


