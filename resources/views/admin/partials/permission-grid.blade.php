@php
    use Illuminate\Support\Str;

    // --- الجزء الخاص بتحضير البيانات ---
    $permissionsCollection = collect($permissions ?? []);
    $valueType = $valueType ?? 'name';
    $checkboxName = $checkboxName ?? 'permissions[]';

    $selectedPermissions = collect($selected ?? $selectedPermissions ?? [])
        ->map(function ($value) use ($valueType) {
            if ($value instanceof \Spatie\Permission\Models\Permission) {
                return $valueType === 'id' ? $value->id : $value->name;
            }

            return $value;
        })
        ->filter()
        ->values()
        ->all();

    $allowedGroups = collect($allowedGroups ?? $onlyGroups ?? [])
        ->filter()
        ->map(fn ($value) => is_string($value) ? $value : (string) $value)
        ->values()
        ->all();

    $groupsConfig = config('permissions.groups', []);

    if (!empty($allowedGroups)) {
        $groupsConfig = collect($groupsConfig)
            ->only($allowedGroups)
            ->toArray();
    }

    $formatEnglish = function ($value) {
        return (string) Str::of($value ?? '')
            ->replace(['::', '.', '_', '-'], ' ')
            ->replaceMatches('/\s+/', ' ')
            ->title();
    };

    $renderGroups = [];
    $groupIndex = 0;

    foreach ($groupsConfig as $groupKey => $groupData) {
        $groupPermissions = collect($groupData['permissions'] ?? [])
            ->map(function ($label, $permissionName) use ($permissionsCollection, $valueType, $formatEnglish) {
                $permission = $permissionsCollection->firstWhere('name', $permissionName);

                if (!$permission) {
                    return null;
                }

                return [
                    'model' => $permission,
                    'name' => $permission->name,
                    'label' => $label,
                    'english_label' => $formatEnglish($permission->name),
                    'value' => $valueType === 'id' ? $permission->id : $permission->name,
                ];
            })
            ->filter()
            ->values();

        if ($groupPermissions->isEmpty()) {
            continue;
        }

        $renderGroups[] = [
            'index' => $groupIndex++,
            'key' => $groupKey,
            'label' => $groupData['label'] ?? ucfirst($groupKey),
            'label_en' => $groupData['label_en'] ?? $formatEnglish($groupKey),
            'permissions' => $groupPermissions,
        ];
    }

    $renderGroups = collect($renderGroups);

    $renderedNames = $renderGroups
        ->flatMap(fn ($group) => $group['permissions']->pluck('name'))
        ->all();

    $orphanPermissions = $permissionsCollection
        ->reject(fn ($permission) => in_array($permission->name, $renderedNames, true))
        ->values();

    if ($orphanPermissions->isNotEmpty()) {
        $renderGroups = $renderGroups->push([
            'index' => $groupIndex++,
            'key' => 'misc',
            'label' => 'صلاحيات أخرى',
            'label_en' => 'Miscellaneous',
            'permissions' => $orphanPermissions->map(fn ($permission) => [
                'model' => $permission,
                'name' => $permission->name,
                'label' => $permission->name,
                'english_label' => $formatEnglish($permission->name),
                'value' => $valueType === 'id' ? $permission->id : $permission->name,
            ]),
        ]);
    }

    $renderGroups = $renderGroups->values();

    $typography = [];
    if (isset($permissionTypography) && is_array($permissionTypography)) {
        $typography = $permissionTypography;
    }

    $fontFamilySetting = trim($typography['font_family'] ?? 'Tajawal, sans-serif');
    if ($fontFamilySetting === '') {
        $fontFamilySetting = 'Tajawal, sans-serif';
    }

    $fontSizeSetting = (float) ($typography['font_size'] ?? 0.85);
    if ($fontSizeSetting <= 0) {
        $fontSizeSetting = 0.85;
    }
    if ($fontSizeSetting < 0.5) {
        $fontSizeSetting = 0.5;
    } elseif ($fontSizeSetting > 2) {
        $fontSizeSetting = 2;
    }

    $permissionWrapperId = 'permission-grid-' . uniqid();
@endphp

@push('styles')
    <style>
        #{{ $permissionWrapperId }} {
            --permission-font-family: {!! json_encode($fontFamilySetting) !!};
            --permission-font-size: {{ $fontSizeSetting }}rem;
            font-family: var(--permission-font-family);
            font-size: var(--permission-font-size);
        }

        .permission-group {
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            background-color: #fff;
            overflow: hidden;
            margin-bottom: 1rem;
        }

        .permission-group__header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 0.75rem 1rem;
        }

        .permission-group__header h6 {
            font-size: calc(var(--permission-font-size) + 0.1rem);
            font-weight: 600;
            margin-bottom: 0.1rem;
        }

        .permission-group__header small {
            font-size: 0.8em;
            color: #6c757d;
        }

        .permission-group__body {
            padding: 0.5rem 1rem 1rem 1rem;
        }

        .permission-item .form-check {
            padding-top: 0.6rem;
            padding-bottom: 0.6rem;
            border-bottom: 1px dashed #e9ecef;
            margin-bottom: 0;
            display: flex;
            align-items: flex-start;
        }

        .permission-group__body .row > .permission-item:last-child .form-check {
            border-bottom: none;
        }

        .permission-item .form-check-label {
            line-height: 1.4;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            padding-right: 0.5rem;
            width: 100%;
            cursor: pointer;
        }

        .permission-item .form-check-input {
            margin-top: 0.2em;
            width: 1.1em;
            height: 1.1em;
            flex-shrink: 0;
            cursor: pointer;
        }

        .permission-label-ar {
            font-weight: 500;
            color: #212529;
            margin-bottom: 0.1rem;
            font-size: 0.95em;
        }

        .permission-label-en {
            font-size: 0.8em;
            color: #6c757d;
        }

        .permission-group__header .btn-group-sm > .btn {
            font-size: 0.75rem;
            padding: 0.2rem 0.5rem;
        }
    </style>
