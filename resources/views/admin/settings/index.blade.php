@extends('admin.layout')
@section('title', 'إعدادات الموقع')

@push('styles')
<link rel="stylesheet" href="https://cdn.quilljs.com/1.3.7/quill.snow.css">
<style>
    /* تنسيقات لجعل الخانات بارزة ومميزة */
    .tab-pane .card {
        border: 1px solid #e9ecef;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        transition: all 0.2s ease-in-out;
    }
    .tab-pane .card:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        transform: translateY(-2px);
    }
    .tab-pane .card-header {
        background-color: #f8f9fa;
    }

    .quill-editor-wrapper .ql-toolbar.ql-snow {
        direction: rtl;
        text-align: right;
        border-radius: 0.5rem 0.5rem 0 0;
    }

    .quill-editor-wrapper .ql-toolbar.ql-snow .ql-formats {
        margin-right: 0;
        margin-left: 0.75rem;
    }

    .quill-editor-wrapper .ql-container.ql-snow {
        border-radius: 0 0 0.5rem 0.5rem;
        min-height: 220px;
    }

    .quill-editor-wrapper .ql-editor {
        direction: rtl;
        text-align: right;
        font-family: inherit;
    }

    .quill-editor-wrapper .ql-editor:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13,110,253,.25);
    }

    .quill-editor-wrapper .ql-editor.ql-blank::before {
        right: 1rem;
        left: auto;
        font-style: normal;
        color: #adb5bd;
    }

    .image-controls {
        display: none;
        margin-top: 0.75rem;
        padding: 0.75rem;
        border: 1px dashed #ced4da;
        border-radius: 0.5rem;
        background-color: #f8f9fa;
    }

    .image-controls.active {
        display: block;
    }

    .image-controls .form-label {
        font-size: 0.85rem;
        margin-bottom: 0.35rem;
    }

    .image-controls .image-size-value {
        font-size: 0.8rem;
        color: #6c757d;
    }

    .welcome-preview-overlay {
        position: fixed;
        inset: 0;
        z-index: 2000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .welcome-preview-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(3px);
    }

    .welcome-preview-card {
        position: relative;
        z-index: 1;
        width: min(92vw, 480px);
        min-height: 420px;
        border-radius: 1.5rem;
        padding: 2.25rem 1.5rem 1.5rem;
        background: #ffffff;
        box-shadow: 0 20px 48px rgba(0, 0, 0, 0.24);
        overflow: hidden;
    }

    .welcome-preview-card.is-image-only {
        width: auto;
        min-height: 0;
        padding: 0;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
    }

    .welcome-preview-close {
        position: absolute;
        top: 0.75rem;
        left: 0.75rem;
        width: 2.2rem;
        height: 2.2rem;
        border: 0;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(17, 24, 39, 0.75);
        color: #ffffff;
        transition: transform 0.2s ease;
    }

    .welcome-preview-close:hover {
        transform: scale(1.05);
    }

    .welcome-preview-content {
        width: 100%;
        text-align: center;
    }

    .welcome-preview-content img {
        max-width: 100%;
        height: auto;
        display: block;
        margin: 0 auto;
    }

    .welcome-preview-card.is-image-only .welcome-preview-content {
        width: auto;
    }

    .welcome-preview-card.is-image-only .welcome-preview-content img {
        width: auto;
        max-width: calc(100vw - 1rem);
        max-height: 88vh;
        margin: 0;
        border-radius: 1rem;
    }

    body.welcome-preview-open {
        overflow: hidden;
    }
</style>
@endpush

