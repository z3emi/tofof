@extends('admin.layout')
@section('title', 'سلة محذوفات المقالات')

@push('styles')
<style>
  :root{ --brand:#cd8985; --line:#eadbcd; }
  .card-header{ background:#fff; border-bottom:1px solid var(--line); }
</style>
@endpush

@section('content')
<div class="card shadow-sm">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h4 class="mb-0 text-danger"><i class="bi bi-trash"></i> سلة محذوفات المقالات</h4>
    <a href="{{ route('admin.blog.posts.index') }}" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-right me-1"></i> العودة للمقالات
    </a>
  </div>

  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered text-center align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>الصورة</th>
            <th>العنوان</th>
            <th>تاريخ الحذف</th>
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
              <td class="text-start fw-bold">{{ $post->title }}</td>
              <td>{{ $post->deleted_at->format('Y-m-d H:i') }}</td>
              <td>
                <form action="{{ route('admin.blog.posts.restore', $post->id) }}" method="POST" class="d-inline-block">
                  @csrf
                  <button type="submit" class="btn btn-sm btn-success m-1" title="استرجاع">
                    <i class="bi bi-arrow-counterclockwise"></i> استرجاع
                  </button>
                </form>

                <form action="{{ route('admin.blog.posts.forceDelete', $post->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('تنبيه: سيتم مسح المقال نهائياً ولا يمكن التراجع. هل أنت متأكد؟')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger m-1" title="حذف نهائي">
                    <i class="bi bi-trash-fill"></i> حذف نهائي
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="py-4 text-muted">سلة المحذوفات فارغة.</td>
            </tr>
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
