@extends('admin.layout')
@section('title', 'تعديل قسم: ' . $category->name)

@section('content')
<form action="{{ route('admin.blog.categories.update', $category->id) }}" method="POST">
    @method('PUT')
    <div class="card shadow-sm">
        <div class="card-header">
            <h4 class="mb-0">تعديل القسم</h4>
        </div>
        <div class="card-body">
            @include('admin.blog.categories._form')
        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
            <a href="{{ route('admin.blog.categories.index') }}" class="btn btn-secondary">إلغاء</a>
        </div>
    </div>
</form>
@endsection
