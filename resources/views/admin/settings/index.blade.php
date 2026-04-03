@extends('admin.layout')
@section('title', 'إعدادات الموقع')

@push('styles')
<link rel="stylesheet" href="https://cdn.quilljs.com/1.3.7/quill.snow.css">
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem 0 3rem; color: white; border-radius: 0 !important; }
    .nav-tabs-custom { border-bottom: none !important; margin-top: 1.5rem; }
    .nav-tabs-custom .nav-link { color: rgba(255,255,255,0.7) !important; border: none !important; border-bottom: 3px solid transparent !important; padding: 1rem 1.5rem !important; font-weight: 600 !important; transition: all 0.3s; }
    .nav-tabs-custom .nav-link:hover { color: #fff !important; }
    .nav-tabs-custom .nav-link.active { color: #fff !important; background: transparent !important; border-bottom-color: var(--accent-gold) !important; }
    
    .settings-group-card { border: 1px solid #f1f5f9; border-radius: 16px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); transition: all 0.2s; background: #fff; height: 100%; }
    .settings-group-card:hover { border-color: #e2e8f0; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); }
    .settings-group-header { background: #fafbff; border-bottom: 1px solid #f1f5f9; padding: 1.25rem; border-radius: 16px 16px 0 0; }
    .settings-group-header h6 { margin: 0; font-weight: 700; color: var(--primary-dark); }
    
    .quill-editor-wrapper .ql-toolbar.ql-snow { border-radius: 12px 12px 0 0; border-color: #e2e8f0; background: #fafbff; }
    .quill-editor-wrapper .ql-container.ql-snow { border-radius: 0 0 12px 12px; border-color: #e2e8f0; min-height: 200px; }

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

    /* WhatsApp Premium Styles */
    .whatsapp-card {
        max-width: 600px;
        margin: 2rem auto;
        border-radius: 24px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }
    .wa-header {
        background: #25D366;
        padding: 2.5rem 1.5rem;
        color: white;
        text-align: center;
    }
    .wa-header i { font-size: 3.5rem; }
    .wa-body { padding: 3rem 2rem; background: #fff; }
    .wa-status-badge {
        padding: 0.6rem 1.5rem;
        border-radius: 50px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 2rem;
    }
    .wa-connected { background: rgba(37, 211, 102, 0.1); color: #128C7E; border: 1px solid rgba(37, 211, 102, 0.2); }
    .wa-disconnected { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }
    .wa-qr-container {
        background: #fff;
        border: 1px dashed #cbd5e1;
        padding: 1.5rem;
        border-radius: 20px;
        display: inline-block;
        margin-top: 1rem;
        transition: transform 0.3s ease;
    }
    .wa-qr-container:hover { transform: scale(1.02); }
    .wa-instruction-step {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        text-align: right;
        margin-bottom: 1rem;
        font-size: 0.9rem;
        color: #475569;
    }
    .wa-step-number {
        width: 24px;
        height: 24px;
        background: #25D366;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-weight: bold;
        font-size: 0.75rem;
    }

    /* Google Preview Styles */
    .google-preview-card {
        background: #fff;
        border: 1px solid #dfe1e5;
        border-radius: 12px;
        padding: 1.5rem;
        max-width: 600px;
        box-shadow: 0 1px 6px rgba(32,33,36,0.1);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        text-align: right;
    }
    .gp-url { color: #202124; font-size: 14px; margin-bottom: 4px; display: block; opacity: 0.8; }
    .gp-title { color: #1a0dab; font-size: 20px; line-height: 1.3; font-weight: 500; display: block; margin-bottom: 5px; }
    .gp-desc { color: #4d5156; font-size: 14px; line-height: 1.58; word-wrap: break-word; }
</style>
@endpush

@section('content')
@php
    $canGeneral = auth()->user()->can('edit-settings');
    $canFrontend = auth()->user()->can('edit-settings-frontend');
    $canSEO = auth()->user()->can('edit-settings-seo');
    $canIntegrations = auth()->user()->can('manage-whatsapp');
    
    $activeTab = 'none';
    if ($canGeneral) {
        $activeTab = 'general';
    } elseif ($canFrontend) {
        $activeTab = 'frontend';
    } elseif ($canSEO) {
        $activeTab = 'seo';
    } elseif ($canIntegrations) {
        $activeTab = 'integrations';
    }
@endphp
<div class="form-card">
    <div class="form-card-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="mb-2 fw-bold text-white"><i class="bi bi-sliders me-2"></i> إعدادات وتكوين النظام</h2>
                <p class="mb-0 opacity-75 fs-6 text-white small">إدارة الخيارات العامة، المظهر، معايير SEO، وتحكم الربط البرمجي.</p>
            </div>
            <div>
                <button type="submit" form="settings-form" class="btn btn-light px-5 py-2 fw-bold text-brand shadow-sm" style="border-radius:12px">
                    <i class="bi bi-check2-circle me-1"></i> حفظ كافة التغييرات
                </button>
            </div>
        </div>

        <ul class="nav nav-tabs nav-tabs-custom" id="settingsTabs" role="tablist">
            @can('edit-settings')
            <li class="nav-item">
                <button class="nav-link {{ $activeTab == 'general' ? 'active' : '' }}" id="general-tab" data-bs-toggle="tab" data-bs-target="#general-tab-pane" type="button" role="tab">عام</button>
            </li>
            @endcan
            @can('edit-settings-frontend')
            <li class="nav-item">
                <button class="nav-link {{ $activeTab == 'frontend' ? 'active' : '' }}" id="frontend-tab" data-bs-toggle="tab" data-bs-target="#frontend-tab-pane" type="button" role="tab">واجهة الموقع</button>
            </li>
            @endcan
            @can('edit-settings-seo')
            <li class="nav-item">
                <button class="nav-link {{ $activeTab == 'seo' ? 'active' : '' }}" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo-tab-pane" type="button" role="tab">SEO</button>
            </li>
            @endcan
            @can('manage-whatsapp')
            <li class="nav-item">
                <button class="nav-link {{ $activeTab == 'integrations' ? 'active' : '' }}" id="integrations-tab" data-bs-toggle="tab" data-bs-target="#integrations-tab-pane" type="button" role="tab">الربط البرمجي</button>
            </li>
            @endcan
        </ul>

    </div>

    <div class="p-4 p-lg-5">
        <form action="{{ route('admin.settings.update') }}" method="POST" id="settings-form">
            @csrf
            @method('PATCH')
            
            <div class="tab-content" id="settingsTabsContent">
                @can('edit-settings')
                <div class="tab-pane fade {{ $activeTab == 'general' ? 'show active' : '' }}" id="general-tab-pane" role="tabpanel" aria-labelledby="general-tab">
                    <div class="row g-4">
                        {{-- ⚡ وضع الصيانة --}}
                        <div class="col-md-6">
                            <div class="settings-group-card">
                                <div class="settings-group-header d-flex align-items-center">
                                    <i class="bi bi-lightning-charge-fill me-2 fs-5 text-warning"></i>
                                    <h6>وضع الصيانة (Maintenance Mode)</h6>
                                </div>
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div>
                                            <h6 class="fw-bold mb-1">تفعيل الإغلاق المؤقت</h6>
                                            <p class="text-muted small mb-0">عند التفعيل، سيظهر تنبيه للزوار بأن الموقع تحت الصيانة.</p>
                                        </div>
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input" type="checkbox" id="maintenance_mode" name="maintenance_mode" style="width: 3.5em; height: 1.75em;" @checked(old('maintenance_mode', $settings['maintenance_mode'] ?? 'off') == 'on')>
                                        </div>
                                    </div>
                                    <div class="alert bg-soft-warning border-0 p-3 mb-0" style="border-radius:12px">
                                        <i class="bi bi-info-circle-fill me-1"></i> سيبقى مدراء النظام قادرين على تصفح الموقع بشكل طبيعي.
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ⏰ مدة الجلسة --}}
                        <div class="col-md-6">
                            <div class="settings-group-card">
                                <div class="settings-group-header d-flex align-items-center">
                                    <i class="bi bi-clock-history me-2 fs-5 text-primary"></i>
                                    <h6>مدة بقاء الجلسة (Session Lifetime)</h6>
                                </div>
                                <div class="card-body p-4">
                                    <label class="small fw-bold text-muted mb-3">مدة تسجيل الدخول التلقائي بالدقائق</label>
                                    <div class="input-group input-group-lg overflow-hidden" style="border-radius:14px">
                                        <input type="number" name="session_lifetime" min="1" class="form-control border-end-0" value="{{ old('session_lifetime', $settings['session_lifetime'] ?? 120) }}" placeholder="120">
                                        <span class="input-group-text bg-light fw-bold px-4">دقيقة</span>
                                    </div>
                                    <p class="text-muted small mt-3 mb-0"><i class="bi bi-shield-lock me-1"></i> يُنصح بضبطها على 120-240 دقيقة للتوزان بين الأمان وسهولة الاستخدام.</p>
                                </div>
                            </div>
                        </div>

                        {{-- 🔐 التحقق عبر واتساب (OTP) --}}
                        <div class="col-md-6">
                            <div class="settings-group-card">
                                <div class="settings-group-header d-flex align-items-center">
                                    <i class="bi bi-shield-check me-2 fs-5 text-success"></i>
                                    <h6>نظام التحقق وسرية البيانات (OTP)</h6>
                                </div>
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div>
                                            <h6 class="fw-bold mb-1">التحقق عبر الواتساب</h6>
                                            <p class="text-muted small mb-0">إرسال كود تحقق للمستخدمين الجدد لتأكيد الهوية.</p>
                                        </div>
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input" type="checkbox" id="otp_enabled" name="otp_enabled" value="1" style="width: 3.5em; height: 1.75em;" {{ !env('OTP_DISABLED', false) ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                    <div class="alert bg-soft-success border-0 p-3 mb-0" style="border-radius:12px">
                                        <i class="bi bi-whatsapp me-1"></i> تتطلب هذه الخاصية ربط حساب الواتساب في تبويب "واتساب".
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 🚚 تكاليف التوصيل --}}
                        <div class="col-md-6">
                            <div class="settings-group-card">
                                <div class="settings-group-header d-flex align-items-center">
                                    <i class="bi bi-truck me-2 fs-5 text-brand"></i>
                                    <h6>تكاليف التوصيل والشحن المجاني</h6>
                                </div>
                                <div class="card-body p-4">
                                    <div class="form-check form-switch mb-4">
                                        <input class="form-check-input" type="checkbox" name="shipping_enabled" id="shipping_enabled" value="1" {{ old('shipping_enabled', $settings['shipping_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold" for="shipping_enabled">تفعيل احتساب التوصيل في السلة</label>
                                    </div>

                                    <div class="form-check form-switch mb-4">
                                        <input class="form-check-input" type="checkbox" name="free_shipping_enabled" id="free_shipping_enabled" value="1" {{ old('free_shipping_enabled', $settings['free_shipping_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold" for="free_shipping_enabled">تفعيل ميزة الشحن المجاني</label>
                                    </div>
                                    
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <label class="small fw-bold text-muted mb-2">سعر التوصيل (د.ع)</label>
                                            <input type="number" name="shipping_cost" class="form-control" value="{{ old('shipping_cost', $settings['shipping_cost'] ?? 5000) }}" style="border-radius:10px">
                                        </div>
                                        <div class="col-6">
                                            <label class="small fw-bold text-muted mb-2">حد الشحن المجاني</label>
                                            <input type="number" name="free_shipping_threshold" class="form-control" value="{{ old('free_shipping_threshold', $settings['free_shipping_threshold'] ?? 50000) }}" style="border-radius:10px">
                                        </div>
                                    </div>
                                    <div class="mt-3 p-2 bg-light rounded-3 small text-muted"> <i class="bi bi-info-circle me-1"></i> يظهر الشحن "مجاني" عندما يتجاوز إجمالي الطلب الحد المحدد أعلاه.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @endcan

                @can('edit-settings-frontend')
                <div class="tab-pane fade {{ $activeTab == 'frontend' ? 'show active' : '' }}" id="frontend-tab-pane" role="tabpanel">
                    <div class="row g-4">
                        <div class="col-12">
                            <div class="settings-group-card">
                                <div class="settings-group-header d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-window-stack me-2 fs-5 text-primary"></i>
                                        <h6 class="mb-0">النافذة المنبثقة الترحيبية (Welcome Popup)</h6>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" id="show_welcome_screen" name="show_welcome_screen" style="width: 3.2em; height: 1.6em;" @checked(old('show_welcome_screen', $settings['show_welcome_screen'] ?? 'off') == 'on')>
                                    </div>
                                </div>
                                <div class="card-body p-4">
                                    <p class="text-muted small mb-3">تظهر هذه النافذة للزائر بمجرد دخوله للموقع، يمكنك استخدامها للعروض الخاصة أو التنبيهات المهمة.</p>
                                    <div class="quill-editor-wrapper">
                                        <div id="welcome-editor" class="quill-editor" data-placeholder="اكتب محتوى النافذة الترحيبية هنا..."></div>
                                        <textarea name="welcome_screen_content" id="welcome-editor-input" class="d-none">{{ old('welcome_screen_content', $settings['welcome_screen_content'] ?? '') }}</textarea>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <button type="button" id="preview-welcome-modal-btn" class="btn btn-soft-primary rounded-pill px-4 fw-bold small"><i class="bi bi-eye me-1"></i> عرض مباشر للنافذة</button>
                                        <span class="small text-muted"><i class="bi bi-info-circle me-1"></i> يدعم إضافة الصور والروابط.</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="settings-group-card">
                                <div class="settings-group-header d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-megaphone me-2 fs-5 text-danger"></i>
                                        <h6 class="mb-0">شريط الإعلانات المتحرك (Notice Bar)</h6>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" id="show_dashboard_notification" name="show_dashboard_notification" style="width: 3.2em; height: 1.6em;" @checked(old('show_dashboard_notification', $settings['show_dashboard_notification'] ?? 'off') == 'on')>
                                    </div>
                                </div>
                                <div class="card-body p-4">
                                    <div class="quill-editor-wrapper mb-4">
                                        <div id="notification-editor" class="quill-editor" data-placeholder="نص الإعلان الذي سيظهر في أعلى المتجر..."></div>
                                        <textarea name="dashboard_notification_content" id="notification-editor-input" class="d-none">{{ old('dashboard_notification_content', $settings['dashboard_notification_content'] ?? '') }}</textarea>
                                    </div>
                                    <div class="row align-items-center bg-light p-3 rounded-4">
                                        <div class="col-md-6">
                                            <label class="small fw-bold text-muted mb-2">طريقة عرض الحركة</label>
                                            <select name="dashboard_notification_animation" class="form-select border-0 shadow-sm">
                                                <option value="none" @selected(old('dashboard_notification_animation', $settings['dashboard_notification_animation'] ?? 'none') == 'none')>نص ثابت بدون حركة</option>
                                                <option value="scroll-left" @selected(old('dashboard_notification_animation', $settings['dashboard_notification_animation'] ?? 'none') == 'scroll-left')>تمرير مستمر جهة اليسار</option>
                                                <option value="scroll-right" @selected(old('dashboard_notification_animation', $settings['dashboard_notification_animation'] ?? 'none') == 'scroll-right')>تمرير مستمر جهة اليمين</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mt-3 mt-md-0">
                                            <label class="small fw-bold text-muted mb-2" for="dashboard_notification_bg_color">لون الخلفية</label>
                                            <input
                                                type="color"
                                                id="dashboard_notification_bg_color"
                                                name="dashboard_notification_bg_color"
                                                class="form-control form-control-color w-100"
                                                value="{{ old('dashboard_notification_bg_color', $settings['dashboard_notification_bg_color'] ?? '#000000') }}"
                                                title="لون خلفية شريط الإعلانات"
                                            >
                                        </div>
                                        <div class="col-md-3 mt-3 mt-md-0">
                                            <label class="small fw-bold text-muted mb-2" for="dashboard_notification_text_color">لون النص</label>
                                            <input
                                                type="color"
                                                id="dashboard_notification_text_color"
                                                name="dashboard_notification_text_color"
                                                class="form-control form-control-color w-100"
                                                value="{{ old('dashboard_notification_text_color', $settings['dashboard_notification_text_color'] ?? '#FFFFFF') }}"
                                                title="لون نص شريط الإعلانات"
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @endcan

                @can('edit-settings-seo')
                <div class="tab-pane fade {{ $activeTab == 'seo' ? 'show active' : '' }}" id="seo-tab-pane" role="tabpanel">
                    <div class="settings-group-card border-0 shadow-sm overflow-hidden" style="border-radius:24px">
                        <div class="settings-group-header d-flex align-items-center bg-white py-4 border-bottom">
                            <i class="bi bi-search me-3 fs-3 text-secondary"></i>
                            <div>
                                <h5 class="fw-bold mb-1">تهيئة محركات البحث وSEO</h5>
                                <p class="text-muted small mb-0">تحكم في كيفية ظهور متجرك على محركات البحث مثل <span class="text-primary fw-bold">Google</span>.</p>
                            </div>
                        </div>
                        <div class="card-body p-4 p-lg-5">
                            <div class="row g-5">
                                <div class="col-lg-7">
                                    <h6 class="fw-bold mb-4 text-dark border-start border-4 border-info ps-3">تكوين البيانات الوصفية (Metadata)</h6>
                                    
                                    {{-- Arabic SEO --}}
                                    <div class="card bg-light border-0 mb-4" style="border-radius:18px">
                                        <div class="card-body p-4">
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label class="small fw-bold text-muted mb-2">اسم المتجر (باللغة العربية)</label>
                                                    <input type="text" name="site_title_ar" id="seo_title_input" class="form-control border-white shadow-sm" style="border-radius:10px" value="{{ old('site_title_ar', $settings['site_title_ar'] ?? 'طفوف') }}">
                                                </div>
                                                <div class="col-12">
                                                    <label class="small fw-bold text-muted mb-2">الوصف التعريفي (AR Meta Description)</label>
                                                    <textarea name="meta_description_ar" id="seo_desc_input" class="form-control border-white shadow-sm" style="border-radius:10px" rows="3">{{ old('meta_description_ar', $settings['meta_description_ar'] ?? '') }}</textarea>
                                                    <div class="d-flex justify-content-between mt-2 small fw-bold">
                                                        <span class="text-muted">الحد الموصى به: 160 حرفاً.</span>
                                                        <span class="text-info" id="char_count">0 / 160</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- English SEO --}}
                                    <div class="card border-0 mb-0" style="border-radius:18px; background: rgba(13, 110, 253, 0.02); border: 1px dashed rgba(13, 110, 253, 0.1) !important;">
                                        <div class="card-body p-4">
                                            <div class="row g-3 text-end" dir="ltr">
                                                <div class="col-12 text-start">
                                                    <label class="small fw-bold text-muted mb-2">Store Name (English)</label>
                                                    <input type="text" name="site_title_en" class="form-control border-white shadow-sm" style="border-radius:10px" value="{{ old('site_title_en', $settings['site_title_en'] ?? 'Tofof') }}">
                                                </div>
                                                <div class="col-12 text-start">
                                                    <label class="small fw-bold text-muted mb-2">Meta Description (EN)</label>
                                                    <textarea name="meta_description_en" class="form-control border-white shadow-sm" style="border-radius:10px" rows="3">{{ old('meta_description_en', $settings['meta_description_en'] ?? '') }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-5">
                                    <h6 class="fw-bold mb-4 text-dark border-start border-4 border-success ps-3">معاينة نتيجة البحث (Preview)</h6>
                                    <div class="bg-light p-4 rounded-4 h-100 d-flex flex-column justify-content-center border border-dashed">
                                        <div class="google-preview-card mb-3 shadow-md mx-auto w-100">
                                            <span class="gp-url">{{ url('/') }} › </span>
                                            <span class="gp-title" id="gp_title">طفوف - اسم المتجر</span>
                                            <span class="gp-desc" id="gp_desc">هنا سيظهر وصف متجرك الذي تكتبه باللغة العربية، تأكد من كتابة وصف جذاب لجذب الزوار.</span>
                                        </div>
                                        <p class="text-center text-muted small mb-0 mt-3 p-2"><i class="bi bi-info-circle me-1"></i> يتم التركيز في المعاينة على النسخة العربية لأنها الأكثر ظهوراً في المنطقة.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                @endcan

                @can('manage-whatsapp')
                <div class="tab-pane fade {{ $activeTab == 'integrations' ? 'show active' : '' }}" id="integrations-tab-pane" role="tabpanel" aria-labelledby="integrations-tab">
                    <div class="row g-4">
                        {{-- Telegram Settings --}}
                        <div class="col-md-12">
                            <div class="settings-group-card">
                                <div class="settings-group-header d-flex align-items-center">
                                    <i class="bi bi-telegram me-2 fs-5 text-info"></i>
                                    <h6>إعدادات تليجرام (Telegram Notifications)</h6>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row">
                                        <div class="col-md-6 mb-4">
                                            <label class="form-label fw-bold small text-muted">بوت توكن (Bot Token)</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light"><i class="bi bi-key-fill text-muted"></i></span>
                                                <input type="password" name="telegram_bot_token" class="form-control" value="{{ old('telegram_bot_token', $settings['telegram_bot_token'] ?? '') }}" placeholder="0000000000:AAHHHxxxx_xxxxxxxxxxxx">
                                            </div>
                                            <p class="text-muted small mt-2">تحصل عليه من @BotFather عند إنشاء البوت.</p>
                                        </div>
                                        <div class="col-md-3 mb-4">
                                            <label class="form-label fw-bold small text-muted">ID كروب الطلبات (Orders ID)</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light"><i class="bi bi-cart-check text-muted"></i></span>
                                                <input type="text" name="telegram_chat_id" class="form-control" value="{{ old('telegram_chat_id', $settings['telegram_chat_id'] ?? '') }}" placeholder="-100xxxxxxxxx">
                                            </div>
                                            <p class="text-muted small mt-2">معرف الكروب الخاص بالطلبات الجديدة.</p>
                                        </div>
                                        <div class="col-md-3 mb-4">
                                            <label class="form-label fw-bold small text-muted">ID كروب النسخة الاحتياطية (Backup ID)</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light"><i class="bi bi-database-fill text-muted"></i></span>
                                                <input type="text" name="telegram_backup_chat_id" class="form-control" value="{{ old('telegram_backup_chat_id', $settings['telegram_backup_chat_id'] ?? '') }}" placeholder="-100xxxxxxxxx">
                                            </div>
                                            <p class="text-muted small mt-2">معرف الكروب الخاص بنسخ قاعدة البيانات.</p>
                                        </div>
                                    </div>
                                    <div class="alert bg-soft-info border-0 p-3 mb-0" style="border-radius:12px">
                                        <i class="bi bi-info-circle-fill me-1"></i> يُرجى التأكد من إضافة البوت كـ "مسؤول" (Admin) داخل الكروبات المختارة لضمان وصول الإشعارات.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endcan

            </div> <!-- closes tab-content -->
        </form>

        <div id="welcome-preview-overlay" class="welcome-preview-overlay d-none" aria-hidden="true">
            <div class="welcome-preview-backdrop" id="welcome-preview-backdrop"></div>
            <div id="welcome-preview-card" class="welcome-preview-card">
                <button type="button" class="welcome-preview-close" id="welcome-preview-close" aria-label="إغلاق المعاينة">
                    <i class="bi bi-x-lg"></i>
                </button>
                <div id="welcome-preview-content" class="welcome-preview-content" dir="rtl"></div>
            </div>
        </div>

        <div class="card-footer text-end py-3">
            <button type="submit" form="settings-form" class="btn text-white px-5 fw-bold" style="background-color: var(--primary-dark);">
                <i class="bi bi-save me-1"></i> حفظ جميع الإعدادات
            </button>
        </div>
    </div>
</div>

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



        // SEO Preview Logic
        const seoTitleInput = document.getElementById('seo_title_input');
        const seoDescInput = document.getElementById('seo_desc_input');
        const gpTitle = document.getElementById('gp_title');
        const gpDesc = document.getElementById('gp_desc');
        const charCount = document.getElementById('char_count');

        const updateSEOPreview = () => {
            const title = seoTitleInput.value || 'طفوف - اسم المتجر';
            const desc = seoDescInput.value || 'هنا سيظهر وصف متجرك الذي تكتبه في الحقل المقابل، تأكد من كتابة وصف جذاب لجذب الزوار من محركات البحث.';
            
            gpTitle.textContent = title;
            gpDesc.textContent = desc;
            
            const len = seoDescInput.value.length;
            charCount.textContent = `${len} / 160`;
            charCount.className = len > 160 ? 'small fw-bold text-danger' : (len > 120 ? 'small fw-bold text-warning' : 'small fw-bold text-info');
        };

        seoTitleInput.addEventListener('input', updateSEOPreview);
        seoDescInput.addEventListener('input', updateSEOPreview);
        updateSEOPreview();

        // تفعيل التبويب من الرابط (e.g., ?tab=integrations)
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab');
        if (tabParam) {
            const tabTriggerEl = document.querySelector(`#${tabParam}-tab`);
            if (tabTriggerEl) {
                const tab = new bootstrap.Tab(tabTriggerEl);
                tab.show();
            }
        }
    });
</script>
@endpush