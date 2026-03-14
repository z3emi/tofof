@extends('admin.layout')

@section('title', 'سجل الحضور والانصراف')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">سجل الحضور والانصراف</h4>
    <a href="{{ route('admin.hr.attendance.create') }}" class="btn btn-primary">تسجيل حضور</a>
</div>

@if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">الموظف</label>
                <select name="employee_id" class="form-select">
                    <option value="">الكل</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" @selected(request('employee_id') == $employee->id)>{{ $employee->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">الحالة</label>
                <select name="status" class="form-select">
                    <option value="">الكل</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">من تاريخ</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">إلى تاريخ</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
            </div>
            <div class="col-md-3 text-end">
                <button class="btn btn-outline-primary me-2" type="submit">تصفية</button>
                <a href="{{ route('admin.hr.attendance.index') }}" class="btn btn-outline-secondary">إعادة تعيين</a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>التاريخ</th>
                    <th>الموظف</th>
                    <th class="text-center">وقت الحضور</th>
                    <th class="text-center">وقت الانصراف</th>
                    <th class="text-center">الحالة</th>
                    <th>سجل بواسطة</th>
                    <th class="text-end">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                    <tr>
                        <td>{{ $record->attendance_date->format('Y-m-d') }}</td>
                        <td>
                            <div class="fw-semibold">{{ $record->employee?->name ?? '—' }}</div>
                            <div class="text-muted small">{{ $record->employee?->phone_number }}</div>
                        </td>
                        <td class="text-center">{{ $record->check_in_at ? $record->check_in_at->format('H:i') : '—' }}</td>
                        <td class="text-center">{{ $record->check_out_at ? $record->check_out_at->format('H:i') : '—' }}</td>
                        <td class="text-center">
                            <span class="badge {{ $record->status === 'present' ? 'bg-success' : ($record->status === 'absent' ? 'bg-danger' : 'bg-warning text-dark') }}">{{ $statuses[$record->status] ?? $record->status }}</span>
                        </td>
                        <td>{{ $record->recorder?->name ?? 'النظام' }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.hr.attendance.edit', $record) }}" class="btn btn-sm btn-outline-primary" data-ignore-row-select>
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.hr.attendance.destroy', $record) }}" method="POST" class="d-inline" data-confirm-message="هل أنت متأكد من حذف هذا السجل؟">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" data-ignore-row-select>
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">لا توجد سجلات حضور حالياً.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $records->links() }}
    </div>
</div>
@endsection
