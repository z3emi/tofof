@extends('admin.layout')

@section('title', 'تعديل المنتج: ' . $product->name_ar)

@section('content')
@push('styles')
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem; color: white; border-radius: 0 !important; }
    .form-section-title { font-weight: 700; color: var(--primary-dark); border-right: 4px solid var(--accent-gold); padding-right: 15px; margin-bottom: 2rem; }
    .card { border-radius: 12px !important; border: 1px solid #f1f5f9 !important; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05) !important; margin-bottom: 2rem; }
    .card-header { border-bottom: 1px solid #f1f5f9 !important; font-weight: 800 !important; background: #fafbff !important; padding: 1.25rem !important; }
    .submit-btn { background: var(--primary-dark); padding: 1rem 3rem; border-radius: 10px; font-weight: 700; color: white; border: none; }
</style>
@endpush

<div class="form-card">
    <div class="form-card-header d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-2 fw-bold text-white"><i class="bi bi-pencil-square me-2"></i> تعديل المنتج: {{ $product->name_ar }}</h2>
            <p class="mb-0 opacity-75 fs-6 text-white small">أدخل التعديلات اللازمة على المواصفات، الصور، أو الأسعار.</p>
        </div>
        <div class="d-none d-md-block"><i class="bi bi-watch opacity-25" style="font-size: 4rem; color: white;"></i></div>
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

        <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('admin.products._form')
        </form>
    </div>
</div>
@endsection