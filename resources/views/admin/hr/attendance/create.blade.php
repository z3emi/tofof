@extends('admin.layout')

@section('title', 'تسجيل حضور')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">تسجيل حضور موظف</h4>
    <a href="{{ route('admin.hr.attendance.index') }}" class="btn btn-secondary">العودة للسجل</a>
</div>

<form action="{{ route('admin.hr.attendance.store') }}" method="POST" class="card shadow-sm">
    @csrf
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">الموظف</label>
                <select name="employee_id" class="form-select" required>
                    <option value="" disabled selected>اختر موظفاً</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" @selected(old('employee_id') == $employee->id)>{{ $employee->name }}</option>
                    @endforeach
                </select>
                @error('employee_id')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">التاريخ</label>
                <input type="date" name="attendance_date" value="{{ old('attendance_date', now()->toDateString()) }}" class="form-control" required>
                @error('attendance_date')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">الحالة</label>
                <select name="status" class="form-select" required>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', 'present') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">وقت الحضور</label>
                <input type="time" name="check_in_at" value="{{ old('check_in_at') }}" class="form-control">
                @error('check_in_at')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">وقت الانصراف</label>
                <input type="time" name="check_out_at" value="{{ old('check_out_at') }}" class="form-control">
                @error('check_out_at')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label">ملاحظات إضافية</label>
                <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                @error('notes')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
    <div class="card-footer text-end">
        <button type="submit" class="btn btn-primary">حفظ السجل</button>
    </div>
</form>
@endsection
