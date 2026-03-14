@extends('admin.layout')

@section('title', 'تعديل منتج')

@section('content')
<form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row">
        {{-- العمود الأيسر: معلومات المنتج --}}
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    {{-- الاسم بالعربي --}}
                    <div class="mb-3">
                        <label for="name_ar" class="form-label">اسم المنتج (عربي) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name_ar') is-invalid @enderror" id="name_ar" name="name_ar" value="{{ old('name_ar', $product->name_ar ?? '') }}" required>
                        @error('name_ar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- الاسم بالإنجليزي --}}
                    <div class="mb-3">
                        <label for="name_en" class="form-label">Product Name (English)</label>
                        <input type="text" class="form-control @error('name_en') is-invalid @enderror" id="name_en" name="name_en" value="{{ old('name_en', $product->name_en ?? '') }}">
                        @error('name_en')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>


                    {{-- ===== الوصف (نفس الإنشاء: Textarea عادية بدون محرر) ===== --}}
                    <div class="mb-3">
                        <label for="description_ar" class="form-label">الوصف (عربي) <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('description_ar') is-invalid @enderror" id="description_ar" name="description_ar" rows="5" required>{{ old('description_ar', $product->description_ar ?? '') }}</textarea>
                        @error('description_ar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="description_en" class="form-label">Description (English)</label>
                        <textarea class="form-control @error('description_en') is-invalid @enderror" id="description_en" name="description_en" rows="5">{{ old('description_en', $product->description_en ?? '') }}</textarea>
                        @error('description_en')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- ===== نهاية الوصف ===== --}}

                    {{-- صور المنتج --}}
                    <div class="mb-3">
                        <label for="images" class="form-label">
                            صور المنتج 
                            @if(!isset($product)) 
                                <span class="text-danger">*</span> 
                            @endif
                            <small class="text-muted">(يمكنك اختيار أكثر من صورة)</small>
                        </label>
                        <input type="file" class="form-control @error('images.*') is-invalid @enderror" id="images" name="images[]" multiple @if(!isset($product)) required @endif>
                        @error('images.*')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- عرض الصور الحالية --}}
                    @if(isset($product) && $product->images->isNotEmpty())
                    <div class="mb-3">
                        <label class="form-label">الصور الحالية</label>
                        <div class="d-flex flex-wrap gap-2 border p-2 rounded" id="image-gallery">
                            @foreach($product->images as $image)
                                <div class="position-relative" id="image-container-{{ $image->id }}">
                                    <img src="{{ asset('storage/' . $image->image_path) }}" class="img-thumbnail" width="100" alt="Product Image">
                                    <button type="button" class="btn btn-sm btn-danger rounded-circle position-absolute top-0 end-0 delete-image-btn" 
                                            data-image-id="{{ $image->id }}" 
                                            style="transform: translate(50%, -50%); line-height: 1;">
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

        {{-- العمود الأيمن: السعر، التصنيف، الحالة --}}
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    {{-- SKU --}}
                    <div class="mb-3">
                        <label for="sku" class="form-label">SKU (رمز المنتج) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('sku') is-invalid @enderror" id="sku" name="sku" value="{{ old('sku', $product->sku ?? '') }}" required>
                        @error('sku')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- السعر --}}
                    <div class="mb-3">
                        <label for="price" class="form-label">سعر البيع <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $product->price ?? '') }}" required step="any">
                        @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- سعر الخصم --}}
                    <div class="mb-3">
                        <label for="sale_price" class="form-label">سعر الخصم (اختياري)</label>
                        <input type="number" class="form-control @error('sale_price') is-invalid @enderror" id="sale_price" name="sale_price" value="{{ old('sale_price', $product->sale_price ?? '') }}" step="any">
                        @error('sale_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- تاريخ بداية الخصم --}}
                    <div class="mb-3">
                        <label for="sale_starts_at" class="form-label">تاريخ بدء الخصم</label>
                        <input type="datetime-local" class="form-control @error('sale_starts_at') is-invalid @enderror" id="sale_starts_at" name="sale_starts_at" value="{{ old('sale_starts_at', isset($product) && $product->sale_starts_at ? $product->sale_starts_at->format('Y-m-d\TH:i') : '') }}">
                        @error('sale_starts_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- تاريخ انتهاء الخصم --}}
                    <div class="mb-3">
                        <label for="sale_ends_at" class="form-label">تاريخ انتهاء الخصم</label>
                        <input type="datetime-local" class="form-control @error('sale_ends_at') is-invalid @enderror" id="sale_ends_at" name="sale_ends_at" value="{{ old('sale_ends_at', isset($product) && $product->sale_ends_at ? $product->sale_ends_at->format('Y-m-d\TH:i') : '') }}">
                        @error('sale_ends_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- البراند / القسم --}}
                    <div class="mb-3">
                        <label for="category_id" class="form-label">البراند <span class="text-danger">*</span></label>
                        <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                            <option value="">-- اختر القسم --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id ?? '') == $category->id)>{{ $category->name_ar }}</option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- ===== NEW: اختيار الفئة الرئيسية/الفرعية (PrimaryCategory) ===== --}}
                    @php
                        use App\Models\PrimaryCategory;

                        // جذور فقط
                        $rootPrimaryCategories = $rootPrimaryCategories
                            ?? PrimaryCategory::active()
                                ->whereNull('parent_id')
                                ->ordered()
                                ->get();

                        // الفئة المختارة مسبقًا (عند التعديل أو رجوع الفالديشن)
                        $selectedPrimary = old(
                            'primary_category_id',
                            (isset($product) && method_exists($product,'primaryCategories') && $product->primaryCategories()->exists())
                                ? $product->primaryCategories()->first()->id
                                : ''
                        );

                        // تحديد الأب/الابن المسبقين لو موجودين
                        $preselectedParentId = '';
                        $preselectedChildId  = '';
                        if ($selectedPrimary) {
                            $selCat = PrimaryCategory::find($selectedPrimary);
                            if ($selCat) {
                                if ($selCat->parent_id) {
                                    // المختار ابن: خزن الأب والابن
                                    $preselectedParentId = $selCat->parent_id;
                                    $preselectedChildId  = $selCat->id;
                                } else {
                                    // المختار جذر: الأب هو نفسه، بدون ابن
                                    $preselectedParentId = $selCat->id;
                                }
                            }
                        }
                    @endphp

                    <div class="mb-3">
                        <label class="form-label">الفئة الرئيسية (Primary)</label>
                        <select id="pc_parent" class="form-select">
                            <option value="">-- اختر الفئة الرئيسية --</option>
                            @foreach($rootPrimaryCategories as $root)
                                <option value="{{ $root->id }}" @selected($preselectedParentId == $root->id)>{{ $root->name_ar }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">الفئة الفرعية (Sub)</label>
                        <select id="pc_child" class="form-select" @if(!$preselectedParentId) disabled @endif></select>
                        <small class="text-muted d-block mt-1">
                            إذا لا توجد فئات فرعية، سيتم اعتماد الفئة الرئيسية تلقائيًا.
                        </small>
                        @error('primary_category_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- القيمة النهائية التي يقرأها السيرفر --}}
                    <input type="hidden" name="primary_category_id" id="primary_category_id" value="{{ $selectedPrimary }}">

                    {{-- الحالة --}}
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $product->is_active ?? true))>
                        <label class="form-check-label" for="is_active">المنتج فعال</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- زر الحفظ --}}
    <div class="mt-4 text-end">
        <button type="submit" class="btn btn-primary">حفظ المنتج</button>
        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">إلغاء</a>
    </div>
