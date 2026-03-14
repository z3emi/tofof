@extends('frontend.profile.layout')
@section('title', 'طلبات السلف')

@section('profile-content')
<div class="surface">
    <div class="page-head mb-3">
        <h2 class="text-2xl font-bold">طلبات السلف المالية</h2>
        <p class="text-muted">يمكنك طلب سلفة مالية وسيتم اعتمادها من مديرك.</p>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form action="{{ route('hr.advance-requests.store') }}" method="POST" class="card mb-4">
        @csrf
        <div class="card-body row g-3">
            <div class="col-md-4">
                <label class="form-label">المبلغ المطلوب (د.ع)</label>
                <input type="number" step="0.01" min="1" name="amount" class="form-control" value="{{ old('amount') }}" required>
                @error('amount')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">تاريخ السداد</label>
                <input type="date" name="repayment_date" class="form-control" value="{{ old('repayment_date') }}" required>
                @error('repayment_date')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label">سبب السلفة (اختياري)</label>
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
            <h5 class="mb-0">سجل طلبات السلف</h5>
        </div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>المبلغ</th>
                        <th>تاريخ السداد</th>
                        <th>الحالة</th>
                        <th>آخر تحديث</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $request)
                        <tr>
                            <td>{{ number_format($request->amount, 2) }} د.ع</td>
                            <td>{{ $request->repayment_date->format('Y-m-d') }}</td>
                            <td>
                                @switch($request->status)
                                    @case('approved')
                                        <span class="badge bg-success">موافق عليه</span>
                                        @break
                                    @case('rejected')
                                        <span class="badge bg-danger">مرفوض</span>
                                        @break
                                    @case('settled')
                                        <span class="badge bg-info">مسدد</span>
                                        @break
                                    @default
                                        <span class="badge bg-warning text-dark">قيد المراجعة</span>
                                @endswitch
                            </td>
                            <td>{{ $request->updated_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">لا توجد طلبات مسجلة.</td>
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
