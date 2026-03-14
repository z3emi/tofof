@extends('admin.layout')

@section('title', __('تعديل سند قبض'))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">{{ __('تعديل سند قبض') }}</h1>
    <a href="{{ route('admin.finance.receipt-vouchers.index') }}" class="btn btn-outline-secondary">{{ __('العودة') }}</a>
</div>

<form method="POST" action="{{ route('admin.finance.receipt-vouchers.update', $voucher) }}" class="card">
    @csrf
    @method('PUT')
    <div class="card-body row g-3">
        <div class="col-md-4">
            <label class="form-label">{{ __('الرقم المرجعي') }}</label>
            <input type="text" name="number" value="{{ old('number', $voucher->number) }}" class="form-control" placeholder="مثال: RV-000123">
            @error('number')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">{{ __('تاريخ السند') }}</label>
            <input type="date" name="voucher_date" value="{{ old('voucher_date', optional($voucher->voucher_date)->toDateString()) }}" class="form-control" required>
            @error('voucher_date')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">{{ __('العميل') }}</label>
            <select name="customer_id" class="form-select" required>
                <option value="">-- {{ __('اختر العميل') }} --</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" @selected(old('customer_id', $voucher->customer_id) == $customer->id)>{{ $customer->name }}</option>
                @endforeach
            </select>
            @error('customer_id')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">{{ __('المندوب المستلم') }}</label>
            <select name="manager_id" class="form-select">
                <option value="">-- {{ __('استلام مباشر') }} --</option>
                @foreach($managers as $manager)
                    <option value="{{ $manager->id }}" @selected(old('manager_id', $voucher->manager_id) == $manager->id)>{{ $manager->name }}</option>
                @endforeach
            </select>
            <small class="text-muted">{{ __('اختر المندوب إذا استلم المبلغ من العميل.') }}</small>
            @error('manager_id')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">{{ __('الصندوق الرئيسي') }}</label>
            <select name="cash_box_id" class="form-select">
                <option value="">-- {{ __('غير محدد') }} --</option>
                @foreach($cashBoxes as $cashBox)
                    <option value="{{ $cashBox->id }}" @selected(old('cash_box_id', $voucher->cash_box_id) == $cashBox->id)>
                        {{ $cashBox->name }} ({{ number_format($cashBox->balance, 2) }})
                    </option>
                @endforeach
            </select>
            <small class="text-muted">{{ __('اختر الصندوق إذا تم استلام المبلغ مباشرة.') }}</small>
            @error('cash_box_id')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">{{ __('الطلب المرتبط (اختياري)') }}</label>
            <select name="order_id" class="form-select">
                <option value="">-- {{ __('بدون') }} --</option>
                @foreach($orders as $order)
                    <option value="{{ $order->id }}" @selected(old('order_id', $voucher->order_id) == $order->id)>
                        #{{ $order->id }} - {{ $order->customer?->name ?? __('عميل') }} - {{ number_format($order->total_amount, 2) }}
                    </option>
                @endforeach
            </select>
            @error('order_id')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">{{ __('المبلغ') }}</label>
            <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount', $voucher->amount) }}" class="form-control" required>
            @error('amount')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label">{{ __('قناة الدفع') }}</label>
            <input type="text" name="transaction_channel" value="{{ old('transaction_channel', $voucher->transaction_channel) }}" class="form-control" placeholder="مثال: نقدي / تحويل بنكي">
            @error('transaction_channel')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
            <label class="form-label">{{ __('الوصف') }}</label>
            <textarea name="description" rows="3" class="form-control">{{ old('description', $voucher->description) }}</textarea>
            @error('description')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="card-footer d-flex justify-content-end gap-2">
        <a href="{{ route('admin.finance.receipt-vouchers.index') }}" class="btn btn-light">{{ __('إلغاء') }}</a>
        <button type="submit" class="btn btn-primary">{{ __('تحديث السند') }}</button>
    </div>
</form>
@endsection
