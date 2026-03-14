@extends('admin.layout')

@section('title', 'سند سماح جديد')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 mb-0">سند سماح جديد</h1>
    <a href="{{ route('admin.finance.allowance-vouchers.index') }}" class="btn btn-secondary">عودة</a>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('admin.finance.allowance-vouchers.store') }}" method="post" class="row g-3">
    @csrf
    <div class="col-md-4">
        <label class="form-label">رقم السند</label>
        <input type="text" name="number" class="form-control" value="{{ old('number') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">تاريخ السند</label>
        <input type="datetime-local" name="voucher_date" class="form-control" value="{{ old('voucher_date', now()->format('Y-m-d\TH:i')) }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">العميل</label>
        <select name="customer_id" class="form-select" required>
            <option value="">-- اختر العميل --</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>
                    {{ $customer->name }} — {{ number_format($customer->balance, 2) }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">نوع السند</label>
        <select name="type" class="form-select" required>
            <option value="">-- اختر النوع --</option>
            <option value="{{ \App\Models\AllowanceVoucher::TYPE_INCREASE }}" @selected(old('type') === \App\Models\AllowanceVoucher::TYPE_INCREASE)>سند سماح له (إضافة دين)</option>
            <option value="{{ \App\Models\AllowanceVoucher::TYPE_DECREASE }}" @selected(old('type') === \App\Models\AllowanceVoucher::TYPE_DECREASE)>سند سماح عليه (إسقاط دين)</option>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">المبلغ</label>
        <input type="number" step="0.01" min="0.01" name="amount" class="form-control" value="{{ old('amount') }}" required>
    </div>
    <div class="col-12">
        <label class="form-label">الوصف</label>
        <textarea name="description" class="form-control" rows="3" placeholder="وصف اختياري">{{ old('description') }}</textarea>
    </div>
    <div class="col-12 text-end">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save"></i> حفظ السند
        </button>
    </div>
</form>
@endsection
