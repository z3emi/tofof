{{-- resources/views/admin/categories/_subcategories.blade.php --}}
@foreach($children as $subcategory)
    <li class="tree-branch" x-data="{ open: false }">
        <div class="cat-card">
            <div class="cat-info">
                @php
                    $img = $subcategory->image ? asset('storage/' . $subcategory->image) : null;
                @endphp

                @if($img)
                    <img src="{{ $img }}" alt="{{ $subcategory->name_ar }}" class="cat-thumb">
                @else
                    <div class="thumb-fallback">No</div>
                @endif

                <div>
                    <h6 class="cat-title">{{ $subcategory->name_ar }}</h6>
                    <div class="chips">
                        <span class="chip chip-kind"><i class="bi bi-diagram-3"></i> قسم فرعي</span>
                        <span class="chip chip-count"><i class="bi bi-box-seam"></i> {{ $subcategory->total_products_count }} منتج</span>
                        <span class="chip chip-id">ID: {{ $subcategory->id }}</span>
                    </div>
                </div>
            </div>

            <div class="actions">
                @if($subcategory->children->isNotEmpty())
                    <button class="toggle-btn"
                            @click="open = !open"
                            :aria-expanded="open.toString()"
                            title="عرض/إخفاء الأقسام الفرعية">
                        <i class="bi bi-chevron-down toggle-icon"></i>
                    </button>
                @endif

                <a href="{{ route('admin.categories.edit', $subcategory->id) }}"
                   class="btn btn-sm btn-outline-info px-2" title="تعديل">
                    <i class="bi bi-pencil"></i>
                </a>

                <form action="{{ route('admin.categories.destroy', $subcategory->id) }}"
                      method="POST" class="d-inline-block"
                      onsubmit="return confirm('هل أنت متأكد؟')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger px-2" title="حذف">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            </div>
        </div>

        @if($subcategory->children->isNotEmpty())
            <div class="children-wrap" x-show="open" x-collapse>
                <ul class="list-unstyled mb-0">
                    @include('admin.categories._subcategories', ['children' => $subcategory->children])
                </ul>
            </div>
        @endif
    </li>
@endforeach
