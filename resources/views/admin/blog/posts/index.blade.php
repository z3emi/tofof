@extends('admin.layout')
@section('title', 'إدارة المقالات')

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h4 class="mb-0">جميع المقالات</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.blog.posts.trash') }}" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-trash me-1"></i> سلة المحذوفات
            </a>
            <a href="{{ route('admin.blog.posts.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i> كتابة مقال جديد
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>الصورة</th>
                        <th>العنوان</th>
                        <th>القسم</th>
                        <th>الكاتب</th>
                        <th>الحالة</th>
                        <th>تاريخ النشر</th>
                        <th>العمليات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($posts as $post)
                        <tr>
                            <td>{{ $post->id }}</td>
                            <td>
                                <img src="{{ $post->image ? asset('storage/' . $post->image) : 'https://placehold.co/100x60' }}" alt="{{ $post->title }}" width="100" class="img-thumbnail">
                            </td>
                            <td class="text-start">{{ $post->title }}</td>
                            <td>{{ $post->category->name }}</td>
                            <td>{{ $post->author->name }}</td>
                            <td>
                                @if($post->is_published)
                                    <span class="badge bg-success">منشور</span>
                                @else
                                    <span class="badge bg-warning text-dark">مسودة</span>
                                @endif
                            </td>
                            <td>{{ $post->published_at ? $post->published_at->format('Y-m-d') : '-' }}</td>
                            <td>
                                <a href="{{ route('admin.blog.posts.show', $post->id) }}" class="btn btn-sm btn-outline-primary m-1 px-2" title="عرض">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.blog.posts.edit', $post->id) }}" class="btn btn-sm btn-outline-info m-1 px-2" title="تعديل">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.blog.posts.destroy', $post->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا المقال؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger m-1 px-2" title="حذف">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8">لا توجد مقالات لعرضها.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $posts->links() }}
        </div>
    </div>
</div>
@endsection
