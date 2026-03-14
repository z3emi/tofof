@extends('admin.layout')

@section('title', 'إدارة المصاريف')

@push('styles')
<style>
    .card-header {
        background-color: #f9f5f1;
        border-bottom: 2px solid #cd8985;
    }
    .card-header h4 {
        color: #cd8985;
    }
    .btn-primary {
        background-color: #cd8985;
        border-color: #cd8985;
    }
    .btn-primary:hover {
        background-color: #dcaca9;
        border-color: #dcaca9;
    }
</style>
@endpush

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <h4 class="mb-0">سجل المصاريف</h4>
        <a href="{{ route('admin.expenses.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i>
            إضافة مصروف جديد
        </a>
    </div>
    <div class="card-body">
        {{-- فورم فلترة بالتاريخ --}}
        <form method="GET" action="{{ route('admin.expenses.index') }}" class="mb-4 p-3 bg-light border rounded">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label for="month" class="form-label">الشهر</label>
                    <select name="month" id="month" class="form-select">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 10)) }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-5">
                     <label for="year" class="form-label">السنة</label>
                    <select name="year" id="year" class="form-select">
                        @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-secondary">فلترة</button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle">
                <thead class="table-light">
                    <tr>
                        <th>الوصف</th>
                        <th>المبلغ</th>
                        <th>تاريخ الصرف</th>
                        <th>العمليات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($expenses as $expense)
                        <tr>
                            <td class="text-start ps-3">{{ $expense->description }}</td>
                            <td>{{ number_format($expense->amount, 0) }} د.ع</td>
                            <td>{{ $expense->expense_date->format('Y-m-d') }}</td>
                            <td>
                                <a href="{{ route('admin.expenses.edit', $expense->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route('admin.expenses.destroy', $expense->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('هل أنت متأكد؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-4">لا توجد مصاريف مسجلة لهذا الشهر.</td>
                        </tr>
                    @endforelse
                </tbody>
                {{-- تم وضع الإجمالي هنا ليكون أكثر وضوحاً --}}
                @if($expenses->isNotEmpty())
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td class="text-start ps-3">إجمالي المصاريف للفترة المحددة:</td>
                        <td colspan="3" class="text-start ps-3 fs-5">{{ number_format($totalExpenses, 0) }} د.ع</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
        
        <div class="mt-3">
            {{ $expenses->links() }}
        </div>
    </div>
</div>
@endsection
