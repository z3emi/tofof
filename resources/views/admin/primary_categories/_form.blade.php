@php($item = $item ?? new \App\Models\PrimaryCategory())
@php($parents = $parents ?? \App\Models\PrimaryCategory::ordered()->get())
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">الاسم (AR) <span class="text-danger">*</span></label>
        <input type="text" name="name_ar" class="form-control @error('name_ar') is-invalid @enderror"
               value="{{ old('name_ar', $item->name_ar) }}" required>
        @error('name_ar') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">الاسم (EN)</label>
        <input type="text" name="name_en" class="form-control @error('name_en') is-invalid @enderror"
               value="{{ old('name_en', $item->name_en) }}">
        @error('name_en') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Slug</label>
        <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
               value="{{ old('slug', $item->slug) }}" placeholder="اتركه فارغ يُنشأ تلقائيًا">
        @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3 mb-3">
        <label class="form-label">الترتيب</label>
        <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror"
               value="{{ old('sort_order', $item->sort_order ?? 0) }}" min="0">
        @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- الحالة (نرسل 0 دومًا عند الإطفاء) --}}
    <div class="col-md-3 mb-3">
        <label class="form-label d-block">الحالة</label>
        <input type="hidden" name="is_active" value="0">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                   @checked(old('is_active', $item->is_active ?? true))>
            <label class="form-check-label" for="is_active">فعّال</label>
        </div>
        @error('is_active') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    {{-- اختيار الفئة الأب (اختياري) --}}
    <div class="col-md-6 mb-3">
        <label class="form-label">الفئة الأب (اختياري)</label>
        <select name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
            <option value="">— بدون أب —</option>
            @foreach($parents as $p)
                <option value="{{ $p->id }}" @selected(old('parent_id', $item->parent_id) == $p->id)>
                    {{ $p->name_ar }}
                </option>
            @endforeach
        </select>
        @error('parent_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- الصورة --}}
    <div class="col-md-6 mb-3">
        <label class="form-label">الصورة (PNG/JPG/WEBP)</label>
        <input type="file" name="image_file" class="form-control @error('image_file') is-invalid @enderror" accept=".png,.jpg,.jpeg,.gif,.webp">
        @error('image_file') <div class="invalid-feedback">{{ $message }}</div> @enderror

        @if($item->image)
            <div class="mt-2 d-flex align-items-center gap-3">
                <img src="{{ asset('storage/'.$item->image) }}" alt="image" style="height:60px;object-fit:contain;border:1px dashed #ddd;padding:4px;border-radius:8px;">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remove_image" id="remove_image" value="1">
                    <label class="form-check-label" for="remove_image">حذف الصورة الحالية</label>
                </div>
            </div>
        @endif
    </div>
</div>
