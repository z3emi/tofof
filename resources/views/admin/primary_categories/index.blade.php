@extends('admin.layout')
@section('title', 'شجرة الفئات')

@push('styles')
<style>
  :root{
    --brand:#cd8985;
    --brand-dark:#be6661;
    --line:#eadbcd;
    --soft:#f9f5f1;
    --border:#e9ecef;
  }
  .card-header{ background:#fff; border-bottom:1px solid var(--line); }

  .filters-bar{
    background:#fff; border:1px solid var(--line); border-radius:.65rem;
    padding:10px; box-shadow:0 .25rem .5rem rgba(0,0,0,.03); margin-bottom:1rem;
  }
  .filters-bar .form-control,.filters-bar .form-select{ height:38px; padding:.3rem .6rem; }
  .filters-bar .btn{ padding:.35rem .75rem; }

  .category-tree{ position:relative; }

  .cat-card{
    background:#fff; border:1px solid var(--line); border-right:4px solid var(--brand);
    border-radius:.65rem; padding:.75rem 1rem;
    display:flex; align-items:center; justify-content:space-between; gap:1rem;
    transition: background .15s ease, box-shadow .15s ease, transform .15s ease;
  }
  .cat-card:hover{ background:#fffaf8; box-shadow:0 4px 12px rgba(0,0,0,.04); transform: translateY(-1px); }

  .cat-info{ display:flex; align-items:center; gap:1rem; }
  .cat-thumb{ width:48px; height:48px; border-radius:10px; object-fit:cover; border:1px solid #eee; background:#fff; }
  .thumb-fallback{ width:48px; height:48px; border-radius:10px; background:#fff; display:flex; align-items:center; justify-content:center; border:1px solid #eee; color:var(--brand); }

  .chips{ display:flex; align-items:center; gap:.35rem; flex-wrap:wrap; }
  .chip{ display:inline-flex; align-items:center; gap:.35rem; background:#fff; border:1px dashed #dcdcdc; border-radius:999px; padding:.2rem .55rem; }
  .chip-kind{ background:var(--line); border-color:var(--line); }
  .chip-count{ background:var(--soft); border-color:var(--soft); }

  .actions{ display:flex; gap:.35rem; align-items:center; }
  .btn-icon{ display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; border-radius:8px; padding:0; }
  .toggle-btn{ background:var(--soft); border:1px solid var(--line); color:var(--brand); width:34px; height:34px; border-radius:8px; display:flex; align-items:center; justify-content:center; transition:.15s; }
  .toggle-btn:hover{ background:#ffeceb; border-color:#ffd7d5; color:var(--brand-dark); }
  .toggle-icon{ transition: transform .18s; }
  [aria-expanded="true"] .toggle-icon{ transform: rotate(180deg); }

  .children-wrap{ border-right:2px solid var(--line); margin-right:1.25rem; padding-right:1rem; margin-top:.75rem; }

  @media (max-width: 575px){
    .cat-card{ flex-direction: column; align-items: stretch; }
    .actions{ justify-content:flex-end }
  }

  html[dir="rtl"] .filters-bar .form-select{ padding-right: .6rem; padding-left: 2.25rem; background-position: left .65rem center; background-size: 12px 12px; }
  html[dir="ltr"] .filters-bar .form-select{ padding-left: .6rem; padding-right: 2.25rem; background-position: right .65rem center; background-size: 12px 12px; }

  .pagination .page-item .page-link{ color:var(--brand); border:1px solid var(--line); background:#fff; }
  .pagination .page-item.active .page-link{ color:#fff; background:var(--brand); border-color:var(--brand); }
  .pagination .page-item .page-link:hover{ color:#fff; background:var(--brand-dark); border-color:var(--brand-dark); }
  .pagination .page-item.disabled .page-link{ color:#ccc; background:#f9f9f9; border-color:var(--line); }
</style>
@endpush

@section('content')
@php
  // يدعم أي اسم متغيّر يجي من الكنترولر
  $roots = ($items ?? null) ?: (($primaryCategories ?? null) ?: ($categories ?? collect()));

  // 🔽 إضافة ترتيب للجذور فقط (بدون حذف أي سطر منطق قديم)
  $rootsSorted = ($roots instanceof \Illuminate\Support\Collection)
      ? $roots->sortBy([['sort_order','asc'], ['name_ar','asc'], ['id','asc']])->values()
      : $roots;
@endphp

<div class="card shadow-sm">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h4 class="mb-0">شجرة الفئات</h4>

    <div class="d-flex gap-2">
      <a href="{{ route('admin.primary-categories.export') }}" class="btn btn-sm btn-success" title="تصدير Excel" aria-label="تصدير Excel">
        <i class="bi bi-file-earmark-excel"></i>
      </a>
      <a href="{{ route('admin.primary-categories.trash') }}" class="btn btn-outline-danger btn-sm">
        <i class="bi bi-trash me-1"></i> سلة المحذوفات
      </a>
      @can('create-primary-categories')
      <a href="{{ route('admin.primary-categories.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle me-1"></i> إضافة فئة
      </a>
      @endcan
    </div>
  </div>

  <div class="card-body">
    <form method="GET" action="{{ route('admin.primary-categories.index') }}" class="row g-2 filters-bar">
      <div class="col">
        <input type="text" name="search" class="form-control" placeholder="ابحث بالاسم..." value="{{ request('search') }}">
      </div>
      <div class="col-auto">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-search me-1"></i> بحث
        </button>
      </div>
      @if(request()->filled('search'))
      <div class="col-auto">
        <a href="{{ route('admin.primary-categories.index') }}" class="btn btn-outline-secondary">مسح</a>
      </div>
      @endif
    </form>

    <div class="category-tree" x-data>
      @forelse ($rootsSorted as $node)
        <div class="mb-3" x-data="{ open: false }">
          <div class="cat-card">
            <div class="cat-info">
              @php $img = $node->image ? asset('storage/' . $node->image) : null; @endphp
              @if($img)
                <img src="{{ $img }}" alt="{{ $node->name_ar }}" class="cat-thumb">
              @else
                <div class="thumb-fallback">No</div>
              @endif

              <div>
                <h6 class="cat-title">{{ $node->name_ar }}</h6>
                <div class="chips">
                  <span class="chip chip-kind"><i class="bi bi-diagram-3"></i> فئة رئيسية</span>
                  <span class="chip chip-count"><i class="bi bi-box-seam"></i> {{ $node->products_count ?? (method_exists($node,'products') ? $node->products()->count() : 0) }} منتج</span>
                  <span class="chip">ID: {{ $node->id }}</span>
                </div>
              </div>
            </div>

            <div class="actions">
              @if($node->children && $node->children->isNotEmpty())
                <button class="toggle-btn" @click="open = !open" :aria-expanded="open.toString()" title="عرض/إخفاء الفروع">
                  <i class="bi bi-chevron-down toggle-icon"></i>
                </button>
              @endif

              @can('edit-primary-categories')
              <a href="{{ route('admin.primary-categories.edit', $node) }}" class="btn btn-sm btn-outline-primary btn-icon" data-bs-toggle="tooltip" title="تعديل">
                <i class="bi bi-pencil"></i>
              </a>
              @endcan

              @can('delete-primary-categories')
              <form action="{{ route('admin.primary-categories.destroy', $node) }}" method="POST" class="d-inline-block" onsubmit="return confirm('حذف الفئة وكل فروعها؟')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger btn-icon" data-bs-toggle="tooltip" title="حذف">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
              @endcan
            </div>
          </div>

          @if($node->children && $node->children->isNotEmpty())
            <div class="children-wrap" x-show="open" x-collapse>
              <ul class="list-unstyled mb-0">
                @include('admin.primary_categories._subcategories', ['children' => $node->children])
              </ul>
            </div>
          @endif
        </div>
      @empty
        <div class="text-center py-4">
          <p class="mb-0 text-muted">لا توجد فئات لعرضها.</p>
        </div>
      @endforelse
    </div>

    @if($roots instanceof \Illuminate\Contracts\Pagination\Paginator)
      <div class="d-flex justify-content-end mt-3">
        {{ $roots->links() }}
      </div>
    @endif
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
  const triggers = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  triggers.forEach(function (el) { new bootstrap.Tooltip(el); });
});
</script>
@endpush
