@extends('admin.layout')

@php use App\Support\Sort; @endphp

@section('title', 'شحنات التصنيع')

@section('content')
@php
    $canManageInventory = auth()->user()?->can('manage-inventory');
    $showSelectionColumn = $canManageInventory;
    $sortOptions = [
        'route' => 'admin.manufacturing.shipments.index',
        'allowed' => $allowedSorts ?? [],
        'default_column' => $defaultSortColumn ?? 'shipped_at',
        'default_direction' => $defaultSortDirection ?? 'desc',
    ];
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

<div class="card shadow-sm" id="manufacturingShipmentsListingCard">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h4 class="mb-1">شحنات أوامر التصنيع</h4>
            <p class="text-muted mb-0">تتبع الشحنات القادمة من الموردين أو المصانع الخارجية.</p>
        </div>
        @if($canManageInventory)
        <div class="dropdown">
            <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="manufacturingShipmentsActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-gear-fill me-1"></i> إجراءات
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="manufacturingShipmentsActionsDropdown">
                <li class="dropdown-header">إجراءات على الشحنة</li>
                <li>
                    <button type="button"
                            class="dropdown-item selection-action"
                            data-selection-group="manufacturing-shipments"
                            data-action-type="navigate"
                            data-url-template="{{ route('admin.manufacturing.shipments.edit', ['shipment' => '__ID__']) }}">
                        <i class="bi bi-pencil me-2"></i> تعديل الشحنة
                    </button>
                </li>
                <li>
                    <button type="button"
                            class="dropdown-item text-danger selection-action"
                            data-selection-group="manufacturing-shipments"
                            data-action-type="submit-form"
                            data-form-target="manufacturingShipmentDeleteForm"
                            data-url-template="{{ route('admin.manufacturing.shipments.destroy', ['shipment' => '__ID__']) }}"
                            data-confirm="حذف هذه الشحنة؟"
                            data-confirm-multiple="هل تريد حذف الشحنات المحددة (عدد: __COUNT__)؟"
                            data-allow-multiple="true">
                        <i class="bi bi-trash me-2"></i> حذف الشحنة
                    </button>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a href="{{ route('admin.manufacturing.shipments.create') }}" class="dropdown-item">
                        <i class="bi bi-plus-circle me-2"></i> شحنة جديدة
                    </a>
                </li>
            </ul>
        </div>
        @endif
    </div>
    <div class="card-body">
        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        <form method="GET" action="{{ route('admin.manufacturing.shipments.index') }}" class="filter-bar" id="manufacturingShipmentsFilterBar">
            <input type="hidden" name="order_id" value="{{ $filters['order_id'] ?? null }}">
            <input type="hidden" name="warehouse_id" value="{{ $filters['warehouse_id'] ?? null }}">
            <input type="hidden" name="date_from" value="{{ $filters['date_from'] ?? null }}">
            <input type="hidden" name="date_to" value="{{ $filters['date_to'] ?? null }}">
            <input type="hidden" name="tracking" value="{{ $filters['tracking'] ?? null }}">
            <div class="filter-bar__group filter-bar__group--search flex-grow-1">
                <div class="input-group filter-bar__input-group">
                    <span class="input-group-text filter-bar__icon"><i class="bi bi-search"></i></span>
                    <input type="search" name="search" class="form-control filter-bar__input" placeholder="ابحث بالرقم أو المرجع" value="{{ $filters['search'] ?? '' }}">
                </div>
            </div>
            <div class="filter-bar__group filter-bar__group--submit">
                <button type="submit" class="btn btn-primary filter-bar__submit">بحث</button>
            </div>
            <div class="filter-bar__group filter-bar__group--actions" id="manufacturingShipmentsFilterActions">
                <button type="button"
                        class="table-action-button filter-trigger-button"
                        data-bs-toggle="modal"
                        data-bs-target="#manufacturingShipmentsFiltersModal"
                        title="خيارات التصفية"
                        aria-label="خيارات التصفية">
                    <i class="bi bi-funnel"></i>
                </button>
                @can('export-excel')
                    <button type="button"
                            class="table-action-button table-action-button--download"
                            data-export-target="#manufacturingShipmentsTable"
                            data-export-name="شحنات التصنيع"
                            title="تصدير إلى Excel"
                            aria-label="تصدير إلى Excel">
                        <i class="bi bi-file-earmark-spreadsheet"></i>
                    </button>
                @endcan
                <button type="button"
                        class="table-action-button table-action-button--refresh"
                        data-refresh-target="#manufacturingShipmentsListingCard"
                        data-refresh-url="{{ request()->fullUrl() }}"
                        title="تحديث القائمة"
                        aria-label="تحديث القائمة">
                    <i class="bi bi-arrow-repeat"></i>
                </button>
            </div>
            @if(($filters['search'] ?? null) || ($filters['order_id'] ?? null) || ($filters['warehouse_id'] ?? null) || ($filters['date_from'] ?? null) || ($filters['date_to'] ?? null) || ($filters['tracking'] ?? null))
            <div class="filter-bar__group">
                <a href="{{ route('admin.manufacturing.shipments.index') }}" class="btn btn-outline-secondary btn-sm">إعادة التعيين</a>
            </div>
            @endif
        </form>

        <div class="modal fade filter-modal" id="manufacturingShipmentsFiltersModal" tabindex="-1" aria-labelledby="manufacturingShipmentsFiltersModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="manufacturingShipmentsFiltersModalLabel">تصفية شحنات التصنيع</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <div class="modal-body">
                        <form method="GET" action="{{ route('admin.manufacturing.shipments.index') }}" class="row g-3">
                            <input type="hidden" name="search" value="{{ $filters['search'] ?? '' }}">
                            <div class="col-12">
                                <label for="manufacturingShipmentsOrder" class="form-label">أمر التصنيع</label>
                                <select name="order_id" id="manufacturingShipmentsOrder" class="form-select">
                                    <option value="">كل الأوامر</option>
                                    @foreach($orderOptions as $orderId => $reference)
                                        <option value="{{ $orderId }}" {{ (string) ($filters['order_id'] ?? '') === (string) $orderId ? 'selected' : '' }}>{{ $reference }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="manufacturingShipmentsWarehouse" class="form-label">المخزن</label>
                                <select name="warehouse_id" id="manufacturingShipmentsWarehouse" class="form-select">
                                    <option value="">كل المخازن</option>
                                    @foreach($warehouseOptions as $warehouseId => $warehouseName)
                                        <option value="{{ $warehouseId }}" {{ (string) ($filters['warehouse_id'] ?? '') === (string) $warehouseId ? 'selected' : '' }}>{{ $warehouseName }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="manufacturingShipmentsDateFrom" class="form-label">تاريخ الشحن من</label>
                                <input type="date" name="date_from" id="manufacturingShipmentsDateFrom" class="form-control" value="{{ $filters['date_from'] ?? '' }}">
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="manufacturingShipmentsDateTo" class="form-label">تاريخ الشحن إلى</label>
                                <input type="date" name="date_to" id="manufacturingShipmentsDateTo" class="form-control" value="{{ $filters['date_to'] ?? '' }}">
                            </div>
                            <div class="col-12">
                                <label for="manufacturingShipmentsTracking" class="form-label">رقم التتبع</label>
                                <select name="tracking" id="manufacturingShipmentsTracking" class="form-select">
                                    <option value="">الكل</option>
                                    <option value="with" {{ ($filters['tracking'] ?? '') === 'with' ? 'selected' : '' }}>بها تتبع</option>
                                    <option value="without" {{ ($filters['tracking'] ?? '') === 'without' ? 'selected' : '' }}>بدون تتبع</option>
                                </select>
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn w-100 btn-filter-submit">تطبيق الفلاتر</button>
                            </div>
                            <div class="col-12 mt-2">
                                <a href="{{ route('admin.manufacturing.shipments.index') }}" class="btn btn-outline-secondary w-100 btn-filter-reset">إعادة التعيين</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0" id="manufacturingShipmentsTable" data-table-toolbar data-toolbar-target="#manufacturingShipmentsFilterActions">
                <thead class="table-light">
                    <tr>
                        @if($showSelectionColumn)
                        <th class="text-center" style="width: 48px;">تحديد</th>
                        @endif
                        <th>{!! Sort::link('order_id', 'أمر التصنيع', $sortOptions) !!}</th>
                        <th>{!! Sort::link('warehouse_id', 'المخزن', $sortOptions) !!}</th>
                        <th>رقم التتبع</th>
                        <th class="text-end">{!! Sort::link('shipped_quantity', 'الكمية', $sortOptions) !!}</th>
                        <th>{!! Sort::link('shipped_at', 'تاريخ الشحن', $sortOptions) !!}</th>
                        <th data-default-hidden="true">آخر تحديث</th>
                        <th>ملاحظات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shipments as $shipment)
                        <tr>
                            @if($showSelectionColumn)
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input selection-checkbox" data-selection-group="manufacturing-shipments" value="{{ $shipment->id }}">
                            </td>
                            @endif
                            <td>{{ $shipment->order?->reference ?? '—' }}</td>
                            <td>{{ $shipment->warehouse?->name ?? 'غير محدد' }}</td>
                            <td>{{ $shipment->tracking_number ?? '—' }}</td>
                            <td class="text-end">{{ number_format($shipment->shipped_quantity, 0) }}</td>
                            <td>{{ optional($shipment->shipped_at)->format('Y-m-d') ?? '—' }}</td>
                            <td>{{ optional($shipment->updated_at)->format('Y-m-d H:i') ?? '—' }}</td>
                            <td>{{ $shipment->notes ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ 7 + ($showSelectionColumn ? 1 : 0) }}" class="text-center py-4 text-muted">لا توجد شحنات مسجلة.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer d-flex flex-wrap gap-3 justify-content-between align-items-center">
        <form method="GET" action="{{ route('admin.manufacturing.shipments.index') }}" class="d-flex align-items-center gap-2">
            @foreach(request()->except(['per_page', 'page']) as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <label for="manufacturingShipmentsPerPage" class="mb-0">عدد الصفوف:</label>
            <select name="per_page" id="manufacturingShipmentsPerPage" class="form-select form-select-sm" onchange="this.form.submit()">
                @foreach([10, 15, 20, 30, 50, 100] as $option)
                    <option value="{{ $option }}" {{ $shipments->perPage() === $option ? 'selected' : '' }}>{{ $option }}</option>
                @endforeach
            </select>
        </form>
        {{ $shipments->links() }}
    </div>
</div>
@if($showSelectionColumn)
<form id="manufacturingShipmentDeleteForm" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>
@endif
@endsection

@if($showSelectionColumn)
    @push('scripts')
        @include('admin.partials.selection-script', [
            'group' => 'manufacturing-shipments',
            'emptyMessage' => 'يرجى اختيار شحنة أولاً لتنفيذ الإجراء المطلوب.'
        ])
    @endpush
@endif
