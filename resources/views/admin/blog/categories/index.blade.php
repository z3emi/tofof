@extends('admin.layout')
@section('title', 'أقسام المدونة')

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h4 class="mb-0">أقسام المدونة</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.blog.categories.trash') }}" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-trash me-1"></i> سلة المحذوفات
            </a>
            <a href="{{ route('admin.blog.categories.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i> إضافة قسم جديد
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>اسم القسم</th>
                        <th>عدد المقالات</th>
                        <th>العمليات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td>{{ $category->id }}</td>
                            <td>{{ $category->name }}</td>
                            <td><span class="badge bg-secondary">{{ $category->posts_count }}</span></td>
                            <td>
                                <a href="{{ route('admin.blog.categories.edit', $category->id) }}" class="btn btn-sm btn-outline-info m-1 px-2" title="تعديل">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.blog.categories.destroy', $category->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا القسم؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger m-1 px-2" title="حذف">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4">لا توجد أقسام لعرضها.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $categories->links() }}
        </div>
    </div>
</div>
@endsection
