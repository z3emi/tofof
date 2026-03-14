@extends('admin.layout')

@section('title', 'وصفات التصنيع (BOM)')

@php
    use Illuminate\Support\Str;
    use App\Support\Sort;
@endphp

@section('content')
@php
    $canManageInventory = auth()->user()?->can('manage-inventory');
    $showSelectionColumn = $canManageInventory;
    $sortOptions = [
        'route' => 'admin.manufacturing.boms.index',
        'allowed' => $allowedSorts ?? [],
        'default_column' => $defaultSortColumn ?? 'updated_at',
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

<div class="card shadow-sm" id="manufacturingBomsListingCard">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h4 class="mb-1">وصفات التصنيع</h4>
            <p class="text-muted mb-0">تعريف مكونات كل منتج ومتغيراته.</p>
        </div>
        @if($canManageInventory)
        <div class="dropdown">
            <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="bomActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-gear-fill me-1"></i> إجراءات
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="bomActionsDropdown">
                <li class="dropdown-header">إجراءات على الوصفة</li>
                <li>
                    <button type="button"
                            class="dropdown-item selection-action"
                            data-selection-group="manufacturing-boms"
                            data-action-type="navigate"
                            data-url-template="{{ route('admin.manufacturing.boms.edit', ['bom' => '__ID__']) }}">
                        <i class="bi bi-pencil me-2"></i> تعديل الوصفة
                    </button>
                </li>
                <li>
                    <button type="button"
                            class="dropdown-item text-danger selection-action"
                            data-selection-group="manufacturing-boms"
                            data-action-type="submit-form"
                            data-form-target="bomDeleteForm"
                            data-url-template="{{ route('admin.manufacturing.boms.destroy', ['bom' => '__ID__']) }}"
                            data-confirm="حذف هذه الوصفة؟"
                            data-confirm-multiple="هل تريد حذف الوصفات المحددة (عدد: __COUNT__)؟"
                            data-allow-multiple="true">
                        <i class="bi bi-trash me-2"></i> حذف الوصفة
                    </button>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a href="{{ route('admin.manufacturing.boms.create') }}" class="dropdown-item">
                        <i class="bi bi-plus-circle me-2"></i> وصفة جديدة
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
        <form method="GET" action="{{ route('admin.manufacturing.boms.index') }}" class="filter-bar" id="manufacturingBomsFilterBar">
            <input type="hidden" name="product_id" value="{{ $filters['product_id'] ?? null }}">
            <input type="hidden" name="has_notes" value="{{ $filters['has_notes'] ?? null }}">
            <input type="hidden" name="updated_from" value="{{ $filters['updated_from'] ?? null }}">
            <input type="hidden" name="updated_to" value="{{ $filters['updated_to'] ?? null }}">
            <div class="filter-bar__group filter-bar__group--search flex-grow-1">
                <div class="input-group filter-bar__input-group">
                    <span class="input-group-text filter-bar__icon"><i class="bi bi-search"></i></span>
                    <input type="search" name="search" class="form-control filter-bar__input" placeholder="ابحث باسم المنتج أو المتغير" value="{{ $filters['search'] ?? '' }}">
                </div>
            </div>
            <div class="filter-bar__group filter-bar__group--submit">
                <button type="submit" class="btn btn-primary filter-bar__submit">بحث</button>
            </div>
            <div class="filter-bar__group filter-bar__group--actions" id="manufacturingBomsFilterActions">
                <button type="button"
                        class="table-action-button filter-trigger-button"
                        data-bs-toggle="modal"
                        data-bs-target="#manufacturingBomsFiltersModal"
                        title="خيارات التصفية"
                        aria-label="خيارات التصفية">
                    <i class="bi bi-funnel"></i>
                </button>
                @can('export-excel')
                    <button type="button"
                            class="table-action-button table-action-button--download"
                            data-export-target="#manufacturingBomsTable"
                            data-export-name="وصفات التصنيع"
                            title="تصدير إلى Excel"
                            aria-label="تصدير إلى Excel">
                        <i class="bi bi-file-earmark-spreadsheet"></i>
                    </button>
                @endcan
                <button type="button"
                        class="table-action-button table-action-button--refresh"
                        data-refresh-target="#manufacturingBomsListingCard"
                        data-refresh-url="{{ request()->fullUrl() }}"
                        title="تحديث القائمة"
                        aria-label="تحديث القائمة">
                    <i class="bi bi-arrow-repeat"></i>
                </button>
            </div>
            @if(($filters['search'] ?? null) || ($filters['product_id'] ?? null) || ($filters['has_notes'] ?? null) || ($filters['updated_from'] ?? null) || ($filters['updated_to'] ?? null))
            <div class="filter-bar__group">
                <a href="{{ route('admin.manufacturing.boms.index') }}" class="btn btn-outline-secondary btn-sm">إعادة التعيين</a>
            </div>
            @endif
        </form>

        <div class="modal fade filter-modal" id="manufacturingBomsFiltersModal" tabindex="-1" aria-labelledby="manufacturingBomsFiltersModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="manufacturingBomsFiltersModalLabel">تصفية وصفات التصنيع</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <div class="modal-body">
                        <form method="GET" action="{{ route('admin.manufacturing.boms.index') }}" class="row g-3">
                            <input type="hidden" name="search" value="{{ $filters['search'] ?? '' }}">
                            <div class="col-12">
                                <label for="bomFilterProduct" class="form-label">المنتج</label>
                                <select name="product_id" id="bomFilterProduct" class="form-select">
                                    <option value="">كل المنتجات</option>
                                    @foreach($productOptions as $productId => $productName)
                                        <option value="{{ $productId }}" {{ (string) ($filters['product_id'] ?? '') === (string) $productId ? 'selected' : '' }}>{{ $productName }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="bomUpdatedFrom" class="form-label">آخر تحديث من</label>
                                <input type="date" name="updated_from" id="bomUpdatedFrom" class="form-control" value="{{ $filters['updated_from'] ?? '' }}">
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="bomUpdatedTo" class="form-label">آخر تحديث إلى</label>
                                <input type="date" name="updated_to" id="bomUpdatedTo" class="form-control" value="{{ $filters['updated_to'] ?? '' }}">
                            </div>
                            <div class="col-12">
                                <label for="bomNotesState" class="form-label">ملاحظات الوصفة</label>
                                <select name="has_notes" id="bomNotesState" class="form-select">
                                    <option value="">الكل</option>
                                    <option value="with" {{ ($filters['has_notes'] ?? '') === 'with' ? 'selected' : '' }}>بها ملاحظات</option>
                                    <option value="without" {{ ($filters['has_notes'] ?? '') === 'without' ? 'selected' : '' }}>بدون ملاحظات</option>
                                </select>
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn w-100 btn-filter-submit">تطبيق الفلاتر</button>
                            </div>
                            <div class="col-12 mt-2">
                                <a href="{{ route('admin.manufacturing.boms.index') }}" class="btn btn-outline-secondary w-100 btn-filter-reset">إعادة التعيين</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0" id="manufacturingBomsTable" data-table-toolbar data-toolbar-target="#manufacturingBomsFilterActions">
                <thead class="table-light">
                    <tr>
                        @if($showSelectionColumn)
                        <th class="text-center" style="width: 48px;">تحديد</th>
                        @endif
                        <th>{!! Sort::link('product_id', 'المنتج', $sortOptions) !!}</th>
                        <th>المتغير</th>
                        <th>{!! Sort::link('items_count', 'عدد المكونات', $sortOptions) !!}</th>
                        <th>{!! Sort::link('updated_at', 'آخر تحديث', $sortOptions) !!}</th>
                        <th data-default-hidden="true">الملاحظات</th>
                        <th class="text-center">التفاصيل</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($boms as $bom)
                        <tr data-dblclick-url="{{ route('admin.manufacturing.boms.show', $bom) }}"
                            data-dblclick-label="عرض تفاصيل وصفة {{ $bom->product?->name_ar ?? 'غير مسمى' }}">
                            @if($showSelectionColumn)
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input selection-checkbox" data-selection-group="manufacturing-boms" value="{{ $bom->id }}">
                            </td>
                            @endif
                            <td>{{ $bom->product?->name_ar ?? 'منتج محذوف' }}</td>
                            <td>{{ $bom->variant_name ?? '—' }}</td>
                            <td>{{ number_format($bom->items_count ?? 0) }}</td>
                            <td>{{ optional($bom->updated_at)->format('Y-m-d') ?? '—' }}</td>
                            <td>{{ $bom->notes ? Str::limit($bom->notes, 60) : '—' }}</td>
                            <td class="text-center">
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ 6 + ($showSelectionColumn ? 1 : 0) }}" class="text-center py-4 text-muted">لا توجد وصفات حالياً.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer d-flex flex-wrap gap-3 justify-content-between align-items-center">
        <form method="GET" action="{{ route('admin.manufacturing.boms.index') }}" class="d-flex align-items-center gap-2">
            @foreach(request()->except(['per_page', 'page']) as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <label for="bomsPerPage" class="mb-0">عدد الصفوف:</label>
            <select name="per_page" id="bomsPerPage" class="form-select form-select-sm" onchange="this.form.submit()">
                @foreach([10, 15, 20, 30, 50, 100] as $option)
                    <option value="{{ $option }}" {{ $boms->perPage() === $option ? 'selected' : '' }}>{{ $option }}</option>
                @endforeach
            </select>
        </form>
        {{ $boms->links() }}
    </div>
</div>
@if($showSelectionColumn)
<form id="bomDeleteForm" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>
@endif
@endsection

@if($showSelectionColumn)
    @push('scripts')
        @include('admin.partials.selection-script', [
            'group' => 'manufacturing-boms',
            'emptyMessage' => 'يرجى اختيار وصفة تصنيع أولاً لتنفيذ الإجراء المطلوب.'
        ])
    @endpush
@endif
