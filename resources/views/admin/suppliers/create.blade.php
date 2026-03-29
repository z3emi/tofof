@extends('admin.layout')

@section('title', 'إضافة مورد جديد')

@section('content')
@push('styles')
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem; color: white; border-radius: 0 !important; }
    .form-section-title { font-weight: 700; color: var(--primary-dark); border-right: 4px solid var(--accent-gold); padding-right: 15px; margin-bottom: 2rem; }
    .submit-btn { background: var(--primary-dark); padding: 1rem 3rem; border-radius: 10px; font-weight: 700; color: white; border: none; transition: all 0.3s ease; }
    .submit-btn:hover { background: var(--primary-medium); transform: translateY(-2px); }
</style>
@endpush

<div class="form-card">
    <div class="form-card-header">
        <h2 class="mb-2 fw-bold text-white"><i class="bi bi-truck me-2"></i> إضافة مورد جديد</h2>
        <p class="mb-0 opacity-75 fs-6 text-white small">قم بتسجيل بيانات الموردين لضبط عمليات الشراء والمخزون.</p>
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

        <form action="{{ route('admin.suppliers.store') }}" method="POST">
            @csrf

            <div class="mb-5">
                <h5 class="form-section-title">بيانات التواصل والتعريف</h5>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="name" class="form-label fw-bold small">اسم المورد / الشركة <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" style="border-radius:12px; padding:0.8rem" id="name" name="name" value="{{ old('name') }}" placeholder="أدخل اسم الجهة الموردة" required>
                    </div>
                    <div class="col-md-6">
                        <label for="phone" class="form-label fw-bold small">رقم الهاتف <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" style="border-radius:12px; padding:0.8rem" id="phone" name="phone" value="{{ old('phone') }}" placeholder="مثال: 07XXXXXXXX" required>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label fw-bold small">البريد الإلكتروني (اختياري)</label>
                        <input type="email" class="form-control" style="border-radius:12px; padding:0.8rem" id="email" name="email" value="{{ old('email') }}" placeholder="supplier@example.com">
                    </div>
                </div>
            </div>

            <div class="mb-5">
                <h5 class="form-section-title">الموقع والوصف</h5>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="address" class="form-label fw-bold small">عنوان المورد بالتفصيل</label>
                        <textarea class="form-control" style="border-radius:12px; padding:0.8rem" id="address" name="address" rows="3" placeholder="المحافظة، المنطقة، شارع...">{{ old('address') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label for="description" class="form-label fw-bold small">ملاحظات إضافية</label>
                        <textarea class="form-control" style="border-radius:12px; padding:0.8rem" id="description" name="description" rows="3" placeholder="أي معلومات تود إضافتها عن هذا المورد">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-3 pt-5 border-top">
                <a href="{{ route('admin.suppliers.index') }}" class="btn btn-light px-5 py-3 rounded-3 fw-bold">إلغاء والعودة</a>
                <button type="submit" class="submit-btn shadow-sm py-3 px-5">حفظ بيانات المورد</button>
            </div>
        </form>
    </div>
</div>
@endsection
