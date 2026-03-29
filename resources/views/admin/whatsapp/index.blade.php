@extends('admin.layout')
@section('title', 'ربط واتساب')

@push('styles')
<style>
    .whatsapp-card {
        max-width: 800px;
        margin: 2rem auto;
        border-radius: 24px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        background: #fff;
    }
    .wa-header {
        background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
        padding: 3rem 2rem;
        color: white;
        text-align: center;
    }
    .wa-header i { font-size: 4rem; text-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    .wa-body { padding: 4rem 2rem; }
    .wa-status-badge {
        padding: 0.75rem 2rem;
        border-radius: 50px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 2.5rem;
        font-size: 1rem;
    }
    .wa-connected { background: rgba(37, 211, 102, 0.1); color: #128C7E; border: 1px solid rgba(37, 211, 102, 0.2); }
    .wa-disconnected { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }
    .wa-qr-container {
        background: #f8fafc;
        border: 2px dashed #cbd5e1;
        padding: 2rem;
        border-radius: 30px;
        display: inline-block;
        margin-top: 1rem;
        transition: all 0.3s ease;
    }
    .wa-qr-container:hover { transform: translateY(-5px); border-color: #25D366; }
    .wa-instruction-step {
        display: flex;
        align-items: flex-start;
        gap: 1.25rem;
        text-align: right;
        margin-bottom: 1.5rem;
        font-size: 1rem;
        color: #334155;
    }
    .wa-step-number {
        width: 28px;
        height: 28px;
        background: #25D366;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-weight: bold;
        font-size: 0.85rem;
    }
    .premium-spinner {
        width: 60px;
        height: 60px;
        border: 4px solid rgba(37, 211, 102, 0.1);
        border-top: 4px solid #25D366;
        border-radius: 50%;
        animation: wa-spin 1s linear infinite;
        margin: 0 auto 1.5rem;
    }
    @keyframes wa-spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="whatsapp-card">
        <div class="wa-header">
            <i class="bi bi-whatsapp mb-3 d-block"></i>
            <h2 class="fw-bold mb-1 text-white">إدارة اتصال واتساب</h2>
            <p class="opacity-90 mt-2 mb-0 h5">اربط متجرك بواتساب لإرسال الإشعارات التلقائية للعملاء</p>
        </div>
        
        <div class="wa-body text-center" id="whatsapp-session-box" 
             data-status-url="{{ route('admin.whatsapp.status') }}" 
             data-logout-url="{{ route('admin.whatsapp.logout') }}" 
             data-csrf="{{ csrf_token() }}">
            
            <div id="wa-loading-state">
                <div class="premium-spinner"></div>
                <div class="h5 text-muted fw-bold">جاري فحص حالة الاتصال...</div>
            </div>

            <div id="wa-connected-state" class="d-none">
                <div class="wa-status-badge wa-connected animate__animated animate__pulse animate__infinite">
                    <i class="bi bi-check-circle-fill"></i> متصل بنجاح
                </div>
                <h3 class="fw-bold mb-2 text-dark" id="wa-phone-number"></h3>
                <p class="text-muted h6 mb-5">النظام جاهز لإرسال الرسائل التلقائية وتحقق OTP.</p>
                
                <div class="p-4 bg-light rounded-4 mb-5 mx-auto" style="max-width: 450px;">
                    <div class="d-flex align-items-center gap-3 text-start">
                        <i class="bi bi-info-circle-fill text-info fs-4"></i>
                        <p class="mb-0 small text-dark">عند قطع الاتصال، سيتوقف النظام عن إرسال الرسائل البرمجية حتى يتم ربط حساب جديد.</p>
                    </div>
                </div>

                <button type="button" id="wa-logout-btn" class="btn btn-danger px-5 py-3 rounded-pill fw-bold shadow-sm">
                    <i class="bi bi-power me-2"></i> قطع الاتصال بالخدمة
                </button>
            </div>

            <div id="wa-disconnected-state" class="d-none">
                <div class="wa-status-badge wa-disconnected mb-5">
                    <i class="bi bi-pause-circle-fill"></i> الخدمة في انتظار الربط
                </div>
                
                <div class="row justify-content-center mb-5 text-start" dir="rtl">
                    <div class="col-md-10 col-lg-8">
                        <div class="wa-instruction-step"><span class="wa-step-number">1</span><span>افتح تطبيق <b>واتساب</b> على هاتفك المحمول.</span></div>
                        <div class="wa-instruction-step"><span class="wa-step-number">2</span><span>افتح <b>القائمة</b> (أو الإعدادات) واختر <b>الأجهزة المرتبطة</b>.</span></div>
                        <div class="wa-instruction-step"><span class="wa-step-number">3</span><span>اضغط على <b>ربط جهاز</b> وقم بتوجيه الكاميرا نحو الكود أدناه.</span></div>
                    </div>
                </div>

                <div class="wa-qr-container" id="wa-qr-box">
                    <img id="wa-qr-image" class="img-fluid d-none" style="width:280px; height:280px">
                    <div id="wa-qr-help" class="py-5 text-muted fw-medium px-4 h6" style="max-width:280px; line-height:1.8">جاري توليد كود الاستجابة السريعة (QR Code)...</div>
                </div>
                
                <div class="mt-5">
                    <button type="button" id="wa-refresh-btn" class="btn btn-outline-brand px-5 py-2 rounded-pill fw-bold">
                        <i class="bi bi-arrow-clockwise me-2"></i> تحديث الرمز أو المحاولة مجدداً
                    </button>
                </div>
            </div>
            
            <div id="wa-error-state" class="d-none">
                <div class="alert alert-danger px-4 py-4 rounded-4 mb-4 border-0 shadow-sm mx-auto" style="max-width: 500px;">
                    <i class="bi bi-x-circle-fill me-2 fs-4"></i> 
                    <span class="h6 fw-bold">عذراً، تعذر الاتصال بخادم واتساب حالياً.</span>
                </div>
                <button type="button" onclick="window.location.reload()" class="btn btn-brand px-5 py-2 rounded-pill fw-bold">
                    إعادة محاولة الاتصال
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const whatsappBox = document.getElementById('whatsapp-session-box');
        if (!whatsappBox) return;

        const statusUrl = whatsappBox.dataset.statusUrl;
        const logoutUrl = whatsappBox.dataset.logoutUrl;
        const csrfToken = whatsappBox.dataset.csrf;

        const loadingState = document.getElementById('wa-loading-state');
        const connectedState = document.getElementById('wa-connected-state');
        const disconnectedState = document.getElementById('wa-disconnected-state');
        const errorState = document.getElementById('wa-error-state');
        const phoneNumber = document.getElementById('wa-phone-number');
        const qrImage = document.getElementById('wa-qr-image');
        const qrHelp = document.getElementById('wa-qr-help');
        const logoutBtn = document.getElementById('wa-logout-btn');
        const refreshBtn = document.getElementById('wa-refresh-btn');

        let loading = false;

        const showState = (state) => {
            loadingState.classList.add('d-none');
            connectedState.classList.add('d-none');
            disconnectedState.classList.add('d-none');
            errorState.classList.add('d-none');

            if (state === 'loading') { loadingState.classList.remove('d-none'); return; }
            if (state === 'connected') { connectedState.classList.remove('d-none'); return; }
            if (state === 'disconnected') { disconnectedState.classList.remove('d-none'); return; }
            errorState.classList.remove('d-none');
        };

        const loadWhatsAppStatus = async (force = false) => {
            if (loading && !force) return;
            loading = true;

            try {
                const response = await fetch(statusUrl, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!response.ok) throw new Error('Request failed');
                const payload = await response.json();

                if (payload.status === 'connected') {
                    phoneNumber.textContent = payload.phone || '-';
                    showState('connected');
                } else {
                    if (payload.qr) {
                        qrImage.src = payload.qr;
                        qrImage.classList.remove('d-none');
                        qrHelp.textContent = 'امسح الباركود من تطبيق واتساب على الهاتف.';
                    } else {
                        qrImage.removeAttribute('src');
                        qrImage.classList.add('d-none');
                        if (payload.status === 'initializing' || payload.status === 'authenticated') {
                            qrHelp.textContent = 'الخدمة تعمل الآن، انتظر قليلاً حتى يتم توليد الباركود.';
                        } else if (payload.last_error) {
                            qrHelp.textContent = 'تعذر توليد الباركود: ' + payload.last_error;
                        } else {
                            qrHelp.textContent = 'لم يتم استلام باركود حتى الآن. جرّب التحديث بعد ثوانٍ.';
                        }
                    }
                    showState('disconnected');
                }
            } catch (error) {
                console.error('WhatsApp status error:', error);
                showState('error');
            } finally { loading = false; }
        };

        if (logoutBtn) {
            logoutBtn.addEventListener('click', async () => {
                if(!confirm('هل أنت متأكد من قطع الاتصال؟ سيتم تعطيل إشعارات واتساب تلقائياً.')) return;
                
                const originalHtml = logoutBtn.innerHTML;
                logoutBtn.disabled = true;
                logoutBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> جاري قطع الاتصال...';

                try {
                    const response = await fetch(logoutUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const payload = await response.json().catch(() => ({}));

                    if (!response.ok || payload.success === false) {
                        const errorMsg = payload.message || 'فشل تسجيل الخروج من واتساب. حاول مرة أخرى.';
                        if (window.showToast) window.showToast('فشل العملية', errorMsg, 'error');
                        qrHelp.textContent = 'فشل تسجيل الخروج: ' + errorMsg;
                    } else {
                        if (window.showToast) window.showToast('تم بنجاح', 'تم قطع الاتصال بخدمة واتساب.', 'success');
                        qrHelp.textContent = 'تم تسجيل الخروج بنجاح. يتم الآن توليد كود جديد...';
                    }
                } catch (err) {
                    console.error('Logout error:', err);
                    if (window.showToast) window.showToast('خطأ في الاتصال', 'تعذر الوصول إلى الخادم لإتمام عملية تسجيل الخروج.', 'error');
                } finally {
                    logoutBtn.disabled = false;
                    logoutBtn.innerHTML = originalHtml;
                    await loadWhatsAppStatus(true);
                }
            });
        }

        /* START: WhatsApp Refresh Animation */
        const style = document.createElement('style');
        style.textContent = `
            @keyframes wa-spin-btn { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
            .wa-spin { display: inline-block; animation: wa-spin-btn 1s linear infinite; }
        `;
        document.head.appendChild(style);

        if (refreshBtn) {
            refreshBtn.addEventListener('click', async () => {
                const icon = refreshBtn.querySelector('i');
                refreshBtn.disabled = true;
                if (icon) icon.classList.add('wa-spin');
                
                try {
                    await loadWhatsAppStatus(true);
                    if (window.showToast) window.showToast('تحديث', 'تم تحديث حالة الواتساب وجاري جلب الباركود...', 'info');
                } finally {
                    setTimeout(() => {
                        refreshBtn.disabled = false;
                        if (icon) icon.classList.remove('wa-spin');
                    }, 1000);
                }
            });
        }

        showState('loading');
        loadWhatsAppStatus();
        setInterval(loadWhatsAppStatus, 3000);
    });
</script>
@endpush
