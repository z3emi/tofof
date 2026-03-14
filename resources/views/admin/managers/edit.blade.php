@extends('admin.layout')

@section('title', 'تعديل المدير: ' . $manager->name)

@push('styles')
<style>
  .extra-small{font-size:.78rem}
  .avatar-wrap{position:relative;width:96px;height:96px}
  .avatar-wrap img{width:100%;height:100%;object-fit:cover;border-radius:18px;border:2px solid #eee}
  .avatar-edit{position:absolute;left:50%;transform:translateX(-50%);bottom:-8px;width:36px;height:36px;display:grid;place-items:center;border:none;border-radius:12px;background:#FF5722;color:#fff}
  .avatar-edit:hover{background:#FF5722}
  .role-card{position:relative;display:block}
  .role-card input{position:absolute;opacity:0;pointer-events:none}
  .role-card .card{border:1px solid #e4e9f2;transition:all .25s ease;border-radius:1.25rem;background:linear-gradient(135deg,rgba(248,249,250,.65),#fff);min-height:180px;position:relative}
  .role-card .card:hover{transform:translateY(-4px);box-shadow:0 12px 28px rgba(15,23,42,.08)}
  .role-card input:checked + .card{border-color:#FF5722;box-shadow:0 16px 32px rgba(74, 74, 74,.25);background:linear-gradient(135deg,rgba(74, 74, 74,.15),rgba(255,255,255,.9))}
  .role-card input:focus-visible + .card{outline:2px solid #FF5722;outline-offset:3px}
  .role-card__icon{width:44px;height:44px;background:rgba(74, 74, 74,.18);border-radius:14px;display:grid;place-items:center;color:#FF5722;font-size:1.15rem}
  .role-card__badge{background:rgba(74, 74, 74,.12);color:#FF5722;border-radius:999px;padding:.35rem .75rem;font-size:.75rem}
  .role-card__selected{position:absolute;top:12px;right:12px;background:#FF5722;color:#fff;border-radius:999px;padding:.35rem .75rem;font-size:.7rem;font-weight:600;opacity:0;transform:translateY(-6px);transition:all .2s ease;pointer-events:none}
  .role-card input:checked + .card .role-card__selected{opacity:1;transform:translateY(0)}
  .role-card__footer{background:rgba(255,255,255,.6);border-radius:.75rem;padding:.4rem .75rem;color:#6c757d;font-size:.75rem}
  .role-card.border-danger .card{border-color:#dc3545!important;box-shadow:0 0 0 .2rem rgba(220,53,69,.25)}
</style>
@endpush

@php
    $governorateOptions = $governorates ?? [];
    $selectedGovernorates = collect(old('governorates', $selectedGovernorates ?? $manager->assignedGovernorates() ?? []))->filter()->all();
    $avatarSrc = $manager->avatar_url ?? asset('storage/avatars/default.jpg');
    $fieldPermissions = array_merge([
        'name' => false,
        'email' => false,
        'phone' => false,
        'password' => false,
        'status' => false,
        'contact' => false,
    ], $fieldPermissions ?? []);

    if (old('reset_avatar')) {
        $avatarSrc = asset('storage/avatars/default.jpg');
    }
@endphp

@section('content')
@can('edit-managers')
<form action="{{ route('admin.managers.update', $manager->id) }}" method="POST" enctype="multipart/form-data" class="js-required-check" data-required-message="يرجى تعبئة الحقول الإلزامية قبل حفظ التغييرات.">
    @csrf
    @method('PUT')

    <div class="alert alert-warning d-none" data-required-warning></div>

    <div class="accordion" id="userEditAccordion">

        {{-- المعلومات الأساسية --}}
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingOne">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                    تعديل معلومات المدير
                </button>
            </h2>
            <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#userEditAccordion">
                <div class="accordion-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- صورة المدير --}}
                    <div class="mb-3">
                        <label class="form-label d-block">صورة المدير (Avatar)</label>
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <div class="avatar-wrap">
                                <img id="avatarPreview" src="{{ $avatarSrc }}" alt="avatar"
                                     onerror="this.onerror=null;this.src='{{ asset('storage/avatars/default.jpg') }}';">
                                <label for="avatar" class="avatar-edit" title="تغيير الصورة">
                                    <i class="bi bi-camera-fill"></i>
                                </label>
                            </div>
                            <div class="flex-grow-1" style="min-width:260px">
                                <input type="file" id="avatar" name="avatar" class="form-control" accept="image/*" onchange="previewAvatar(event)">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" value="1" id="reset_avatar" name="reset_avatar">
                                    <label class="form-check-label" for="reset_avatar">
                                        استخدام الصورة الافتراضية
                                    </label>
                                </div>
                                <small class="text-muted d-block mt-1">الأنواع المسموحة: jpg, jpeg, png, webp — الحد الأقصى: 2MB</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">اسم اليوزر</label>
                            <input type="text" class="form-control {{ $fieldPermissions['name'] ? '' : 'bg-light' }}" id="name" name="name" value="{{ old('name', $manager->name) }}" required @unless($fieldPermissions['name']) readonly @endunless>
                            @unless($fieldPermissions['name'])
                                <small class="text-muted d-block mt-1">لا تملك صلاحية تعديل الاسم.</small>
                            @endunless
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">البريد الإلكتروني</label>
                            @if($fieldPermissions['contact'])
                                <input type="email" class="form-control {{ $fieldPermissions['email'] ? '' : 'bg-light' }}" id="email" name="email" value="{{ old('email', $manager->email) }}" @unless($fieldPermissions['email']) readonly @endunless>
                                @unless($fieldPermissions['email'])
                                    <small class="text-muted d-block mt-1">لا تملك صلاحية تعديل البريد الإلكتروني.</small>
                                @endunless
                            @else
                                <div class="form-control-plaintext text-muted">بيانات التواصل مخفية - تحتاج صلاحية عرض بيانات المدراء.</div>
                            @endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone_number" class="form-label">رقم الهاتف</label>
                            @if($fieldPermissions['contact'])
                                <input type="text" class="form-control {{ $fieldPermissions['phone'] ? '' : 'bg-light' }}" id="phone_number" name="phone_number" value="{{ old('phone_number', $manager->phone_number) }}" required @unless($fieldPermissions['phone']) readonly @endunless>
                                @unless($fieldPermissions['phone'])
                                    <small class="text-muted d-block mt-1">لا تملك صلاحية تعديل رقم الهاتف.</small>
                                @endunless
                            @else
                                <div class="form-control-plaintext text-muted">رقم الهاتف مخفي - تحتاج صلاحية عرض بيانات المدراء.</div>
                            @endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="is_active">حالة الحساب</label>
                            @if($fieldPermissions['status'])
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $manager->phone_verified_at ? 1 : 0))>
                                    <label class="form-check-label" for="is_active">تفعيل الحساب فوراً بدون الحاجة لكود التحقق</label>
                                </div>
                                <small class="text-muted">عند إلغاء التفعيل لن يتمكن المدير من تسجيل الدخول حتى تعيد تفعيله.</small>
                            @else
                                <div class="form-control-plaintext text-muted">{{ $manager->phone_verified_at ? 'الحساب مفعل حالياً' : 'الحساب غير مفعل' }}</div>
                                <small class="text-muted d-block">لا تملك صلاحية تغيير حالة التفعيل.</small>
                            @endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">كلمة المرور الجديدة (اتركه فارغاً لعدم التغيير)</label>
                            @if($fieldPermissions['password'])
                                <input type="password" class="form-control" id="password" name="password">
                            @else
                                <div class="form-control-plaintext text-muted">لا تملك صلاحية تغيير كلمة المرور.</div>
                            @endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">تأكيد كلمة المرور الجديدة</label>
                            @if($fieldPermissions['password'])
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                            @else
                                <div class="form-control-plaintext text-muted">صلاحية تغيير كلمة المرور مطلوبة لإدخال كلمة مرور جديدة.</div>
                            @endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="manager_id" class="form-label">المشرف المباشر</label>
                            <select name="manager_id" id="manager_id" class="form-select">
                                <option value="">بدون مشرف (إدارة عليا)</option>
                                @foreach(($managers ?? collect()) as $availableManager)
                                    <option value="{{ $availableManager->id }}" {{ (int) old('manager_id', $manager->manager_id) === $availableManager->id ? 'selected' : '' }}>
                                        {{ $availableManager->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">اختر المشرف المسؤول عن هذا الحساب لضبط وصول الطلبات بشكل صحيح.</small>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">المحافظات المخصصة (الزون)</label>
                            <div class="border rounded-3 p-3" id="governorate_selector">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                    <span class="fw-semibold" style="color:#FF5722;">اختر المحافظات التي يمكن للمدير متابعتها</span>
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
                            <small class="text-muted d-block mt-2">يمكنك اختيار أكثر من محافظة، أو تركها فارغة لعرض العملاء الشخصيين فقط.</small>
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

        {{-- الدور الوظيفي --}}
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingTwo">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                    اختيار الدور الوظيفي
                </button>
            </h2>
            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#userEditAccordion">
                <div class="accordion-body">
                    <p class="text-muted small mb-3">اختر الدور المناسب، سيتم تطبيق جميع صلاحياته على هذا المدير مباشرة.</p>
                    <div class="row g-3">
                        @forelse($roles as $role)
                            <div class="col-md-4">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="form-check">
                                        <input type="radio" class="form-check-input" name="role" id="role_{{ $role->id }}" value="{{ $role->name }}" @checked(old('role', optional($manager->roles->first())->name) === $role->name) required>
                                        <label class="form-check-label fw-semibold" for="role_{{ $role->id }}">{{ $role->name }}</label>
                                    </div>
                                    <p class="text-muted small mb-0 mt-2">يتضمن {{ $role->permissions_count }} صلاحية.</p>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="alert alert-warning mb-0">لم يتم تعريف أدوار بعد.</div>
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

    <div class="mt-4">
        <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
        <a href="{{ route('admin.managers.index') }}" class="btn btn-secondary">العودة للوحة الإداريين</a>
    </div>
</form>


@else
<div class="alert alert-danger">ليس لديك صلاحية لتعديل المدراء.</div>
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
