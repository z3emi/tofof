@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="description" class="form-label">وصف المصروف</label>
        <input type="text" class="form-control" id="description" name="description" value="{{ old('description', $expense->description ?? '') }}" placeholder="مثال: إيجار المحل، فاتورة كهرباء..." required>
    </div>
    <div class="col-md-6 mb-3">
        <label for="amount" class="form-label">المبلغ (د.ع)</label>
        <input type="number" step="any" class="form-control" id="amount" name="amount" value="{{ old('amount', $expense->amount ?? '') }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label for="expense_date" class="form-label">تاريخ الصرف</label>
        <input type="date" class="form-control" id="expense_date" name="expense_date" value="{{ old('expense_date', isset($expense) ? $expense->expense_date->format('Y-m-d') : date('Y-m-d')) }}" required>
    </div>
    <div class="col-12 mb-3">
        <label for="notes" class="form-label">ملاحظات (اختياري)</label>
        <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes', $expense->notes ?? '') }}</textarea>
    </div>
</div>

<hr>
<button type="submit" class="btn btn-primary">{{ isset($expense) ? 'تحديث المصروف' : 'حفظ المصروف' }}</button>
<a href="{{ route('admin.expenses.index') }}" class="btn btn-secondary">إلغاء</a>