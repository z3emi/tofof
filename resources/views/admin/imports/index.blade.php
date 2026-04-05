@extends('admin.layout')
@section('title', 'استيراد البيانات')

@push('styles')
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem; color: white; border-radius: 0 !important; }
    .form-card-body { padding: 1.5rem 1.25rem; }

    .import-panel {
        max-width: 980px;
        margin: 0 auto;
        border: 1px solid #e7edf4;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 8px 26px rgba(15, 23, 42, .06);
        background: #fff;
    }

    .import-panel .alert {
        border-radius: .75rem;
        border: 1px solid #d9e2ee;
    }
    .import-panel .form-label {
        font-weight: 700;
        color: #334155;
        font-size: .92rem;
    }
    .import-panel .form-control,
    .import-panel .form-select {
        min-height: 44px;
        border-radius: .7rem;
        border-color: #d6deea;
    }
    .import-panel .form-control:focus,
    .import-panel .form-select:focus {
        border-color: var(--primary-medium);
        box-shadow: 0 0 0 .22rem rgba(109, 14, 22, .12);
    }

    .import-actions { margin-top: 1rem; text-align: center; }
    .import-btn {
        min-height: 42px;
        border-radius: .7rem;
        padding: .5rem 1rem;
        font-weight: 700;
        border: 0;
        background: linear-gradient(135deg, var(--primary-medium) 0%, var(--primary-dark) 100%);
        color: #fff;
        box-shadow: 0 8px 18px rgba(109, 14, 22, .22);
    }
    .import-btn:hover { filter: brightness(.97); color: #fff; }

    @media (max-width: 767.98px) {
        .form-card-header { padding: 1.35rem 1rem; }
        .form-card-header h2 { font-size: 1.15rem; }
        .form-card-body { padding: 1rem .8rem; }
        .import-btn { width: 100%; }
    }
</style>
@endpush

@section('content')
<div class="form-card">
    <div class="form-card-header">
        <h2 class="mb-2 fw-bold text-white"><i class="bi bi-cloud-arrow-up me-2"></i>استيراد البيانات من Excel</h2>
        <p class="mb-0 opacity-75 fs-6 text-white small">اختر القسم وارفع الملف لمعاينة الأعمدة قبل تنفيذ الاستيراد.</p>
    </div>

    <div class="form-card-body">
    <div class="import-panel p-3 p-md-4">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <form action="{{ route('admin.imports.preview') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="form-label">اختر القسم</label>
                <select name="section" class="form-select" required>
                    <option value="products">منتجات</option>
                    <option value="categories">البراندات</option>
                    <option value="brands">الفئات</option>
                    <option value="users">مستخدمين</option>
                    <option value="clients">عملاء</option>
                    <option value="discounts">أكواد خصم</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">ملف Excel</label>
                <input type="file" name="file" class="form-control" required>
            </div>

            <div class="import-actions">
                <button type="submit" class="btn import-btn">رفع الملف</button>
            </div>
        </form>
    </div>
    </div>
</div>
@endsection