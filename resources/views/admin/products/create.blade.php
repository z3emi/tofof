@extends('admin.layout')

@section('title', 'إضافة منتج جديد')

@push('styles')
<style>
    /* تصفير كافة الحواف والمسافات البادئة للحاوية الرئيسية */
    .main-content {
        padding: 0 !important;
    }

    /* استهداف حاوية Bootstrap لضمان عدم وجود أي Padding جانبي */
    .main-content .container-fluid {
        padding-left: 0 !important;
        padding-right: 0 !important;
        max-width: 100% !important;
    }

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
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
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
        border-radius: 10px;
        padding: 0.8rem 1.2rem;
        border: 1px solid #e2e8f0;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background-color: #fcfcfc;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-light);
        box-shadow: 0 0 0 4px rgba(109, 14, 22, 0.08);
        background-color: #fff;
    }

    .card {
        border-radius: 12px !important;
        border: 1px solid #f1f5f9 !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05) !important;
        margin-bottom: 2rem;
    }

    .card-header {
        border-bottom: 1px solid #f1f5f9 !important;
        font-weight: 800 !important;
        background: #fafbff !important;
        padding: 1.25rem !important;
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
        box-shadow: 0 10px 20px rgba(109, 14, 22, 0.2);
    }
</style>
@endpush

@section('content')
<div class="form-card">
    <div class="form-card-header">
        <div>
            <h2 class="mb-2 fw-bold text-white"><i class="bi bi-plus-circle-fill me-2"></i> إضافة منتج جديد</h2>
            <p class="mb-0 opacity-75 fs-6 text-white">أدخل تفاصيل المنتج، الصور، والأسعار لضمه للمتجر.</p>
        </div>
        <div class="d-none d-md-block">
            <i class="bi bi-box-seam-fill opacity-25" style="font-size: 4rem; color: white;"></i>
        </div>
    </div>
    
    <div class="p-4 p-lg-5">
        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-5">
                <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                    <h5 class="mb-0 fw-bold">تنبيه! هناك أخطاء في الحقول</h5>
                </div>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="w-100">
            @csrf
            @include('admin.products._form')
        </form>
    </div>
</div>
@endsection