</form>
@endsection

@push('scripts')
{{-- تحميل أبناء الفئة الرئيسية ديناميكيًا --}}
<script>
(function(){
    const parentSel = document.getElementById('pc_parent');
    const childSel  = document.getElementById('pc_child');
    const finalInp  = document.getElementById('primary_category_id');
    const preChild  = @json($preselectedChildId ?: null);

    // قالب الراوت
    const endpointTmpl = "{{ route('admin.primary-categories.children', ['primary_category' => '__ID__']) }}";

    function clearChildren() {
        childSel.innerHTML = '<option value="">— اختر الفئة الفرعية —</option>';
    }

    function syncFinal() {
        // إذا عندنا فروع ومختارين ابن => نعتمد الابن
        if (!childSel.disabled && childSel.value) {
            finalInp.value = childSel.value;
        } else {
            // إذا ماكو فروع أو ما مختارين ابن => نعتمد الأب
            finalInp.value = parentSel.value || '';
        }
    }

    async function loadChildren(parentId, preselectChildId=null) {
        clearChildren();
        childSel.disabled = true;
        if (!parentId) { syncFinal(); return; }

        try {
            const url = endpointTmpl.replace('__ID__', parentId);
            const res = await fetch(url, { headers: {'Accept':'application/json'} });
            const data = await res.json();

            if (Array.isArray(data) && data.length) {
                const frag = document.createDocumentFragment();
                const def = document.createElement('option');
                def.value = '';
                def.textContent = '— اختر الفئة الفرعية —';
                frag.appendChild(def);

                data.forEach(row => {
                    const opt = document.createElement('option');
                    opt.value = row.id;
                    opt.textContent = row.name_ar || ('#'+row.id);
                    frag.appendChild(opt);
                });

                childSel.innerHTML = '';
                childSel.appendChild(frag);
                childSel.disabled = false;

                // في وضع التعديل: نختار الابن السابق إن وُجد
                if (preselectChildId) {
                    childSel.value = String(preselectChildId);
                }
            } else {
                // لا يوجد أبناء
                clearChildren();
                childSel.disabled = true;
            }
        } catch(e) {
            clearChildren();
            childSel.disabled = true;
            console.error('Load children failed', e);
        } finally {
            syncFinal();
        }
    }

    parentSel?.addEventListener('change', e => {
        loadChildren(e.target.value);
    });

    childSel?.addEventListener('change', syncFinal);

    // تهيئة أولية (صفحة التعديل)
    if (parentSel && parentSel.value) {
        loadChildren(parentSel.value, preChild);
    } else {
        syncFinal();
    }
})();
</script>

{{-- حذف صور المنتج أجاكس --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const imageGallery = document.getElementById('image-gallery');
    if (imageGallery) {
        imageGallery.addEventListener('click', function (e) {
            if (e.target.closest('.delete-image-btn')) {
                e.preventDefault();
                const button = e.target.closest('.delete-image-btn');
                const imageId = button.dataset.imageId;

                if (confirm('هل أنت متأكد من حذف هذه الصورة؟')) {
                    fetch(`/admin/products/images/${imageId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById(`image-container-${imageId}`).remove();
                        } else {
                            alert(data.message || 'فشل حذف الصورة.');
                        }
                    })
                    .catch(() => alert('حدث خطأ في الاتصال.'));
                }
            }
        });
    }
});
</script>
@endpush
