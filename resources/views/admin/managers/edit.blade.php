@extends('admin.layout')

@section('title', 'تعديل المدير: ' . $manager->name)

@section('content')
@can('edit-managers')
@push('styles')
<style>
    .form-card {
        border-radius: 0 !important;
        border: none !important;
        box-shadow: none !important;
        background: #fff;
        width: 100% !important;
        margin: 0 !important;
    }

    .form-card-header {
        background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%);
        padding: 2.5rem 3rem;
        color: white;
        border-radius: 0 !important;
    }

    .form-section-title {
        font-weight: 700;
        color: var(--primary-dark);
        border-right: 4px solid var(--accent-gold);
        padding-right: 15px;
        margin-bottom: 2rem;
    }

    .avatar-upload-container {
        position: relative;
        width: 140px;
        height: 140px;
        margin: 0 auto 1.5rem;
    }

    .avatar-preview {
        width: 100%;
        height: 100%;
        border-radius: 20px;
        object-fit: cover;
        border: 4px solid #fff;
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }

    .avatar-edit-btn {
        position: absolute;
        bottom: -5px;
        left: -5px;
        width: 40px;
        height: 40px;
        background: var(--accent-gold);
        color: white;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    .form-label {
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 0.6rem;
        font-size: 0.95rem;
    }

    .form-control, .form-select {
        border-radius: 12px;
        padding: 0.8rem 1.2rem;
        border: 1px solid #e2e8f0;
        background-color: #fcfcfc;
    }

    .readonly-field {
        background-color: #f8fafc !important;
        color: #94a3b8 !important;
    }

    .role-selection-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
    }

    .role-card-inner {
        border: 2px solid #f1f5f9;
        border-radius: 15px;
        padding: 1.5rem;
        background: #fafbff;
        text-align: center;
        transition: all 0.3s ease;
    }

    .role-card-item input:checked + .role-card-inner {
        border-color: var(--primary-dark);
        background: white;
        box-shadow: 0 10px 20px rgba(109, 14, 22, 0.1);
    }

    .submit-btn {
        background: var(--primary-dark);
        padding: 1rem 3rem;
        border-radius: 10px;
        font-weight: 700;
        color: white;
        border: none;
    }

    .cancel-btn {
        background: #f1f5f9;
        padding: 1rem 3rem;
        border-radius: 10px;
        font-weight: 700;
        color: var(--text-dark);
        text-decoration: none;
    }
</style>
@endpush

@php
    $fieldPermissions = array_merge([
        'name' => false,
        'email' => false,
        'phone' => false,
        'password' => false,
        'status' => false,
        'contact' => false,
    ], $fieldPermissions ?? []);
@endphp

