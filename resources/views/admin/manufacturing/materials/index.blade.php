@extends('admin.layout')

@section('title', 'إدارة المواد الخام')

@php
    use Illuminate\Support\Str;
    use App\Support\Sort;
@endphp

@section('content')
@php
    $canManageInventory = auth()->user()?->can('manage-inventory');
    $showSelectionColumn = $canManageInventory;
    $sortOptions = [
        'route' => 'admin.manufacturing.materials.index',
        'allowed' => $allowedSorts ?? [],
        'default_column' => $defaultSortColumn ?? 'name',
        'default_direction' => $defaultSortDirection ?? 'asc',
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

<div class="card shadow-sm" id="manufacturingMaterialsListingCard">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h4 class="mb-1">إدارة المواد الخام</h4>
            <p class="text-muted mb-0">إضافة وتعديل المواد الخام المستخدمة في عمليات التصنيع.</p>
        </div>
        @if($canManageInventory)
        <div class="dropdown">
            <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="materialsActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-gear-fill me-1"></i> إجراءات
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="materialsActionsDropdown">
                <li class="dropdown-header">إجراءات على المادة</li>
                <li>
                    <button type="button"
                            class="dropdown-item selection-action"
                            data-selection-group="manufacturing-materials"
                            data-action-type="navigate"
                            data-url-template="{{ route('admin.manufacturing.materials.edit', ['material' => '__ID__']) }}">
                        <i class="bi bi-pencil me-2"></i> تعديل المادة
                    </button>
                </li>
                <li>
                    <button type="button"
                            class="dropdown-item text-danger selection-action"
                            data-selection-group="manufacturing-materials"
                            data-action-type="submit-form"
                            data-form-target="materialDeleteForm"
                            data-url-template="{{ route('admin.manufacturing.materials.destroy', ['material' => '__ID__']) }}"
                            data-confirm="هل أنت متأكد من حذف هذه المادة؟"
                            data-confirm-multiple="هل تريد حذف المواد المحددة (عدد: __COUNT__)؟"
                            data-allow-multiple="true">
                        <i class="bi bi-trash me-2"></i> حذف المادة
                    </button>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a href="{{ route('admin.manufacturing.materials.create') }}" class="dropdown-item">
                        <i class="bi bi-plus-circle me-2"></i> مادة جديدة
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
        <form method="GET" action="{{ route('admin.manufacturing.materials.index') }}" class="filter-bar" id="manufacturingMaterialsFilterBar">
            <input type="hidden" name="unit" value="{{ $filters['unit'] ?? null }}">
            <input type="hidden" name="notes" value="{{ $filters['notes'] ?? null }}">
            <input type="hidden" name="cost_min" value="{{ $filters['cost_min'] ?? null }}">
            <input type="hidden" name="cost_max" value="{{ $filters['cost_max'] ?? null }}">
            <div class="filter-bar__group filter-bar__group--search flex-grow-1">
                <div class="input-group filter-bar__input-group">
                    <span class="input-group-text filter-bar__icon"><i class="bi bi-search"></i></span>
                    <input type="search" name="search" class="form-control filter-bar__input" placeholder="ابحث عن مادة أو SKU" value="{{ $filters['search'] ?? '' }}">
                </div>
            </div>
            <div class="filter-bar__group filter-bar__group--submit">
                <button type="submit" class="btn btn-primary filter-bar__submit">بحث</button>
            </div>
            <div class="filter-bar__group filter-bar__group--actions" id="manufacturingMaterialsFilterActions">
                <button type="button"
                        class="table-action-button filter-trigger-button"
                        data-bs-toggle="modal"
                        data-bs-target="#manufacturingMaterialsFiltersModal"
                        title="خيارات التصفية"
                        aria-label="خيارات التصفية">
                    <i class="bi bi-funnel"></i>
                </button>
                @can('export-excel')
                    <button type="button"
                            class="table-action-button table-action-button--download"
                            data-export-target="#manufacturingMaterialsTable"
                            data-export-name="مواد التصنيع"
                            title="تصدير إلى Excel"
                            aria-label="تصدير إلى Excel">
                        <i class="bi bi-file-earmark-spreadsheet"></i>
                    </button>
                @endcan
                <button type="button"
                        class="table-action-button table-action-button--refresh"
                        data-refresh-target="#manufacturingMaterialsListingCard"
                        data-refresh-url="{{ request()->fullUrl() }}"
                        title="تحديث القائمة"
                        aria-label="تحديث القائمة">
                    <i class="bi bi-arrow-repeat"></i>
                </button>
            </div>
            @if(($filters['search'] ?? null) || ($filters['unit'] ?? null) || ($filters['notes'] ?? null) || ($filters['cost_min'] ?? null) || ($filters['cost_max'] ?? null))
            <div class="filter-bar__group">
                <a href="{{ route('admin.manufacturing.materials.index') }}" class="btn btn-outline-secondary btn-sm">إعادة التعيين</a>
            </div>
            @endif
        </form>

        <div class="modal fade filter-modal" id="manufacturingMaterialsFiltersModal" tabindex="-1" aria-labelledby="manufacturingMaterialsFiltersModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="manufacturingMaterialsFiltersModalLabel">تصفية المواد الخام</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <div class="modal-body">
                        <form method="GET" action="{{ route('admin.manufacturing.materials.index') }}" class="row g-3">
                            <input type="hidden" name="search" value="{{ $filters['search'] ?? '' }}">
                            <div class="col-12">
                                <label for="materialFilterUnit" class="form-label">الوحدة</label>
                                <select name="unit" id="materialFilterUnit" class="form-select">
                                    <option value="">كل الوحدات</option>
                                    @foreach($unitOptions as $unit)
                                        <option value="{{ $unit }}" {{ ($filters['unit'] ?? '') === $unit ? 'selected' : '' }}>{{ $unit }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="materialCostMin" class="form-label">تكلفة الوحدة من</label>
                                <input type="number" step="0.01" name="cost_min" id="materialCostMin" class="form-control" value="{{ $filters['cost_min'] ?? '' }}">
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="materialCostMax" class="form-label">تكلفة الوحدة إلى</label>
                                <input type="number" step="0.01" name="cost_max" id="materialCostMax" class="form-control" value="{{ $filters['cost_max'] ?? '' }}">
                            </div>
                            <div class="col-12">
                                <label for="materialNotesState" class="form-label">ملاحظات المادة</label>
                                <select name="notes" id="materialNotesState" class="form-select">
                                    <option value="">الكل</option>
                                    <option value="with" {{ ($filters['notes'] ?? '') === 'with' ? 'selected' : '' }}>بها ملاحظات</option>
                                    <option value="without" {{ ($filters['notes'] ?? '') === 'without' ? 'selected' : '' }}>بدون ملاحظات</option>
                                </select>
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn w-100 btn-filter-submit">تطبيق الفلاتر</button>
                            </div>
                            <div class="col-12 mt-2">
                                <a href="{{ route('admin.manufacturing.materials.index') }}" class="btn btn-outline-secondary w-100 btn-filter-reset">إعادة التعيين</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0" id="manufacturingMaterialsTable" data-table-toolbar data-toolbar-target="#manufacturingMaterialsFilterActions">
                <thead class="table-light">
                    <tr>
                        @if($showSelectionColumn)
                        <th class="text-center" style="width: 48px;">تحديد</th>
                        @endif
                        <th>{!! Sort::link('name', 'الاسم', $sortOptions) !!}</th>
                        <th>{!! Sort::link('sku', 'الرمز (SKU)', $sortOptions) !!}</th>
                        <th>{!! Sort::link('unit', 'الوحدة', $sortOptions) !!}</th>
                        <th class="text-end">{!! Sort::link('cost_per_unit', 'تكلفة الوحدة', $sortOptions) !!}</th>
                        <th data-default-hidden="true">{!! Sort::link('updated_at', 'آخر تحديث', $sortOptions) !!}</th>
                        <th>ملاحظات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($materials as $material)
                        <tr>
                            @if($showSelectionColumn)
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input selection-checkbox" data-selection-group="manufacturing-materials" value="{{ $material->id }}">
                            </td>
                            @endif
                            <td class="fw-semibold">{{ $material->name }}</td>
                            <td>{{ $material->sku ?? '—' }}</td>
                            <td>{{ $material->unit ?? '—' }}</td>
                            <td class="text-end">{{ \App\Support\Currency::format($material->cost_per_unit) }}</td>
                            <td>{{ optional($material->updated_at)->format('Y-m-d') ?? '—' }}</td>
                            <td>{{ $material->notes ? Str::limit($material->notes, 60) : '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 6 + ($showSelectionColumn ? 1 : 0) }}" class="text-center py-4 text-muted">لا توجد مواد مسجلة حتى الآن.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer d-flex flex-wrap gap-3 justify-content-between align-items-center">
        <form method="GET" action="{{ route('admin.manufacturing.materials.index') }}" class="d-flex align-items-center gap-2">
            @foreach(request()->except(['per_page', 'page']) as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <label for="materialsPerPage" class="mb-0">عدد الصفوف:</label>
            <select name="per_page" id="materialsPerPage" class="form-select form-select-sm" onchange="this.form.submit()">
                @foreach([10, 15, 20, 30, 50, 100] as $option)
                    <option value="{{ $option }}" {{ $materials->perPage() === $option ? 'selected' : '' }}>{{ $option }}</option>
                @endforeach
            </select>
        </form>
        {{ $materials->links() }}
    </div>
</div>
@if($showSelectionColumn)
<form id="materialDeleteForm" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>
@endif
@endsection

@if($showSelectionColumn)
    @push('scripts')
        @include('admin.partials.selection-script', [
            'group' => 'manufacturing-materials',
            'emptyMessage' => 'يرجى اختيار مادة أولاً لتنفيذ الإجراء المطلوب.'
        ])
    @endpush
@endif
