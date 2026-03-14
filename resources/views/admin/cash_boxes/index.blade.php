@extends('admin.layout')

@section('title', __('الصناديق النقدية'))

@push('styles')
<style>
    .cash-card {
        border-radius: 1rem !important;
        border: 0;
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .cash-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 18px 32px rgba(15, 23, 42, 0.18) !important;
    }
    .transaction-badge {
        min-width: 80px;
        text-align: center;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">{{ __('إدارة الصناديق النقدية') }}</h1>
            <p class="text-muted mb-0">{{ __('تابع الرصيد الإجمالي وعمليات القبض والصرف لكل صندوق.') }}</p>
        </div>
        <form method="GET" action="{{ route('admin.finance.cash-boxes.index') }}" class="d-flex gap-2">
            <select name="month" class="form-select">
                @for ($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" @selected($month == $m)>{{ date('F', mktime(0, 0, 0, $m, 10)) }}</option>
                @endfor
            </select>
            <select name="year" class="form-select">
                @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                    <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
                @endfor
            </select>
            <button type="submit" class="btn btn-dark">{{ __('تحديث الفترة') }}</button>
        </form>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-4 col-md-6">
            <div class="card cash-card shadow-sm h-100" style="background: linear-gradient(135deg,#d5f4e6,#80e1c0);">
                <div class="card-body">
                    <p class="text-muted mb-1">{{ __('إجمالي الأرصدة') }}</p>
                    <h3 class="fw-bold mb-0">{{ number_format($totals['balance'], 0) }} {{ __('د.ع') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card cash-card shadow-sm h-100" style="background: linear-gradient(135deg,#d7e3fc,#829bff);">
                <div class="card-body">
                    <p class="text-muted mb-1">{{ __('إجمالي المقبوض خلال الفترة') }}</p>
                    <h3 class="fw-bold mb-0">{{ number_format($totals['credits'], 0) }} {{ __('د.ع') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card cash-card shadow-sm h-100" style="background: linear-gradient(135deg,#ffe0e0,#ffa69e);">
                <div class="card-body">
                    <p class="text-muted mb-1">{{ __('إجمالي المصروف خلال الفترة') }}</p>
                    <h3 class="fw-bold mb-0">{{ number_format($totals['debits'], 0) }} {{ __('د.ع') }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-5">
        <div class="card-header bg-white border-0">
            <h5 class="mb-0 fw-bold">{{ __('إضافة صندوق جديد') }}</h5>
        </div>
        <form method="POST" action="{{ route('admin.finance.cash-boxes.store') }}">
            @csrf
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('اسم الصندوق') }}</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('الكود') }}</label>
                    <input type="text" name="code" class="form-control" value="{{ old('code') }}" placeholder="CB-0001">
                    @error('code')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('نوع الصندوق') }}</label>
                    <select name="type" class="form-select" required>
                        @foreach ($types as $value => $label)
                            <option value="{{ $value }}" @selected(old('type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('type')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('العملة') }}</label>
                    <input type="text" name="currency" class="form-control" value="{{ old('currency', 'IQD') }}" maxlength="3">
                    @error('currency')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('الرصيد الافتتاحي (اختياري)') }}</label>
                    <input type="number" step="0.01" min="0" name="balance" class="form-control" value="{{ old('balance') }}">
                    @error('balance')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_primary" id="is_primary" value="1" @checked(old('is_primary'))>
                        <label class="form-check-label" for="is_primary">{{ __('تعيين كصندوق رئيسي') }}</label>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">{{ __('ملاحظات') }}</label>
                    <textarea name="notes" rows="2" class="form-control" placeholder="{{ __('اكتب أي تفاصيل إضافية حول الصندوق') }}">{{ old('notes') }}</textarea>
                </div>
            </div>
            <div class="card-footer bg-white border-0 text-end">
                <button type="submit" class="btn btn-primary px-4">{{ __('حفظ الصندوق') }}</button>
            </div>
        </form>
    </div>

    <div class="row g-4">
        @forelse ($cashBoxes as $cashBox)
            <div class="col-xl-4 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-0">{{ $cashBox->name }}</h5>
                            <small class="text-muted">{{ __('الكود') }}: {{ $cashBox->code }} • {{ __('الرصيد الحالي') }}: {{ number_format($cashBox->balance, 0) }} {{ $cashBox->currency }}</small>
                        </div>
                        @if($cashBox->is_primary)
                            <span class="badge bg-success-subtle text-success">{{ __('رئيسي') }}</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <div class="text-muted small">{{ __('مقبوض هذا الشهر') }}</div>
                                <div class="fw-semibold text-success">{{ number_format($cashBox->month_credits ?? 0, 0) }} {{ $cashBox->currency }}</div>
                            </div>
                            <div class="text-end">
                                <div class="text-muted small">{{ __('مصروف هذا الشهر') }}</div>
                                <div class="fw-semibold text-danger">{{ number_format($cashBox->month_debits ?? 0, 0) }} {{ $cashBox->currency }}</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <button class="btn btn-sm btn-outline-primary w-100" type="button" data-bs-toggle="collapse" data-bs-target="#cash-box-form-{{ $cashBox->id }}" aria-expanded="false">
                                <i class="bi bi-gear me-1"></i>{{ __('تعديل بيانات الصندوق') }}
                            </button>
                        </div>

                        <div class="collapse" id="cash-box-form-{{ $cashBox->id }}">
                            <form method="POST" action="{{ route('admin.finance.cash-boxes.update', $cashBox) }}" class="border rounded-3 p-3 mb-3 bg-light-subtle">
                                @csrf
                                @method('PUT')
                                <div class="mb-3">
                                    <label class="form-label">{{ __('الاسم') }}</label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', $cashBox->name) }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">{{ __('الكود') }}</label>
                                    <input type="text" name="code" class="form-control" value="{{ old('code', $cashBox->code) }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">{{ __('النوع') }}</label>
                                    <select name="type" class="form-select">
                                        @foreach ($types as $value => $label)
                                            <option value="{{ $value }}" @selected(old('type', $cashBox->type) === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">{{ __('العملة') }}</label>
                                    <input type="text" name="currency" class="form-control" value="{{ old('currency', $cashBox->currency) }}" maxlength="3">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">{{ __('ملاحظات') }}</label>
                                    <textarea name="notes" rows="2" class="form-control">{{ old('notes', $cashBox->notes) }}</textarea>
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="primary-{{ $cashBox->id }}" name="is_primary" value="1" @checked(old('is_primary', $cashBox->is_primary))>
                                    <label class="form-check-label" for="primary-{{ $cashBox->id }}">{{ __('تعيين كصندوق رئيسي') }}</label>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">{{ __('تحديث البيانات') }}</button>
                                </div>
                            </form>
                        </div>

                        <div class="border rounded-3 p-3">
                            <h6 class="fw-semibold mb-3">{{ __('آخر الحركات') }}</h6>
                            <ul class="list-unstyled mb-0">
                                @forelse ($cashBox->transactions as $transaction)
                                    <li class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="transaction-badge badge {{ $transaction->type === \App\Models\CashBoxTransaction::TYPE_CREDIT ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                            {{ $transaction->type === \App\Models\CashBoxTransaction::TYPE_CREDIT ? __('قبض') : __('صرف') }}
                                        </span>
                                        <div class="text-end">
                                            <div class="fw-semibold">{{ number_format($transaction->amount, 0) }} {{ $cashBox->currency }}</div>
                                            <div class="text-muted small">{{ optional($transaction->transaction_date)->format('Y-m-d H:i') }}</div>
                                        </div>
                                    </li>
                                @empty
                                    <li class="text-muted text-center">{{ __('لا توجد حركات حديثة.') }}</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-light border text-center">{{ __('لم يتم إنشاء صناديق بعد.') }}</div>
            </div>
        @endforelse
    </div>
</div>
@endsection
