@extends('admin.layout')

@section('title', 'إضافة مدير جديد')

@php
    $governorateOptions = $governorates ?? [];
    $selectedGovernorates = collect(old('governorates', []))->filter()->all();
@endphp

@section('content')
@can('create-managers')
@push('styles')
<style>
    .extra-small {
        font-size: 0.78rem;
    }

    .role-card {
        position: relative;
        display: block;
    }

    .role-card input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .role-card .card {
        border: 1px solid #e4e9f2;
        transition: all .25s ease;
        border-radius: 1.25rem;
        background: linear-gradient(135deg, rgba(248, 249, 250, 0.65), #ffffff);
        min-height: 180px;
        position: relative;
    }

    .role-card .card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
    }

    .role-card input:checked + .card {
        border-color: #FF5722;
        box-shadow: 0 16px 32px rgba(74, 74, 74, 0.25);
        background: linear-gradient(135deg, rgba(74, 74, 74, 0.15), rgba(255, 255, 255, 0.9));
    }

    .role-card input:focus-visible + .card {
        outline: 2px solid #FF5722;
        outline-offset: 3px;
    }

    .role-card__icon {
        width: 44px;
        height: 44px;
        background: rgba(74, 74, 74, 0.18);
        border-radius: 14px;
        display: grid;
        place-items: center;
        color: #FF5722;
        font-size: 1.15rem;
    }

    .role-card__badge {
        background: rgba(74, 74, 74, 0.12);
        color: #FF5722;
        border-radius: 999px;
        padding: 0.35rem 0.75rem;
        font-size: 0.75rem;
    }

    .role-card__selected {
        position: absolute;
        top: 12px;
        right: 12px;
        background: #FF5722;
        color: #fff;
        border-radius: 999px;
        padding: 0.35rem 0.75rem;
        font-size: 0.7rem;
        font-weight: 600;
        opacity: 0;
        transform: translateY(-6px);
        transition: all .2s ease;
        pointer-events: none;
    }

    .role-card input:checked + .card .role-card__selected {
        opacity: 1;
        transform: translateY(0);
    }

    .role-card__footer {
        background: rgba(255, 255, 255, 0.6);
        border-radius: 0.75rem;
        padding: 0.4rem 0.75rem;
        color: #6c757d;
        font-size: 0.75rem;
    }

    .role-card.border-danger .card {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }
</style>
@endpush

<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">إنشاء حساب مدير جديد</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.managers.store') }}" method="POST" class="js-required-check" data-required-message="يرجى تعبئة جميع الحقول المطلوبة قبل الحفظ.">
            @csrf

            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <p class="mb-0">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="alert alert-warning d-none" data-required-warning></div>

            <div class="accordion" id="userCreateAccordion">
                {{-- المعلومات الأساسية --}}
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingBasic">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBasic" aria-expanded="true" aria-controls="collapseBasic">
                            المعلومات الأساسية
                        </button>
                    </h2>
                    <div id="collapseBasic" class="accordion-collapse collapse show" aria-labelledby="headingBasic" data-bs-parent="#userCreateAccordion">
                        <div class="accordion-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">اسم اليوزر</label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone_number" class="form-label">رقم الهاتف</label>
                                    <input type="text" class="form-control" id="phone_number" name="phone_number" value="{{ old('phone_number') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">البريد الإلكتروني (اختياري)</label>
                                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">كلمة المرور</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="password_confirmation" class="form-label">تأكيد كلمة المرور</label>
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="manager_id" class="form-label">المشرف المباشر</label>
                                    <select name="manager_id" id="manager_id" class="form-select">
                                        <option value="">بدون مشرف (إدارة عليا)</option>
                                        @foreach(($managers ?? collect()) as $availableManager)
                                            <option value="{{ $availableManager->id }}" {{ old('manager_id') == $availableManager->id ? 'selected' : '' }}>
                                                {{ $availableManager->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">استخدم هذا الحقل لربط المدير بمشرفه المباشر حتى يستطيع المشرف رؤية طلباته.</small>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">المحافظات المخصصة (الزون)</label>
                                    <div class="border rounded-3 p-3" id="governorate_selector">
                                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                            <span class="fw-semibold">اختر المحافظات التي يمكن للمدير متابعتها</span>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-primary" data-action="select-all">تحديد الكل</button>
                                                <button type="button" class="btn btn-outline-secondary" data-action="clear-all">مسح الكل</button>
                                            </div>
                                        </div>
                                        <div class="row g-2">
                                            @foreach($governorateOptions as $governorate)
                                                @php $inputId = 'gov_' . \Illuminate\Support\Str::slug($governorate); @endphp
                                                <div class="col-md-4 col-sm-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input governorate-checkbox" type="checkbox" value="{{ $governorate }}" id="{{ $inputId }}" name="governorates[]" @checked(in_array($governorate, $selectedGovernorates, true))>
                                                        <label class="form-check-label" for="{{ $inputId }}">{{ $governorate }}</label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mt-2">يمكنك اختيار أكثر من محافظة، أو تركها فارغة لعرض العملاء المرتبطين بالمدير فقط.</small>
                                    @error('governorates')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                    @error('governorates.*')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- الأدوار --}}
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingRole">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRole" aria-expanded="false" aria-controls="collapseRole">
                            اختيار الدور الوظيفي
                        </button>
                    </h2>
                    <div id="collapseRole" class="accordion-collapse collapse" aria-labelledby="headingRole" data-bs-parent="#userCreateAccordion">
                        <div class="accordion-body">
                            <p class="text-muted small mb-3">اختر دوراً واحداً فقط ليتم منحه لهذا المدير.</p>
                            <div class="row g-3">
                                @forelse(($roles ?? collect()) as $role)
                                    <div class="col-md-4">
                                        <div class="border rounded-3 p-3 h-100">
                                            <div class="form-check">
                                                <input type="radio" class="form-check-input" name="role" id="role_{{ $role->id }}" value="{{ $role->name }}" @checked(old('role') === $role->name) required>
                                                <label class="form-check-label fw-semibold" for="role_{{ $role->id }}">{{ $role->name }}</label>
                                            </div>
                                            <p class="text-muted small mb-0 mt-2">يتضمن {{ $role->permissions_count }} صلاحية.</p>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <div class="alert alert-warning mb-0">لم يتم إنشاء أدوار بعد.</div>
                                    </div>
                                @endforelse
                            </div>
                            @error('role')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <hr class="mt-4">
            <button type="submit" class="btn btn-primary">إنشاء المدير</button>
            <a href="{{ route('admin.managers.index') }}" class="btn btn-secondary">إلغاء</a>
        </form>
    </div>
</div>
@else
<div class="alert alert-danger">ليس لديك صلاحية لإنشاء المدراء.</div>
@endcan
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('governorate_selector');
    if (!container) {
        return;
    }

    const checkboxes = container.querySelectorAll('.governorate-checkbox');

    container.querySelectorAll('[data-action="select-all"]').forEach((button) => {
        button.addEventListener('click', () => {
            checkboxes.forEach((checkbox) => {
                checkbox.checked = true;
            });
        });
    });

    container.querySelectorAll('[data-action="clear-all"]').forEach((button) => {
        button.addEventListener('click', () => {
            checkboxes.forEach((checkbox) => {
                checkbox.checked = false;
            });
        });
    });
});
</script>
@endpush
