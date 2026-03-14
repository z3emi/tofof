@extends('admin.layout')

@section('title', 'تعديل فئة رئيسية ثانية')

@section('content')
<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">تعديل فئة: {{ $item->name_ar }}</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.primary-categories.update', $item) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            @include('admin.primary_categories._form', ['item' => $item, 'parents' => $parents])
            <div class="d-flex gap-2">
                <button class="btn btn-primary">تحديث</button>
                <a href="{{ route('admin.primary-categories.index') }}" class="btn btn-secondary">رجوع</a>
            </div>
        </form>
    </div>
</div>
@endsection
