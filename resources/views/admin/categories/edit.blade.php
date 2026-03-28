@extends('admin.layout')
@section('title', 'تعديل القسم')
@section('content')
<div class="card shadow-sm">
    <div class="card-header"><h5 class="mb-0">تعديل القسم: {{ $category->name_ar }}</h5></div>
    <div class="card-body">
        <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error) <p class="mb-0">{{ $error }}</p> @endforeach
                </div>
            @endif
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name_ar" class="form-label">اسم القسم (عربي) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name_ar" name="name_ar" value="{{ old('name_ar', $category->name_ar) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="name_en" class="form-label">اسم القسم (إنكليزي)</label>
                    <input type="text" class="form-control" id="name_en" name="name_en" value="{{ old('name_en', $category->name_en) }}">
                </div>
            </div>

            {{-- **THE CHANGE IS HERE**: Added the parent category dropdown --}}
            <div class="mb-3">
                <label for="parent_id" class="form-label">القسم الأب (اختياري)</label>
                <select class="form-select" id="parent_id" name="parent_id">
                    <option value="">-- قسم رئيسي --</option>
                    @foreach($parentCategories as $parent)
                        <option value="{{ $parent->id }}" {{ $category->parent_id == $parent->id ? 'selected' : '' }}>
                            {{ $parent->name_ar }} @if($parent->name_en) ({{ $parent->name_en }}) @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="image" class="form-label">صورة القسم الجديدة (اتركه فارغاً لعدم التغيير)</label>
                <input class="form-control" type="file" id="image" name="image">
                @if($category->image)
                    <img src="{{ asset('storage/' . $category->image) }}" width="100" class="mt-2 img-thumbnail">
                @endif
            </div>
            <button type="submit" class="btn btn-primary">تحديث</button>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">إلغاء</a>
        </form>
    </div>
</div>
@endsection
