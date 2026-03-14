@extends('admin.layout')

@section('title', __('سندات القبض'))

@php
    $authUser = auth('admin')->user() ?? auth()->user();
    $canCreateVoucher = $authUser?->can('create-receipt-voucher') ?? false;
    $canEditAnyVoucher = $authUser?->can('edit-receipt-voucher') ?? false;
    $canEditOwnVoucher = $authUser?->can('view-own-receipt-voucher') ?? false;
    $canDeleteVoucher = $authUser?->can('delete-receipt-voucher') ?? false;
    $showSelectionColumn = $canEditAnyVoucher || $canEditOwnVoucher || $canDeleteVoucher;
    $showHeaderActions = $canCreateVoucher || $showSelectionColumn;
    $activeOrderScope = request('order_scope');
@endphp

@push('styles')
<style>
    .table-row-selected {
        outline: 2px solid #FF5722;
        outline-offset: -2px;
    }

    .table-row-selected td {
        background-color: #fdf5f4 !important;
    }
</style>
@endpush

@section('content')
<div class="card shadow-sm" id="receiptVouchersListingCard">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2" style="background-color: #F0F2F5;">
        <h4 class="mb-0" style="color: #FF5722;">{{ __('إدارة سندات القبض') }}</h4>
        @if($showHeaderActions)
            <div class="dropdown">
                <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="receiptVoucherActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-gear-fill me-1"></i> {{ __('إجراءات') }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="receiptVoucherActionsDropdown">
                    @if($showSelectionColumn)
                        <li class="dropdown-header">{{ __('إجراءات على السند') }}</li>
                        @if($canEditAnyVoucher || $canEditOwnVoucher)
                            <li>
                                <button type="button"
                                        class="dropdown-item selection-action"
                                        data-selection-group="receipt-vouchers"
                                        data-action-type="navigate"
                                        data-url-template="{{ route('admin.finance.receipt-vouchers.edit', ['receipt_voucher' => '__ID__']) }}">
                                    <i class="bi bi-pencil me-2"></i> {{ __('تعديل السند') }}
                                </button>
                            </li>
                        @endif
                        @if($canDeleteVoucher)
                            <li>
                                <button type="button"
                                        class="dropdown-item text-danger selection-action"
                                        data-selection-group="receipt-vouchers"
                                        data-action-type="submit-form"
                                        data-form-target="receiptVoucherDeleteForm"
                                        data-url-template="{{ route('admin.finance.receipt-vouchers.destroy', ['receipt_voucher' => '__ID__']) }}"
                                        data-confirm="{{ __('هل أنت متأكد من حذف هذا السند؟') }}"
                                        data-confirm-multiple="{{ __('هل تريد حذف السندات المحددة (عدد: __COUNT__)؟') }}"
                                        data-allow-multiple="true">
                                    <i class="bi bi-trash me-2"></i> {{ __('حذف السند') }}
                                </button>
                            </li>
                        @endif
                        @if($canCreateVoucher)
                            <li><hr class="dropdown-divider"></li>
                        @endif
                    @endif
                    @if($canCreateVoucher)
                        <li>
                            <a href="{{ route('admin.finance.receipt-vouchers.create') }}" class="dropdown-item">
                                <i class="bi bi-plus-circle me-2"></i> {{ __('إنشاء سند قبض جديد') }}
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        @endif
    </div>

    <div class="card-body">
        <form method="GET" action="{{ route('admin.finance.receipt-vouchers.index') }}" class="filter-bar" id="receiptVoucherFilterForm">
            <input type="hidden" name="customer_id" value="{{ request('customer_id') }}">
            <input type="hidden" name="manager_id" value="{{ request('manager_id') }}">
            <input type="hidden" name="cash_box_id" value="{{ request('cash_box_id') }}">
            <input type="hidden" name="transaction_channel" value="{{ request('transaction_channel') }}">
            <input type="hidden" name="date_from" value="{{ request('date_from') }}">
            <input type="hidden" name="date_to" value="{{ request('date_to') }}">
            <input type="hidden" name="min_amount" value="{{ request('min_amount') }}">
            <input type="hidden" name="max_amount" value="{{ request('max_amount') }}">
            <input type="hidden" name="order_scope" value="{{ $activeOrderScope }}">
            <input type="hidden" name="with_notes" value="{{ request('with_notes') }}">
            <input type="hidden" name="per_page" value="{{ request('per_page', $perPage ?? 20) }}">

            <div class="filter-bar__group filter-bar__group--search flex-grow-1">
                <div class="input-group filter-bar__input-group">
                    <span class="input-group-text filter-bar__icon"><i class="bi bi-search"></i></span>
                    <input type="text"
                           name="search"
                           class="form-control filter-bar__input"
                           placeholder="{{ __('ابحث برقم السند أو اسم العميل أو الملاحظات...') }}"
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="filter-bar__group filter-bar__group--submit">
                <button type="submit" class="btn btn-primary filter-bar__submit">{{ __('بحث') }}</button>
            </div>
            <div class="filter-bar__group filter-bar__group--actions" id="receiptVouchersFilterActions">
                <button type="button"
                        class="table-action-button filter-trigger-button"
                        data-bs-toggle="modal"
                        data-bs-target="#receiptVoucherFiltersModal"
                        title="{{ __('خيارات التصفية') }}"
                        aria-label="{{ __('خيارات التصفية') }}">
                    <i class="bi bi-funnel"></i>
                </button>
                @can('export-excel')
                    <button type="button"
                            class="table-action-button table-action-button--download"
                            data-export-target="#receiptVouchersTable"
                            data-export-name="{{ __('سندات القبض') }}"
                            title="{{ __('تصدير إلى Excel') }}"
                            aria-label="{{ __('تصدير إلى Excel') }}">
                        <i class="bi bi-file-earmark-spreadsheet"></i>
                    </button>
                @endcan
                <button type="button"
                        class="table-action-button table-action-button--refresh"
                        data-refresh-target="#receiptVouchersListingCard"
                        data-refresh-url="{{ request()->fullUrl() }}"
                        title="{{ __('تحديث القائمة') }}"
                        aria-label="{{ __('تحديث القائمة') }}">
                    <i class="bi bi-arrow-repeat"></i>
                </button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle" id="receiptVouchersTable" data-table-toolbar data-toolbar-target="#receiptVouchersFilterActions">
                <thead class="table-light">
                    <tr>
                        @if($showSelectionColumn)
                            <th class="text-center" style="width: 48px;">{{ __('تحديد') }}</th>
                        @endif
                        <th>{{ __('رقم السند') }}</th>
                        <th>{{ __('تاريخ السند') }}</th>
                        <th>{{ __('العميل') }}</th>
                        <th>{{ __('المبلغ') }}</th>
                        <th>{{ __('جهة الاستلام') }}</th>
                        <th>{{ __('طريقة/قناة الدفع') }}</th>
                        <th>{{ __('الملاحظات') }}</th>
                        <th>{{ __('الحالة') }}</th>
                        <th class="text-center">{{ __('عمليات سريعة') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vouchers as $voucher)
                        @php
                            $canEditRow = ($canEditAnyVoucher || ($canEditOwnVoucher && $voucher->manager_id === optional($authUser)->id));
                            $canDeleteRow = $canDeleteVoucher;
                        @endphp
                        <tr>
                            @if($showSelectionColumn)
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input selection-checkbox" data-selection-group="receipt-vouchers" value="{{ $voucher->getKey() }}">
                                </td>
                            @endif
                            <td>{{ $voucher->number }}</td>
                            <td>{{ optional($voucher->voucher_date)->format('Y-m-d') ?? '—' }}</td>
                            <td>{{ $voucher->customer?->name ?? '—' }}</td>
                            <td>{{ number_format($voucher->amount, 2) }}</td>
                            <td>
                                @if($voucher->cashBox)
                                    <span class="badge bg-success">{{ __('صندوق: :name', ['name' => $voucher->cashBox->name]) }}</span>
                                @elseif($voucher->manager)
                                    <span class="badge bg-info text-dark">{{ __('مندوب: :name', ['name' => $voucher->manager->name]) }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('غير محدد') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($voucher->transaction_channel)
                                    <span class="badge bg-light text-dark">{{ $voucher->transaction_channel }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $voucher->description ? \Illuminate\Support\Str::limit($voucher->description, 60) : '—' }}</td>
                            <td>
                                @if($voucher->order_id)
                                    <span class="badge bg-primary">{{ __('مرتبط بطلب #:id', ['id' => $voucher->order_id]) }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('سند مستقل') }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-2" data-ignore-row-select>
                                    @if($canEditRow)
                                        <a href="{{ route('admin.finance.receipt-vouchers.edit', $voucher) }}" class="btn btn-sm btn-outline-primary">
                                            {{ __('تعديل') }}
                                        </a>
                                    @endif
                                    @if($canDeleteRow)
                                        <form method="POST" action="{{ route('admin.finance.receipt-vouchers.destroy', $voucher) }}" onsubmit="return confirm('{{ __('هل أنت متأكد من حذف هذا السند؟') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('حذف') }}</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            @php
                                $colspan = 9 + ($showSelectionColumn ? 1 : 0);
                            @endphp
                            <td colspan="{{ $colspan }}" class="py-4 text-muted">{{ __('لا توجد سندات حالياً.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
            <form method="GET" action="{{ route('admin.finance.receipt-vouchers.index') }}" class="d-flex align-items-center">
                @foreach(request()->except(['per_page', 'page']) as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <label for="per_page" class="me-2">{{ __('عدد السجلات في الصفحة:') }}</label>
                <select name="per_page" id="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach(($perPageOptions ?? [10, 15, 20, 25, 50, 100]) as $size)
                        <option value="{{ $size }}" {{ (int) request('per_page', $perPage ?? 20) === (int) $size ? 'selected' : '' }}>
                            {{ $size }}
                        </option>
                    @endforeach
                </select>
            </form>

            <div>{{ $vouchers->links() }}</div>
        </div>
    </div>
</div>

@if($showSelectionColumn && $canDeleteVoucher)
    <form id="receiptVoucherDeleteForm" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>
@endif

<!-- Filters Modal -->
<div class="modal fade filter-modal" id="receiptVoucherFiltersModal" tabindex="-1" aria-labelledby="receiptVoucherFiltersModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="receiptVoucherFiltersModalLabel">{{ __('تصفية سندات القبض') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('إغلاق') }}"></button>
            </div>
            <div class="modal-body">
                <form method="GET" action="{{ route('admin.finance.receipt-vouchers.index') }}" class="row g-3" id="receiptVoucherFiltersForm">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="per_page" value="{{ request('per_page', $perPage ?? 20) }}">

                    <div class="col-12">
                        <label for="customer_id" class="form-label">{{ __('العميل') }}</label>
                        <select name="customer_id" id="customer_id" class="form-select">
                            <option value="">{{ __('جميع العملاء') }}</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ (int) request('customer_id') === $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="manager_id" class="form-label">{{ __('المندوب') }}</label>
                        <select name="manager_id" id="manager_id" class="form-select">
                            <option value="">{{ __('جميع المندوبين') }}</option>
                            @foreach($managers as $manager)
                                <option value="{{ $manager->id }}" {{ (int) request('manager_id') === $manager->id ? 'selected' : '' }}>
                                    {{ $manager->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="cash_box_id" class="form-label">{{ __('الصندوق') }}</label>
                        <select name="cash_box_id" id="cash_box_id" class="form-select">
                            <option value="">{{ __('جميع الصناديق') }}</option>
                            @foreach($cashBoxes as $cashBox)
                                <option value="{{ $cashBox->id }}" {{ (int) request('cash_box_id') === $cashBox->id ? 'selected' : '' }}>
                                    {{ $cashBox->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="date_from" class="form-label">{{ __('من تاريخ') }}</label>
                        <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>

                    <div class="col-md-6">
                        <label for="date_to" class="form-label">{{ __('إلى تاريخ') }}</label>
                        <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>

                    <div class="col-md-6">
                        <label for="min_amount" class="form-label">{{ __('أقل مبلغ') }}</label>
                        <input type="number" step="0.01" name="min_amount" id="min_amount" class="form-control" value="{{ request('min_amount') }}">
                    </div>

                    <div class="col-md-6">
                        <label for="max_amount" class="form-label">{{ __('أعلى مبلغ') }}</label>
                        <input type="number" step="0.01" name="max_amount" id="max_amount" class="form-control" value="{{ request('max_amount') }}">
                    </div>

                    <div class="col-md-6">
                        <label for="order_scope" class="form-label">{{ __('نوع السند') }}</label>
                        <select name="order_scope" id="order_scope" class="form-select">
                            <option value="" {{ empty($activeOrderScope) ? 'selected' : '' }}>{{ __('الكل') }}</option>
                            <option value="with_order" {{ $activeOrderScope === 'with_order' ? 'selected' : '' }}>{{ __('مرتبط بطلب') }}</option>
                            <option value="without_order" {{ $activeOrderScope === 'without_order' ? 'selected' : '' }}>{{ __('غير مرتبط بطلب') }}</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="transaction_channel" class="form-label">{{ __('قناة الدفع') }}</label>
                        <select name="transaction_channel" id="transaction_channel" class="form-select">
                            <option value="">{{ __('جميع القنوات') }}</option>
                            @foreach($transactionChannels as $channel)
                                <option value="{{ $channel }}" {{ request('transaction_channel') === $channel ? 'selected' : '' }}>
                                    {{ $channel }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="with_notes" name="with_notes" {{ request('with_notes') ? 'checked' : '' }}>
                            <label class="form-check-label" for="with_notes">{{ __('إظهار السندات التي تحتوي على ملاحظات فقط') }}</label>
                        </div>
                    </div>

                    <div class="col-12 d-flex justify-content-between align-items-center">
                        <a href="{{ route('admin.finance.receipt-vouchers.index') }}" class="btn btn-link text-danger">{{ __('مسح الفلاتر') }}</a>
                        <button type="submit" class="btn btn-primary">{{ __('تطبيق') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@if($showSelectionColumn)
    @push('scripts')
        @include('admin.partials.selection-script', [
            'group' => 'receipt-vouchers',
            'emptyMessage' => __('يرجى اختيار سند واحد على الأقل لتنفيذ الإجراء المطلوب.')
        ])
    @endpush
@endif
