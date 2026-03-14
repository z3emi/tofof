@extends('admin.layout')

@section('title', 'تعديل طلب الإجازة')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">تعديل طلب الإجازة للموظف {{ $leaveRequest->employee?->name }}</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.hr.leave-requests.index') }}" class="btn btn-secondary">عودة للقائمة</a>
        <form action="{{ route('admin.hr.leave-requests.destroy', $leaveRequest) }}" method="POST" data-confirm-message="هل أنت متأكد من حذف هذا الطلب؟">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">حذف الطلب</button>
        </form>
    </div>
</div>

<form action="{{ route('admin.hr.leave-requests.update', $leaveRequest) }}" method="POST" class="card shadow-sm">
    @csrf
    @method('PUT')
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">الموظف</label>
                <select name="employee_id" class="form-select" required>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" @selected(old('employee_id', $leaveRequest->employee_id) == $employee->id)>{{ $employee->name }}</option>
                    @endforeach
                </select>
                @error('employee_id')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">المدير المسؤول (اختياري)</label>
                <select name="manager_id" class="form-select">
                    <option value="">—</option>
                    @foreach($managers as $manager)
                        <option value="{{ $manager->id }}" @selected(old('manager_id', $leaveRequest->manager_id) == $manager->id)>{{ $manager->name }}</option>
                    @endforeach
                </select>
                @error('manager_id')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">نوع الإجازة</label>
                <input type="text" name="leave_type" class="form-control" value="{{ old('leave_type', $leaveRequest->leave_type) }}" required>
                @error('leave_type')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">عدد الأيام</label>
                <input type="number" name="days" min="1" class="form-control" value="{{ old('days', $leaveRequest->days) }}" required>
                @error('days')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">تاريخ البدء</label>
                <input type="date" name="start_date" class="form-control" value="{{ old('start_date', optional($leaveRequest->start_date)->format('Y-m-d')) }}">
                @error('start_date')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">حالة الطلب</label>
                <select name="status" class="form-select" required>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $leaveRequest->status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label">السبب (اختياري)</label>
                <textarea name="reason" class="form-control" rows="3">{{ old('reason', $leaveRequest->reason) }}</textarea>
                @error('reason')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-end">
        <button type="submit" class="btn btn-primary">تحديث الطلب</button>
    </div>
</form>
@endsection
