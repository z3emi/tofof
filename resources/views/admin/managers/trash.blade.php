@extends('admin.layout')

@section('title', 'سلة محذوفات الإداريين')

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
    <div class="form-card-header d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-2 fw-bold text-white"><i class="bi bi-trash-fill me-2"></i> سلة محذوفات الإداريين</h2>
            <p class="mb-0 opacity-75 fs-6 text-white small">إدارة حسابات الفريق التي تم حذفها مؤقتاً.</p>
        </div>
        <a href="{{ route('admin.managers.index') }}" class="btn btn-outline-light px-4 fw-bold rounded-3"><i class="bi bi-arrow-right me-1"></i> العودة للمدراء</a>
    </div>

    <div class="p-4 p-lg-5">
        <form method="GET" action="{{ route('admin.managers.trash') }}" class="row g-3 mb-4 p-4 bg-light rounded-4 border align-items-end">
            <div class="col-md-9">
                <label class="small fw-bold text-muted mb-2">بحث في المحذوفات</label>
                <input type="text" name="search" class="form-control search-input" placeholder="ابحث بالاسم أو اسم المستخدم..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn text-white w-100 py-3 fw-bold" style="background:var(--primary-dark); border-radius:12px">بحث وتطبيق</button>
            </div>
        </form>

        <div class="table-container shadow-sm border overflow-hidden">
            <table class="table mb-0 align-middle text-center">
                <thead class="bg-light border-bottom">
                    <tr class="text-muted small fw-bold">
                        <th class="py-3" width="80">#</th>
                        <th class="py-3 text-start">الإداري</th>
                        <th class="py-3">اسم المستخدم</th>
                        <th class="py-3">الأدوار</th>
                        <th class="py-3">تاريخ الحذف</th>
                        <th class="py-3" width="220">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($managers as $manager)
                        <tr>
                            <td class="small text-muted">#{{ $manager->id }}</td>
                            <td class="text-start">
                                <div class="d-flex align-items-center">
                                    <img src="{{ $manager->avatar_url }}" class="rounded-circle border me-3" width="42" height="42" onerror="this.src='{{ asset('storage/avatars/default.jpg') }}'">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $manager->name }}</div>
                                        <div class="small text-muted">{{ $manager->email ?? 'بدون بريد' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="small fw-bold">{{ $manager->phone_number }}</span></td>
                            <td>
                                @foreach($manager->roles as $role)
                                    <span class="badge bg-light text-dark border px-2 py-1">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td class="small text-muted">{{ optional($manager->deleted_at)->format('Y-m-d H:i') }}</td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    @can('restore-managers')
                                        <form action="{{ route('admin.managers.restore', $manager->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success rounded-3 px-3 fw-bold">
                                                <i class="bi bi-arrow-counterclockwise me-1"></i> استرجاع
                                            </button>
                                        </form>
                                    @endcan
                                    @can('force-delete-managers')
                                        <form action="{{ route('admin.managers.forceDelete', $manager->id) }}" method="POST" onsubmit="return confirm('حذف نهائي؟')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-3 px-3 fw-bold">
                                                <i class="bi bi-trash-fill me-1"></i> حذف نهائي
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-5 text-center text-muted">لا توجد عناصر في سلة المحذوفات.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
            <form method="GET" action="{{ route('admin.managers.trash') }}" class="d-flex align-items-center">
                @foreach(request()->except(['per_page', 'page']) as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <label for="per_page" class="me-2">عدد الإداريين:</label>
                <select name="per_page" id="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach([5, 10, 25, 50, 100] as $size)
                        <option value="{{ $size }}" {{ request('per_page', $perPage ?? 10) == $size ? 'selected' : '' }}>
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