@endpush

<div class="permission-grid-wrapper" id="{{ $permissionWrapperId }}">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-lg-between gap-3 mb-4">
        <div>
            <h5 class="mb-1 fw-semibold">قائمة الصلاحيات</h5>
            <p class="text-muted small mb-0">حدد الصلاحيات المناسبة لكل مجموعة، ويمكنك تحديد أو إلغاء جميع الصلاحيات لكل مجموعة بسهولة.</p>
        </div>
        <span class="badge bg-light text-dark border fw-semibold px-3 py-2">
            {{ $renderGroups->sum(fn ($group) => $group['permissions']->count()) }} صلاحية متاحة
        </span>
    </div>

    @if($renderGroups->isEmpty())
        <div class="alert alert-info mb-0">لا توجد صلاحيات معرفة حالياً.</div>
    @else
        <div class="d-flex flex-column gap-3">
            @foreach($renderGroups as $group)
                @php
                    $groupCheckboxesContainerId = 'permission-group-body-' . $group['index'];
                @endphp
                <section class="permission-group" id="permission-group-{{ $group['index'] }}">
                    <header class="permission-group__header d-flex flex-column flex-lg-row align-items-lg-center justify-content-lg-between gap-2">
                        <div>
                            <h6 class="mb-1">{{ $group['label'] }}</h6>
                            <small class="text-muted d-block">{{ $group['label_en'] }}</small>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <div class="btn-group btn-group-sm" role="group" aria-label="تحديد الصلاحيات">
                                <button type="button" class="btn btn-outline-secondary" data-permission-select-group="{{ $groupCheckboxesContainerId }}" data-mode="check">تحديد الكل</button>
                                <button type="button" class="btn btn-outline-secondary" data-permission-select-group="{{ $groupCheckboxesContainerId }}" data-mode="uncheck">إلغاء الكل</button>
                            </div>
                        </div>
                    </header>

                    <div class="permission-group__body" id="{{ $groupCheckboxesContainerId }}">
                        <div class="row g-0">
                            @foreach($group['permissions'] as $permission)
                                @php
                                    $isChecked = in_array($permission['value'], $selectedPermissions, true);
                                    $inputId = 'permission-' . Str::slug($permission['name']);
                                @endphp
                                <div class="col-12 col-sm-6 col-md-4 col-lg-3 permission-item">
                                    <div class="form-check">
                                        <input class="form-check-input permission-checkbox"
                                               type="checkbox"
                                               name="{{ $checkboxName }}"
                                               id="{{ $inputId }}"
                                               value="{{ $permission['value'] }}"
                                               @checked($isChecked)>
                                        <label class="form-check-label" for="{{ $inputId }}">
                                            <span class="permission-label-ar">{{ $permission['label'] }}</span>
                                            <span class="permission-label-en">{{ $permission['english_label'] }}</span>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endforeach
        </div>
    @endif
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('[data-permission-select-group]').forEach(function (button) {
                    button.addEventListener('click', function (event) {
                        event.preventDefault();
                        const containerId = this.getAttribute('data-permission-select-group');
                        const mode = this.getAttribute('data-mode');
                        const container = document.getElementById(containerId);

                        if (!container) {
                            console.warn('Permission group container not found:', containerId);
                            return;
                        }

                        const shouldCheck = mode === 'check';
                        container.querySelectorAll('.permission-checkbox').forEach(function (checkbox) {
                            checkbox.checked = shouldCheck;
                            checkbox.dispatchEvent(new Event('change', { bubbles: true }));
                        });
                    });
                });

                document.querySelectorAll('.permission-group').forEach(groupSection => {
                    const headerCheckbox = groupSection.querySelector('.permission-group__header .select-all-permissions');
                    const bodyCheckboxes = groupSection.querySelectorAll('.permission-group__body .permission-checkbox');

                    const updateHeaderCheckboxState = () => {
                        if (!headerCheckbox || !bodyCheckboxes || bodyCheckboxes.length === 0) return;
                        const allChecked = Array.from(bodyCheckboxes).every(cb => cb.checked);
                        headerCheckbox.checked = allChecked;
                        headerCheckbox.disabled = false;
                    };

                    if (headerCheckbox) {
                        headerCheckbox.addEventListener('change', function() {
                            bodyCheckboxes.forEach(cb => {
                                cb.checked = this.checked;
                                cb.dispatchEvent(new Event('change', { bubbles: true }));
                            });
                        });
                    }

                    bodyCheckboxes.forEach(cb => {
                        cb.addEventListener('change', updateHeaderCheckboxState);
                    });

                    updateHeaderCheckboxState();
                });
            });
        </script>
    @endpush
@endonce
