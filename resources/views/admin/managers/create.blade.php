@extends('admin.layout')

@section('title', 'إضافة مدير جديد')

@section('content')
@can('create-managers')
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
        transition: all 0.3s ease;
        background-color: #fcfcfc;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-light);
        box-shadow: 0 0 0 4px rgba(109, 14, 22, 0.08);
        background-color: #fff;
    }

    .role-selection-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
    }

    .role-card-item {
        cursor: pointer;
        position: relative;
    }

    .role-card-item input {
        display: none;
    }

    .role-card-inner {
        border: 2px solid #f1f5f9;
        border-radius: 15px;
        padding: 1.5rem;
        transition: all 0.3s ease;
        height: 100%;
        background: #fafbff;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .role-card-item:hover .role-card-inner {
        border-color: var(--primary-light);
        transform: translateY(-5px);
    }

    .role-card-item input:checked + .role-card-inner {
        border-color: var(--primary-dark);
        background: white;
        box-shadow: 0 10px 20px rgba(109, 14, 22, 0.1);
    }

    .role-icon {
        width: 50px;
        height: 50px;
        background: rgba(109, 14, 22, 0.1);
        color: var(--primary-dark);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }

    .submit-btn {
        background: var(--primary-dark);
        border: none;
        padding: 1rem 3rem;
        border-radius: 10px;
        font-weight: 700;
        color: white;
        transition: all 0.3s ease;
    }

    .submit-btn:hover {
        background: var(--primary-medium);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(109, 14, 22, 0.3);
    }

    .cancel-btn {
        background: #f1f5f9;
        padding: 1rem 3rem;
        border-radius: 10px;
        font-weight: 700;
        color: var(--text-dark);
        text-decoration: none;
        transition: all 0.3s ease;
    }
</style>
@endpush

<div class="form-card">
    <div class="form-card-header">
        <h2 class="mb-2 fw-bold text-white"><i class="bi bi-person-plus-fill me-2"></i> إضافة مدير جديد للنظام</h2>
        <p class="mb-0 opacity-75 fs-6 text-white">أدخل المعلومات الأساسية وحدد الدور الوظيفي للمدير الجديد.</p>
    </div>
    
    <div class="p-4 p-lg-5">
        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-5">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.managers.store') }}" method="POST">
            @csrf

            <!-- المعلومات الأساسية -->
            <div class="mb-5">
                <h5 class="form-section-title">المعلومات الشخصية والوظيفية</h5>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="name" class="form-label">الاسم الكامل <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" placeholder="أدخل اسم المدير المعتمد" required>
                    </div>
                    <div class="col-md-6">
                        <label for="phone_number" class="form-label">اسم المستخدم (رقم الهاتف) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="phone_number" name="phone_number" value="{{ old('phone_number') }}" placeholder="أدخل رقم الهاتف كاسم مستخدم" required>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">البريد الإلكتروني (اختياري)</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" placeholder="example@tofof.com">
                    </div>
                    <div class="col-md-6">
                        <label for="manager_id" class="form-label">المشرف المباشر</label>
                        <select name="manager_id" id="manager_id" class="form-select">
                            <option value="">بدون مشرف (إدارة عليا)</option>
                            @foreach(($managers ?? collect()) as $availableManager)
                                <option value="{{ $availableManager->id }}" {{ old('manager_id') == $availableManager->id ? 'selected' : '' }}>
                                    {{ $availableManager->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- الأمان -->
            <div class="mb-5">
                <h5 class="form-section-title">إعدادات الحماية والأمان</h5>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="password" class="form-label">كلمة المرور القوية <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                    </div>
                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label">تأكيد كلمة المرور <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="••••••••" required>
                    </div>
                </div>
            </div>

            <!-- الصلاحيات -->
            <div class="mb-5">
                <h5 class="form-section-title">تحديد الصلاحيات (الدور الوظيفي)</h5>
                <p class="text-muted small mb-4">اختر الدور الوظيفي الذي سيحصل عليه المدير، والذي سيحدد امكانياته داخل لوحة التحكم.</p>
                
                <div class="role-selection-grid">
                    @forelse(($roles ?? collect()) as $role)
                        <label class="role-card-item">
                            <input type="radio" name="role" value="{{ $role->name }}" @checked(old('role') === $role->name) required>
                            <div class="role-card-inner">
                                <div class="role-icon">
                                    <i class="bi bi-shield-check"></i>
                                </div>
                                <div class="role-name">{{ $role->name }}</div>
                                <div class="role-desc">يمنح هذا الدور {{ $role->permissions_count }} صلاحية محددة للنظام.</div>
                            </div>
                        </label>
                    @empty
                        <div class="col-12 text-center py-5">
                            <div class="alert alert-warning border-0">لا توجد أدوار إشرافية معرفة في النظام حالياً.</div>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="d-flex justify-content-end gap-3 pt-5 border-top">
                <a href="{{ route('admin.managers.index') }}" class="cancel-btn">إلغاء والعودة</a>
                <button type="submit" class="submit-btn shadow-sm">حفظ وإنشاء الحساب</button>
            </div>
        </form>
    </div>
</div>
@else
<div class="p-5 text-center">
    <div class="alert alert-danger rounded-4 shadow-sm p-5 d-inline-block">
        <i class="bi bi-shield-exclamation display-1 mb-3"></i>
        <h4>عذراً، ليس لديك الصلاحية الكافية لإنشاء حسابات إدارية.</h4>
    </div>
</div>
@endcan
@endsection
