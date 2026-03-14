@extends('admin.layout')

@section('title', __('تعديل سند الإيداع'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">{{ __('تعديل سند الإيداع') }}</h1>
    <a href="{{ route('admin.finance.deposit-vouchers.index') }}" class="btn btn-outline-secondary">{{ __('العودة') }}</a>
</div>

<form method="POST" action="{{ route('admin.finance.deposit-vouchers.update', $voucher) }}" class="card">
    @csrf
    @method('PUT')
    <div class="card-body row g-3">
        <div class="col-md-4">
            <label class="form-label">{{ __('الرقم المرجعي') }}</label>
            <input type="text" name="number" value="{{ old('number', $voucher->number) }}" class="form-control" placeholder="مثال: DV-000123">
            @error('number')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">{{ __('تاريخ الإيداع') }}</label>
            <input type="date" name="voucher_date" value="{{ old('voucher_date', optional($voucher->voucher_date)->format('Y-m-d')) }}" class="form-control" required>
            @error('voucher_date')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">{{ __('المندوب') }}</label>
            <select name="manager_id" class="form-select" required>
                <option value="">-- {{ __('اختر المندوب') }} --</option>
                @foreach($managers as $manager)
                    <option value="{{ $manager->id }}" @selected(old('manager_id', $voucher->manager_id) == $manager->id)>
                        {{ $manager->name }} ({{ number_format($manager->cash_on_hand, 2) }})
                    </option>
                @endforeach
            </select>
            @error('manager_id')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">{{ __('الصندوق المستلم') }}</label>
            <select name="cash_box_id" class="form-select" required>
                <option value="">-- {{ __('اختر الصندوق') }} --</option>
                @foreach($cashBoxes as $cashBox)
                    <option value="{{ $cashBox->id }}" @selected(old('cash_box_id', $voucher->cash_box_id) == $cashBox->id)>
                        {{ $cashBox->name }} ({{ number_format($cashBox->balance, 2) }})
                    </option>
                @endforeach
            </select>
            @error('cash_box_id')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">{{ __('المبلغ') }}</label>
            <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount', $voucher->amount) }}" class="form-control" required>
            @error('amount')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
            <label class="form-label">{{ __('الوصف') }}</label>
            <textarea name="description" rows="3" class="form-control">{{ old('description', $voucher->description) }}</textarea>
            @error('description')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="card-footer d-flex justify-content-end gap-2">
        <a href="{{ route('admin.finance.deposit-vouchers.index') }}" class="btn btn-light">{{ __('إلغاء') }}</a>
        <button type="submit" class="btn btn-primary">{{ __('حفظ التعديلات') }}</button>
    </div>
</form>
@endsection