@section('content')
<form action="{{ route('admin.settings.update') }}" method="POST" id="settings-form">
    @csrf
    @method('PATCH')

    <div class="card shadow-sm">
        <div class="card-header">
            {{-- Tabs Navigation --}}
            <ul class="nav nav-tabs card-header-tabs" id="settingsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general-tab-pane" type="button" role="tab" aria-controls="general-tab-pane" aria-selected="true">
                        <i class="bi bi-gear-wide-connected me-1"></i> عام
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="frontend-tab" data-bs-toggle="tab" data-bs-target="#frontend-tab-pane" type="button" role="tab" aria-controls="frontend-tab-pane" aria-selected="false">
                        <i class="bi bi-display me-1"></i> الواجهة
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo-tab-pane" type="button" role="tab" aria-controls="seo-tab-pane" aria-selected="false">
                        <i class="bi bi-google me-1"></i> SEO
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="whatsapp-tab" data-bs-toggle="tab" data-bs-target="#whatsapp-tab-pane" type="button" role="tab" aria-controls="whatsapp-tab-pane" aria-selected="false">
                        <i class="bi bi-whatsapp me-1"></i> واتساب
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            {{-- Tabs Content --}}
            <div class="tab-content" id="settingsTabsContent">
                {{-- General Settings Tab --}}
                <div class="tab-pane fade show active" id="general-tab-pane" role="tabpanel" aria-labelledby="general-tab" tabindex="0">
                    <div class="row g-4">
                        {{-- ⚡ وضع الصيانة --}}
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header"><h6 class="mb-0 text-danger">⚡ وضع الصيانة</h6></div>
                                <div class="card-body">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="maintenance_mode" name="maintenance_mode" @checked(old('maintenance_mode', $settings['maintenance_mode'] ?? 'off') == 'on')>
                                        <label class="form-check-label" for="maintenance_mode">تفعيل</label>
                                    </div>
                                    <small class="text-muted">يؤدي إلى عرض صفحة توقف (503).</small>
                                </div>
                            </div>
                        </div>

                        {{-- ⏰ مدة الجلسة --}}
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header"><h6 class="mb-0">⏰ مدة الجلسة</h6></div>
                                <div class="card-body">
                                    <input type="number" name="session_lifetime" min="1" class="form-control" value="{{ old('session_lifetime', $settings['session_lifetime'] ?? 120) }}">
                                    <small class="text-muted">بالدقائق قبل تسجيل الخروج التلقائي.</small>
                                </div>
                            </div>
                        </div>

                        {{-- 🔐 التحقق عبر واتساب (OTP) --}}
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="mb-0">🔐 التحقق عبر واتساب (OTP)</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-check form-switch">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            id="otp_enabled"
                                            name="otp_enabled"
                                            value="1"
                                            {{ !env('OTP_DISABLED', false) ? 'checked' : '' }}
                                        >
                                        <label class="form-check-label" for="otp_enabled">
                                            تفعيل التحقق عبر واتساب عند إنشاء حساب جديد
                                        </label>
                                    </div>
                                    <small class="text-muted d-block mt-2">
                                        عند إلغاء التفعيل، سيتم إنشاء الحسابات مباشرة بدون إرسال رمز تحقق على واتساب.
                                    </small>
                                </div>
                            </div>
                        </div>

                        {{-- 🚚 تكلفة التوصيل --}}
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header"><h6 class="mb-0">🚚 تكلفة التوصيل</h6></div>
                                <div class="card-body">
                                    <label for="shipping_cost" class="form-label">سعر التوصيل الأساسي (د.ع)</label>
                                    <input type="number" min="0" step="100" name="shipping_cost" id="shipping_cost" class="form-control"
                                           value="{{ old('shipping_cost', $settings['shipping_cost'] ?? config('shop.default_shipping_cost')) }}">
                                    <small class="text-muted">يُطبق تلقائياً على الطلبات التي لا تصل لحد الشحن المجاني.</small>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Frontend Settings Tab --}}
                <div class="tab-pane fade" id="frontend-tab-pane" role="tabpanel" aria-labelledby="frontend-tab" tabindex="0">
                    <div class="row g-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header"><h5 class="mb-0">🌟 الشاشة الترحيبية</h5></div>
                                <div class="card-body">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="show_welcome_screen" name="show_welcome_screen" @checked(old('show_welcome_screen', $settings['show_welcome_screen'] ?? 'off') == 'on')>
                                        <label class="form-check-label" for="show_welcome_screen">عرض للزوار مرة واحدة فقط لكل جلسة</label>
                                    </div>
                                    <div class="quill-editor-wrapper">
                                        <div id="welcome-editor" class="quill-editor" data-placeholder="اكتب رسالة الترحيب هنا..."></div>
                                        <textarea name="welcome_screen_content" id="welcome-editor-input" class="d-none">{{ old('welcome_screen_content', $settings['welcome_screen_content'] ?? '') }}</textarea>
                                    </div>
                                    <small class="text-muted d-block mt-2">تقدر تضيف صورة من زر الصورة داخل المحرر عبر رابط مباشر للصورة.</small>
                                    <button type="button" id="preview-welcome-modal-btn" class="btn btn-outline-primary btn-sm mt-3">
                                        <i class="bi bi-eye-fill me-1"></i> عرض النافذة
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header"><h5 class="mb-0">📢 شريط الإشعارات</h5></div>
                                <div class="card-body">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="show_dashboard_notification" name="show_dashboard_notification" @checked(old('show_dashboard_notification', $settings['show_dashboard_notification'] ?? 'off') == 'on')>
                                        <label class="form-check-label" for="show_dashboard_notification">تفعيل الشريط أعلى الموقع</label>
                                    </div>
                                    <div class="quill-editor-wrapper mb-3">
                                        <div id="notification-editor" class="quill-editor" data-placeholder="أدخل رسالة شريط الإشعارات..."></div>
                                        <textarea name="dashboard_notification_content" id="notification-editor-input" class="d-none">{{ old('dashboard_notification_content', $settings['dashboard_notification_content'] ?? '') }}</textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="dashboard_notification_animation" class="form-label">نوع الحركة</label>
                                        <select name="dashboard_notification_animation" id="dashboard_notification_animation" class="form-select">
                                            <option value="none" @selected(old('dashboard_notification_animation', $settings['dashboard_notification_animation'] ?? 'none') == 'none')>ثابت (بدون حركة)</option>
                                            <option value="scroll-left" @selected(old('dashboard_notification_animation', $settings['dashboard_notification_animation'] ?? 'none') == 'scroll-left')>تحرك من اليمين إلى اليسار</option>
                                            <option value="scroll-right" @selected(old('dashboard_notification_animation', $settings['dashboard_notification_animation'] ?? 'none') == 'scroll-right')>تحرك من اليسار إلى اليمين</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- SEO Settings Tab --}}
                <div class="tab-pane fade" id="seo-tab-pane" role="tabpanel" aria-labelledby="seo-tab" tabindex="0">
                    <div class="row g-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header"><h6 class="mb-0">إعدادات محركات البحث (SEO)</h6></div>
                                <div class="card-body">
                                    {{-- عنوان ووصف عربي --}}
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="site_title_ar" class="form-label">عنوان الموقع (عربي)</label>
                                            <input type="text" name="site_title_ar" id="site_title_ar" class="form-control"
                                                   value="{{ old('site_title_ar', $settings['site_title_ar'] ?? ($settings['site_title'] ?? 'طفوف | وجهتك الأولى للجمال')) }}">
                                            <small class="text-muted">يظهر في تبويب المتصفح ونتائج البحث (AR).</small>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="site_title_en" class="form-label">Site Title (English)</label>
                                            <input type="text" name="site_title_en" id="site_title_en" class="form-control"
                                                   value="{{ old('site_title_en', $settings['site_title_en'] ?? '') }}">
                                            <small class="text-muted">Meta Title shown in browser/search (EN).</small>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="meta_description_ar" class="form-label">وصف الموقع (عربي)</label>
                                            <textarea name="meta_description_ar" id="meta_description_ar" class="form-control" rows="4">{{ old('meta_description_ar', $settings['meta_description_ar'] ?? ($settings['meta_description'] ?? '')) }}</textarea>
                                            <small class="text-muted">150-160 حرفًا تقريبًا (AR).</small>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="meta_description_en" class="form-label">Meta Description (EN)</label>
                                            <textarea name="meta_description_en" id="meta_description_en" class="form-control" rows="4">{{ old('meta_description_en', $settings['meta_description_en'] ?? '') }}</textarea>
                                            <small class="text-muted">Approx. 150-160 characters (EN).</small>
                                        </div>
                                    </div>

                                    <hr class="my-3">

                                    {{-- الحقول القديمة (fallback) تبقى موجودة للاحتياط/التوافقية --}}
                                    <div class="mb-3">
                                        <label for="site_title" class="form-label">عنوان الموقع (Meta Title — قديم/احتياطي)</label>
                                        <input type="text" name="site_title" id="site_title" class="form-control"
                                               value="{{ old('site_title', $settings['site_title'] ?? 'طفوف | وجهتك الأولى للجمال') }}">
                                    </div>
                                    <div class="mb-3">
                                        <label for="meta_description" class="form-label">وصف الموقع (Meta Description — قديم/احتياطي)</label>
                                        <textarea name="meta_description" id="meta_description" class="form-control" rows="3">{{ old('meta_description', $settings['meta_description'] ?? '') }}</textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label for="site_url" class="form-label">رابط الموقع الأساسي (للكانونيكال — اختياري)</label>
                                        <input type="url" name="site_url" id="site_url" class="form-control"
                                               placeholder="https://tofofstore.com"
                                               value="{{ old('site_url', $settings['site_url'] ?? '') }}">
                                        <small class="text-muted">يُستخدم لإنشاء <code>rel=canonical</code> تلقائيًا عند الحاجة.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- /SEO Tab -->

                {{-- WhatsApp Session Tab --}}
                <div class="tab-pane fade" id="whatsapp-tab-pane" role="tabpanel" aria-labelledby="whatsapp-tab" tabindex="0">
                    <div class="row g-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">ربط واتساب عبر QR</h6>
                                </div>
                                <div class="card-body" id="whatsapp-session-box"
                                    data-status-url="{{ route('admin.whatsapp.status') }}"
                                    data-logout-url="{{ route('admin.whatsapp.logout') }}"
                                    data-csrf="{{ csrf_token() }}">

                                    <div id="wa-loading-state" class="text-muted d-flex align-items-center gap-2">
                                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                        <span>جاري جلب حالة واتساب...</span>
                                    </div>

                                    <div id="wa-connected-state" class="d-none">
                                        <div class="alert alert-success mb-3">
                                            <div class="mb-1"><strong>الحالة:</strong> متصل</div>
                                            <div><strong>رقم الواتساب الحالي:</strong> <span id="wa-phone-number">-</span></div>
                                        </div>
                                        <button type="button" id="wa-logout-btn" class="btn btn-outline-danger">
                                            تسجيل خروج واتساب
                                        </button>
                                    </div>

                                    <div id="wa-disconnected-state" class="d-none text-center">
                                        <div class="alert alert-warning text-start">
                                            <strong>الحالة:</strong> غير متصل. قم بمسح QR لتسجيل الدخول.
                                        </div>
                                        <img id="wa-qr-image" src="" alt="WhatsApp QR" class="img-fluid border rounded p-2 bg-white" style="max-width: 320px;">
                                        <div class="mt-3 text-muted">امسح الباركود من تطبيق واتساب على الهاتف.</div>
                                        <button type="button" id="wa-refresh-btn" class="btn btn-outline-secondary mt-3">
                                            تحديث الباركود
                                        </button>
                                    </div>

                                    <div id="wa-error-state" class="d-none alert alert-danger mb-0">
                                        تعذر الاتصال بخدمة واتساب. تأكد من تشغيل خدمة Node.js.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="welcome-preview-overlay" class="welcome-preview-overlay d-none" aria-hidden="true">
            <div class="welcome-preview-backdrop" id="welcome-preview-backdrop"></div>
            <div id="welcome-preview-card" class="welcome-preview-card">
                <button type="button" class="welcome-preview-close" id="welcome-preview-close" aria-label="إغلاق المعاينة">
                    <i class="bi bi-x-lg"></i>
                </button>
                <div id="welcome-preview-content" class="welcome-preview-content" dir="rtl"></div>
            </div>
        </div>

        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary">📁 حفظ الإعدادات</button>
        </div>
    </div>
