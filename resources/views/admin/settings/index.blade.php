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
                ['link', 'blockquote', 'code-block'],
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
                    'link', 'blockquote', 'code-block'
                ]
            });

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
            });

            const form = hiddenInput.closest('form');
            if (form) {
                form.addEventListener('submit', () => {
                    syncToInput();
                });
            }
        });
    });
</script>
@endpush