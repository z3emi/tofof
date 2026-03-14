@extends('admin.layout')
@section('title', 'سلة محذوفات الأقسام')

@push('styles')
<style>
  :root{
    --brand:#cd8985;
    --line:#eadbcd;
  }
  .card-header{ background:#fff; border-bottom:1px solid var(--line); }
  .table-responsive { min-height: 300px; }
  .cat-thumb{ width:48px; height:48px; border-radius:10px; object-fit:cover; border:1px solid #eee; background:#fff; }
</style>
@endpush

@section('content')
<div class="card shadow-sm">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h4 class="mb-0 text-danger"><i class="bi bi-trash"></i> سلة محذوفات الأقسام</h4>
    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-right me-1"></i> العودة للأقسام
    </a>
  </div>

  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered text-center align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>الصورة</th>
            <th>اسم القسم</th>
            <th>تاريخ الحذف</th>
            <th>العمليات</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($categories as $cat)
            <tr>
              <td>{{ $cat->id }}</td>
              <td>
                @if($cat->image)
                  <img src="{{ asset('storage/' . $cat->image) }}" alt="{{ $cat->name_ar }}" class="cat-thumb">
                @else
                  <div class="thumb-fallback text-muted">No Img</div>
                @endif
              </td>
              <td class="text-start fw-bold">{{ $cat->name_ar }}</td>
              <td>{{ $cat->deleted_at->format('Y-m-d H:i') }}</td>
              <td>
                @can('edit-categories')
                <form action="{{ route('admin.categories.restore', $cat->id) }}" method="POST" class="d-inline-block">
                  @csrf
                  <button type="submit" class="btn btn-sm btn-success m-1" title="استرجاع">
                    <i class="bi bi-arrow-counterclockwise"></i> استرجاع
                  </button>
                </form>
                @endcan

                @can('edit-categories')
                <form action="{{ route('admin.categories.forceDelete', $cat->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('تنبيه: سيتم مسح القسم نهائياً ولا يمكن التراجع. هل أنت متأكد؟')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger m-1" title="حذف نهائي">
                    <i class="bi bi-trash-fill"></i> حذف نهائي
                  </button>
                </form>
                @endcan
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
      {{ $categories->links() }}
    </div>
  </div>
</div>
@endsection
