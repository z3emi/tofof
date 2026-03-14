@extends('layouts.app')
@section('title', 'إعادة تعيين كلمة السر')
@push('styles')
<style>
    /* تحسينات للخلفية (وضع فاتح) */
    body {
        background-color: #f9f5f1;
        background-image:
            radial-gradient(circle at 10% 20%, rgba(205, 137, 133, 0.05) 0%, transparent 20%),
            radial-gradient(circle at 90% 80%, rgba(205, 137, 133, 0.05) 0%, transparent 20%);
    }

    /* تحسينات للبطاقة (وضع فاتح) */
    .bg-white {
        background-color: #ffffff;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        border: 1px solid #f3e5e3;
    }

    /* الحقول */
    input[type="text"], input[type="password"] {
        transition: all 0.3s ease;
        background-color: #fff;
        border: 2px solid #e5e7eb;
        color: #111827;
    }
    input[type="text"]::placeholder,
    input[type="password"]::placeholder { color:#9ca3af; }

    input[type="text"]:focus, input[type="password"]:focus {
        background-color: #fff9f9;
        border-color: #cd8985;
        box-shadow: 0 0 0 3px rgba(205, 137, 133, 0.2);
        outline: none;
    }

    /* الأزرار */
    button { transition: all 0.3s ease; }

    /* تحسينات للجوال */
    @media (max-width: 640px) {
        .container { padding-left: 1rem; padding-right: 1rem; }
        .py-8 { padding-top: 2rem; padding-bottom: 2rem; }
        .px-6 { padding-left: 1.5rem; padding-right: 1.5rem; }
    }

    /* RTL */
    [dir="rtl"] input[type="text"],
    [dir="rtl"] input[type="password"] { direction: rtl; text-align: right; }

    /* أيقونات الحقول */
    .field-icon-left {
        position: absolute; left: 1rem; top: 50%; transform: translateY(-50%);
        pointer-events: none; z-index: 10;
    }
    .field-icon-right {
        position: absolute; right: 1rem; top: 50%; transform: translateY(-50%);
        cursor: pointer; z-index: 10;
    }
    [dir="rtl"] .field-icon-left { right: 1rem; left: auto; }
    [dir="rtl"] .field-icon-right { left: 1rem; right: auto; }

    /* مسافات داخلية للحقول مع الأيقونات */
    .input-with-icons { padding-right: 3rem !important; padding-left: 3rem !important; }
    [dir="rtl"] .input-with-icons { padding-right: 3rem !important; padding-left: 3rem !important; }
    .input-with-icon-right { padding-left: 3rem !important; }
    [dir="rtl"] .input-with-icon-right { padding-left: 0 !important; padding-right: 3rem !important; }
    .input-with-icon-left { padding-right: 3rem !important; }
    [dir="rtl"] .input-with-icon-left { padding-right: 0 !important; padding-left: 3rem !important; }

    /* =========================
       وضع داكن (Dark Mode)
       ========================= */
    html.dark body{
        background-color:#0b0f14;
        background-image:
            radial-gradient(circle at 10% 20%, rgba(205,137,133,.05) 0%, transparent 22%),
            radial-gradient(circle at 90% 80%, rgba(205,137,133,.05) 0%, transparent 22%);
    }
    html.dark .bg-white{
        background-color:#0f172a;               /* سطح البطاقة */
        border-color:#1f2937;
        box-shadow:0 10px 30px rgba(0,0,0,.22);
    }
    /* نصوص عامة */
    html.dark .text-gray-600{ color:#d1d5db !important; }
    html.dark .text-gray-500{ color:#94a3b8 !important; }
    html.dark .text-brand-text{ color:#e5e7eb !important; }

    /* الحقول في الوضع الداكن */
    html.dark input[type="text"],
    html.dark input[type="password"]{
        background-color:#0f172a;
        border-color:#1f2937;
        color:#e5e7eb;
    }
    html.dark input[type="text"]::placeholder,
    html.dark input[type="password"]::placeholder{ color:#94a3b8; }
    html.dark input[type="text"]:focus,
    html.dark input[type="password"]:focus{
        background-color:#0f172a;
        border-color:#cd8985;
        box-shadow:0 0 0 3px rgba(205,137,133,.22);
    }

    /* أيقونات Bootstrap الرمادية */
    html.dark .text-gray-400{ color:#9aa4b2 !important; }

    /* الفواصل والحدود داخل البطاقة */
    html.dark .border-gray-200{ border-color:#374151 !important; }

    /* =========================
       إخفاء البحث + الفوتر لهذه الصفحة فقط
       ========================= */
    header form[action*="{{ route('products.search') }}"],
    header form[role="search"],
    header .search, header .search-form, header .searchbar,
    header .main-search, header .desktop-search, header .mobile-search,
    header .container form {
        display: none !important;
    }
    footer, .site-footer, .footer, .footer-mobile {
        display: none !important;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8" dir="rtl">
    <div class="max-w-md w-full space-y-8">
        <!-- Logo and Title -->
        <div class="text-center mb-10">
            <div class="flex justify-center mb-4">
                <div class="w-20 h-20 rounded-full bg-brand-secondary flex items-center justify-center">
                    <img src="{{ asset('logo.png') }}" alt="Tofof Logo" class="w-16 h-16">
                </div>
            </div>
            <h2 class="text-3xl font-extrabold text-brand-text mb-2">أدخل الرمز وكلمة السر الجديدة</h2>
            <p class="text-gray-600">لقد أرسلنا رمزًا إلى رقم هاتفك</p>
            <p class="text-brand-primary font-medium">{{ session('phone_number_for_reset') }}</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="py-8 px-6 md:px-8">
                @if (session('status'))
                    <div class="mb-6 bg-green-50 border-r-4 border-green-500 p-4 rounded-md dark:bg-[#0f172a] dark:border-green-600">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="bi bi-check-circle-fill text-green-500 text-xl"></i>
                            </div>
                            <div class="mr-3">
                                <h3 class="text-sm font-medium text-green-800 dark:text-green-300">نجاح</h3>
                                <div class="mt-2 text-sm text-green-700 dark:text-green-200">
                                    <p>{{ session('status') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border-r-4 border-red-500 p-4 rounded-md dark:bg-[#1f2937] dark:border-red-500">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="bi bi-exclamation-triangle-fill text-red-500 text-xl"></i>
                            </div>
                            <div class="mr-3">
                                <h3 class="text-sm font-medium text-red-800 dark:text-red-300">حدث خطأ</h3>
                                <div class="mt-2 text-sm text-red-700 dark:text-red-300/90">
                                    <ul class="list-disc pr-5 space-y-1">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update.with.otp') }}" x-data="{ 
                    showPassword: false,
                    showConfirmPassword: false
                }">
                    @csrf
                    <input type="hidden" name="phone_number" value="{{ session('phone_number_for_reset') }}">

                    <div class="mb-6">
                        <label for="otp" class="block text-gray-700 font-medium mb-2 dark:text-gray-200">رمز التحقق (OTP)</label>
                        <div class="relative">
                            <i class="bi bi-shield-lock text-gray-400 field-icon-right"></i>
                            <input id="otp" type="text"
                                   class="w-full px-4 py-3 input-with-icon-right border-2 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-primary focus:border-transparent"
                                   name="otp" required autofocus placeholder="أدخل الرمز المرسل">
                        </div>
                    </div>

                    <div class="mb-6">
                        <label for="password" class="block text-gray-700 font-medium mb-2 dark:text-gray-200">كلمة المرور الجديدة</label>
                        <div class="relative">
                            <i class="bi bi-lock text-gray-400 field-icon-left"></i>
                            <span class="field-icon-right text-gray-500 hover:text-brand-primary" @click="showPassword = !showPassword">
                                <i class="bi text-lg" :class="showPassword ? 'bi-eye-slash' : 'bi-eye'"></i>
                            </span>
                            <input :type="showPassword ? 'text' : 'password'" id="password"
                                   class="w-full px-4 py-3 input-with-icons border-2 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-primary focus:border-transparent"
                                   name="password" required placeholder="أدخل كلمة المرور الجديدة">
                        </div>
                    </div>

                    <div class="mb-8">
                        <label for="password-confirm" class="block text-gray-700 font-medium mb-2 dark:text-gray-200">تأكيد كلمة المرور</label>
                        <div class="relative">
                            <i class="bi bi-lock-fill text-gray-400 field-icon-left"></i>
                            <span class="field-icon-right text-gray-500 hover:text-brand-primary" @click="showConfirmPassword = !showConfirmPassword">
                                <i class="bi text-lg" :class="showConfirmPassword ? 'bi-eye-slash' : 'bi-eye'"></i>
                            </span>
                            <input :type="showConfirmPassword ? 'text' : 'password'" id="password-confirm"
                                   class="w-full px-4 py-3 input-with-icons border-2 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-primary focus:border-transparent"
                                   name="password_confirmation" required placeholder="أعد كتابة كلمة المرور">
                        </div>
                    </div>

                    <div class="mb-6">
                        <button type="submit"
                                class="w-full bg-brand-dark text-white font-bold py-3 px-4 rounded-full hover:bg-brand-primary transition duration-300 transform hover:-translate-y-0.5 shadow-lg hover:shadow-xl flex items-center justify-center">
                            <i class="bi bi-shield-check ml-2"></i>
                            إعادة تعيين كلمة السر
                        </button>
                    </div>
                </form>

                <!-- Divider -->
                <div class="relative mb-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200 dark:border-gray-700"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-4 bg-white text-gray-500 dark:bg-[#0f172a] dark:text-gray-400">أو</span>
                    </div>
                </div>

                <!-- رابط العودة لتسجيل الدخول -->
                <div class="text-center">
                    <p class="text-gray-600 dark:text-gray-300 mb-3">تذكرت كلمة السر؟</p>
                    <a class="w-full block border-2 border-brand-primary text-brand-primary font-bold py-3 px-4 rounded-full hover:bg-brand-primary hover:text-white transition duration-300" href="{{ route('login') }}">
                        العودة لتسجيل الدخول
                    </a>
                </div>

                <!-- زر التواصل عبر واتساب -->
                <div class="mt-8 text-center pt-6 border-top border-gray-100 dark:border-gray-700">
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-3">بحاجة لمساعدة؟ تواصل معنا على الواتساب:</p>
                    <a href="https://wa.me/9647701234567" target="_blank"
                       class="inline-flex items-center px-5 py-3 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-full shadow transition duration-300 transform hover:-translate-y-0.5">
                        <i class="bi bi-whatsapp text-xl ml-2"></i>
                        تواصل عبر واتساب
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
  // Fallback لإخفاء البحث والفوتر إذا كان في ستايلات Utility تغيّر العرض
  document.addEventListener('DOMContentLoaded', function () {
    var searchSelectors = [
      'header form[action*="{{ route('products.search') }}"]',
      'header form[role="search"]',
      'header .search', 'header .search-form', 'header .searchbar',
      'header .main-search', 'header .desktop-search', 'header .mobile-search',
      'header .container form'
    ];
    var footerSelectors = ['footer', '.site-footer', '.footer', '.footer-mobile'];

    searchSelectors.forEach(function (sel) {
      document.querySelectorAll(sel).forEach(function (el) {
        el.style.setProperty('display', 'none', 'important');
        el.classList.add('hidden');
      });
    });
    footerSelectors.forEach(function (sel) {
      document.querySelectorAll(sel).forEach(function (el) {
        el.style.setProperty('display', 'none', 'important');
        el.classList.add('hidden');
      });
    });
  });
</script>
@endpush
