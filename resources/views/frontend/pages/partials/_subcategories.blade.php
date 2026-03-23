<li class="subcategory-item" x-data="{ open: false }">
  <div class="cat-card cat-card--lvl{{ $level }} group">
    <a href="{{ route('shop', ['category' => $subcategory->slug]) }}" class="cat-info no-underline">
      @php $img = $subcategory->image ? asset('storage/' . $subcategory->image) : null; @endphp
      @if($img)
        <img src="{{ $img }}" alt="{{ $subcategory->name_translated }}" class="cat-thumb">
      @else
        <div class="thumb-fallback">🧴</div>
      @endif
      <div class="min-w-0">
        <h6 class="cat-title truncate">{{ $subcategory->name_translated }}</h6>
        <div class="chips">
          @if($level == 2)
            <span class="chip chip-kind chip-kind--sub"><i class="bi bi-diagram-3"></i> {{ __('shop.sub') }}</span>
          @endif
          <span class="chip chip-count"><i class="bi bi-box-seam"></i> {{ __('shop.products_count', ['count' => $subcategory->total_products_count]) }}</span>
        </div>
      </div>
    </a>
    <div class="actions">
      @if($subcategory->children->isNotEmpty())
        <button type="button" class="toggle-btn"
                @click="open=!open" :aria-expanded="open.toString()" aria-label="عرض/إخفاء الأقسام الفرعية">
          <i class="bi bi-chevron-down toggle-icon" :class="open ? 'rotate' : ''"></i>
        </button>
      @endif
    </div>
  </div>
  
  @if($subcategory->children->isNotEmpty())
    <div class="children-container children-container--lvl{{ $level + 1 }}" x-show="open" x-transition.opacity x-transition.duration.200ms style="display:none">
      <ul class="children-list">
        @foreach($subcategory->children as $child)
          @include('frontend.pages.partials._subcategory_item', ['subcategory' => $child, 'level' => $level + 1])
        @endforeach
      </ul>
    </div>
  @endif
</li>