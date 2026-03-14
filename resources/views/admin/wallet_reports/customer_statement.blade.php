@extends('admin.layout')

@section('title', __('كشف حساب العميل'))

@push('styles')
<style>
    .statement-summary-card {
        border: 0;
        border-radius: 0.85rem;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        transition: transform 0.2s ease;
    }

    .statement-summary-card:hover {
        transform: translateY(-2px);
    }

    .statement-summary-card__label {
        font-size: 0.85rem;
        color: rgba(15, 23, 42, 0.65);
    }

    .statement-summary-card__value {
        font-weight: 700;
        font-size: 1.35rem;
        color: #1f2937;
    }

    .statement-summary-card--primary {
        background: linear-gradient(135deg, #F0F2F5, #fdf2f8);
    }

    .statement-summary-card--success {
        background: linear-gradient(135deg, #ecfdf5, #d1fae5);
    }

    .statement-summary-card--warning {
        background: linear-gradient(135deg, #fff7ed, #ffedd5);
    }

    .statement-summary-card--info {
        background: linear-gradient(135deg, #eff6ff, #dbeafe);
    }

    .statement-row--debit > * {
        background-color: #fff4e6 !important;
        border-color: rgba(240, 171, 0, 0.25) !important;
    }

    .statement-row--credit > * {
        background-color: #e9f7ef !important;
        border-color: rgba(16, 185, 129, 0.18) !important;
    }

    .statement-row--debit:hover > *,
    .statement-row--credit:hover > * {
        filter: brightness(0.98);
    }

    .statement-empty-state {
        border: 2px dashed rgba(148, 163, 184, 0.4);
        border-radius: 1rem;
        padding: 2.5rem 1.5rem;
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.75), rgba(255, 255, 255, 0.95));
    }
</style>
@endpush

@section('content')
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">{{ __('كشف حساب العميل') }}</h1>
        <p class="text-muted mb-0">{{ __('تابع فواتير الآجل وسندات القبض للعميل من واجهة مألوفة تشبه إدارة الطلبات.') }}</p>
    </div>
    @if($selectedCustomer)
        <div class="order-icon-toolbar">
            <a href="{{ route('admin.users.show', $selectedCustomer) }}"
               class="order-icon-button"
               title="{{ __('عرض ملف العميل') }}"
               aria-label="{{ __('عرض ملف العميل') }}">
                <i class="bi bi-person-lines-fill"></i>
            </a>
            <button type="button"
                    class="order-icon-button order-icon-button--accent"
                    data-bs-toggle="modal"
                    data-bs-target="#customerStatementFiltersModal"
                    title="{{ __('خيارات التصفية') }}"
                    aria-label="{{ __('خيارات التصفية') }}">
                <i class="bi bi-funnel"></i>
            </button>
        </div>
    @endif
</div>

<div class="card shadow-sm" id="customerStatementCard">
    <div class="card-body border-bottom pb-0">
        <form method="GET" action="{{ request()->url() }}" class="filter-bar" id="customerStatementFilters">
            <input type="hidden" name="scope" value="{{ request('scope') }}">
            <input type="hidden" name="from_date" value="{{ request('from_date') }}">
            <input type="hidden" name="to_date" value="{{ request('to_date') }}">

            <div class="filter-bar__group filter-bar__group--search flex-grow-1">
                <div class="input-group filter-bar__input-group">
                    <span class="input-group-text filter-bar__icon"><i class="bi bi-person-vcard"></i></span>
                    <select name="customer_id"
                            id="customerStatementCustomer"
                            class="form-select filter-bar__input"
                            required>
                        <option value="">-- {{ __('اختر العميل') }} --</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" @selected(request('customer_id') == $customer->id)>
                                {{ $customer->name }} — {{ number_format($customer->balance, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="filter-bar__group filter-bar__group--actions" id="customerStatementFilterActions">
                <button type="button"
                        class="table-action-button filter-trigger-button"
                        data-bs-toggle="modal"
                        data-bs-target="#customerStatementFiltersModal"
                        title="{{ __('خيارات التصفية') }}"
                        aria-label="{{ __('خيارات التصفية') }}">
                    <i class="bi bi-sliders"></i>
                </button>
                @can('export-excel')
                    <button type="button"
                            class="table-action-button table-action-button--download"
                            data-export-target="#customerStatementTable"
                            data-export-name="{{ __('كشف-حساب-العميل') }}"
                            title="{{ __('تصدير إلى Excel') }}"
                            aria-label="{{ __('تصدير إلى Excel') }}">
                        <i class="bi bi-file-earmark-spreadsheet"></i>
                    </button>
                @endcan
                <button type="button"
                        class="table-action-button table-action-button--refresh"
                        data-refresh-target="#customerStatementCard"
                        data-refresh-url="{{ request()->fullUrl() }}"
                        title="{{ __('تحديث القائمة') }}"
                        aria-label="{{ __('تحديث القائمة') }}">
                    <i class="bi bi-arrow-repeat"></i>
                </button>
            </div>
        </form>
    </div>

    <div class="card-body">
        @if(!$selectedCustomer)
            <div class="statement-empty-state text-center text-muted">
                <div class="fs-4 fw-semibold mb-1">{{ __('اختر عميلًا لعرض كشف الحساب.') }}</div>
                <p class="mb-0">{{ __('يمكنك استخدام القائمة بالأعلى للانتقال مباشرة إلى كشف الحساب دون الحاجة للضغط على زر.') }}</p>
            </div>
        @else
            <div class="mb-4">
                <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between">
                    <div>
                        <h2 class="h5 mb-1">{{ $selectedCustomer->name }}</h2>
                        <div class="text-muted">
                            {{ __('الرصيد الحالي') }}:
                            <strong>{{ number_format($selectedCustomer->balance, 2) }}</strong>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-3">
                        <div class="card statement-summary-card statement-summary-card--info">
                            <div class="card-body py-3 px-4">
                                <div class="statement-summary-card__label">{{ __('الرصيد الافتتاحي') }}</div>
                                <div class="statement-summary-card__value">{{ number_format($totals['opening'] ?? 0, 2) }}</div>
                            </div>
                        </div>
                        <div class="card statement-summary-card statement-summary-card--warning">
                            <div class="card-body py-3 px-4">
                                <div class="statement-summary-card__label">{{ __('إجمالي المدين (دين)') }}</div>
                                <div class="statement-summary-card__value">{{ number_format($totals['debit'] ?? 0, 2) }}</div>
                            </div>
                        </div>
                        <div class="card statement-summary-card statement-summary-card--success">
                            <div class="card-body py-3 px-4">
                                <div class="statement-summary-card__label">{{ __('إجمالي الدائن (تحصيل)') }}</div>
                                <div class="statement-summary-card__value">{{ number_format($totals['credit'] ?? 0, 2) }}</div>
                            </div>
                        </div>
                        <div class="card statement-summary-card statement-summary-card--primary">
                            <div class="card-body py-3 px-4">
                                <div class="statement-summary-card__label">{{ __('الرصيد الختامي') }}</div>
                                <div class="statement-summary-card__value">{{ number_format($totals['closing'] ?? $selectedCustomer->balance ?? 0, 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle text-center"
                       id="customerStatementTable"
                       data-table-toolbar
                       data-toolbar-target="#customerStatementFilterActions">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('التاريخ') }}</th>
                            <th>{{ __('المرجع') }}</th>
                            <th>{{ __('الوصف') }}</th>
                            <th>{{ __('نوع الحركة') }}</th>
                            <th>{{ __('الرصيد قبل') }}</th>
                            <th>{{ __('مدين (+)') }}</th>
                            <th>{{ __('دائن (-)') }}</th>
                            <th>{{ __('الرصيد بعد') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                            @php
                                /** @var \App\Models\CustomerTransaction $transaction */
                                $isDebit = $transaction->type === \App\Models\CustomerTransaction::TYPE_DEBIT;
                                $related = $transaction->relatedModel;
                                $reference = match ($transaction->related_model_type) {
                                    \App\Models\Order::class => $related ? ('#' . $related->id) : ('#' . $transaction->related_model_id),
                                    \App\Models\ReceiptVoucher::class => $related?->number ? ('#' . $related->number) : ('#' . $transaction->related_model_id),
                                    \App\Models\AllowanceVoucher::class => $related?->number ? ('#' . $related->number) : ('#' . $transaction->related_model_id),
                                    default => $transaction->related_model_id ? ('#' . $transaction->related_model_id) : '—',
                                };

                                $typeLabel = match ($transaction->related_model_type) {
                                    \App\Models\Order::class => __('فاتورة آجل'),
                                    \App\Models\ReceiptVoucher::class => __('سند قبض'),
                                    \App\Models\AllowanceVoucher::class => match ($related?->type) {
                                        \App\Models\AllowanceVoucher::TYPE_INCREASE => __('سند سماح له'),
                                        \App\Models\AllowanceVoucher::TYPE_DECREASE => __('سند سماح عليه'),
                                        default => __('سند سماح'),
                                    },
                                    default => $isDebit ? __('دين على العميل') : __('تحصيل من العميل'),
                                };

                                $description = $transaction->description;
                                if (!$description && $transaction->related_model_type === \App\Models\Order::class && $related) {
                                    $labels = \App\Models\Order::saleTypeLabels();
                                    $description = $labels[$related->sale_type] ?? __('طلب آجل');
                                }

                                if (!$description && $transaction->related_model_type === \App\Models\ReceiptVoucher::class && $related) {
                                    $description = __('تم التحصيل بواسطة :name', ['name' => $related->manager?->name ?? $related->collector?->name ?? __('النظام')]);
                                }

                                if (!$description && $transaction->related_model_type === \App\Models\AllowanceVoucher::class && $related) {
                                    $description = \App\Models\AllowanceVoucher::typeLabels()[$related->type] ?? __('سند سماح');
                                }

                                $beforeBalance = isset($transaction->computed_balance_before)
                                    ? (float) $transaction->computed_balance_before
                                    : ((float) $transaction->balance_after - ($isDebit ? (float) $transaction->amount : -(float) $transaction->amount));

                                $afterBalance = isset($transaction->computed_balance_after)
                                    ? (float) $transaction->computed_balance_after
                                    : (float) $transaction->balance_after;
                            @endphp
                            <tr @class(['statement-row--debit' => $isDebit, 'statement-row--credit' => !$isDebit])>
                                <td>{{ optional($transaction->transaction_date)->format('Y-m-d H:i') ?? '—' }}</td>
                                <td class="fw-semibold">{{ $reference }}</td>
                                <td class="text-start">{{ $description ?: '—' }}</td>
                                <td>
                                    <span @class([
                                        'badge',
                                        'bg-warning text-dark' => $isDebit,
                                        'bg-success' => !$isDebit,
                                    ])>{{ $typeLabel }}</span>
                                </td>
                                <td>{{ number_format($beforeBalance, 2) }}</td>
                                <td class="fw-semibold text-danger">{{ $isDebit ? number_format($transaction->amount, 2) : '—' }}</td>
                                <td class="fw-semibold text-success">{{ !$isDebit ? number_format($transaction->amount, 2) : '—' }}</td>
                                <td class="fw-semibold">{{ number_format($afterBalance, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-5 text-muted">{{ __('لا توجد حركات في الفترة المحددة.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<div class="modal fade filter-modal" id="customerStatementFiltersModal" tabindex="-1" aria-labelledby="customerStatementFiltersModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customerStatementFiltersModalLabel">{{ __('خيارات التصفية') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('إغلاق') }}"></button>
            </div>
            <div class="modal-body">
                <form method="GET" action="{{ request()->url() }}" class="row g-3">
                    <input type="hidden" name="customer_id" value="{{ request('customer_id') }}">
                    <div class="col-12">
                        <label for="modal_scope" class="form-label">{{ __('نوع الحركات') }}</label>
                        <select name="scope" id="modal_scope" class="form-select">
                            <option value="">{{ __('الكل') }}</option>
                            <option value="orders" @selected(request('scope') === 'orders')>{{ __('فواتير آجل') }}</option>
                            <option value="receipts" @selected(request('scope') === 'receipts')>{{ __('سندات قبض') }}</option>
                            <option value="allowances" @selected(request('scope') === 'allowances')>{{ __('سندات السماح') }}</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="modal_from_date" class="form-label">{{ __('من تاريخ') }}</label>
                        <input type="date" name="from_date" id="modal_from_date" class="form-control" value="{{ request('from_date') }}">
                    </div>
                    <div class="col-md-6">
                        <label for="modal_to_date" class="form-label">{{ __('إلى تاريخ') }}</label>
                        <input type="date" name="to_date" id="modal_to_date" class="form-control" value="{{ request('to_date') }}">
                    </div>
                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary w-100">{{ __('تطبيق التصفية') }}</button>
                    </div>
                    <div class="col-12">
                        <a href="{{ request()->url() }}" class="btn btn-outline-secondary w-100">{{ __('إعادة تعيين') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const select = document.getElementById('customerStatementCustomer');
        const form = document.getElementById('customerStatementFilters');

        if (select && form) {
            select.addEventListener('change', function () {
                form.requestSubmit();
            });
        }
    });
</script>
@endpush
