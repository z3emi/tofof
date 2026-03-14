@extends('admin.layout')

@section('title', 'طلبات الإجازة')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h4 class="mb-0">طلبات الإجازة</h4>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.hr.leave-requests.create') }}" class="btn btn-primary">إضافة طلب جديد</a>
        <form method="GET" class="d-flex align-items-center">
        <select name="status" class="form-select me-2">
            <option value="">كل الحالات</option>
            <option value="pending" @selected($status === 'pending')>قيد المراجعة</option>
            <option value="approved" @selected($status === 'approved')>موافق عليه</option>
            <option value="rejected" @selected($status === 'rejected')>مرفوض</option>
        </select>
        <button class="btn btn-primary">تصفية</button>
        </form>
    </div>
</div>

@if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>الموظف</th>
                    <th>المدير المباشر</th>
                    <th>نوع الإجازة</th>
                    <th class="text-center">عدد الأيام</th>
                    <th>تاريخ البدء</th>
                    <th>الحالة</th>
                    <th>آخر تحديث</th>
                    <th class="text-end">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $request)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $request->employee?->name ?? '—' }}</div>
                            <div class="text-muted small">{{ $request->employee?->phone_number ?? '—' }}</div>
                        </td>
                        <td>{{ $request->manager?->name ?? '—' }}</td>
                        <td>{{ $request->leave_type }}</td>
                        <td class="text-center">{{ $request->days }}</td>
                        <td>{{ optional($request->start_date)->format('Y-m-d') ?? '—' }}</td>
                        <td>
                            @switch($request->status)
                                @case('approved')
                                    <span class="badge bg-success">موافق عليه</span>
                                    @break
                                @case('rejected')
                                    <span class="badge bg-danger">مرفوض</span>
                                    @break
                                @default
                                    <span class="badge bg-warning text-dark">قيد المراجعة</span>
                            @endswitch
                        </td>
                        <td>{{ optional($request->updated_at)->diffForHumans() }}</td>
                        <td class="text-end">
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.hr.leave-requests.edit', $request) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.hr.leave-requests.destroy', $request) }}" method="POST" data-confirm-message="هل أنت متأكد من حذف هذا الطلب؟" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                            @if($request->status === 'pending')
                                <div class="d-inline-flex align-items-center gap-2 mt-2">
                                    <form action="{{ route('admin.hr.leave-requests.update', $request) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="update_mode" value="status_only">
                                        <input type="hidden" name="status" value="approved">
                                        <button class="btn btn-sm btn-success" type="submit">اعتماد</button>
                                    </form>
                                    <form action="{{ route('admin.hr.leave-requests.update', $request) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="update_mode" value="status_only">
                                        <input type="hidden" name="status" value="rejected">
                                        <button class="btn btn-sm btn-danger" type="submit">رفض</button>
                                    </form>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">لا توجد طلبات حالياً.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $requests->links() }}
    </div>
</div>
@endsection
