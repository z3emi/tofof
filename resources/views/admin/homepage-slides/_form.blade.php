@csrf

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">محتوى السلايد - العربية</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="section" class="form-label">نوع السلايدر <span class="text-danger">*</span></label>
                        <select name="section" id="section" class="form-select @error('section') is-invalid @enderror" required>
                            @foreach(\App\Models\HomepageSlide::sections() as $value => $label)
                                <option value="{{ $value }}" @selected(old('section', optional($homepageSlide)->section ?? $selectedSection ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('section') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="sort_order" class="form-label">الترتيب</label>
                        <input type="number" min="1" name="sort_order" id="sort_order" class="form-control @error('sort_order') is-invalid @enderror"
                               value="{{ old('sort_order', $homepageSlide->sort_order ?? '') }}" placeholder="يُترك فارغًا للإضافة في النهاية">
                        @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <label for="title" class="form-label">العنوان (اختياري)</label>
                        <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title', $homepageSlide->title ?? '') }}">
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <label for="subtitle" class="form-label">النص الوصفي</label>
                        <textarea name="subtitle" id="subtitle" rows="4" class="form-control @error('subtitle') is-invalid @enderror">{{ old('subtitle', $homepageSlide->subtitle ?? '') }}</textarea>
                        @error('subtitle') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="button_text" class="form-label">نص الزر</label>
                        <input type="text" name="button_text" id="button_text" class="form-control @error('button_text') is-invalid @enderror"
                               value="{{ old('button_text', $homepageSlide->button_text ?? '') }}">
                        @error('button_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="button_url" class="form-label">رابط الزر</label>
                        <input type="text" name="button_url" id="button_url" class="form-control @error('button_url') is-invalid @enderror"
                               value="{{ old('button_url', $homepageSlide->button_url ?? '') }}" placeholder="/shop أو https://example.com">
                        @error('button_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <label for="alt_text" class="form-label">نص بديل للصورة</label>
                        <input type="text" name="alt_text" id="alt_text" class="form-control @error('alt_text') is-invalid @enderror"
                               value="{{ old('alt_text', $homepageSlide->alt_text ?? '') }}">
                        @error('alt_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">محتوى السلايد - English</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label for="title_en" class="form-label">Title (Optional)</label>
                        <input type="text" name="title_en" id="title_en" class="form-control @error('title_en') is-invalid @enderror"
                               value="{{ old('title_en', $homepageSlide->title_en ?? '') }}" dir="ltr">
                        @error('title_en') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <label for="subtitle_en" class="form-label">Description Text</label>
                        <textarea name="subtitle_en" id="subtitle_en" rows="4" class="form-control @error('subtitle_en') is-invalid @enderror" dir="ltr">{{ old('subtitle_en', $homepageSlide->subtitle_en ?? '') }}</textarea>
                        @error('subtitle_en') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="button_text_en" class="form-label">Button Text</label>
                        <input type="text" name="button_text_en" id="button_text_en" class="form-control @error('button_text_en') is-invalid @enderror"
                               value="{{ old('button_text_en', $homepageSlide->button_text_en ?? '') }}" dir="ltr">
                        @error('button_text_en') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">&nbsp;</label>
                        <small class="text-muted d-block">
                            <i class="bi bi-info-circle"></i> The button URL is the same for both languages
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">صورة الخلفية</h5>
            </div>
            <div class="card-body">
                <label for="background_image" class="form-label">رفع صورة</label>
                <input type="file" name="background_image" id="background_image" accept=".jpg,.jpeg,.png,.webp"
                       class="form-control @error('background_image') is-invalid @enderror">
                @error('background_image') <div class="invalid-feedback">{{ $message }}</div> @enderror

                <small class="text-muted d-block mt-2">يفضّل صورة أفقية واضحة للهيرو والسلايدرات.</small>
                <div class="alert alert-info mt-3 py-3 px-3 border-0 shadow-sm" style="background: rgba(13, 202, 240, 0.1); color: #055160; border-radius: 12px; border-right: 4px solid #0dcaf0 !important;">
                    <div class="d-flex">
                        <i class="bi bi-aspect-ratio-fill fs-4 me-3 mt-1"></i>
                        <div>
                            <div class="fw-bold mb-2" style="font-size: 1rem;">القياسات الموصى بها للأبعاد الجديدة:</div>
                            <ul class="list-unstyled mb-0" style="font-size: 0.9rem; line-height: 1.6;">
                                <li class="mb-2">
                                    <span class="badge bg-info text-white me-1">الهيرو سلايدر (الأول):</span>
                                    <strong>1920 × 700 بكسل</strong> <small>(نسبة 2.7:1)</small>
                                </li>
                                    <li>
                                        <span class="badge bg-secondary text-white me-1">السلايدرات الترويجية (2 و 3):</span>
                                        <strong>1920 × 540 بكسل</strong> <small>(نسبة 3.5:1 تقريباً)</small>
                                    </li>
                            </ul>
                            <div class="mt-2 text-muted small">
                                <i class="bi bi-info-circle"></i> يرجى التأكد من أن الصورة أفقية (عرضية) لضمان عدم قص الأطراف بشكل سيء.
                            </div>
                        </div>
                    </div>
                </div>

                @if(!empty($homepageSlide?->background_image_url))
                    <div class="mt-3">
                        <div class="small text-muted mb-2">المعاينة الحالية</div>
                            <img src="{{ $homepageSlide->background_image_url }}" alt="{{ $homepageSlide->alt_text ?: ($homepageSlide->title ?: 'صورة سلايدر') }}"
                             class="img-fluid rounded border" style="max-height: 220px; object-fit: cover; width: 100%;">
                    </div>
                @endif

                <div class="form-check form-switch mt-4">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                           @checked(old('is_active', $homepageSlide->is_active ?? true))>
                    <label class="form-check-label" for="is_active">السلايد ظاهر في الموقع</label>
                </div>
            </div>
        </div>

        <div class="card mt-4 shadow-sm border-warning">
            <div class="card-header bg-warning bg-opacity-10">
                <h5 class="mb-0 text-warning-emphasis"><i class="bi bi-image"></i> تعتيم السلايد (Overlay)</h5>
            </div>
            <div class="card-body">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="show_overlay" name="show_overlay" value="1"
                           @checked(old('show_overlay', $homepageSlide->show_overlay ?? true))>
                    <label class="form-check-label fw-bold" for="show_overlay">تفعيل طبقة التعتيم</label>
                </div>

                <div class="mb-3">
                    <label for="overlay_color" class="form-label fw-bold">لون التعتيم</label>
                    <input type="color" name="overlay_color" id="overlay_color" class="form-control form-control-color w-100"
                           value="{{ old('overlay_color', $homepageSlide->overlay_color ?? '#000000') }}">
                </div>

                <div class="mb-0">
                    <label for="overlay_strength" class="form-label fw-bold">قوة التعتيم (0 - 1)</label>
                    <input type="range" name="overlay_strength" id="overlay_strength" class="form-range" min="0" max="1" step="0.05"
                           value="{{ old('overlay_strength', $homepageSlide->overlay_strength ?? '0.50') }}"
                           oninput="this.nextElementSibling.value = this.value">
                    <output class="badge bg-primary fs-6">{{ old('overlay_strength', $homepageSlide->overlay_strength ?? '0.50') }}</output>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-body d-flex justify-content-end gap-2">
        <a href="{{ route('admin.homepage-slides.index') }}" class="btn btn-outline-secondary">إلغاء</a>
        <button type="submit" class="btn btn-primary">حفظ</button>
    </div>
</div>
