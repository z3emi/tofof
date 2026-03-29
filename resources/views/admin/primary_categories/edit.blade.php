@extends('admin.layout')

@section('title', 'تعديل فئة: ' . $item->name_ar)

@push('styles')
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem; color: white; border-radius: 0 !important; }
    .form-section { background: #fff; border-radius: 15px; border: 1px solid #f1f5f9; padding: 2rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    .submit-btn { background: var(--primary-dark); border: none; padding: 0.8rem 2.5rem; border-radius: 10px; font-weight: 700; color: white; transition: 0.3s; }
    .submit-btn:hover { background: var(--primary-medium); transform: translateY(-2px); box-shadow: 0 10px 20px rgba(109, 14, 22, 0.2); }
</style>
@endpush

@section('content')
<div class="form-card">
    <div class="form-card-header d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-2 fw-bold text-white"><i class="bi bi-pencil-square me-2"></i> تعديل الفئة</h2>
            <p class="mb-0 opacity-75 fs-6 text-white small">أنت الآن تقوم بتحديث بيانات الفئة: <strong>{{ $item->name_ar }}</strong></p>
        </div>
        <a href="{{ route('admin.primary-categories.index') }}" class="btn btn-outline-light px-4 fw-bold rounded-3"><i class="bi bi-arrow-right me-1"></i> العودة للقائمة</a>
    </div>

    <div class="p-4 p-lg-5">
        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
                <ul class="mb-0">@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
            </div>
        @endif

        <div class="form-section">
            <form action="{{ route('admin.primary-categories.update', $item->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('admin.primary_categories._form', ['item' => $item, 'parents' => $parents])
                <div class="mt-4 pt-3 border-top d-flex gap-2">
                    <button type="submit" class="submit-btn"><i class="bi bi-cloud-upload me-1"></i> تحديث البيانات</button>
                    <a href="{{ route('admin.primary-categories.index') }}" class="btn btn-outline-secondary px-4 py-2 fw-bold" style="border-radius:10px">إلغاء</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
