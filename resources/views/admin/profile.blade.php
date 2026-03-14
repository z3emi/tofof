@extends('admin.layout')

@section('title', 'الملف الشخصي')

@section('content')
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-0 pt-4 pb-0">
                <h5 class="mb-0 fw-bold">معلومات المدير</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted">الاسم</label>
                    <input type="text" class="form-control bg-light" value="{{ $manager->name }}" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted">البريد الإلكتروني</label>
                    <input type="email" class="form-control bg-light" value="{{ $manager->email }}" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted">رقم الهاتف</label>
                    <input type="text" class="form-control bg-light" value="{{ $manager->phone_number }}" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted">الدور والصلاحيات</label>
                    <input type="text" class="form-control bg-light" value="{{ $manager->roles->pluck('name')->join(', ') }}" readonly>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-0 pt-4 pb-0">
                <h5 class="mb-0 fw-bold">تغيير كلمة المرور</h5>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <form action="{{ route('admin.profile.password') }}" method="POST">
                    @csrf
                    @method('PATCH')
                    
                    <div class="mb-3">
                        <label class="form-label">كلمة المرور الحالية</label>
                        <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">كلمة المرور الجديدة</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">تأكيد كلمة المرور الجديدة</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">تحديث كلمة المرور</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
