@extends('admin.layout')

@section('title', 'تعديل المصروف: ' . $expense->title)

@section('content')
@push('styles')
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem; color: white; border-radius: 0 !important; }
    .form-section-title { font-weight: 700; color: var(--primary-dark); border-right: 4px solid var(--accent-gold); padding-right: 15px; margin-bottom: 2rem; }
    .submit-btn { background: var(--primary-dark); padding: 1rem 3rem; border-radius: 10px; font-weight: 700; color: white; border: none; }
</style>
@endpush

<div class="form-card">
    <div class="form-card-header">
        <h2 class="mb-2 fw-bold text-white"><i class="bi bi-wallet2 me-2"></i> تعديل بيانات المصروف: {{ $expense->title }}</h2>
        <p class="mb-0 opacity-75 fs-6 text-white small">قم بتحديث مبلغ أو تاريخ أو عنوان القيد المالي المختار.</p>
    </div>
    
    <div class="p-4 p-lg-5">
        <form action="{{ route('admin.expenses.update', $expense->id) }}" method="POST">
            @csrf @method('PUT')

            <div class="mb-5">
                <h5 class="form-section-title">المعلومات المالية</h5>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="title" class="form-label fw-bold small">عنوان المصروف <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" style="border-radius:12px; padding:0.8rem" id="title" name="title" value="{{ old('title', $expense->title) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="amount" class="form-label fw-bold small">المبلغ (د.ع) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" style="border-radius:12px; padding:0.8rem" id="amount" name="amount" value="{{ old('amount', $expense->amount) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="expense_date" class="form-label fw-bold small">تاريخ الصرف <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" style="border-radius:12px; padding:0.8rem" id="expense_date" name="expense_date" value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-12">
                        <label for="description" class="form-label fw-bold small">أدخل وصفاً مفصلاً للمشار إليه</label>
                        <textarea class="form-control" style="border-radius:12px; padding:0.8rem" id="description" name="description" rows="3">{{ old('description', $expense->description) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-3 pt-5 border-top">
                <a href="{{ route('admin.expenses.index') }}" class="btn btn-light px-5 py-3 rounded-3 fw-bold">إلغاء</a>
                <button type="submit" class="submit-btn shadow-sm py-3 px-5">تحديث القيد المالي</button>
            </div>
        </form>
    </div>
</div>
@endsection