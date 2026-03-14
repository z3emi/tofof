@extends('admin.layout')

@section('title', 'تعديل طلب السلفة')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">تعديل طلب السلفة للموظف {{ $advanceRequest->employee?->name }}</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.hr.advance-requests.index') }}" class="btn btn-secondary">عودة للقائمة</a>
        <form action="{{ route('admin.hr.advance-requests.destroy', $advanceRequest) }}" method="POST" data-confirm-message="هل أنت متأكد من حذف هذا الطلب؟">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">حذف الطلب</button>
        </form>
    </div>
</div>

<form action="{{ route('admin.hr.advance-requests.update', $advanceRequest) }}" method="POST" class="card shadow-sm">
    @csrf
    @method('PUT')
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">الموظف</label>
                <select name="employee_id" class="form-select" required>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" @selected(old('employee_id', $advanceRequest->employee_id) == $employee->id)>{{ $employee->name }}</option>
                    @endforeach
                </select>
                @error('employee_id')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">المدير المسؤول (اختياري)</label>
                <select name="manager_id" class="form-select">
                    <option value="">—</option>
                    @foreach($managers as $manager)
                        <option value="{{ $manager->id }}" @selected(old('manager_id', $advanceRequest->manager_id) == $manager->id)>{{ $manager->name }}</option>
                    @endforeach
                </select>
                @error('manager_id')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">المبلغ</label>
                <input type="number" step="0.01" min="1" name="amount" class="form-control" value="{{ old('amount', $advanceRequest->amount) }}" required>
                @error('amount')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">تاريخ السداد</label>
                <input type="date" name="repayment_date" class="form-control" value="{{ old('repayment_date', $advanceRequest->repayment_date->format('Y-m-d')) }}" required>
                @error('repayment_date')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">حالة الطلب</label>
                <select name="status" class="form-select" required>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $advanceRequest->status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label">السبب (اختياري)</label>
                <textarea name="reason" class="form-control" rows="3">{{ old('reason', $advanceRequest->reason) }}</textarea>
                @error('reason')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-end">
        <button type="submit" class="btn btn-primary">تحديث الطلب</button>
    </div>
</form>
@endsection
