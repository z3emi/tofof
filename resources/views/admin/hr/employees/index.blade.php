@extends('admin.layout')

@section('title', 'إدارة ملفات الموظفين')

@php
    $canManageEmployeeProfiles = auth()->user()?->can('manage_employee_profiles');
    $showSelectionColumn = $canManageEmployeeProfiles;
    $sortOptions = [
        'route' => 'admin.hr.employees.index',
        'allowed' => $allowedSorts ?? [],
        'default_column' => $defaultSortColumn ?? 'created_at',
        'default_direction' => $defaultSortDirection ?? 'desc',
    ];
    $sortClass = \App\Support\Sort::class;
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

    .employee-header__subtitle {
        color: #8c6f7a;
        font-size: 0.85rem;
    }

    .employee-filter-bar {
        margin-bottom: 1.5rem;
    }

    .employee-filter-bar .form-select,
    .employee-filter-bar .form-control {
        min-width: 190px;
    }

    @media (max-width: 768px) {
        .employee-filter-bar .form-select,
        .employee-filter-bar .form-control {
            min-width: 100%;
        }
    }

    .employee-metric-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        border-radius: 999px;
        padding: 0.25rem 0.7rem;
        background: rgba(74, 74, 74, 0.12);
        color: #a3477c;
        font-weight: 600;
        font-size: 0.8rem;
    }

    .employee-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        overflow: hidden;
        background: rgba(74, 74, 74, 0.1);
        display: grid;
        place-items: center;
        font-weight: 600;
        color: #a3477c;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .employee-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .employee-nationality-thumb {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 56px;
        height: 40px;
        border-radius: 0.6rem;
        overflow: hidden;
        border: 1px solid rgba(74, 74, 74, 0.35);
        background-color: #fff;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .employee-nationality-thumb:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(74, 74, 74, 0.22);
    }

    .employee-nationality-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .employee-empty-state {
        padding: 2.5rem 1rem;
        color: #6c757d;
    }
</style>
@endpush

