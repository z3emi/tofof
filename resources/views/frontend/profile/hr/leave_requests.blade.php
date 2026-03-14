@extends('frontend.profile.layout')
@section('title', 'طلبات الإجازة')

@section('profile-content')
<div class="surface">
    <div class="page-head mb-3">
        <h2 class="text-2xl font-bold">طلبات الإجازة</h2>
        <p class="text-muted">قدم طلب إجازة وسيتم إشعار مديرك للموافقة.</p>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form action="{{ route('hr.leave-requests.store') }}" method="POST" class="card mb-4">
        @csrf
        <div class="card-body row g-3">
            <div class="col-md-4">
                <label class="form-label">نوع الإجازة</label>
                <input type="text" name="leave_type" class="form-control" value="{{ old('leave_type') }}" required>
                @error('leave_type')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">عدد الأيام</label>
                <input type="number" name="days" min="1" class="form-control" value="{{ old('days', 1) }}" required>
                @error('days')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">تاريخ البدء</label>
                <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}">
                @error('start_date')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label">سبب الإجازة (اختياري)</label>
                <textarea name="reason" rows="3" class="form-control">{{ old('reason') }}</textarea>
                @error('reason')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="card-footer text-end">
            <button class="btn btn-primary">إرسال الطلب</button>
        </div>
    </form>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">سجل الطلبات</h5>
        </div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>نوع الإجازة</th>
                        <th>عدد الأيام</th>
                        <th>تاريخ البدء</th>
                        <th>الحالة</th>
                        <th>آخر تحديث</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $request)
                        <tr>
                            <td>{{ $request->leave_type }}</td>
                            <td>{{ $request->days }}</td>
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
                            <td>{{ $request->updated_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">لم تقم بتقديم أي طلبات بعد.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $requests->links() }}
        </div>
    </div>
</div>
@endsection
