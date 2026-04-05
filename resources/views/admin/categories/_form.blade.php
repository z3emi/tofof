@php($item = $item ?? new \App\Models\Category())
@php($parentCategories = $parentCategories ?? \App\Models\Category::where('id', '!=', $item->id)->get())

<div class="row g-4">
    <div class="col-md-6">
        <label class="form-label fw-bold small text-muted">الاسم (بالعربي) <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-fonts text-brand"></i></span>
            <input type="text" name="name_ar" class="form-control border-start-0 @error('name_ar') is-invalid @enderror"
                   value="{{ old('name_ar', $item->name_ar) }}" required placeholder="مثلاً: روليكس">
        </div>
        @error('name_ar') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-bold small text-muted">الاسم (بالإنجليزي) <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-alphabet text-muted"></i></span>
            <input type="text" name="name_en" class="form-control border-start-0 text-start @error('name_en') is-invalid @enderror"
                   value="{{ old('name_en', $item->name_en) }}" required placeholder="e.g. Rolex or Casio">
        </div>
        @error('name_en') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-bold small text-muted">الرابط البديل (Slug)</label>
        <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
               value="{{ old('slug', $item->slug) }}" placeholder="اتركه فارغاً للتوليد التلقائي">
        @error('slug') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        <div class="form-text x-small mt-1 text-muted">يُستخدم في روابط المتجر (URL).</div>
    </div>

    <div class="col-md-3">
        <label class="form-label fw-bold small text-muted">البراند الأب (اختياري)</label>
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-diagram-3 text-muted"></i></span>
            <select name="parent_id" class="form-select border-start-0 @error('parent_id') is-invalid @enderror">
                <option value="">— براند رئيسي —</option>
                @foreach($parentCategories as $p)
                    <option value="{{ $p->id }}" @selected(old('parent_id', $item->parent_id) == $p->id)>
                        {{ $p->name_ar }}
                    </option>
                @endforeach
            </select>
        </div>
        @error('parent_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label fw-bold small text-muted">التسلسل (الترتيب)</label>
        <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror"
               value="{{ old('sort_order', $item->sort_order) }}" min="1" placeholder="اتركه فارغًا للتلقائي">
        @error('sort_order') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label fw-bold small text-muted d-block">الحالة</label>
        <input type="hidden" name="is_active" value="0">
        <div class="form-check form-switch pt-2">
            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                   @checked(old('is_active', $item->id ? $item->is_active : true))>
            <label class="form-check-label fw-bold text-dark" for="is_active">تفعيل البراند</label>
        </div>
        @error('is_active') <div class="invalid-feedback d-block text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-12">
        <label class="form-label fw-bold small text-muted">شعار البراند</label>
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-image text-muted"></i></span>
            <input type="file" name="image" id="brand_image_file" class="form-control border-start-0 @error('image') is-invalid @enderror" 
                   accept="image/*" onchange="previewBrandImg(this)">
        </div>
        @error('image') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

        <div class="mt-3 d-flex align-items-center gap-4 p-3 bg-light rounded-4 border" id="brand-image-preview-wrapper" 
             style="{{ $item->image ? '' : 'display:none !important' }}">
            <div class="position-relative shadow-sm" style="background:#fff; padding:10px; border-radius:15px; border:1px solid #eee;">
                <img id="brandPreview" src="{{ $item->image ? asset('storage/'.$item->image) : '' }}" 
                     alt="preview" style="height:80px; width:80px; object-fit:contain;">
            </div>
            <div>
                <div class="fw-bold small text-dark mb-1">{{ $item->image ? 'الشعار الحالي' : 'معاينة الشعار الجديد' }}</div>
                <div class="text-muted d-block small opacity-75" style="max-width: 300px;">
                    يفضل أن تكون الصورة بخلفية شفافة وبأبعاد مربعة (1:1) للحصول على أفضل النتائج.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function previewBrandImg(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) { 
                $('#brandPreview').attr('src', e.target.result);
                $('#brand-image-preview-wrapper').attr('style', 'display:flex !important');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

