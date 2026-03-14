@extends('admin.layout')

@section('title', 'إضافة طلب إجازة يدوي')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">تسجيل طلب إجازة جديد</h4>
    <a href="{{ route('admin.hr.leave-requests.index') }}" class="btn btn-secondary">عودة للقائمة</a>
</div>

<form action="{{ route('admin.hr.leave-requests.store') }}" method="POST" class="card shadow-sm">
    @csrf
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">الموظف</label>
                <select name="employee_id" class="form-select" required>
                    <option value="">اختر الموظف</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" @selected(old('employee_id') == $employee->id)>
                            {{ $employee->name }}
                        </option>
                    @endforeach
                </select>
                @error('employee_id')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">المدير المسؤول (اختياري)</label>
                <select name="manager_id" class="form-select">
                    <option value="">—</option>
                    @foreach($managers as $manager)
                        <option value="{{ $manager->id }}" @selected(old('manager_id') == $manager->id)>
                            {{ $manager->name }}
                        </option>
                    @endforeach
                </select>
                @error('manager_id')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">نوع الإجازة</label>
                <input type="text" name="leave_type" class="form-control" value="{{ old('leave_type') }}" required>
                @error('leave_type')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">عدد الأيام</label>
                <input type="number" min="1" name="days" class="form-control" value="{{ old('days', 1) }}" required>
                @error('days')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">تاريخ البدء</label>
                <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}">
                @error('start_date')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label">السبب (اختياري)</label>
                <textarea name="reason" class="form-control" rows="3">{{ old('reason') }}</textarea>
                @error('reason')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">حالة الطلب</label>
                <select name="status" class="form-select" required>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', 'pending') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-end">
        <button type="submit" class="btn btn-primary">حفظ الطلب</button>
    </div>
</form>
@endsection
