@extends('admin.layout')

@section('title', 'تعديل البراند: ' . $category->name_ar)

@section('content')
@push('styles')
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; min-height: calc(100vh - 70px); }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem; color: white; border-radius: 0 !important; }
    .form-section-title { font-weight: 700; color: var(--primary-dark); border-right: 4px solid var(--accent-gold); padding-right: 15px; margin-bottom: 2rem; }
    
    .form-control, .form-select { border-radius: 10px; padding: 0.8rem 1.2rem; border: 1px solid #e2e8f0; }
    .submit-btn { background: var(--primary-dark); padding: 1rem 3.5rem; border-radius: 10px; font-weight: 700; color: white; border: none; transition: 0.3s; }
    .submit-btn:hover { background: var(--primary-medium); transform: translateY(-2px); box-shadow: 0 10px 20px rgba(109, 14, 22, 0.2); color: white; }
</style>
@endpush

<div class="form-card">
    <div class="form-card-header d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-2 fw-bold text-white"><i class="bi bi-pencil-square me-2"></i> تعديل البراند: {{ $category->name_ar }}</h2>
            <p class="mb-0 opacity-75 fs-6 text-white small">أنت الآن تقوم بتعديل بيانات العلامة التجارية المختارة.</p>
        </div>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-light px-4 fw-bold rounded-3">
            <i class="bi bi-arrow-right me-1"></i> العودة للبراندات
        </a>
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

        <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-5">
                <h5 class="form-section-title">بيانات البراند</h5>
                @include('admin.categories._form', ['item' => $category, 'parentCategories' => $parentCategories])
            </div>

            <div class="d-flex justify-content-end gap-3 pt-5 border-top">
                <a href="{{ route('admin.categories.index') }}" class="btn btn-light px-4 py-2 rounded-3">إلغاء</a>
                <button type="submit" class="submit-btn shadow-sm">
                    <i class="bi bi-check2-circle me-1"></i> حفظ التعديلات
                </button>
            </div>
        </form>
    </div>
</div>
@endsection


