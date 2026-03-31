{{--
    ملف partial مشترك بين create وedit
    لا يحتوي على @extends ولا @section ولا <form> — هذه مسؤولية الصفحة المضيفة
--}}

{{-- ══════════════════════════════════════════
     الصف الأول: المعلومات الأساسية + التصنيف
══════════════════════════════════════════ --}}
<div class="row g-4 mb-4">

    {{-- بطاقة: المعلومات الأساسية --}}
    <div class="col-lg-8">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white d-flex align-items-center gap-2 fw-semibold">
                <i class="bi bi-pencil-square text-primary"></i> المعلومات الأساسية
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name_ar" class="form-label">اسم المنتج (عربي) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name_ar') is-invalid @enderror"
                               id="name_ar" name="name_ar"
                               value="{{ old('name_ar', $product->name_ar ?? '') }}" required>
                        @error('name_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="name_en" class="form-label">Product Name (English)</label>
                        <input type="text" class="form-control @error('name_en') is-invalid @enderror"
                               id="name_en" name="name_en"
                               value="{{ old('name_en', $product->name_en ?? '') }}">
                        @error('name_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label for="description_ar" class="form-label">الوصف (عربي) <span class="text-danger">*</span></label>
                        <textarea class="form-control rich-editor @error('description_ar') is-invalid @enderror"
                                  id="description_ar" name="description_ar" rows="5" required>{{ old('description_ar', $product->description_ar ?? '') }}</textarea>
                        @error('description_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label for="description_en" class="form-label">Description (English)</label>
                        <textarea class="form-control rich-editor @error('description_en') is-invalid @enderror"
                                  id="description_en" name="description_en" rows="5">{{ old('description_en', $product->description_en ?? '') }}</textarea>
                        @error('description_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- بطاقة: التصنيف والحالة --}}
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white d-flex align-items-center gap-2 fw-semibold">
                <i class="bi bi-tags text-success"></i> التصنيف والحالة
            </div>
            <div class="card-body">

                {{-- SKU --}}
                <div class="mb-3">
                    <label for="sku" class="form-label">رمز المنتج (SKU) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('sku') is-invalid @enderror"
                           id="sku" name="sku"
                           value="{{ old('sku', $product->sku ?? '') }}" required>
                    @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- البراند --}}
                <div class="mb-3">
                    <label for="category_id" class="form-label">البراند <span class="text-danger">*</span></label>
                    <select class="form-select @error('category_id') is-invalid @enderror"
                            id="category_id" name="category_id" required>
                        <option value="">-- اختر البراند --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}"
                                @selected(old('category_id', $product->category_id ?? '') == $category->id)>
                                {{ $category->name_ar }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- الفئة الرئيسية --}}
                @php
                    use App\Models\PrimaryCategory;
                    $rootPrimaryCategories = $rootPrimaryCategories
                        ?? PrimaryCategory::active()->whereNull('parent_id')->ordered()->get();

                    $selectedPrimary = old(
                        'primary_category_id',
                        (isset($product) && method_exists($product,'primaryCategories') && $product->primaryCategories()->exists())
                            ? $product->primaryCategories()->first()->id
                            : ''
                    );
                    $preselectedParentId = '';
                    $preselectedChildId  = '';
                    if ($selectedPrimary) {
                        $selCat = PrimaryCategory::find($selectedPrimary);
                        if ($selCat) {
                            if ($selCat->parent_id) {
                                $preselectedParentId = $selCat->parent_id;
                                $preselectedChildId  = $selCat->id;
                            } else {
                                $preselectedParentId = $selCat->id;
                            }
                        }
                    }
                @endphp

                <div class="mb-3">
                    <label class="form-label">الفئة الرئيسية</label>
                    <select id="pc_parent" class="form-select">
                        <option value="">-- اختر الفئة الرئيسية --</option>
                        @foreach($rootPrimaryCategories as $root)
                            <option value="{{ $root->id }}" @selected($preselectedParentId == $root->id)>
                                {{ $root->name_ar }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">الفئة الفرعية</label>
                    <select id="pc_child" class="form-select" @if(!$preselectedParentId) disabled @endif></select>
                    <small class="text-muted">إذا لا توجد فئات فرعية، تُعتمد الفئة الرئيسية تلقائيًا.</small>
                    @error('primary_category_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <input type="hidden" name="primary_category_id" id="primary_category_id" value="{{ $selectedPrimary }}">

                <hr class="my-3">

                {{-- حالة المنتج --}}
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch"
                           id="is_active" name="is_active" value="1"
                           @checked(old('is_active', $product->is_active ?? true))>
                    <label class="form-check-label" for="is_active">المنتج فعال</label>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════
     الصف الثاني: الصور + الأسعار
══════════════════════════════════════════ --}}
<div class="row g-4 mb-4">

    {{-- بطاقة: الصور --}}
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex align-items-center gap-2 fw-semibold">
                <i class="bi bi-images text-warning"></i> صور المنتج
            </div>
            <div class="card-body">

                <div class="mb-3">
                    <label for="images" class="form-label">
                        رفع صور جديدة
                        @if(!isset($product))<span class="text-danger">*</span>@endif
                        <small class="text-muted">(يمكن اختيار أكثر من صورة)</small>
                    </label>
                    <input type="file"
                           class="form-control @error('images.*') is-invalid @enderror"
                           id="images" name="images[]" multiple
                           accept="image/*"
                           @if(!isset($product)) required @endif>
                    @error('images.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- معاينة الصور الجديدة --}}
                <div id="new-images-preview" class="d-flex flex-wrap gap-2 mb-3"></div>

                @if(isset($product) && $product->images->isNotEmpty())
                <div>
                    <label class="form-label text-muted small mb-1">الصور الحالية</label>
                    <div class="d-flex flex-wrap gap-2 border rounded p-2" id="image-gallery">
                        @foreach($product->images as $image)
                            <div class="position-relative" id="image-container-{{ $image->id }}">
                                <img src="{{ asset('storage/' . $image->image_path) }}"
                                     class="img-thumbnail rounded"
                                     style="width:90px;height:90px;object-fit:cover;"
                                     alt="">
                                <button type="button"
                                        class="btn btn-danger btn-sm rounded-circle position-absolute top-0 end-0 delete-image-btn p-0"
                                        data-image-id="{{ $image->id }}"
                                        style="width:22px;height:22px;font-size:11px;transform:translate(40%,-40%);">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>

    {{-- بطاقة: الأسعار --}}
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white d-flex align-items-center gap-2 fw-semibold">
                <i class="bi bi-cash-coin text-danger"></i> الأسعار والمخزون
            </div>
            <div class="card-body">

                <div class="mb-3">
                    <label for="price" class="form-label">سعر البيع <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('price') is-invalid @enderror"
                           id="price" name="price"
                           value="{{ old('price', $product->price ?? '') }}"
                           required step="any" min="0">
                    @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="sale_price" class="form-label">
                        سعر الخصم <small class="text-muted">(اختياري)</small>
                    </label>
                    <input type="number" class="form-control @error('sale_price') is-invalid @enderror"
                           id="sale_price" name="sale_price"
                           value="{{ old('sale_price', $product->sale_price ?? '') }}"
                           step="any" min="0">
                    @error('sale_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="sale_starts_at" class="form-label">بداية الخصم</label>
                    <input type="datetime-local"
                           class="form-control @error('sale_starts_at') is-invalid @enderror"
                           id="sale_starts_at" name="sale_starts_at"
                           value="{{ old('sale_starts_at', isset($product) && $product->sale_starts_at ? $product->sale_starts_at->format('Y-m-d\TH:i') : '') }}">
                    @error('sale_starts_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="sale_ends_at" class="form-label">نهاية الخصم</label>
                    <input type="datetime-local"
                           class="form-control @error('sale_ends_at') is-invalid @enderror"
                           id="sale_ends_at" name="sale_ends_at"
                           value="{{ old('sale_ends_at', isset($product) && $product->sale_ends_at ? $product->sale_ends_at->format('Y-m-d\TH:i') : '') }}">
                    @error('sale_ends_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <hr class="my-3">

                <div class="mb-3">
                    <label for="stock_quantity" class="form-label">الكمية المتاحة (المخزون) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-box-seam"></i></span>
                        <input type="number" class="form-control @error('stock_quantity') is-invalid @enderror"
                               id="stock_quantity" name="stock_quantity"
                               value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}"
                               required min="0">
                    </div>
                    @error('stock_quantity')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════
     الصف الثالث: الخيارات والتركيبات (عرض كامل)
══════════════════════════════════════════ --}}
<div class="row mb-4">
    <div class="col-12">
        @include('admin.products._options_builder')
    </div>
</div>

{{-- ══════════════════════════════════════════
     أزرار الحفظ
══════════════════════════════════════════ --}}
<div class="d-flex gap-2 justify-content-end">
    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary px-4">
        <i class="bi bi-x-lg me-1"></i> إلغاء
    </a>
    <button type="submit" class="btn btn-primary px-4">
        <i class="bi bi-save me-1"></i> حفظ المنتج
    </button>
</div>

@push('styles')
{{-- Summernote CSS --}}
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<style>
    /* Force Summernote Editor to LTR */
    .note-editor.note-frame { 
        border: 1px solid #dee2e6; 
        border-radius: 8px; 
        overflow: hidden; 
        background: #fff; 
        direction: ltr !important; 
        text-align: left !important; 
    }
    
    /* Ensure all internal elements follow LTR */
    .note-editor.note-frame * {
        direction: ltr !important;
        text-align: left !important;
    }

    .note-btn:not(.note-color-btn) { 
        background: #fff !important; 
        border: 1px solid #eee !important; 
        color: #333 !important; 
    }
    .note-btn:hover { background: #f8f9fa !important; }
    .note-modal-content { border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    
    /* FIX: Summernote Color Palette issues */
    .note-color-palette {
        width: 172px !important;
        padding: 5px !important;
    }
    .note-color-row {
        height: 20px !important;
        display: flex !important;
        margin-bottom: 2px !important;
    }
    .note-color-btn {
        width: 18px !important;
        height: 18px !important;
        padding: 0 !important;
        margin: 1px !important;
        border: 1px solid #e0e0e0 !important;
        display: inline-block !important;
        cursor: pointer !important;
        /* Do NOT set background-color here with !important as it will override the inline color */
    }
    
    /* Custom Styling for the palette labels */
    .note-color-palette .note-color-reset, 
    .note-color-palette .note-color-select {
        padding: 6px !important;
        margin: 5px 0 !important;
        width: 100% !important;
        text-align: center !important;
        background: #f8f9fa !important;
        border: 1px solid #eee !important;
        border-radius: 4px !important;
        font-size: 11px !important;
        color: #333 !important;
        cursor: pointer !important;
    }
    .note-color-palette .note-color-reset:hover, 
    .note-color-palette .note-color-select:hover {
        background: #e9ecef !important;
    }

    /* Fix for toolbar button layout in RTL page */
    .note-toolbar {
        background: #f8f9fa !important;
        border-bottom: 1px solid #eee !important;
        display: flex !important;
        flex-wrap: wrap !important;
        justify-content: flex-start !important;
    }
    
    .note-btn-group {
        margin-right: 5px !important;
        margin-left: 0 !important;
    }
</style>
@endpush

@push('scripts')
{{-- jQuery and Summernote JS --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

{{-- الفئة الرئيسية: تحميل الفئات الفرعية ديناميكيًا --}}
<script>
(function () {
    const parentSel = document.getElementById('pc_parent');
    const childSel  = document.getElementById('pc_child');
    const finalInp  = document.getElementById('primary_category_id');
    const preChild  = @json($preselectedChildId ?: null);
    const endpointTmpl = "{{ route('admin.primary-categories.children', ['primary_category' => '__ID__']) }}";

    function clearChildren() {
        childSel.innerHTML = '<option value="">— اختر الفئة الفرعية —</option>';
    }

    function syncFinal() {
        finalInp.value = (!childSel.disabled && childSel.value)
            ? childSel.value
            : (parentSel.value || '');
    }

    async function loadChildren(parentId, preselectChildId = null) {
        clearChildren();
        childSel.disabled = true;
        if (!parentId) { syncFinal(); return; }

        try {
            const url = endpointTmpl.replace('__ID__', parentId);
            const res  = await fetch(url, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();

            if (Array.isArray(data) && data.length) {
                const frag = document.createDocumentFragment();
                const def  = document.createElement('option');
                def.value = '';
                def.textContent = '— اختر الفئة الفرعية —';
                frag.appendChild(def);

                data.forEach(row => {
                    const opt = document.createElement('option');
                    opt.value = row.id;
                    opt.textContent = row.name_ar || ('#' + row.id);
                    frag.appendChild(opt);
                });

                childSel.innerHTML = '';
                childSel.appendChild(frag);
                childSel.disabled = false;

                if (preselectChildId) {
                    childSel.value = String(preselectChildId);
                }
            } else {
                clearChildren();
                childSel.disabled = true;
            }
        } catch (e) {
            clearChildren();
            childSel.disabled = true;
            console.error('Load children failed', e);
        } finally {
            syncFinal();
        }
    }

    parentSel?.addEventListener('change', e => loadChildren(e.target.value));
    childSel?.addEventListener('change', syncFinal);

    if (parentSel && parentSel.value) {
        loadChildren(parentSel.value, preChild);
    } else {
        syncFinal();
    }
})();
</script>

<script>
$(document).ready(function() {
    $('.rich-editor').summernote({
        placeholder: 'Enter description here...',
        tabsize: 2,
        height: 250,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ]
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // معاينة الصور الجديدة قبل الرفع
    const imagesInput = document.getElementById('images');
    const previewContainer = document.getElementById('new-images-preview');

    if (imagesInput) {
        imagesInput.addEventListener('change', function() {
            previewContainer.innerHTML = '';
            const files = Array.from(this.files);
            
            files.forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'position-relative';
                    div.innerHTML = `
                        <img src="${e.target.result}" class="img-thumbnail rounded" style="width:90px;height:90px;object-fit:cover;">
                        <span class="badge bg-primary position-absolute top-100 start-50 translate-middle mt-1 small" style="font-size:10px">جديد</span>
                    `;
                    previewContainer.appendChild(div);
                }
                reader.readAsDataURL(file);
            });
        });
    }

    // حذف صورة من معرض المنتج عبر AJAX
    const gallery = document.getElementById('image-gallery');
    if (gallery) {
        gallery.addEventListener('click', function (e) {
            const btn = e.target.closest('.delete-image-btn');
            if (!btn) return;
            e.preventDefault();

            window.confirmAction('هل أنت متأكد من حذف هذه الصورة؟', function() {
                fetch(`/admin/products/images/${btn.dataset.imageId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('image-container-' + btn.dataset.imageId)?.remove();
                        window.showToast('تم بنجاح', 'تم حذف الصورة بنجاح.');
                    } else {
                        window.showToast('خطأ', data.message || 'فشل حذف الصورة.', 'error');
                    }
                })
                .catch(() => window.showToast('خطأ', 'حدث خطأ في الاتصال.', 'error'));
            });
        });
    }
});
</script>
@endpush