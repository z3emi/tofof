@extends('admin.layout')

@section('title', 'كشف حساب عميل')

@section('content')
<div class="page-header mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
        <h1 class="h4 mb-1">كشف حساب عميل</h1>
        <p class="text-muted mb-0 small">استعرض الحركات الآجلة، صدّرها، وطبّق الفلاتر بسرعة دون الخروج من الصفحة.</p>
    </div>
    @if($customer)
        <div class="text-end">
            <span class="text-muted small">إجمالي الرصيد المستحق</span>
            <div class="fs-5 fw-semibold text-primary">{{ number_format($totals['balance'] ?? 0, 2) }}</div>
        </div>
    @endif
</div>

<form action="{{ route('admin.accounting.reports.customer-statement') }}" method="get" id="statementFilters">
    <div class="card shadow-sm border-0 mb-4" id="statementFiltersCard">
        <div class="card-body">
            <div class="filter-bar" id="statementFilterBar">
                <div class="filter-bar__group filter-bar__group--customer flex-grow-1">
                    <label class="form-label">العميل</label>
                    <select name="customer_id" id="customer_id" class="form-select js-customer-select" data-placeholder="اختر العميل" required>
                        @foreach($customers as $option)
                            <option value="{{ $option->id }}" @selected(optional($customer)->id == $option->id)>
                                {{ $option->display_name }}@if($option->phone_number) - {{ $option->phone_number }}@endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-bar__group filter-bar__group--search">
                    <label class="form-label visually-hidden">بحث</label>
                    <div class="input-group filter-bar__input-group">
                        <span class="input-group-text filter-bar__icon"><i class="bi bi-search"></i></span>
                        <input type="search" name="q" class="form-control filter-bar__input" placeholder="ابحث في النوع أو المرجع أو التفاصيل" value="{{ $search ?? '' }}">
                    </div>
                </div>

                <div class="filter-bar__group filter-bar__group--submit">
                    <button type="submit" class="btn btn-primary filter-bar__submit">بحث</button>
                </div>

                <div class="filter-bar__group filter-bar__group--actions" id="statementToolbar">
                    <button type="button" class="table-action-button filter-trigger-button" data-bs-toggle="modal" data-bs-target="#statementFilterModal" title="خيارات التصفية" aria-label="خيارات التصفية">
                        <i class="bi bi-sliders"></i>
                    </button>
                    <a href="{{ route('admin.accounting.reports.customer-statement', array_filter([
                        'customer_id' => request('customer_id') ?? optional($customer)->id,
                        'direction' => request('direction'),
                    ] + ($search ? ['q' => $search] : []) + ($from ? ['from' => $from] : []) + ($to ? ['to' => $to] : []))) }}"
                       class="table-action-button table-action-button--refresh"
                       title="تحديث القائمة"
                       aria-label="تحديث القائمة">
                        <i class="bi bi-arrow-repeat"></i>
                    </a>
                    @can('export-excel')
                        <button type="submit" name="export" value="1" class="table-action-button table-action-button--download" title="تصدير إلى Excel" aria-label="تصدير إلى Excel">
                            <i class="bi bi-file-earmark-spreadsheet"></i>
                        </button>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="statementFilterModal" tabindex="-1" aria-labelledby="statementFilterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="statementFilterModalLabel">الفلاتر المتقدمة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label">من تاريخ</label>
                            <input type="date" name="from" class="form-control" value="{{ $from ?? '' }}">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">إلى تاريخ</label>
                            <input type="date" name="to" class="form-control" value="{{ $to ?? '' }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">ترتيب الحركات</label>
                            <select name="direction" class="form-select">
                                <option value="desc" @selected(($direction ?? 'desc') === 'desc')>الأحدث أولاً</option>
                                <option value="asc" @selected(($direction ?? 'desc') === 'asc')>الأقدم أولاً</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <a href="{{ route('admin.accounting.reports.customer-statement', ['customer_id' => request('customer_id') ?? optional($customer)->id]) }}" class="btn btn-link text-danger" data-statement-reset>إعادة الضبط</a>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">تطبيق</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@if($customer)
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body d-flex flex-wrap gap-4 justify-content-between">
            <div class="statement-summary-box">
                <span class="text-muted small d-block">الرصيد الافتتاحي</span>
                <span class="fs-5 fw-semibold">{{ number_format($openingBalance ?? 0, 2) }}</span>
            </div>
            <div class="statement-summary-box">
                <span class="text-muted small d-block">إجمالي المدين</span>
                <span class="fs-5 fw-semibold text-danger">{{ number_format($totals['debit'] ?? 0, 2) }}</span>
            </div>
            <div class="statement-summary-box">
                <span class="text-muted small d-block">إجمالي الدائن</span>
                <span class="fs-5 fw-semibold text-success">{{ number_format($totals['credit'] ?? 0, 2) }}</span>
            </div>
            <div class="statement-summary-box">
                <span class="text-muted small d-block">الرصيد بعد الحركات</span>
                <span class="fs-5 fw-semibold text-primary">{{ number_format($totals['balance'] ?? 0, 2) }}</span>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <span class="fw-semibold">{{ $customer->display_name }}</span>
                @if($customer->phone_number)
                    <span class="text-muted small ms-2">{{ $customer->phone_number }}</span>
                @endif
            </div>
            <span class="badge bg-light text-dark">{{ $rows->count() }} حركة</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped align-middle text-center mb-0 statement-table"
                       id="customerStatementTable"
                       data-table-toolbar
                       data-toolbar-target="#statementToolbar">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">التاريخ</th>
                            <th scope="col">النوع</th>
                            <th scope="col">المرجع</th>
                            <th scope="col" class="text-danger">مدين</th>
                            <th scope="col" class="text-success">دائن</th>
                            <th scope="col">الرصيد</th>
                            <th scope="col">تفاصيل</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td class="text-nowrap">{{ $row['date'] }}</td>
                                <td>
                                    <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis statement-type-badge">{{ $row['type'] }}</span>
                                </td>
                                <td class="fw-semibold">{{ $row['reference'] }}</td>
                                <td class="text-danger">{{ $row['debit'] ? number_format($row['debit'], 2) : '—' }}</td>
                                <td class="text-success">{{ $row['credit'] ? number_format($row['credit'], 2) : '—' }}</td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary-emphasis">{{ number_format($row['balance'], 2) }}</span>
                                </td>
                                <td class="text-muted">{{ $row['details'] ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">لا توجد حركات مطابقة للمرشحات الحالية</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light">
                        <tr class="fw-semibold">
                            <td colspan="3" class="text-end">الإجمالي</td>
                            <td class="text-danger">{{ number_format($totals['debit'] ?? 0, 2) }}</td>
                            <td class="text-success">{{ number_format($totals['credit'] ?? 0, 2) }}</td>
                            <td>{{ number_format($totals['balance'] ?? 0, 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endif
@endsection

@push('styles')
    @once
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @endonce
    <style>
        .filter-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            align-items: flex-end;
        }

        .filter-bar__group {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
            min-width: 200px;
        }

        .filter-bar__group--actions {
            flex-direction: row;
            align-items: center;
            gap: 0.45rem;
            margin-inline-start: auto;
        }

        .filter-bar__group--search {
            flex-grow: 1;
            min-width: 240px;
        }

        .filter-bar__input-group .filter-bar__icon {
            background-color: #f8f9fa;
        }

        .filter-bar__submit {
            min-width: 110px;
        }

        .table-action-button {
            width: 44px;
            height: 44px;
            border-radius: 0.85rem;
            border: 1px solid rgba(195, 141, 146, 0.45);
            background: rgba(47, 26, 36, 0.82);
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
            text-decoration: none;
        }

        .table-action-button .bi {
            font-size: 1.1rem;
        }

        .table-action-button:hover,
        .table-action-button:focus {
            border-color: rgba(195, 141, 146, 0.35);
            background: #f1f3f5;
            color: #2f1a24;
            box-shadow: none;
            transform: none;
        }

        .table-action-button--download {
            background: linear-gradient(135deg, #FF5722, #FFCCBC);
            border-color: rgba(129, 212, 250, 0.8);
        }

        .table-action-button--download:hover,
        .table-action-button--download:focus {
            background: linear-gradient(135deg, #FF5722, #FFCCBC);
            border-color: rgba(129, 212, 250, 0.8);
            color: #fff;
        }

        .statement-summary-box {
            min-width: 170px;
        }

        .statement-table tbody tr:hover {
            background-color: #f9f5ff;
        }

        .statement-type-badge {
            font-weight: 600;
            background-color: rgba(108, 117, 125, 0.15) !important;
        }

        .select2-container--default .select2-selection--single {
            height: 38px;
            padding: 4px 12px;
            border-radius: 0.5rem;
            border-color: #ced4da;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 30px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            top: 6px;
        }
    </style>
@endpush

@push('scripts')
    @once
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @endonce
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('statementFilters');
            const customerSelect = document.querySelector('.js-customer-select');

            if (customerSelect) {
                const $select = window.jQuery ? window.jQuery(customerSelect) : null;
                if ($select && $select.select2) {
                    $select.select2({
                        width: '100%',
                        allowClear: false,
                        dir: 'rtl',
                    });

                    $select.on('change', function () {
                        if (form) {
                            form.requestSubmit();
                        }
                    });
                } else {
                    customerSelect.addEventListener('change', function () {
                        if (form) {
                            form.requestSubmit();
                        }
                    });
                }
            }

            const resetLink = document.querySelector('[data-statement-reset]');
            if (resetLink) {
                resetLink.addEventListener('click', function () {
                    const modal = document.getElementById('statementFilterModal');
                    if (modal) {
                        const instance = bootstrap.Modal.getInstance(modal);
                        instance?.hide();
                    }
                });
            }
        });
    </script>
@endpush
