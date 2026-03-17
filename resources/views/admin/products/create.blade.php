@extends('admin.layout')

@section('title', 'إضافة منتج جديد')

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="mb-0 fw-bold">
        <i class="bi bi-plus-circle text-primary me-2"></i> إضافة منتج جديد
    </h4>
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-right me-1"></i> العودة للقائمة
    </a>
</div>

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
    <strong>يرجى تصحيح الأخطاء التالية:</strong>
    <ul class="mb-0 mt-1">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @include('admin.products._form')
</form>

@endsection