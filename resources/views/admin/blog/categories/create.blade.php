@extends('admin.layout')
@section('title', 'إضافة قسم جديد للمدونة')

@section('content')
<form action="{{ route('admin.blog.categories.store') }}" method="POST">
    <div class="card shadow-sm">
        <div class="card-header">
            <h4 class="mb-0">إضافة قسم جديد</h4>
        </div>
        <div class="card-body">
            @include('admin.blog.categories._form')
        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary">حفظ القسم</button>
            <a href="{{ route('admin.blog.categories.index') }}" class="btn btn-secondary">إلغاء</a>
        </div>
    </div>
</form>
@endsection
