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
                        <select class="form-select select2-with-image @error('category_id') is-invalid @enderror"
                                id="category_id" name="category_id" required>
                            <option value="">-- اختر البراند --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}"
                                    data-image="{{ $category->image_url }}"
                                    @selected(old('category_id', $product->category_id ?? '') == $category->id)>
                                    {{ $category->name_ar }}{{ method_exists($category, 'trashed') && $category->trashed() ? ' (محذوفة)' : '' }}
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
                    $selectedPrimaryFallback = old('primary_category_id_fallback', $selectedPrimary);
                    $preselectedParentId = '';
                    $preselectedChildId  = '';
                    if ($selectedPrimaryFallback) {
                        $selCat = PrimaryCategory::find($selectedPrimaryFallback);
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
                    <select id="pc_parent" name="primary_category_id_fallback" class="form-select select2-with-image">
                        <option value="">-- اختر الفئة الرئيسية --</option>
                        @foreach($rootPrimaryCategories as $root)
                            <option value="{{ $root->id }}" 
                                    data-image="{{ $root->image_url }}"
                                    @selected($preselectedParentId == $root->id)>
                                {{ $root->name_ar }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">الفئة الفرعية</label>
                    <select id="pc_child" class="form-select select2-with-image" @if(!$preselectedParentId) disabled @endif></select>
                    <small class="text-muted">إذا لا توجد فئات فرعية، تُعتمد الفئة الرئيسية تلقائيًا.</small>
                    @error('primary_category_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <input type="hidden" name="primary_category_id" id="primary_category_id" value="{{ $selectedPrimaryFallback }}">

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

                @if(isset($product))
                    <div class="mb-3">
                        <label for="new_images_position" class="form-label">مكان إدراج الصور الجديدة</label>
                        <select class="form-select" id="new_images_position" name="new_images_position">
                            <option value="last" @selected(old('new_images_position', 'last') === 'last')>في النهاية</option>
                            <option value="middle" @selected(old('new_images_position') === 'middle')>في المنتصف</option>
                            <option value="first" @selected(old('new_images_position') === 'first')>في البداية</option>
                        </select>
                        <small class="text-muted">ينطبق هذا فقط على الصور التي ترفعها الآن.</small>
                    </div>
                @endif

                {{-- معاينة الصور الجديدة --}}
                <div id="new-images-preview" class="d-flex flex-wrap gap-2 mb-3"></div>

                @if(isset($product) && $product->images->isNotEmpty())
                <div>
                    <label class="form-label text-muted small mb-1">الصور الحالية (اسحب أو استخدم الأسهم للترتيب)</label>
                    <div class="image-gallery-grid border rounded p-2" id="image-gallery">
                        @foreach($product->images as $image)
                            <div class="position-relative image-order-item" id="image-container-{{ $image->id }}" data-image-id="{{ $image->id }}" draggable="true">
                                <img src="{{ asset('storage/' . $image->image_path) }}"
                                     class="img-thumbnail rounded"
                                     style="width:90px;height:90px;object-fit:cover;"
                                     alt="">
                                <div class="image-order-actions mt-1 d-flex justify-content-center gap-1">
                                    <button type="button" class="btn btn-light btn-sm border move-image-right" title="تقديم لليمين">
                                        <i class="bi bi-arrow-right"></i>
                                    </button>
                                    <button type="button" class="btn btn-light btn-sm border move-image-left" title="تأخير لليسار">
                                        <i class="bi bi-arrow-left"></i>
                                    </button>
                                    <span class="btn btn-light btn-sm border image-drag-handle" title="اسحب للترتيب">
                                        <i class="bi bi-grip-vertical"></i>
                                    </span>
                                </div>
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
{{-- Select2 CSS --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
{{-- Summernote CSS --}}
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<style>
    /* Select2 Skinning */
    .select2-container--default .select2-selection--single {
        border: 1px solid #dee2e6;
        border-radius: 10px;
        height: 48px;
        padding: 8px 12px;
        background-color: #fcfcfc;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 46px;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 30px;
        color: #2C2C2C;
    }
    .select2-dropdown {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    .select-item-with-img {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .select-item-with-img img {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        object-fit: cover;
        background: #f8f9fa;
        border: 1px solid #eee;
    }
    .select-item-with-img .text {
        font-weight: 500;
    }
</style>
<style>
    .note-editor.note-frame {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
        background: #fff;
    }

    /* Arabic editor content should be RTL/right aligned */
    .note-editor.rtl-editor .note-editable {
        direction: rtl !important;
        text-align: right !important;
    }

    /* English editor content should be LTR/left aligned */
    .note-editor.ltr-editor .note-editable {
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

    .image-gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(96px, 1fr));
        gap: 12px;
    }

    .image-order-item {
        padding: 4px;
        border-radius: 8px;
        transition: background-color 0.2s ease;
    }

    .image-order-item.dragging {
        opacity: 0.5;
        background: #f8f9fa;
    }

    .image-order-item.drag-over {
        outline: 2px dashed #0d6efd;
        outline-offset: 2px;
    }

    .image-order-actions .btn {
        width: 26px;
        height: 26px;
        padding: 0;
        line-height: 1;
    }

    .image-drag-handle {
        cursor: grab;
    }
</style>
@endpush

@push('scripts')
{{-- jQuery, Select2 and Summernote JS --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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
        if ($(childSel).data('select2')) {
            $(childSel).trigger('change');
        }
    }

    function syncFinal() {
        finalInp.value = (!childSel.disabled && childSel.value)
            ? childSel.value
            : (parentSel.value || '');
    }

    async function loadChildren(parentId, preselectChildId = null) {
        clearChildren();
        if (!parentId) { 
            childSel.disabled = true;
            syncFinal(); 
            return; 
        }

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
                    // 👈 إضافة الصورة هنا
                    if (row.image_url) {
                        opt.setAttribute('data-image', row.image_url);
                    }
                    frag.appendChild(opt);
                });

                childSel.innerHTML = '';
                childSel.appendChild(frag);
                childSel.disabled = false;

                if (preselectChildId) {
                    childSel.value = String(preselectChildId);
                }
            } else {
                childSel.disabled = true;
            }
        } catch (e) {
            childSel.disabled = true;
            console.error('Load children failed', e);
        } finally {
            if ($(childSel).data('select2')) {
                $(childSel).trigger('change'); // إعادة تنشيط سلكت 2
            }
            syncFinal();
        }
    }

    parentSel?.addEventListener('change', e => loadChildren(e.target.value));
    childSel?.addEventListener('change', syncFinal);

    // دالة تهيئة Select2 مع الصور
    function initSelect2WithImages() {
        function formatState(state) {
            if (!state.id) return state.text;
            const img = $(state.element).data('image');
            if (!img) return state.text;
            
            return $(
                '<div class="select-item-with-img">' +
                '<img src="' + img + '" />' +
                '<span class="text">' + state.text + '</span>' +
                '</div>'
            );
        }

        $('.select2-with-image').each(function() {
            const $this = $(this);
            $this.select2({
                templateResult: formatState,
                templateSelection: formatState,
                language: {
                    noResults: function() { return "لا توجد نتائج"; }
                },
                width: '100%',
                dir: 'rtl'
            });
        });
    }

    $(document).ready(function() {
        initSelect2WithImages();
        
        if (parentSel && parentSel.value) {
            loadChildren(parentSel.value, preChild);
        } else {
            syncFinal();
        }

        // Safety net: make sure the hidden field reflects current selections right before submit.
        const formEl = parentSel?.closest('form');
        if (formEl) {
            formEl.addEventListener('submit', () => {
                syncFinal();
            });
        }
    });

})();
</script>

<script>
$(document).ready(function() {
    function normalizeDescriptionHtml(html) {
        const wrapper = document.createElement('div');
        wrapper.innerHTML = html || '';

        wrapper.querySelectorAll('ul, ol').forEach((list) => {
            const paragraphNodes = [];

            Array.from(list.children).forEach((child) => {
                if (child.tagName && child.tagName.toLowerCase() === 'li') {
                    const content = child.innerHTML.trim();
                    if (!content) {
                        return;
                    }

                    const p = document.createElement('p');
                    p.innerHTML = content;
                    paragraphNodes.push(p);
                }
            });

            if (paragraphNodes.length) {
                list.replaceWith(...paragraphNodes);
            } else {
                list.replaceWith(document.createElement('p'));
            }
        });

        return wrapper.innerHTML;
    }

    function attachDescriptionNormalizer(textareaSelector) {
        const $textarea = $(textareaSelector);
        const formEl = $textarea.closest('form').get(0);
        if (!formEl) {
            return;
        }

        formEl.addEventListener('submit', function() {
            const currentHtml = $textarea.summernote('code');
            const normalizedHtml = normalizeDescriptionHtml(currentHtml);
            $textarea.summernote('code', normalizedHtml);
        });
    }

    $('#description_ar').summernote({
        placeholder: 'اكتب وصف المنتج بالعربي...',
        tabsize: 2,
        height: 250,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'clear']],
            ['color', ['color']],
            ['para', ['paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ],
        callbacks: {
            onInit: function() {
                const $editor = $(this).next('.note-editor');
                $editor.addClass('rtl-editor');
                $editor.find('.note-editable').attr('dir', 'rtl').css('text-align', 'right');
            }
        }
    });

    $('#description_en').summernote({
        placeholder: 'Enter description here...',
        tabsize: 2,
        height: 250,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'clear']],
            ['color', ['color']],
            ['para', ['paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ],
        callbacks: {
            onInit: function() {
                const $editor = $(this).next('.note-editor');
                $editor.addClass('ltr-editor');
                $editor.find('.note-editable').attr('dir', 'ltr').css('text-align', 'left');
            }
        }
    });

    attachDescriptionNormalizer('#description_ar');
    attachDescriptionNormalizer('#description_en');
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // معاينة الصور الجديدة قبل الرفع
    const imagesInput = document.getElementById('images');
    const previewContainer = document.getElementById('new-images-preview');
    const gallery = document.getElementById('image-gallery');
    const orderHiddenStore = document.getElementById('image_order_store');
    const form = imagesInput?.closest('form') || gallery?.closest('form');

    function getOrderedImageIds() {
        if (!gallery) return [];
        return Array.from(gallery.querySelectorAll('.image-order-item'))
            .map((el) => Number(el.dataset.imageId || 0))
            .filter((id) => id > 0);
    }

    function syncImageOrderInputs() {
        if (!form) return;

        form.querySelectorAll('input[data-image-order="1"]').forEach((el) => el.remove());
        if (orderHiddenStore) {
            orderHiddenStore.remove();
        }

        getOrderedImageIds().forEach((id) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'image_order[]';
            input.value = String(id);
            input.setAttribute('data-image-order', '1');
            form.appendChild(input);
        });
    }

    function moveImageItem(item, direction) {
        if (!item || !gallery) return;
        const sibling = direction === 'right' ? item.previousElementSibling : item.nextElementSibling;
        if (!sibling) return;

        if (direction === 'right') {
            gallery.insertBefore(item, sibling);
        } else {
            gallery.insertBefore(sibling, item);
        }

        syncImageOrderInputs();
    }

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

    // ترتيب وحذف صور معرض المنتج
    if (gallery) {
        let draggedItem = null;

        gallery.querySelectorAll('.image-order-item').forEach((item) => {
            item.addEventListener('dragstart', function () {
                draggedItem = item;
                item.classList.add('dragging');
            });

            item.addEventListener('dragend', function () {
                item.classList.remove('dragging');
                gallery.querySelectorAll('.image-order-item').forEach((el) => el.classList.remove('drag-over'));
                draggedItem = null;
            });

            item.addEventListener('dragover', function (e) {
                e.preventDefault();
                if (item !== draggedItem) {
                    item.classList.add('drag-over');
                }
            });

            item.addEventListener('dragleave', function () {
                item.classList.remove('drag-over');
            });

            item.addEventListener('drop', function (e) {
                e.preventDefault();
                item.classList.remove('drag-over');
                if (!draggedItem || draggedItem === item) return;

                const items = Array.from(gallery.querySelectorAll('.image-order-item'));
                const draggedIndex = items.indexOf(draggedItem);
                const targetIndex = items.indexOf(item);

                if (draggedIndex < targetIndex) {
                    gallery.insertBefore(draggedItem, item.nextSibling);
                } else {
                    gallery.insertBefore(draggedItem, item);
                }

                syncImageOrderInputs();
            });
        });

        gallery.addEventListener('click', function (e) {
            const moveRightBtn = e.target.closest('.move-image-right');
            if (moveRightBtn) {
                e.preventDefault();
                moveImageItem(moveRightBtn.closest('.image-order-item'), 'right');
                return;
            }

            const moveLeftBtn = e.target.closest('.move-image-left');
            if (moveLeftBtn) {
                e.preventDefault();
                moveImageItem(moveLeftBtn.closest('.image-order-item'), 'left');
                return;
            }

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
                        syncImageOrderInputs();
                        window.showToast('تم بنجاح', 'تم حذف الصورة بنجاح.');
                    } else {
                        window.showToast('خطأ', data.message || 'فشل حذف الصورة.', 'error');
                    }
                })
                .catch(() => window.showToast('خطأ', 'حدث خطأ في الاتصال.', 'error'));
            });
        });

        syncImageOrderInputs();
    }

    if (form) {
        form.addEventListener('submit', syncImageOrderInputs);
    }
});
</script>
@endpush