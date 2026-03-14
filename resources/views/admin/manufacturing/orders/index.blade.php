@extends('admin.layout')

@php use App\Support\Sort; @endphp

@section('title', 'أوامر التصنيع')

@section('content')
@php
    $canManageInventory = auth()->user()?->can('manage-inventory');
    $showSelectionColumn = $canManageInventory;
    $sortOptions = [
        'route' => 'admin.manufacturing.orders.index',
        'allowed' => $allowedSorts ?? [],
        'default_column' => $defaultSortColumn ?? 'created_at',
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

<div class="card shadow-sm" id="manufacturingOrdersListingCard">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h4 class="mb-1">أوامر التصنيع</h4>
            <p class="text-muted mb-0">متابعة أوامر الإنتاج وحالة تنفيذها.</p>
        </div>
        @if($canManageInventory)
        <div class="dropdown">
            <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="manufacturingOrdersActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-gear-fill me-1"></i> إجراءات
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="manufacturingOrdersActionsDropdown">
                <li class="dropdown-header">إجراءات على الأمر</li>
                <li>
                    <button type="button"
                            class="dropdown-item selection-action"
                            data-selection-group="manufacturing-orders"
                            data-action-type="navigate"
                            data-url-template="{{ route('admin.manufacturing.orders.edit', ['order' => '__ID__']) }}">
                        <i class="bi bi-pencil me-2"></i> تعديل الأمر
                    </button>
                </li>
                <li>
                    <button type="button"
                            class="dropdown-item text-danger selection-action"
                            data-selection-group="manufacturing-orders"
                            data-action-type="submit-form"
                            data-form-target="manufacturingOrderDeleteForm"
                            data-url-template="{{ route('admin.manufacturing.orders.destroy', ['order' => '__ID__']) }}"
                            data-confirm="حذف هذا الأمر؟"
                            data-confirm-multiple="هل تريد حذف أوامر التصنيع المحددة (عدد: __COUNT__)؟"
                            data-allow-multiple="true">
                        <i class="bi bi-trash me-2"></i> حذف الأمر
                    </button>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a href="{{ route('admin.manufacturing.orders.create') }}" class="dropdown-item">
                        <i class="bi bi-plus-circle me-2"></i> أمر جديد
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
        <form method="GET" action="{{ route('admin.manufacturing.orders.index') }}" class="filter-bar" id="manufacturingOrdersFilterBar">
            <input type="hidden" name="status" value="{{ $filters['status'] ?? null }}">
            <input type="hidden" name="product_id" value="{{ $filters['product_id'] ?? null }}">
            <input type="hidden" name="starts_from" value="{{ $filters['starts_from'] ?? null }}">
            <input type="hidden" name="starts_to" value="{{ $filters['starts_to'] ?? null }}">
            <input type="hidden" name="due_from" value="{{ $filters['due_from'] ?? null }}">
            <input type="hidden" name="due_to" value="{{ $filters['due_to'] ?? null }}">
            <input type="hidden" name="shipments" value="{{ $filters['shipments'] ?? null }}">
            <div class="filter-bar__group filter-bar__group--search flex-grow-1">
                <div class="input-group filter-bar__input-group">
                    <span class="input-group-text filter-bar__icon"><i class="bi bi-search"></i></span>
                    <input type="search" name="search" class="form-control filter-bar__input" placeholder="ابحث بالمرجع أو المنتج" value="{{ $filters['search'] ?? '' }}">
                </div>
            </div>
            <div class="filter-bar__group filter-bar__group--submit">
                <button type="submit" class="btn btn-primary filter-bar__submit">بحث</button>
            </div>
            <div class="filter-bar__group filter-bar__group--actions" id="manufacturingOrdersFilterActions">
                <button type="button"
                        class="table-action-button filter-trigger-button"
                        data-bs-toggle="modal"
                        data-bs-target="#manufacturingOrdersFiltersModal"
                        title="خيارات التصفية"
                        aria-label="خيارات التصفية">
                    <i class="bi bi-funnel"></i>
                </button>
                @can('export-excel')
                    <button type="button"
                            class="table-action-button table-action-button--download"
                            data-export-target="#manufacturingOrdersTable"
                            data-export-name="أوامر التصنيع"
                            title="تصدير إلى Excel"
                            aria-label="تصدير إلى Excel">
                        <i class="bi bi-file-earmark-spreadsheet"></i>
                    </button>
                @endcan
                <button type="button"
                        class="table-action-button table-action-button--refresh"
                        data-refresh-target="#manufacturingOrdersListingCard"
                        data-refresh-url="{{ request()->fullUrl() }}"
                        title="تحديث القائمة"
                        aria-label="تحديث القائمة">
                    <i class="bi bi-arrow-repeat"></i>
                </button>
            </div>
            @if(($filters['search'] ?? null) || ($filters['status'] ?? null) || ($filters['product_id'] ?? null) || ($filters['starts_from'] ?? null) || ($filters['starts_to'] ?? null) || ($filters['due_from'] ?? null) || ($filters['due_to'] ?? null) || ($filters['shipments'] ?? null))
            <div class="filter-bar__group">
                <a href="{{ route('admin.manufacturing.orders.index') }}" class="btn btn-outline-secondary btn-sm">إعادة التعيين</a>
            </div>
            @endif
        </form>

        <div class="modal fade filter-modal" id="manufacturingOrdersFiltersModal" tabindex="-1" aria-labelledby="manufacturingOrdersFiltersModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="manufacturingOrdersFiltersModalLabel">تصفية أوامر التصنيع</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <div class="modal-body">
                        <form method="GET" action="{{ route('admin.manufacturing.orders.index') }}" class="row g-3">
                            <input type="hidden" name="search" value="{{ $filters['search'] ?? '' }}">
                            <div class="col-12">
                                <label for="manufacturingOrdersStatus" class="form-label">الحالة</label>
                                <select name="status" id="manufacturingOrdersStatus" class="form-select">
                                    <option value="">كل الحالات</option>
                                    <option value="planned" {{ ($filters['status'] ?? '') === 'planned' ? 'selected' : '' }}>مخطط</option>
                                    <option value="in_progress" {{ ($filters['status'] ?? '') === 'in_progress' ? 'selected' : '' }}>قيد التنفيذ</option>
                                    <option value="completed" {{ ($filters['status'] ?? '') === 'completed' ? 'selected' : '' }}>مكتمل</option>
                                    <option value="cancelled" {{ ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' }}>ملغى</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="manufacturingOrdersProduct" class="form-label">المنتج</label>
                                <select name="product_id" id="manufacturingOrdersProduct" class="form-select">
                                    <option value="">كل المنتجات</option>
                                    @foreach($productOptions as $productId => $productName)
                                        <option value="{{ $productId }}" {{ (string) ($filters['product_id'] ?? '') === (string) $productId ? 'selected' : '' }}>{{ $productName }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="manufacturingOrdersStartsFrom" class="form-label">تاريخ البدء من</label>
                                <input type="date" name="starts_from" id="manufacturingOrdersStartsFrom" class="form-control" value="{{ $filters['starts_from'] ?? '' }}">
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="manufacturingOrdersStartsTo" class="form-label">تاريخ البدء إلى</label>
                                <input type="date" name="starts_to" id="manufacturingOrdersStartsTo" class="form-control" value="{{ $filters['starts_to'] ?? '' }}">
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="manufacturingOrdersDueFrom" class="form-label">تاريخ التسليم من</label>
                                <input type="date" name="due_from" id="manufacturingOrdersDueFrom" class="form-control" value="{{ $filters['due_from'] ?? '' }}">
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="manufacturingOrdersDueTo" class="form-label">تاريخ التسليم إلى</label>
                                <input type="date" name="due_to" id="manufacturingOrdersDueTo" class="form-control" value="{{ $filters['due_to'] ?? '' }}">
                            </div>
                            <div class="col-12">
                                <label for="manufacturingOrdersShipments" class="form-label">الشحنات المرتبطة</label>
                                <select name="shipments" id="manufacturingOrdersShipments" class="form-select">
                                    <option value="">الكل</option>
                                    <option value="with" {{ ($filters['shipments'] ?? '') === 'with' ? 'selected' : '' }}>مع شحنات</option>
                                    <option value="without" {{ ($filters['shipments'] ?? '') === 'without' ? 'selected' : '' }}>بدون شحنات</option>
                                </select>
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn w-100 btn-filter-submit">تطبيق الفلاتر</button>
                            </div>
                            <div class="col-12 mt-2">
                                <a href="{{ route('admin.manufacturing.orders.index') }}" class="btn btn-outline-secondary w-100 btn-filter-reset">إعادة التعيين</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0" id="manufacturingOrdersTable" data-table-toolbar data-toolbar-target="#manufacturingOrdersFilterActions">
                <thead class="table-light">
                    <tr>
                        @if($showSelectionColumn)
                        <th class="text-center" style="width: 48px;">تحديد</th>
                        @endif
                        <th>{!! Sort::link('reference', 'المرجع', $sortOptions) !!}</th>
                        <th>المنتج</th>
                        <th class="text-end">{!! Sort::link('planned_quantity', 'الكمية المخطط لها', $sortOptions) !!}</th>
                        <th class="text-end">{!! Sort::link('completed_quantity', 'الكمية المكتملة', $sortOptions) !!}</th>
                        <th>{!! Sort::link('status', 'الحالة', $sortOptions) !!}</th>
                        <th>{!! Sort::link('starts_at', 'تاريخ البدء', $sortOptions) !!}</th>
                        <th>{!! Sort::link('due_at', 'تاريخ التسليم', $sortOptions) !!}</th>
                        <th class="text-end">{!! Sort::link('total_cost', 'التكلفة', $sortOptions) !!}</th>
                        <th data-default-hidden="true">آخر تحديث</th>
                        <th class="text-center">التفاصيل</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr data-dblclick-url="{{ route('admin.manufacturing.orders.show', $order) }}"
                            data-dblclick-label="عرض تفاصيل أمر التصنيع {{ $order->reference }}">
                            @if($showSelectionColumn)
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input selection-checkbox" data-selection-group="manufacturing-orders" value="{{ $order->id }}">
                            </td>
                            @endif
                            <td class="fw-semibold">{{ $order->reference }}</td>
                            <td>
                                <div>{{ $order->product?->name_ar ?? 'منتج محذوف' }}</div>
                                <small class="text-muted">{{ $order->variant_name ?? 'بدون متغير' }}</small>
                            </td>
                            <td class="text-end">{{ number_format($order->planned_quantity, 0) }}</td>
                            <td class="text-end">{{ number_format($order->completed_quantity, 0) }}</td>
                            <td>
                                @php
                                    $statusClasses = [
                                        'planned' => 'bg-secondary',
                                        'in_progress' => 'bg-info',
                                        'completed' => 'bg-success',
                                        'cancelled' => 'bg-danger',
                                    ];
                                    $statusLabels = [
                                        'planned' => 'مخطط',
                                        'in_progress' => 'قيد التنفيذ',
                                        'completed' => 'مكتمل',
                                        'cancelled' => 'ملغى',
                                    ];
                                @endphp
                                <span class="badge {{ $statusClasses[$order->status] ?? 'bg-secondary' }}">{{ $statusLabels[$order->status] ?? $order->status }}</span>
                            </td>
                            <td>{{ optional($order->starts_at)->format('Y-m-d') ?? '—' }}</td>
                            <td>{{ optional($order->due_at)->format('Y-m-d') ?? '—' }}</td>
                            <td class="text-end">{{ \App\Support\Currency::format($order->total_cost) }}</td>
                            <td>{{ optional($order->updated_at)->format('Y-m-d H:i') ?? '—' }}</td>
                            <td class="text-center">
                            </td>
                        </tr>
                    @empty
                        @php
                            $ordersColspan = 10 + ($showSelectionColumn ? 1 : 0);
                        @endphp
                        <tr><td colspan="{{ $ordersColspan }}" class="text-center py-4 text-muted">لا توجد أوامر تصنيع حالياً.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer d-flex flex-wrap gap-3 justify-content-between align-items-center">
        <form method="GET" action="{{ route('admin.manufacturing.orders.index') }}" class="d-flex align-items-center gap-2">
            @foreach(request()->except(['per_page', 'page']) as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <label for="manufacturingOrdersPerPage" class="mb-0">عدد الصفوف:</label>
            <select name="per_page" id="manufacturingOrdersPerPage" class="form-select form-select-sm" onchange="this.form.submit()">
                @foreach([10, 15, 20, 30, 50, 100] as $option)
                    <option value="{{ $option }}" {{ $orders->perPage() === $option ? 'selected' : '' }}>{{ $option }}</option>
                @endforeach
            </select>
        </form>
        {{ $orders->links() }}
    </div>
</div>
@if($showSelectionColumn)
<form id="manufacturingOrderDeleteForm" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>
@endif
@endsection

@if($showSelectionColumn)
    @push('scripts')
        @include('admin.partials.selection-script', [
            'group' => 'manufacturing-orders',
            'emptyMessage' => 'يرجى اختيار أمر تصنيع أولاً لتنفيذ الإجراء المطلوب.'
        ])
    @endpush
@endif
