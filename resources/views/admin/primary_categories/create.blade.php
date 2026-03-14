@extends('admin.layout')

@section('title', 'إضافة فئة رئيسية ثانية')

@section('content')
<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">إضافة فئة</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.primary-categories.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('admin.primary_categories._form', ['item' => $item, 'parents' => $parents])
            <div class="d-flex gap-2">
                <button class="btn btn-primary">حفظ</button>
                <a href="{{ route('admin.primary-categories.index') }}" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
