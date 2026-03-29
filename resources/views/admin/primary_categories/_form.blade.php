@php($item = $item ?? new \App\Models\PrimaryCategory())
@php($parents = $parents ?? \App\Models\PrimaryCategory::ordered()->where('id', '!=', $item->id)->get())

<div class="row g-4">
    <div class="col-md-6">
        <label class="form-label fw-bold small text-muted">الاسم (بالعربي) <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-fonts text-brand"></i></span>
            <input type="text" name="name_ar" class="form-control border-start-0 @error('name_ar') is-invalid @enderror"
                   value="{{ old('name_ar', $item->name_ar) }}" required placeholder="مثلاً: ساعات رجالية">
        </div>
        @error('name_ar') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-bold small text-muted">الاسم (بالإنجليزي)</label>
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-alphabet text-muted"></i></span>
            <input type="text" name="name_en" class="form-control border-start-0 text-start @error('name_en') is-invalid @enderror"
                   value="{{ old('name_en', $item->name_en) }}" placeholder="e.g. Men's Watches">
        </div>
        @error('name_en') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-bold small text-muted">الرابط البديل (Slug)</label>
        <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
               value="{{ old('slug', $item->slug) }}" placeholder="اتركه فارغاً للتوليد التلقائي">
        @error('slug') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        <div class="form-text x-small mt-1">يُستخدم في روابط المتجر (URL).</div>
    </div>

    <div class="col-md-3">
        <label class="form-label fw-bold small text-muted">الترتيب</label>
        <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror"
               value="{{ old('sort_order', $item->sort_order ?? 0) }}" min="0">
        @error('sort_order') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label fw-bold small text-muted d-block">الحالة</label>
        <input type="hidden" name="is_active" value="0">
        <div class="form-check form-switch pt-2">
            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                   @checked(old('is_active', $item->is_active ?? true))>
            <label class="form-check-label fw-bold text-dark" for="is_active">تفعيل الفئة</label>
        </div>
        @error('is_active') <div class="invalid-feedback d-block text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-bold small text-muted">الفئة الأب (اختياري)</label>
        <select name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
            <option value="">— فئة رئيسية —</option>
            @foreach($parents as $p)
                <option value="{{ $p->id }}" @selected(old('parent_id', $item->parent_id) == $p->id)>
                    {{ $p->name_ar }}
                </option>
            @endforeach
        </select>
        @error('parent_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-bold small text-muted">أيقونة / صورة الفئة</label>
        <input type="file" name="image_file" id="image_file" class="form-control @error('image_file') is-invalid @enderror" 
               accept=".png,.jpg,.jpeg,.gif,.webp" onchange="previewCategoryImg(this)">
        @error('image_file') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

        <div class="mt-3 d-flex align-items-center gap-3 p-3 bg-light rounded-4 border" id="image-preview-wrapper" 
             style="{{ $item->image ? '' : 'display:none !important' }}">
            <div class="position-relative">
                <img id="catPreview" src="{{ $item->image ? asset('storage/'.$item->image) : '' }}" 
                     alt="preview" style="height:70px; width:70px; object-fit:contain; background:#fff; padding:5px; border-radius:12px; border:1px solid #ddd;">
                @if($item->image)
                <div class="position-absolute top-0 start-0 translate-middle">
                    <div class="form-check p-0">
                        <input class="form-check-input ms-0 shadow-sm" type="checkbox" name="remove_image" id="remove_image" value="1" style="width:20px; height:20px;">
                    </div>
                </div>
                @endif
            </div>
            <div>
                <div class="fw-bold small text-dark">{{ $item->image ? 'الصورة الحالية' : 'معاينة الصورة الجديدة' }}</div>
                @if($item->image)
                <label class="text-danger small cursor-pointer" for="remove_image">تأشير للحذف</label>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    function previewCategoryImg(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) { 
                $('#catPreview').attr('src', e.target.result);
                $('#image-preview-wrapper').attr('style', 'display:flex !important');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

