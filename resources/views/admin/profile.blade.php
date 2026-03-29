@extends('admin.layout')

@section('title', 'الملف الشخصي')

@push('styles')
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem; color: white; border-radius: 0 !important; }
    
    .profile-avatar-container {
        position: relative;
        display: inline-block;
        margin-bottom: 1.5rem;
    }
    .profile-avatar-big {
        width: 140px;
        height: 140px;
        border-radius: 20px;
        object-fit: cover;
        border: 4px solid #fff;
        box-shadow: var(--shadow-md);
        background: #fff;
    }
    .avatar-edit-badge {
        position: absolute;
        bottom: -10px;
        right: -10px;
        background: var(--accent-gold);
        color: white;
        width: 38px;
        height: 38px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 3px solid #fff;
        cursor: pointer;
        transition: var(--transition);
        box-shadow: var(--shadow-sm);
    }
    .avatar-edit-badge:hover {
        transform: scale(1.1);
        background: var(--primary-light);
    }
    .section-box {
        background: #fafbff;
        border: 1px solid #f1f5f9;
        border-radius: 20px;
        padding: 2rem;
        height: 100%;
    }
    .form-label {
        font-weight: 700;
        font-size: 0.85rem;
        color: var(--text-light);
        margin-bottom: 0.5rem;
    }
    .value-input {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        padding: 0.75rem 1rem;
        background: #fff;
    }
    [data-theme="dark"] .section-box {
        background: rgba(255,255,255,0.02);
        border-color: rgba(255,255,255,0.05);
    }
    [data-theme="dark"] .value-input {
        background: var(--bg-light);
        border-color: rgba(255,255,255,0.1);
    }
</style>
@endpush

@section('content')
<div class="form-card">
    <div class="form-card-header">
        <div class="d-flex align-items-center gap-4">
            <div class="d-none d-md-block">
                <div class="bg-white p-2 rounded-4 shadow-sm">
                    <img src="{{ $manager->avatar_url }}" width="80" height="80" class="rounded-3 object-fit-cover shadow-xs border">
                </div>
            </div>
            <div>
                <h2 class="mb-1 fw-bold text-white"><i class="bi bi-person-circle me-2"></i> إعدادات الملف الشخصي</h2>
                <p class="mb-0 opacity-75 fs-6 text-white small">إدارة معلوماتك الشخصية، البريد الإلكتروني، وتأمين الحساب.</p>
            </div>
        </div>
    </div>

    <div class="p-4 p-lg-5">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-5 border-0 shadow-sm rounded-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill me-3 fs-4"></i>
                    <div>{{ session('success') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row g-5">
            <!-- Part 1: Details & Avatar -->
            <div class="col-lg-8">
                <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')
                    
                    <div class="section-box shadow-xs">
                        <div class="text-center text-md-start mb-4">
                            <div class="profile-avatar-container">
                                <img src="{{ $manager->avatar_url }}" alt="Profile" class="profile-avatar-big shadow-sm" id="avatarPreview">
                                <label for="avatarInput" class="avatar-edit-badge">
                                    <i class="bi bi-camera-fill"></i>
                                </label>
                                <input type="file" name="avatar" id="avatarInput" class="d-none" accept="image/*" onchange="previewImage(this)">
                            </div>
                            <h4 class="fw-bold mb-1">{{ $manager->name }}</h4>
                            <span class="badge bg-soft-brand text-brand rounded-pill px-3 py-2 border">
                                <i class="bi bi-shield-check me-1"></i> {{ $manager->roles->pluck('name')->first() ?? 'مدير النظام' }}
                            </span>
                        </div>

                        <hr class="my-4 opacity-50">

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">الاسم الكامل</label>
                                <input type="text" name="name" class="form-control value-input @error('name') is-invalid @enderror" value="{{ old('name', $manager->name) }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">البريد الإلكتروني</label>
                                <input type="email" name="email" class="form-control value-input @error('email') is-invalid @enderror" value="{{ old('email', $manager->email) }}" required>
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">اسم المستخدم (للعرض)</label>
                                <input type="text" class="form-control value-input bg-soft-light" value="{{ $manager->phone_number }}" readonly disabled>
                                <small class="text-muted small mt-1 d-block"><i class="bi bi-info-circle me-1"></i> لا يمكن تغيير اسم المستخدم حالياً.</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">تاريخ الانضمام</label>
                                <input type="text" class="form-control value-input bg-soft-light" value="{{ $manager->created_at->format('Y-m-d') }}" readonly disabled>
                            </div>
                        </div>

                        <div class="mt-5 pt-3">
                            <button type="submit" class="btn btn-brand px-5 py-3 fw-bold">
                                <i class="bi bi-cloud-arrow-up me-2"></i> حفظ التغييرات الأساسية
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Part 2: Security -->
            <div class="col-lg-4">
                <div class="section-box shadow-xs border-top border-4 border-danger">
                    <h5 class="fw-bold mb-4 d-flex align-items-center">
                        <i class="bi bi-shield-lock-fill me-2 text-danger"></i> أمان الحساب
                    </h5>
                    
                    <form action="{{ route('admin.profile.password') }}" method="POST">
                        @csrf
                        @method('PATCH')
                        
                        <div class="mb-4">
                            <label class="form-label text-danger">كلمة المرور الحالية</label>
                            <input type="password" name="current_password" class="form-control value-input @error('current_password') is-invalid @enderror" required placeholder="أدخل الحالية...">
                            @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">كلمة المرور الجديدة</label>
                            <input type="password" name="password" class="form-control value-input @error('password') is-invalid @enderror" required placeholder="أدخل الجديدة...">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-5">
                            <label class="form-label">تأكيد الكلمة الجديدة</label>
                            <input type="password" name="password_confirmation" class="form-control value-input" required placeholder="كرر الجديدة...">
                        </div>

                        <button type="submit" class="btn btn-danger w-100 py-3 fw-bold shadow-sm">
                            <i class="bi bi-key-fill me-2"></i> تحديث كلمة المرور
                        </button>
                    </form>

                    <div class="mt-4 p-3 bg-soft-warning rounded-4 border border-warning small text-dark">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        احرص على استخدام كلمة مرور قوية تحتوي على رموز وأرقام.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatarPreview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush
