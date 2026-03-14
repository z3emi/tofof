@extends('admin.layout')
@section('title', 'عرض المقال: ' . $post->title)

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">تفاصيل المقال</h4>
        <div>
            <a href="{{ route('admin.blog.posts.edit', $post->id) }}" class="btn btn-info btn-sm">تعديل</a>
            <a href="{{ route('admin.blog.posts.index') }}" class="btn btn-secondary btn-sm">العودة للمقالات</a>
        </div>
    </div>
    <div class="card-body">
        @if($post->image)
            <img src="{{ asset('storage/' . $post->image) }}" class="img-fluid rounded mb-4" alt="{{ $post->title }}">
        @endif
        <h1 class="card-title">{{ $post->title }}</h1>
        <div class="text-muted mb-3">
            <span><strong>القسم:</strong> {{ $post->category->name }}</span> | 
            <span><strong>الكاتب:</strong> {{ $post->author->name }}</span> | 
            <span><strong>تاريخ النشر:</strong> {{ $post->published_at ? $post->published_at->format('Y-m-d') : 'لم ينشر بعد' }}</span>
        </div>
        <hr>
        <div class="mt-4">
            {!! $post->body !!}
        </div>
    </div>
</div>
@endsection
