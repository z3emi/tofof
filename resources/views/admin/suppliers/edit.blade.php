@extends('admin.layout')

@section('title', 'تعديل المورد: ' . $supplier->name)

@section('content')
@push('styles')
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem; color: white; border-radius: 0 !important; }
    .form-section-title { font-weight: 700; color: var(--primary-dark); border-right: 4px solid var(--accent-gold); padding-right: 15px; margin-bottom: 2rem; }
    .submit-btn { background: var(--primary-dark); padding: 1rem 3rem; border-radius: 10px; font-weight: 700; color: white; border: none; transition: all 0.3s ease; }
</style>
@endpush

<div class="form-card">
    <div class="form-card-header">
        <h2 class="mb-2 fw-bold text-white"><i class="bi bi-truck me-2"></i> تعديل المورد: {{ $supplier->name }}</h2>
        <p class="mb-0 opacity-75 fs-6 text-white small">قم بتحديث بيانات التواصل أو العناوين الخاصة بهذا المورد.</p>
    </div>
    
    <div class="p-4 p-lg-5">
        <form action="{{ route('admin.suppliers.update', $supplier->id) }}" method="POST">
            @csrf @method('PUT')

            <div class="mb-5">
                <h5 class="form-section-title">المعلومات الأساسية</h5>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="name" class="form-label fw-bold small">اسم المورد / الشركة <span class="text-danger">*</span></label>
                        <input type="text" class="form-control shadow-xs" style="border-radius:12px; padding:0.8rem" id="name" name="name" value="{{ old('name', $supplier->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="phone" class="form-label fw-bold small">رقم الهاتف <span class="text-danger">*</span></label>
                        <input type="text" class="form-control shadow-xs" style="border-radius:12px; padding:0.8rem" id="phone" name="phone" value="{{ old('phone', $supplier->phone) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label fw-bold small">البريد الإلكتروني</label>
                        <input type="email" class="form-control shadow-xs" style="border-radius:12px; padding:0.8rem" id="email" name="email" value="{{ old('email', $supplier->email) }}">
                    </div>
                </div>
            </div>

            <div class="mb-5">
                <h5 class="form-section-title">التفاصيل الموقع</h5>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="address" class="form-label fw-bold small">العنوان</label>
                        <textarea class="form-control" style="border-radius:12px; padding:0.8rem" id="address" name="address" rows="3">{{ old('address', $supplier->address) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label for="description" class="form-label fw-bold small">ملاحظات</label>
                        <textarea class="form-control" style="border-radius:12px; padding:0.8rem" id="description" name="description" rows="3">{{ old('description', $supplier->description) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-3 pt-5 border-top">
                <a href="{{ route('admin.suppliers.index') }}" class="btn btn-light px-5 py-3 rounded-3 fw-bold">إلغاء</a>
                <button type="submit" class="submit-btn shadow-sm py-3 px-5">حفظ التحديثات</button>
            </div>
        </form>
    </div>
</div>
@endsection