@section('content')
<div class="card shadow-sm">
    <div class="card-header flex-wrap gap-3 align-items-center">
        <div>
            <h4 class="mb-1">ملفات الموظفين</h4>
            <span class="employee-header__subtitle">نفس تجربة لوحة المدراء لكن مخصصة لمتابعة بيانات فريق العمل والرواتب.</span>
        </div>
        <div class="d-flex align-items-center gap-2 ms-auto">
            @if($canManageEmployeeProfiles)
                <a href="{{ route('admin.hr.employees.create') }}" class="btn btn-sm btn-success">
                    <i class="bi bi-person-plus me-1"></i> إضافة موظف
                </a>
            @endif

            @if($showSelectionColumn)
            <div class="dropdown">
                <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="employeesActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-gear-fill me-1"></i> إجراءات سريعة
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="employeesActionsDropdown">
                    <li class="dropdown-header">الموظف المحدد</li>
                    <li>
                        <button type="button"
                                class="dropdown-item selection-action"
                                data-selection-group="hr-employees"
                                data-action-type="navigate"
                                data-url-template="{{ route('admin.hr.employees.edit', ['employee' => '__ID__']) }}">
                            <i class="bi bi-pencil me-2"></i> تعديل الملف
                        </button>
                    </li>
                </ul>
            </div>
            @endif
        </div>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.hr.employees.index') }}" class="filter-bar employee-filter-bar">
            <div class="filter-bar__group filter-bar__group--search flex-grow-1">
                <div class="input-group filter-bar__input-group">
                    <span class="input-group-text filter-bar__icon"><i class="bi bi-search"></i></span>
                    <input type="search" name="search" class="form-control filter-bar__input" placeholder="ابحث بالاسم أو الهاتف" value="{{ $search }}">
                </div>
            </div>

            <div class="filter-bar__group">
                <label for="manager_id" class="form-label mb-0">المشرف المباشر</label>
                <select name="manager_id" id="manager_id" class="form-select filter-bar__control">
                    <option value="">كل المشرفين</option>
                    <option value="unassigned" {{ ($managerFilter === 'unassigned') ? 'selected' : '' }}>بدون مشرف</option>
                    @foreach($managerOptions as $managerId => $managerName)
                        <option value="{{ $managerId }}" {{ (string) $managerFilter === (string) $managerId ? 'selected' : '' }}>{{ $managerName }}</option>
                    @endforeach
                </select>
            </div>

            <input type="hidden" name="sort_by" value="{{ request('sort_by', $sortBy ?? $defaultSortColumn ?? 'created_at') }}">
            <input type="hidden" name="sort_dir" value="{{ request('sort_dir', $sortDir ?? $defaultSortDirection ?? 'desc') }}">

            <div class="filter-bar__group filter-bar__group--submit">
                <button class="btn btn-primary filter-bar__submit" type="submit">تطبيق الفلاتر</button>
                <a href="{{ route('admin.hr.employees.index') }}" class="btn btn-outline-secondary">إعادة تعيين</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle table-enhanced mb-0" data-toolbar-target="#employeesTableToolbar">
                <thead>
                    <tr>
                        @if($showSelectionColumn)
                            <th class="text-center" style="width: 54px;">تحديد</th>
                        @endif
                        <th>{!! $sortClass::link('name', 'الموظف', $sortOptions) !!}</th>
                        <th class="text-center">الجنسية</th>
                        <th>{!! $sortClass::link('manager_id', 'المشرف', $sortOptions) !!}</th>
                        <th class="text-end">{!! $sortClass::link('base_salary', 'الراتب الأساسي', $sortOptions) !!}</th>
                        <th class="text-end">{!! $sortClass::link('allowances', 'البدلات', $sortOptions) !!}</th>
                        <th class="text-end">{!! $sortClass::link('commission_rate', 'نسبة العمولة', $sortOptions) !!}</th>
                        @if($canManageEmployeeProfiles)
                            <th class="text-end">خيارات</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                        @php
                            $employeeCurrency = $employee->salary_currency ?? \App\Support\Currency::IQD;
                            $displayBase = \App\Support\Currency::convertFromSystem($employee->base_salary ?? 0, $employeeCurrency);
                            $displayAllowances = \App\Support\Currency::convertFromSystem($employee->allowances ?? 0, $employeeCurrency);
                        @endphp
                        <tr data-dblclick-url="{{ route('admin.hr.employees.edit', $employee) }}" data-selection-id="{{ $employee->id }}">
                            @if($showSelectionColumn)
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input selection-checkbox" data-selection-group="hr-employees" value="{{ $employee->id }}">
                            </td>
                            @endif
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="employee-avatar">
                                        @if($employee->profile_photo_url)
                                            <img src="{{ $employee->profile_photo_url }}" alt="الصورة الشخصية لـ {{ $employee->name }}">
                                        @else
                                            <span>{{ \Illuminate\Support\Str::substr($employee->name, 0, 1) }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $employee->name }}</div>
                                        <div class="text-muted small">{{ $employee->phone_number ?? '—' }}</div>
                                        @if($employee->hasIncompleteProfile())
                                            <span class="employee-metric-badge mt-2" title="{{ implode('، ', $employee->missingProfileFields()) }}">
                                                <i class="bi bi-exclamation-triangle"></i>
                                                نقص بيانات
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                @if($employee->nationality_card_url)
                                    <a href="{{ $employee->nationality_card_url }}" class="employee-nationality-thumb" target="_blank" title="عرض صورة الجنسية">
                                        <img src="{{ $employee->nationality_card_url }}" alt="صورة الجنسية لـ {{ $employee->name }}">
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $employee->manager?->name ?? 'غير محدد' }}</td>
                            <td class="text-end">{{ \App\Support\Currency::formatForCurrency($displayBase, $employeeCurrency) }}</td>
                            <td class="text-end">{{ \App\Support\Currency::formatForCurrency($displayAllowances, $employeeCurrency) }}</td>
                            <td class="text-end">{{ number_format(($employee->commission_rate ?? 0) * 100, 2) }}%</td>
                            @if($canManageEmployeeProfiles)
                                <td class="text-end">
                                    <a href="{{ route('admin.hr.employees.edit', $employee) }}" class="btn btn-sm btn-outline-primary" title="تعديل" data-ignore-row-select>
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            @php
                                $baseColumns = 6; // الموظف، الجنسية، المشرف، الراتب الأساسي، البدلات، العمولة
                                $columnSpan = $baseColumns + ($canManageEmployeeProfiles ? 1 : 0) + ($showSelectionColumn ? 1 : 0);
                            @endphp
                            <td colspan="{{ $columnSpan }}" class="text-center employee-empty-state">
                                لا يوجد موظفون مسجلون حالياً.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer d-flex flex-wrap gap-3 justify-content-between align-items-center">
        <form method="GET" action="{{ route('admin.hr.employees.index') }}" class="d-flex align-items-center gap-2">
            @foreach(request()->except(['per_page', 'page']) as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <label for="per_page" class="mb-0">عدد الصفوف:</label>
            <select name="per_page" id="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                @foreach([5, 10, 25, 50, 100] as $size)
                    <option value="{{ $size }}" {{ (int) request('per_page', $perPage ?? 15) === $size ? 'selected' : '' }}>{{ $size }}</option>
                @endforeach
            </select>
        </form>

        <div>
            {{ $employees->withQueryString()->links() }}
        </div>
    </div>
</div>

<div id="employeesTableToolbar" class="table-enhancements"></div>
@endsection

@if($showSelectionColumn)
    @push('scripts')
        @include('admin.partials.selection-script', [
            'group' => 'hr-employees',
            'emptyMessage' => 'يرجى اختيار موظف أولاً لتنفيذ الإجراء المطلوب.'
        ])
    @endpush
@endif