</form>

<form action="{{ route('admin.settings.logoutAll') }}" method="POST" id="logout-all-form" class="d-none" onsubmit="return confirm('هل أنت متأكد؟ سيتم تسجيل خروج جميع المستخدمين فوراً.');">
    @csrf
</form>
@endsection

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const quillInstances = {};
        const quillFields = [
            {
                editorSelector: '#welcome-editor',
                inputSelector: '#welcome-editor-input'
            },
            {
                editorSelector: '#notification-editor',
                inputSelector: '#notification-editor-input'
            },
        ];

        const defaultModules = {
            toolbar: [
                [{ header: [2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ color: [] }, { background: [] }],
                [{ list: 'ordered' }, { list: 'bullet' }],
                [{ align: [] }],
                ['link', 'image', 'blockquote', 'code-block'],
                ['clean']
            ],
            history: {
                delay: 500,
                maxStack: 500,
                userOnly: true
            }
        };

        quillFields.forEach(({ editorSelector, inputSelector }) => {
            const editorElement = document.querySelector(editorSelector);
            const hiddenInput = document.querySelector(inputSelector);

            if (!editorElement || !hiddenInput) {
                return;
            }

            const placeholder = editorElement.dataset.placeholder || '';

            const quill = new Quill(editorElement, {
                theme: 'snow',
                modules: defaultModules,
                placeholder,
                bounds: editorElement,
                formats: [
                    'header', 'bold', 'italic', 'underline', 'strike',
                    'color', 'background', 'list', 'bullet', 'align',
                    'link', 'image', 'blockquote', 'code-block'
                ]
            });

            quillInstances[editorSelector] = quill;

            const controls = document.createElement('div');
            controls.className = 'image-controls';
            controls.innerHTML = `
                <div class="row g-2 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label">حجم الصورة</label>
                        <input type="range" min="20" max="100" value="100" class="form-range image-size-range">
                        <div class="image-size-value">100%</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">مكان الصورة</label>
                        <select class="form-select form-select-sm image-align-select">
                            <option value="right">يمين</option>
                            <option value="center" selected>وسط</option>
                            <option value="left">يسار</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="button" class="btn btn-outline-secondary btn-sm image-reset-btn">إعادة</button>
                    </div>
                </div>
            `;

            const wrapper = editorElement.closest('.quill-editor-wrapper');
            if (wrapper) {
                wrapper.appendChild(controls);
            }

            const sizeRange = controls.querySelector('.image-size-range');
            const sizeValue = controls.querySelector('.image-size-value');
            const alignSelect = controls.querySelector('.image-align-select');
            const resetBtn = controls.querySelector('.image-reset-btn');
            let selectedImage = null;

            const applyImageStyles = (imageElement, width, align) => {
                if (!imageElement) {
                    return;
                }

                const safeWidth = Math.min(100, Math.max(20, Number(width) || 100));
                imageElement.style.width = `${safeWidth}%`;
                imageElement.style.maxWidth = '100%';
                imageElement.style.height = 'auto';
                imageElement.style.display = 'block';

                if (align === 'right') {
                    imageElement.style.marginLeft = 'auto';
                    imageElement.style.marginRight = '0';
                } else if (align === 'left') {
                    imageElement.style.marginLeft = '0';
                    imageElement.style.marginRight = 'auto';
                } else {
                    imageElement.style.marginLeft = 'auto';
                    imageElement.style.marginRight = 'auto';
                }

                syncToInput();
            };

            const detectImageAlign = (imageElement) => {
                const marginLeft = imageElement.style.marginLeft;
                const marginRight = imageElement.style.marginRight;

                if (marginLeft === '0px' || marginLeft === '0') {
                    return 'left';
                }

                if (marginRight === '0px' || marginRight === '0') {
                    return 'right';
                }

                return 'center';
            };

            const showImageControls = (imageElement) => {
                selectedImage = imageElement;
                const width = parseInt(imageElement.style.width, 10) || 100;
                sizeRange.value = String(Math.min(100, Math.max(20, width)));
                sizeValue.textContent = `${sizeRange.value}%`;
                alignSelect.value = detectImageAlign(imageElement);
                controls.classList.add('active');
            };

            const hideImageControls = () => {
                selectedImage = null;
                controls.classList.remove('active');
            };

            const initialValue = hiddenInput.value.trim();
            if (initialValue) {
                quill.root.innerHTML = initialValue;
            }

            const syncToInput = () => {
                const html = quill.root.innerHTML;
                hiddenInput.value = html === '<p><br></p>' ? '' : html;
            };

            syncToInput();

            quill.on('text-change', () => {
                syncToInput();

                if (selectedImage && !quill.root.contains(selectedImage)) {
                    hideImageControls();
                }
            });

            quill.root.addEventListener('click', (event) => {
                const target = event.target;

                if (target instanceof HTMLImageElement) {
                    showImageControls(target);
                    return;
                }

                if (!controls.contains(target)) {
                    hideImageControls();
                }
            });

            sizeRange.addEventListener('input', () => {
                sizeValue.textContent = `${sizeRange.value}%`;
                applyImageStyles(selectedImage, sizeRange.value, alignSelect.value);
            });

            alignSelect.addEventListener('change', () => {
                applyImageStyles(selectedImage, sizeRange.value, alignSelect.value);
            });

            resetBtn.addEventListener('click', () => {
                if (!selectedImage) {
                    return;
                }

                sizeRange.value = '100';
                alignSelect.value = 'center';
                sizeValue.textContent = '100%';
                applyImageStyles(selectedImage, 100, 'center');
            });

            document.addEventListener('click', (event) => {
                const target = event.target;
                const clickedInsideEditor = editorElement.contains(target);
                const clickedInsideControls = controls.contains(target);

                if (!clickedInsideEditor && !clickedInsideControls) {
                    hideImageControls();
                }
            });

            const form = hiddenInput.closest('form');
            if (form) {
                form.addEventListener('submit', () => {
                    syncToInput();
                });
            }
        });

        const previewBtn = document.getElementById('preview-welcome-modal-btn');
        const previewOverlay = document.getElementById('welcome-preview-overlay');
        const previewBackdrop = document.getElementById('welcome-preview-backdrop');
        const previewCloseBtn = document.getElementById('welcome-preview-close');
        const previewCard = document.getElementById('welcome-preview-card');
        const previewContent = document.getElementById('welcome-preview-content');
        const welcomeInput = document.getElementById('welcome-editor-input');

        const detectImageOnlyContent = (container) => {
            if (!container) {
                return false;
            }

            const clone = container.cloneNode(true);
            clone.querySelectorAll('script,style,noscript').forEach((el) => el.remove());

            const hasImage = !!clone.querySelector('img');
            const plainText = (clone.textContent || '').replace(/\u00A0/g, ' ').trim();
            return hasImage && plainText.length === 0;
        };

        const renderWelcomePreview = () => {
            if (!previewContent || !welcomeInput || !previewCard) {
                return;
            }

            const welcomeQuill = quillInstances['#welcome-editor'];
            const html = welcomeQuill ? welcomeQuill.root.innerHTML : welcomeInput.value;
            previewContent.innerHTML = html || '';

            const imageOnly = detectImageOnlyContent(previewContent);
            previewCard.classList.toggle('is-image-only', imageOnly);
        };

        const openWelcomePreview = () => {
            if (!previewOverlay) {
                return;
            }

            renderWelcomePreview();
            previewOverlay.classList.remove('d-none');
            previewOverlay.setAttribute('aria-hidden', 'false');
            document.body.classList.add('welcome-preview-open');
        };

        const closeWelcomePreview = () => {
            if (!previewOverlay) {
                return;
            }

            previewOverlay.classList.add('d-none');
            previewOverlay.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('welcome-preview-open');
        };

        if (previewBtn) {
            previewBtn.addEventListener('click', openWelcomePreview);
        }

        if (previewCloseBtn) {
            previewCloseBtn.addEventListener('click', closeWelcomePreview);
        }

        if (previewBackdrop) {
            previewBackdrop.addEventListener('click', closeWelcomePreview);
        }

        const whatsappBox = document.getElementById('whatsapp-session-box');

        if (!whatsappBox) {
            return;
        }

        const statusUrl = whatsappBox.dataset.statusUrl;
        const logoutUrl = whatsappBox.dataset.logoutUrl;
        const csrfToken = whatsappBox.dataset.csrf;

        const loadingState = document.getElementById('wa-loading-state');
        const connectedState = document.getElementById('wa-connected-state');
        const disconnectedState = document.getElementById('wa-disconnected-state');
        const errorState = document.getElementById('wa-error-state');
        const phoneNumber = document.getElementById('wa-phone-number');
        const qrImage = document.getElementById('wa-qr-image');
        const logoutBtn = document.getElementById('wa-logout-btn');
        const refreshBtn = document.getElementById('wa-refresh-btn');

        let loading = false;

        const showState = (state) => {
            loadingState.classList.add('d-none');
            connectedState.classList.add('d-none');
            disconnectedState.classList.add('d-none');
            errorState.classList.add('d-none');

            if (state === 'loading') {
                loadingState.classList.remove('d-none');
                return;
            }

            if (state === 'connected') {
                connectedState.classList.remove('d-none');
                return;
            }

            if (state === 'disconnected') {
                disconnectedState.classList.remove('d-none');
                return;
            }

            errorState.classList.remove('d-none');
        };

        const loadWhatsAppStatus = async () => {
            if (loading) {
                return;
            }

            loading = true;

            try {
                const response = await fetch(statusUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error('Request failed');
                }

                const payload = await response.json();

                if (payload.status === 'connected') {
                    phoneNumber.textContent = payload.phone || '-';
                    showState('connected');
                } else {
                    qrImage.src = payload.qr || '';
                    showState('disconnected');
                }
            } catch (error) {
                showState('error');
            } finally {
                loading = false;
            }
        };

        if (logoutBtn) {
            logoutBtn.addEventListener('click', async () => {
                logoutBtn.disabled = true;

                try {
                    await fetch(logoutUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                } finally {
                    logoutBtn.disabled = false;
                    await loadWhatsAppStatus();
                }
            });
        }

        if (refreshBtn) {
            refreshBtn.addEventListener('click', async () => {
                await loadWhatsAppStatus();
            });
        }

        showState('loading');
        loadWhatsAppStatus();
        setInterval(loadWhatsAppStatus, 3000);
    });
</script>
@endpush