@extends('admin.layout')
@section('title', 'كتابة مقال جديد')

@section('content')
<form action="{{ route('admin.blog.posts.store') }}" method="POST" enctype="multipart/form-data" id="post-form">
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    @include('admin.blog.posts._form')
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header"><h5 class="mb-0">نشر</h5></div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="is_published" name="is_published" value="1" @checked(old('is_published', true))>
                        <label class="form-check-label" for="is_published">نشر المقال فوراً</label>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary">حفظ المقال</button>
                    <a href="{{ route('admin.blog.posts.index') }}" class="btn btn-secondary">إلغاء</a>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
