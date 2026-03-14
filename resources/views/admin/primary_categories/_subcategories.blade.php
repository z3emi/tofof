@php
  // 🔽 ترتيب الأفرع (بدون حذف أي شيء)
  $list = ($children instanceof \Illuminate\Support\Collection)
      ? $children->sortBy([['sort_order','asc'], ['name_ar','asc'], ['id','asc']])->values()
      : $children;
@endphp

@foreach($list as $subcategory)
  <li class="mb-3" x-data="{ open: false }">
    <div class="cat-card">
      <div class="cat-info">
        @php $img = $subcategory->image ? asset('storage/' . $subcategory->image) : null; @endphp
        @if($img)
          <img src="{{ $img }}" alt="{{ $subcategory->name_ar }}" class="cat-thumb">
        @else
          <div class="thumb-fallback">No</div>
        @endif

        <div>
          <h6 class="cat-title">{{ $subcategory->name_ar }}</h6>
          <div class="chips">
            <span class="chip chip-kind"><i class="bi bi-diagram-3"></i> فئة فرعية</span>
            <span class="chip chip-count"><i class="bi bi-box-seam"></i> {{ $subcategory->products_count ?? (method_exists($subcategory,'products') ? $subcategory->products()->count() : 0) }} منتج</span>
            <span class="chip">ID: {{ $subcategory->id }}</span>
          </div>
        </div>
      </div>

      <div class="actions">
        @if($subcategory->children && $subcategory->children->isNotEmpty())
          <button class="toggle-btn" @click="open = !open" :aria-expanded="open.toString()" title="عرض/إخفاء الفروع">
            <i class="bi bi-chevron-down toggle-icon"></i>
          </button>
        @endif

        @can('edit-primary-categories')
        <a href="{{ route('admin.primary-categories.edit', $subcategory) }}"
           class="btn btn-sm btn-outline-primary btn-icon" data-bs-toggle="tooltip" title="تعديل">
          <i class="bi bi-pencil"></i>
        </a>
        @endcan

        @can('delete-primary-categories')
        <form action="{{ route('admin.primary-categories.destroy', $subcategory) }}"
              method="POST" class="d-inline-block"
              onsubmit="return confirm('حذف هذه الفئة؟')">
          @csrf @method('DELETE')
          <button type="submit" class="btn btn-sm btn-outline-danger btn-icon" data-bs-toggle="tooltip" title="حذف">
            <i class="bi bi-trash"></i>
          </button>
        </form>
        @endcan
      </div>
    </div>

    @if($subcategory->children && $subcategory->children->isNotEmpty())
      <div class="children-wrap" x-show="open" x-collapse>
        <ul class="list-unstyled mb-0">
          @include('admin.primary_categories._subcategories', ['children' => $subcategory->children])
        </ul>
      </div>
    @endif
  </li>
@endforeach
