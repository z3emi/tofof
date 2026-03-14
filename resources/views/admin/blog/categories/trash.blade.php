@extends('admin.layout')
@section('title', 'سلة محذوفات أقسام المدونة')

@push('styles')
<style>
  :root{ --brand:#cd8985; --line:#eadbcd; }
  .card-header{ background:#fff; border-bottom:1px solid var(--line); }
</style>
@endpush

@section('content')
<div class="card shadow-sm">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h4 class="mb-0 text-danger"><i class="bi bi-trash"></i> سلة محذوفات أقسام المدونة</h4>
    <a href="{{ route('admin.blog.categories.index') }}" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-right me-1"></i> العودة للأقسام
    </a>
  </div>

  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered text-center align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>اسم القسم</th>
            <th>تاريخ الحذف</th>
            <th>العمليات</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($categories as $category)
            <tr>
              <td>{{ $category->id }}</td>
              <td class="text-start fw-bold">{{ $category->name }}</td>
              <td>{{ $category->deleted_at->format('Y-m-d H:i') }}</td>
              <td>
                <form action="{{ route('admin.blog.categories.restore', $category->id) }}" method="POST" class="d-inline-block">
                  @csrf
                  <button type="submit" class="btn btn-sm btn-success m-1" title="استرجاع">
                    <i class="bi bi-arrow-counterclockwise"></i> استرجاع
                  </button>
                </form>

                <form action="{{ route('admin.blog.categories.forceDelete', $category->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('تنبيه: سيتم مسح القسم نهائياً ولا يمكن التراجع. هل أنت متأكد؟')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger m-1" title="حذف نهائي">
                    <i class="bi bi-trash-fill"></i> حذف نهائي
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="py-4 text-muted">سلة المحذوفات فارغة.</td>
            </tr>
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
