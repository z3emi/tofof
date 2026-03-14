@extends('admin.layout')

@section('title', 'تعديل سجل حضور')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">تعديل سجل الحضور للموظف {{ $attendance->employee?->name }}</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.hr.attendance.index') }}" class="btn btn-secondary">عودة للسجل</a>
        <form action="{{ route('admin.hr.attendance.destroy', $attendance) }}" method="POST" data-confirm-message="هل أنت متأكد من حذف هذا السجل؟">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">حذف السجل</button>
        </form>
    </div>
</div>

<form action="{{ route('admin.hr.attendance.update', $attendance) }}" method="POST" class="card shadow-sm">
    @csrf
    @method('PUT')
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">الموظف</label>
                <select name="employee_id" class="form-select" required>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" @selected(old('employee_id', $attendance->employee_id) == $employee->id)>{{ $employee->name }}</option>
                    @endforeach
                </select>
                @error('employee_id')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">التاريخ</label>
                <input type="date" name="attendance_date" value="{{ old('attendance_date', $attendance->attendance_date->format('Y-m-d')) }}" class="form-control" required>
                @error('attendance_date')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">الحالة</label>
                <select name="status" class="form-select" required>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $attendance->status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">وقت الحضور</label>
                <input type="time" name="check_in_at" value="{{ old('check_in_at', optional($attendance->check_in_at)->format('H:i')) }}" class="form-control">
                @error('check_in_at')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">وقت الانصراف</label>
                <input type="time" name="check_out_at" value="{{ old('check_out_at', optional($attendance->check_out_at)->format('H:i')) }}" class="form-control">
                @error('check_out_at')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label">ملاحظات إضافية</label>
                <textarea name="notes" class="form-control" rows="3">{{ old('notes', $attendance->notes) }}</textarea>
                @error('notes')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
    <div class="card-footer text-end">
        <button type="submit" class="btn btn-primary">تحديث السجل</button>
    </div>
</form>
@endsection
