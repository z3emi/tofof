@csrf
<div class="mb-3">
    <label for="title" class="form-label">عنوان المقال <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $post->title ?? '') }}" required>
    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label for="body" class="form-label">محتوى المقال <span class="text-danger">*</span></label>
    <textarea class="form-control @error('body') is-invalid @enderror" id="body" name="body" rows="15">{{ old('body', $post->body ?? '') }}</textarea>
    @error('body') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label for="excerpt" class="form-label">مقتطف (ملخص قصير)</label>
    <textarea class="form-control @error('excerpt') is-invalid @enderror" id="excerpt" name="excerpt" rows="3">{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
    @error('excerpt') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="blog_category_id" class="form-label">القسم <span class="text-danger">*</span></label>
        <select class="form-select @error('blog_category_id') is-invalid @enderror" id="blog_category_id" name="blog_category_id" required>
            <option value="">-- اختر القسم --</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected(old('blog_category_id', $post->blog_category_id ?? '') == $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        @error('blog_category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="image" class="form-label">الصورة البارزة</label>
        <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image">
        @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
        @if(isset($post) && $post->image)
            <img src="{{ asset('storage/' . $post->image) }}" class="img-thumbnail mt-2" width="150" alt="Current Image">
        @endif
    </div>
</div>

@push('scripts')
<script src="https://cdn.tiny.cloud/1/du3z85vklq5w3g8vsio7qztxeemn1ljmqzedt7n5vndlf6e1/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '#body',
        plugins: 'directionality link image code lists table',
        toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright alignjustify | ltr rtl | bullist numlist | link image table | code',
        directionality: 'rtl',
        height: 500,
        menubar: false,
    });

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('post-form')?.addEventListener('submit', function () {
            tinymce.triggerSave();
        });
    });
</script>
@endpush
