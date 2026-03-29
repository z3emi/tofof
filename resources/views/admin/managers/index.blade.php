@extends('admin.layout')

@section('title', 'إدارة فريق العمل')

@push('styles')
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem; color: white; border-radius: 0 !important; }
    
    .table-danger, .table-danger td { background-color: #fff1f0 !important; text-decoration: line-through; opacity: 0.8; }
    .table-inactive, .table-inactive td { background-color: #f8f9fa !important; opacity: 0.8; }
    
    .pagination .page-item .page-link { color: var(--primary-dark); border-radius: 8px; margin: 0 2px; }
    .pagination .page-item.active .page-link { background-color: var(--primary-dark); border-color: var(--primary-dark); color: #fff; }
    
    .selectable-row { cursor: pointer; transition: all 0.2s; }
    .selectable-row:hover { background-color: rgba(109, 14, 22, 0.02) !important; }
    .selectable-row.selected td { background-color: #fcecea !important; outline: 2px solid var(--primary-dark); }

    .search-box { border-radius: 12px; border: 1px solid #e2e8f0; padding: 0.75rem 1.2rem; background: #fafbff; }
</style>
@endpush

@section('content')
@php
    $adminUser = auth('admin')->user();
    $canCreate = $adminUser?->can('create-managers') ?? false;
    $canTrash = $adminUser?->can('view-trashed-managers') ?? false;
@endphp

<div class="form-card">
    <div class="form-card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2 class="mb-2 fw-bold text-white"><i class="bi bi-people-fill me-2"></i> إدارة فريق العمل</h2>
            <p class="mb-0 opacity-75 fs-6 text-white small">إدارة المدراء، الصلاحيات، وحالة الحسابات في النظام.</p>
        </div>
        <div class="d-flex gap-2">
            <div class="col-toggle-place"></div>
            @if($canTrash)
                <a href="{{ route('admin.managers.trash') }}" class="btn btn-outline-danger p-2 d-inline-flex align-items-center justify-content-center" style="width:40px; height:40px; border-radius:10px" title="المهملات"><i class="bi bi-trash"></i></a>
            @endif
            @if($canCreate)
                <a href="{{ route('admin.managers.create') }}" class="btn btn-light px-4 py-2 rounded-3 fw-bold text-brand"><i class="bi bi-plus-circle me-1"></i> إضافة مدير</a>
            @endif
        </div>
    </div>
    
    <div class="p-4 p-lg-5">
        <form method="GET" action="{{ route('admin.managers.index') }}" class="row g-3 mb-4 p-4 bg-light rounded-4 border align-items-end">
            <div class="col-md-8">
                <label class="small fw-bold text-muted mb-2">بحث سريع (الاسم، البريد أو الهاتف)</label>
                <input type="text" name="search" class="form-control search-input" placeholder="بحث بالاسم، البريد أو اسم المستخدم..." value="{{ request('search') }}">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn text-white px-4 py-3 fw-bold flex-grow-1" style="background:var(--primary-dark); border-radius:12px">بحث</button>
                <button type="button" class="btn btn-outline-dark d-flex align-items-center justify-content-center" style="width:58px; height:58px; border-radius:12px" data-bs-toggle="modal" data-bs-target="#filtersModal" title="فلاتر إضافية">
                    <i class="bi bi-funnel fs-4"></i>
                </button>
                @if(request()->anyFilled(['search','status','role_id','date_from','date_to']))
                    <a href="{{ route('admin.managers.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" style="width:58px; height:58px; border-radius:12px" title="تصفير">
                        <i class="bi bi-arrow-counterclockwise fs-4"></i>
                    </a>
                @endif
            </div>
        </form>

        <div class="table-responsive border rounded-4 overflow-hidden shadow-sm">
            <table class="table mb-0 align-middle text-center" id="managers_table">
                <thead class="bg-light border-bottom">
                    <tr class="text-muted small fw-bold">
                        <th class="py-3" width="50" data-column-id="seq">{!! \App\Support\Sort::link('id', '#') !!}</th>
                        <th class="py-3" width="70" data-hide="true" data-column-id="id">ID</th>
                        <th class="py-3 text-start" data-column-id="name">{!! \App\Support\Sort::link('name', 'المدير') !!}</th>
                        <th class="py-3" data-column-id="username">{!! \App\Support\Sort::link('phone_number', 'اسم المستخدم') !!}</th>
                        <th class="py-3" data-column-id="status">الحالة</th>
                        <th class="py-3" data-column-id="roles">الأدوار</th>
                        <th class="py-3" data-column-id="created_at">{!! \App\Support\Sort::link('created_at', 'تاريخ الانضمام') !!}</th>
                        <th class="py-3" width="160" data-column-id="actions">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($managers as $manager)
                        @php
                            $isSelf = (string)$adminUser?->id === (string)$manager->id;
                            $isBanned = $manager->banned_at;
                            $isInactive = is_null($manager->phone_verified_at) && is_null($manager->banned_at);
                        @endphp
                        <tr class="selectable-row @if($isBanned) table-danger @elseif($isInactive) table-inactive @endif" data-manager-id="{{ $manager->id }}">
                            <td class="small text-muted">{{ $loop->iteration + ($managers->perPage() * ($managers->currentPage() - 1)) }}</td>
                            <td class="small text-muted">#{{ $manager->id }}</td>
                            <td class="text-start">
                                <div class="d-flex align-items-center">
                                    <img src="{{ $manager->avatar_url }}" class="rounded-circle border me-3" width="42" height="42" style="object-fit: cover;" onerror="this.src='{{ asset('storage/avatars/default.jpg') }}'">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $manager->name }}</div>
                                        <div class="small text-muted">{{ $manager->email ?? 'بدون بريد' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-light text-dark border px-2 py-1">{{ $manager->phone_number }}</span></td>
                            <td>
                                @if($isBanned) <span class="badge bg-danger">محظور</span>
                                @elseif($isInactive) <span class="badge bg-warning text-dark">غير نشط</span>
                                @else <span class="badge bg-success">نشط</span> @endif
                            </td>
                            <td>
                                @foreach($manager->roles as $role)
                                    <span class="badge bg-info bg-opacity-10 text-info border-info border-opacity-25 px-2 py-1 mb-1">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td class="text-muted small">{{ $manager->created_at->format('Y-m-d') }}</td>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    @can('edit-managers')
                                        <a href="{{ route('admin.managers.edit', $manager->id) }}" class="btn btn-sm btn-outline-primary rounded-3 px-2 py-1" title="تعديل"><i class="bi bi-pencil"></i></a>
                                    @endcan
                                    @can('ban-managers')
                                        @if($isBanned)
                                            <form action="{{ route('admin.managers.unban', $manager->id) }}" method="POST" class="d-inline">@csrf<button type="submit" class="btn btn-sm btn-outline-success rounded-3 px-2 py-1" title="فك الحظر" onclick="return confirm('تأكيد فك الحظر؟')"><i class="bi bi-unlock"></i></button></form>
                                        @else
                                            <form action="{{ route('admin.managers.ban', $manager->id) }}" method="POST" class="d-inline">@csrf<button type="submit" class="btn btn-sm btn-outline-warning rounded-3 px-2 py-1" title="حظر" onclick="return confirm('تأكيد الحظر؟')"><i class="bi bi-slash-circle"></i></button></form>
                                        @endif
                                    @endcan
                                    @if($adminUser?->can('delete-managers') && !$isSelf)
                                        <form action="{{ route('admin.managers.destroy', $manager->id) }}" method="POST" class="d-inline">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-danger rounded-3 px-2 py-1" title="حذف" onclick="return confirm('تأكيد الحذف للسلة؟')"><i class="bi bi-trash"></i></button></form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-5 text-muted">لا يوجد نتائج لعرضها حالياً.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="small text-muted">عرض {{ $managers->count() }} من أصل {{ $managers->total() }} سجل</div>
            <div>{{ $managers->withQueryString()->links() }}</div>
        </div>
    </div>
</div>

{{-- Filters Modal --}}
<div class="modal fade" id="filtersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:20px">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold mb-0"><i class="bi bi-funnel me-2"></i>تصفية المدراء المتقدمة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form method="GET" action="{{ route('admin.managers.index') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted mb-2">حالة الحساب</label>
                            <select name="status" class="form-select search-input">
                                <option value="">كل الحالات</option>
                                <option value="active" @selected(request('status')=='active')>نشط</option>
                                <option value="banned" @selected(request('status')=='banned')>محظور</option>
                                <option value="inactive" @selected(request('status')=='inactive')>غير مفعل</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted mb-2">الدور / الصلاحية</label>
                            <select name="role_id" class="form-select search-input">
                                <option value="">كل الأدوار</option>
                                @foreach(\Spatie\Permission\Models\Role::all() as $role)
                                    <option value="{{ $role->id }}" @selected(request('role_id')==$role->id)>{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted mb-2">تاريخ الانضمام (من)</label>
                            <input type="date" name="date_from" class="form-control search-input" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted mb-2">تاريخ الانضمام (إلى)</label>
                            <input type="date" name="date_to" class="form-control search-input" value="{{ request('date_to') }}">
                        </div>
                    </div>
                    <div class="mt-5 d-flex gap-2">
                        <button type="submit" class="btn text-white w-100 py-3 fw-bold" style="background:var(--primary-dark); border-radius:12px">تطبيق الفلاتر</button>
                        <a href="{{ route('admin.managers.index') }}" class="btn btn-outline-secondary px-4 py-3" style="border-radius:12px">تصفير الكل</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.selectable-row').forEach(row => {
            row.addEventListener('click', function(e) {
                if(e.target.closest('a') || e.target.closest('button')) return;
                document.querySelectorAll('.selectable-row').forEach(r => r.classList.remove('selected'));
                this.classList.add('selected');
            });
            row.addEventListener('dblclick', function () {
                const id = this.dataset.managerId;
                if (id) window.location.href = `{{ url('admin/managers') }}/${id}/edit`;
            });
        });
    });
</script>
@endsection
