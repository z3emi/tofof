@extends('admin.layout')

@section('title', 'إدارة مقالات المدونة')

@push('styles')
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem; color: white; border-radius: 0 !important; }
    .table-container { border-radius: 15px; border: 1px solid #f1f5f9; overflow: hidden; background: #fff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    .post-thumb { width:80px; height:50px; border-radius:8px; object-fit:cover; border:1px solid #eee; }
</style>
@endpush

@section('content')
<div class="form-card">
    <div class="form-card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2 class="mb-2 fw-bold text-white"><i class="bi bi-journal-text me-2"></i> إدارة محتوى المدونة</h2>
            <p class="mb-0 opacity-75 fs-6 text-white small">كتابة المقالات، القصص، وأحدث الأخبار لزيادة التفاعل مع العملاء.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.blog.posts.trash') }}" class="btn btn-outline-light px-4 fw-bold"><i class="bi bi-trash me-1"></i> المهملات</a>
            <a href="{{ route('admin.blog.posts.create') }}" class="btn btn-light px-4 fw-bold text-brand"><i class="bi bi-plus-circle me-1"></i> كتابة مقال</a>
        </div>
    </div>
    
    <div class="p-4 p-lg-5">
        <div class="table-container shadow-sm border overflow-hidden">
            <table class="table mb-0 align-middle text-center">
                <thead class="bg-light border-bottom">
                    <tr class="text-muted small fw-bold">
                        <th class="py-3" width="60">#</th>
                        <th class="py-3">الصورة</th>
                        <th class="py-3 text-start">عنوان المقال</th>
                        <th class="py-3">القسم</th>
                        <th class="py-3">الكاتب</th>
                        <th class="py-3">الحالة</th>
                        <th class="py-3">تاريخ النشر</th>
                        <th class="py-3" width="150">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($posts as $post)
                        <tr>
                            <td class="small text-muted">#{{ $post->id }}</td>
                            <td><img src="{{ $post->image ?: 'https://placehold.co/80x50?text=No+Img' }}" class="post-thumb"></td>
                            <td class="text-start fw-bold text-dark">{{ $post->title }}</td>
                            <td><span class="badge bg-light text-dark border px-2">{{ $post->category->name }}</span></td>
                            <td class="small">{{ $post->author->name }}</td>
                            <td>
                                @if($post->is_published) <span class="badge bg-success rounded-pill px-3 py-2">منشور</span>
                                @else <span class="badge bg-warning text-dark rounded-pill px-3 py-2">مسودة</span> @endif
                            </td>
                            <td class="small text-muted">{{ $post->published_at ? $post->published_at->format('Y-m-d') : '-' }}</td>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('admin.blog.posts.show', $post->id) }}" class="btn btn-sm btn-outline-primary rounded-3 px-2 py-1"><i class="bi bi-eye"></i></a>
                                    <a href="{{ route('admin.blog.posts.edit', $post->id) }}" class="btn btn-sm btn-outline-info rounded-3 px-2 py-1 text-dark"><i class="bi bi-pencil"></i></a>
                                    <form action="{{ route('admin.blog.posts.destroy', $post->id) }}" method="POST" onsubmit="return confirm('حذف المقال؟')">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-danger rounded-3 px-2 py-1"><i class="bi bi-trash"></i></button></form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-5 text-muted">لا توجد مقالات منشورة حالياً.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($posts->hasPages())
            <div class="mt-4 d-flex justify-content-center">{{ $posts->links() }}</div>
        @endif
    </div>
</div>
@endsection
