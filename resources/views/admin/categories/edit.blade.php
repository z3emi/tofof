@extends('admin.layout')

@section('title', 'تعديل التصنيف: ' . $category->name_ar)

@section('content')
@push('styles')
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem; color: white; border-radius: 0 !important; }
    .form-section-title { font-weight: 700; color: var(--primary-dark); border-right: 4px solid var(--accent-gold); padding-right: 15px; margin-bottom: 2rem; }
    .image-preview-container { width: 180px; height: 180px; border-radius: 20px; border: 4px solid #fff; box-shadow: 0 10px 30px rgba(0,0,0,0.1); position: relative; overflow: hidden; background: #f8fafc; display: flex; align-items: center; justify-content: center; cursor: pointer; }
    .submit-btn { background: var(--primary-dark); padding: 1rem 3rem; border-radius: 10px; font-weight: 700; color: white; border: none; }
</style>
@endpush

<div class="form-card">
    <div class="form-card-header">
        <h2 class="mb-2 fw-bold text-white"><i class="bi bi-tag-fill me-2"></i> تعديل التصنيف: {{ $category->name_ar }}</h2>
        <p class="mb-0 opacity-75 fs-6 text-white small">تعديل الاسم أو الصورة أو التصنيف الأب لهذه الفئة.</p>
    </div>
    
    <div class="p-4 p-lg-5">
        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-5">
                <h5 class="form-section-title">إدارة الصور</h5>
                <div class="d-flex align-items-center gap-4">
                    <div class="text-center">
                        <label class="d-block small text-muted mb-2">الصورة الحالية</label>
                        <img src="{{ $category->image_url }}" style="width:120px; height:120px; object-fit:contain; border-radius:12px; border:1px solid #eee;">
                    </div>
                    <div class="text-center">
                        <label for="image" class="d-block small text-muted mb-2">اختر صورة جديدة</label>
                        <label for="image" class="image-preview-container">
                            <img id="previewImg" src="" style="display:none; width:100%; height:100%; object-fit:contain;">
                            <div id="upload-icon" class="text-center"><i class="bi bi-upload fs-2 text-muted"></i></div>
                        </label>
                        <input type="file" name="image" id="image" class="d-none" accept="image/*" onchange="previewUpdate(this)">
                    </div>
                </div>
            </div>

            <div class="mb-5">
                <h5 class="form-section-title">المعلومات الأساسية</h5>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="name_ar" class="form-label fw-bold">الاسم العربي <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" style="border-radius:12px; padding:0.8rem" id="name_ar" name="name_ar" value="{{ old('name_ar', $category->name_ar) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="name_en" class="form-label fw-bold">الاسم الإنجليزي <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" style="border-radius:12px; padding:0.8rem" id="name_en" name="name_en" value="{{ old('name_en', $category->name_en) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="parent_id" class="form-label fw-bold">التصنيف الأب</label>
                        <select name="parent_id" id="parent_id" class="form-select" style="border-radius:12px; padding:0.8rem">
                            <option value="">تصنيف رئيسي</option>
                            @foreach(($categories ?? collect()) as $cat)
                                <option value="{{ $cat->id }}" {{ old('parent_id', $category->parent_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name_ar }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-3 pt-5 border-top">
                <a href="{{ route('admin.categories.index') }}" class="btn btn-light px-5 py-3 rounded-3 fw-bold">إلغاء</a>
                <button type="submit" class="submit-btn shadow-sm py-3 px-5">حفظ التغييرات</button>
            </div>
        </form>
    </div>
</div>

<script>
    function previewUpdate(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) { $('#previewImg').attr('src', e.target.result).show(); $('#upload-icon').hide(); }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