<div class="form-card">
    <div class="form-card-header">
        <h2 class="mb-2 fw-bold text-white"><i class="bi bi-person-gear me-2"></i> تعديل بيانات المدير: {{ $manager->name }}</h2>
        <p class="mb-0 opacity-75 fs-6 text-white">قم بتحديث معلومات الحساب والصلاحيات من هذا النموذج.</p>
    </div>
    
    <div class="p-4 p-lg-5">
        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.managers.update', $manager->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- الصورة الشخصية -->
            <div class="text-center mb-5">
                <div class="avatar-upload-container">
                    <img src="{{ $manager->avatar_url }}" id="previewImg" class="avatar-preview" onerror="this.src='{{ asset('storage/avatars/default.jpg') }}'">
                    <label for="avatar" class="avatar-edit-btn">
                        <i class="bi bi-camera-fill"></i>
                    </label>
                    <input type="file" id="avatar" name="avatar" class="d-none" accept="image/*" onchange="previewImage(this)">
                </div>
                <div class="form-check d-inline-block">
                    <input class="form-check-input" type="checkbox" name="reset_avatar" id="reset_avatar" value="1">
                    <label class="form-check-label small" for="reset_avatar">استعادة الصورة الافتراضية</label>
                </div>
            </div>

            <div class="mb-5">
                <h5 class="form-section-title">المعلومات الأساسية</h5>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="name" class="form-label">الاسم الكامل</label>
                        <input type="text" class="form-control @unless($fieldPermissions['name']) readonly-field @endunless" 
                               id="name" name="name" value="{{ old('name', $manager->name) }}" 
                               @unless($fieldPermissions['name']) readonly @endunless required>
                        @unless($fieldPermissions['name'])
                            <small class="text-muted"><i class="bi bi-info-circle me-1"></i> لا تملك صلاحية تعديل الاسم.</small>
                        @endunless
                    </div>
                    
                    <div class="col-md-6">
                        <label for="phone_number" class="form-label">اسم المستخدم</label>
                        @if($fieldPermissions['contact'])
                            <input type="text" class="form-control @unless($fieldPermissions['phone']) readonly-field @endunless" 
                                   id="phone_number" name="phone_number" value="{{ old('phone_number', $manager->phone_number) }}" 
                                   @unless($fieldPermissions['phone']) readonly @endunless required>
                            @unless($fieldPermissions['phone'])
                                <small class="text-muted"><i class="bi bi-info-circle me-1"></i> لا تملك صلاحية تعديل اسم المستخدم.</small>
                            @endunless
                        @else
                            <div class="form-control readonly-field text-muted">بيانات مخفية لعدم توفر الصلاحية</div>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">البريد الإلكتروني</label>
                        @if($fieldPermissions['contact'])
                            <input type="email" class="form-control @unless($fieldPermissions['email']) readonly-field @endunless" 
                                   id="email" name="email" value="{{ old('email', $manager->email) }}" 
                                   @unless($fieldPermissions['email']) readonly @endunless>
                        @else
                            <div class="form-control readonly-field text-muted">بيانات مخفية لعدم توفر الصلاحية</div>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <label for="manager_id" class="form-label">المشرف المباشر</label>
                        <select name="manager_id" id="manager_id" class="form-select">
                            <option value="">بدون مشرف (إدارة عليا)</option>
                            @foreach(($managers ?? collect()) as $availableManager)
                                <option value="{{ $availableManager->id }}" {{ (int) old('manager_id', $manager->manager_id) === $availableManager->id ? 'selected' : '' }}>
                                    {{ $availableManager->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="row mb-5 g-4">
                <div class="col-md-6">
                    <h5 class="form-section-title">حالة الحساب والأمان</h5>
                    <div class="p-4 bg-light rounded-4 border">
                        @if($fieldPermissions['status'])
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" style="width: 3rem; height: 1.5rem;" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $manager->phone_verified_at ? 1 : 0))>
                                <label class="form-check-label fw-bold ms-2" for="is_active">تفعيل دخول النظام</label>
                            </div>
                            <p class="text-muted small mb-0">عند إلغاء التفعيل، سيتم حظر المدير من الدخول للوحة التحكم فوراً.</p>
                        @else
                            <div class="d-flex align-items-center">
                                <span class="badge {{ $manager->phone_verified_at ? 'bg-success' : 'bg-danger' }} p-2 px-3 rounded-pill">
                                    {{ $manager->phone_verified_at ? 'نشط' : 'غير نشط' }}
                                </span>
                                <span class="ms-2 text-muted small">لا تملك صلاحية تغيير الحالة.</span>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <h5 class="form-section-title">تغيير كلمة المرور</h5>
                    <div class="row g-3">
                        <div class="col-12">
                            <input type="password" class="form-control @unless($fieldPermissions['password']) readonly-field @endunless" 
                                   id="password" name="password" placeholder="كلمة المرور الجديدة (اتركه فارغاً للتجاهل)" 
                                   @unless($fieldPermissions['password']) readonly @endunless>
                        </div>
                        <div class="col-12">
                            <input type="password" class="form-control @unless($fieldPermissions['password']) readonly-field @endunless" 
                                   id="password_confirmation" name="password_confirmation" placeholder="تأكيد كلمة المرور الجديدة" 
                                   @unless($fieldPermissions['password']) readonly @endunless>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-5">
                <h5 class="form-section-title">الدور الوظيفي والصلاحيات</h5>
                <div class="role-selection-grid">
                    @foreach(($roles ?? collect()) as $role)
                        <label class="role-card-item" style="cursor: pointer;">
                            <input type="radio" name="role" value="{{ $role->name }}" 
                                   class="d-none" @checked(old('role', optional($manager->roles->first())->name) === $role->name) required>
                            <div class="role-card-inner">
                                <div class="role-icon mx-auto mb-3" style="width:50px; height:50px; background:rgba(109, 14, 22, 0.1); color:var(--primary-dark); border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.5rem;">
                                    <i class="bi bi-shield-lock"></i>
                                </div>
                                <div class="role-name fw-bold">{{ $role->name }}</div>
                                <div class="role-desc small text-muted">يتضمن {{ $role->permissions_count }} صلاحية مختلفة.</div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="d-flex justify-content-end gap-3 pt-5 border-top">
                <a href="{{ route('admin.managers.index') }}" class="cancel-btn">إلغاء</a>
                <button type="submit" class="submit-btn shadow-sm">حفظ التعديلات النهائية</button>
            </div>
        </form>
    </div>
</div>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) { $('#previewImg').attr('src', e.target.result); }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@else
<div class="p-5 text-center">
    <div class="alert alert-danger rounded-4 shadow-sm p-5 d-inline-block">
        <i class="bi bi-shield-exclamation display-1 mb-3"></i>
        <h4>عذراً، ليس لديك صلاحية تعديل بيانات المدراء.</h4>
    </div>
</div>
@endcan
@endsection
