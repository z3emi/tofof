@extends('admin.layout')

@section('title', 'سند صرف جديد')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 mb-0">سند صرف جديد</h1>
    <a href="{{ route('admin.accounting.payment-vouchers.index') }}" class="btn btn-secondary">عودة</a>
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

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.accounting.payment-vouchers.store') }}" method="post" class="row g-3">
            @csrf
            <div class="col-md-4">
                <label class="form-label">رقم السند</label>
                <input type="text" name="number" class="form-control" value="{{ old('number') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">التاريخ</label>
                <input type="date" name="voucher_date" class="form-control" value="{{ old('voucher_date', now()->format('Y-m-d')) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">الحساب النقدي</label>
                <select name="cash_account_id" class="form-select" required>
                    <option value="">-- اختر --</option>
                    @foreach($cashAccounts as $cashAccount)
                        <option value="{{ $cashAccount->id }}" data-currency="{{ $cashAccount->currency_code }}" @selected(old('cash_account_id') == $cashAccount->id)>
                            {{ $cashAccount->name }} ({{ $cashAccount->currency_code }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">حساب المصروف</label>
                <select name="expense_account_id" class="form-select" required>
                    <option value="">-- اختر --</option>
                    @foreach($expenseAccounts as $account)
                        <option value="{{ $account->id }}" @selected(old('expense_account_id') == $account->id)>{{ $account->code }} - {{ $account->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">الموظف / المستفيد</label>
                <select name="responsible_manager_id" class="form-select">
                    <option value="">-- بدون تحديد --</option>
                    @foreach($managers as $manager)
                        <option value="{{ $manager->id }}" @selected(old('responsible_manager_id') == $manager->id)>
                            {{ $manager->name }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">حدد الموظف الذي استلم المبلغ لضبط العهد والسلف.</small>
                @error('responsible_manager_id')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">العملة</label>
                <select name="currency_code" id="currency_code" class="form-select" required>
                    <option value="IQD" @selected(old('currency_code', 'IQD') === 'IQD')>دينار عراقي (IQD)</option>
                    <option value="USD" @selected(old('currency_code') === 'USD')>دولار أمريكي (USD)</option>
                </select>
                @error('currency_code')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">المبلغ بالعملة المحددة</label>
                <input type="number" step="0.01" name="currency_amount" class="form-control" data-currency-amount value="{{ old('currency_amount') }}" required>
                @error('currency_amount')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3 {{ old('currency_code', 'IQD') === 'USD' ? '' : 'd-none' }}" data-exchange-wrapper>
                <label class="form-label">سعر الصرف (IQD لكل 1)</label>
                <input type="number" step="0.0001" min="0" name="exchange_rate" class="form-control" data-exchange-rate value="{{ old('exchange_rate') }}" {{ old('currency_code') === 'USD' ? 'required' : '' }}>
                <small class="text-muted">أدخل سعر الصرف عند الدفع بالدولار.</small>
                @error('exchange_rate')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <small class="text-muted" data-base-preview></small>
            </div>
            <div class="col-md-12">
                <label class="form-label">الوصف</label>
                <input type="text" name="description" class="form-control" value="{{ old('description') }}">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">حفظ السند</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const cashSelect = document.querySelector('select[name="cash_account_id"]');
            const currencySelect = document.getElementById('currency_code');
            const exchangeWrapper = document.querySelector('[data-exchange-wrapper]');
            const exchangeInput = document.querySelector('[data-exchange-rate]');
            const amountInput = document.querySelector('[data-currency-amount]');
            const basePreview = document.querySelector('[data-base-preview]');

            function syncCurrencyFromCash() {
                const selectedOption = cashSelect?.selectedOptions[0];
                if (selectedOption && selectedOption.dataset.currency) {
                    currencySelect.value = selectedOption.dataset.currency;
                }
                syncExchangeVisibility();
            }

            function syncExchangeVisibility() {
                const currency = currencySelect?.value || 'IQD';
                const showExchange = currency === 'USD';
                exchangeWrapper?.classList.toggle('d-none', !showExchange);
                exchangeInput?.toggleAttribute('required', showExchange);
                if (!showExchange && exchangeInput) {
                    exchangeInput.value = exchangeInput.value || '';
                }
                updateBasePreview();
            }

            function updateBasePreview() {
                if (!amountInput || !basePreview) {
                    return;
                }

                const amount = parseFloat(amountInput.value || '0');
                const currency = currencySelect?.value || 'IQD';
                const rate = parseFloat(exchangeInput?.value || '0');

                if (!amount || amount <= 0) {
                    basePreview.textContent = '';
                    return;
                }

                if (currency === 'USD') {
                    if (rate > 0) {
                        const base = (amount * rate).toFixed(2);
                        basePreview.textContent = `يعادل ${base} د.ع بعد التحويل.`;
                    } else {
                        basePreview.textContent = 'يرجى تحديد سعر الصرف لحساب القيمة بالدينار.';
                    }
                } else {
                    basePreview.textContent = `القيمة النهائية ${amount.toFixed(2)} د.ع.`;
                }
            }

            cashSelect?.addEventListener('change', syncCurrencyFromCash);
            currencySelect?.addEventListener('change', syncExchangeVisibility);
            exchangeInput?.addEventListener('input', updateBasePreview);
            amountInput?.addEventListener('input', updateBasePreview);

            syncCurrencyFromCash();
            updateBasePreview();
        });
    </script>
@endpush
