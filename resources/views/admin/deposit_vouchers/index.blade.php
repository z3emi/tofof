@extends('admin.layout')

@section('title', __('سندات الإيداع'))

@php
    $authUser = auth('admin')->user() ?? auth()->user();
    $canCreateVoucher = $authUser?->can('create-deposit-voucher') ?? false;
    $canEditVoucher = $authUser?->can('edit-deposit-voucher') ?? false;
    $canDeleteVoucher = $authUser?->can('delete-deposit-voucher') ?? false;
    $showSelectionColumn = $canEditVoucher || $canDeleteVoucher;
    $showHeaderActions = $canCreateVoucher || $showSelectionColumn;
@endphp

@push('styles')
<style>
    .table-row-selected {
        outline: 2px solid #7ab6d6;
        outline-offset: -2px;
    }

    .table-row-selected td {
        background-color: #f2f7fb !important;
    }
</style>
@endpush

@section('content')
<div class="card shadow-sm" id="depositVouchersListingCard">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2" style="background-color: #f3f7f9;">
        <h4 class="mb-0" style="color: #0f5c87;">{{ __('إدارة سندات الإيداع') }}</h4>
        @if($showHeaderActions)
            <div class="dropdown">
                <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="depositVoucherActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-gear-fill me-1"></i> {{ __('إجراءات') }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="depositVoucherActionsDropdown">
                    @if($showSelectionColumn)
                        <li class="dropdown-header">{{ __('إجراءات على السند') }}</li>
                        @if($canEditVoucher)
                            <li>
                                <button type="button"
                                        class="dropdown-item selection-action"
                                        data-selection-group="deposit-vouchers"
                                        data-action-type="navigate"
                                        data-url-template="{{ route('admin.finance.deposit-vouchers.edit', ['deposit_voucher' => '__ID__']) }}">
                                    <i class="bi bi-pencil me-2"></i> {{ __('تعديل السند') }}
                                </button>
                            </li>
                        @endif
                        @if($canDeleteVoucher)
                            <li>
                                <button type="button"
                                        class="dropdown-item text-danger selection-action"
                                        data-selection-group="deposit-vouchers"
                                        data-action-type="submit-form"
                                        data-form-target="depositVoucherDeleteForm"
                                        data-url-template="{{ route('admin.finance.deposit-vouchers.destroy', ['deposit_voucher' => '__ID__']) }}"
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
                            <a href="{{ route('admin.finance.deposit-vouchers.create') }}" class="dropdown-item">
                                <i class="bi bi-plus-circle me-2"></i> {{ __('إنشاء سند إيداع جديد') }}
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        @endif
    </div>

    <div class="card-body">
        <form method="GET" action="{{ route('admin.finance.deposit-vouchers.index') }}" class="filter-bar" id="depositVoucherFilterForm">
            <input type="hidden" name="manager_id" value="{{ request('manager_id') }}">
            <input type="hidden" name="cash_box_id" value="{{ request('cash_box_id') }}">
            <input type="hidden" name="date_from" value="{{ request('date_from') }}">
            <input type="hidden" name="date_to" value="{{ request('date_to') }}">
            <input type="hidden" name="min_amount" value="{{ request('min_amount') }}">
            <input type="hidden" name="max_amount" value="{{ request('max_amount') }}">
            <input type="hidden" name="with_notes" value="{{ request('with_notes') }}">
            <input type="hidden" name="per_page" value="{{ request('per_page', $perPage ?? 20) }}">

            <div class="filter-bar__group filter-bar__group--search flex-grow-1">
                <div class="input-group filter-bar__input-group">
                    <span class="input-group-text filter-bar__icon"><i class="bi bi-search"></i></span>
                    <input type="text"
                           name="search"
                           class="form-control filter-bar__input"
                           placeholder="{{ __('ابحث برقم السند أو اسم المندوب أو الصندوق...') }}"
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="filter-bar__group filter-bar__group--submit">
                <button type="submit" class="btn btn-primary filter-bar__submit">{{ __('بحث') }}</button>
            </div>
            <div class="filter-bar__group filter-bar__group--actions" id="depositVouchersFilterActions">
                <button type="button"
                        class="table-action-button filter-trigger-button"
                        data-bs-toggle="modal"
                        data-bs-target="#depositVoucherFiltersModal"
                        title="{{ __('خيارات التصفية') }}"
                        aria-label="{{ __('خيارات التصفية') }}">
                    <i class="bi bi-funnel"></i>
                </button>
                @can('export-excel')
                    <button type="button"
                            class="table-action-button table-action-button--download"
                            data-export-target="#depositVouchersTable"
                            data-export-name="{{ __('سندات الإيداع') }}"
                            title="{{ __('تصدير إلى Excel') }}"
                            aria-label="{{ __('تصدير إلى Excel') }}">
                        <i class="bi bi-file-earmark-spreadsheet"></i>
                    </button>
                @endcan
                <button type="button"
                        class="table-action-button table-action-button--refresh"
                        data-refresh-target="#depositVouchersListingCard"
                        data-refresh-url="{{ request()->fullUrl() }}"
                        title="{{ __('تحديث القائمة') }}"
                        aria-label="{{ __('تحديث القائمة') }}">
                    <i class="bi bi-arrow-repeat"></i>
                </button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle" id="depositVouchersTable" data-table-toolbar data-toolbar-target="#depositVouchersFilterActions">
                <thead class="table-light">
                    <tr>
                        @if($showSelectionColumn)
                            <th class="text-center" style="width: 48px;">{{ __('تحديد') }}</th>
                        @endif
                        <th>{{ __('رقم السند') }}</th>
                        <th>{{ __('تاريخ السند') }}</th>
                        <th>{{ __('المندوب') }}</th>
                        <th>{{ __('الصندوق المستلم') }}</th>
                        <th>{{ __('المبلغ') }}</th>
                        <th>{{ __('الوصف') }}</th>
                        <th class="text-center">{{ __('عمليات سريعة') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vouchers as $voucher)
                        <tr>
                            @if($showSelectionColumn)
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input selection-checkbox" data-selection-group="deposit-vouchers" value="{{ $voucher->getKey() }}">
                                </td>
                            @endif
                            <td>{{ $voucher->number }}</td>
                            <td>{{ optional($voucher->voucher_date)->format('Y-m-d') ?? '—' }}</td>
                            <td>{{ $voucher->manager?->name ?? '—' }}</td>
                            <td>{{ $voucher->cashBox?->name ?? '—' }}</td>
                            <td>{{ number_format($voucher->amount, 2) }}</td>
                            <td>{{ $voucher->description ? \Illuminate\Support\Str::limit($voucher->description, 60) : '—' }}</td>
                            <td class="text-center">
                                <div class="d-inline-flex gap-2" data-ignore-row-select>
                                    @if($canEditVoucher)
                                        <a href="{{ route('admin.finance.deposit-vouchers.edit', $voucher) }}" class="btn btn-sm btn-outline-primary">
                                            {{ __('تعديل') }}
                                        </a>
                                    @endif
                                    @if($canDeleteVoucher)
                                        <form method="POST" action="{{ route('admin.finance.deposit-vouchers.destroy', $voucher) }}" onsubmit="return confirm('{{ __('هل أنت متأكد من حذف هذا السند؟') }}');">
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
                                $colspan = 7 + ($showSelectionColumn ? 1 : 0);
                            @endphp
                            <td colspan="{{ $colspan }}" class="py-4 text-muted">{{ __('لا توجد سندات إيداع مسجلة حالياً.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
            <form method="GET" action="{{ route('admin.finance.deposit-vouchers.index') }}" class="d-flex align-items-center">
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
    <form id="depositVoucherDeleteForm" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>
@endif

<!-- Filters Modal -->
<div class="modal fade filter-modal" id="depositVoucherFiltersModal" tabindex="-1" aria-labelledby="depositVoucherFiltersModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depositVoucherFiltersModalLabel">{{ __('تصفية سندات الإيداع') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('إغلاق') }}"></button>
            </div>
            <div class="modal-body">
                <form method="GET" action="{{ route('admin.finance.deposit-vouchers.index') }}" class="row g-3" id="depositVoucherFiltersForm">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="per_page" value="{{ request('per_page', $perPage ?? 20) }}">

                    <div class="col-12">
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

                    <div class="col-12">
                        <label for="cash_box_id" class="form-label">{{ __('الصندوق المستلم') }}</label>
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

                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="with_notes" name="with_notes" {{ request('with_notes') ? 'checked' : '' }}>
                            <label class="form-check-label" for="with_notes">{{ __('إظهار السندات التي تحتوي على ملاحظات فقط') }}</label>
                        </div>
                    </div>

                    <div class="col-12 d-flex justify-content-between align-items-center">
                        <a href="{{ route('admin.finance.deposit-vouchers.index') }}" class="btn btn-link text-danger">{{ __('مسح الفلاتر') }}</a>
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
            'group' => 'deposit-vouchers',
            'emptyMessage' => __('يرجى اختيار سند واحد على الأقل لتنفيذ الإجراء المطلوب.')
        ])
    @endpush
@endif
