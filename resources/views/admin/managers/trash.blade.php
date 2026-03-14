@extends('admin.layout')

@section('title', 'سلة محذوفات الإداريين')

@php
    $canViewContactDetails = $canViewContactDetails ?? false;
@endphp

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h4 class="mb-0">سلة محذوفات الإداريين</h4>
            <small class="text-muted">يمكنك استعادة الإداريين أو حذفهم نهائياً من هنا.</small>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.managers.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-right-circle"></i>
                العودة للمدراء
            </a>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.managers.trash') }}" class="row g-2 mb-4">
            <div class="col">
                <input type="text" name="search" class="form-control" placeholder="ابحث بالاسم أو رقم الهاتف..." value="{{ request('search') }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary" style="background-color:#FF5722;border-color:#FF5722;">
                    <i class="bi bi-search me-1"></i> بحث
                </button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>اليوزر</th>
                        <th>
                            @if($canViewContactDetails)
                                رقم الهاتف
                            @else
                                الهاتف (مخفي)
                            @endif
                        </th>
                        <th>المشرف المباشر</th>
                        <th>الأدوار</th>
                        <th>تاريخ الحذف</th>
                        <th>العمليات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($managers as $manager)
                        <tr>
                            <td>{{ $manager->id }}</td>
                            <td>{{ $manager->name }}</td>
                            <td>
                                @if($canViewContactDetails)
                                    {{ $manager->phone_number }}
                                @else
                                    <span class="text-muted">مخفي</span>
                                @endif
                            </td>
                            <td>{{ $manager->manager?->name ?? 'إدارة عليا' }}</td>
                            <td>
                                @if($manager->roles->isNotEmpty())
                                    <span class="badge bg-info text-dark">{{ $manager->roles->pluck('name')->join('، ') }}</span>
                                @else
                                    <span class="text-muted small">بدون دور</span>
                                @endif
                            </td>
                            <td>{{ optional($manager->deleted_at)->format('Y-m-d H:i') }}</td>
                            <td>
                                @can('restore-managers')
                                    <form action="{{ route('admin.managers.restore', $manager->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success m-1 px-2" title="استعادة">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    </form>
                                @endcan
                                @can('force-delete-managers')
                                    <form action="{{ route('admin.managers.forceDelete', $manager->id) }}" method="POST" class="d-inline" data-confirm-message="سيتم حذف هذا المدير نهائياً ولا يمكن التراجع، هل أنت متأكد؟">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger m-1 px-2" title="حذف نهائي">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-4">لا يوجد إداريون محذوفون.</td>
                        </tr>
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
