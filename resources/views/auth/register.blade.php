@php
// قائمة الدول كما في صفحة تسجيل الدخول
$countries = [
    ['name' => 'العراق', 'code' => '+964', 'flag' => '🇮🇶'],
    ['name' => 'مصر', 'code' => '+20', 'flag' => '🇪🇬'],
    ['name' => 'السعودية', 'code' => '+966', 'flag' => '🇸🇦'],
    ['name' => 'الإمارات', 'code' => '+971', 'flag' => '🇦🇪'],
    ['name' => 'الأردن', 'code' => '+962', 'flag' => '🇯🇴'],
    ['name' => 'سوريا', 'code' => '+963', 'flag' => '🇸🇾'],
    ['name' => 'لبنان', 'code' => '+961', 'flag' => '🇱🇧'],
    ['name' => 'فلسطين', 'code' => '+970', 'flag' => '🇵🇸'],
    ['name' => 'قطر', 'code' => '+974', 'flag' => '🇶🇦'],
    ['name' => 'البحرين', 'code' => '+973', 'flag' => '🇧ahrain'],
    ['name' => 'الكويت', 'code' => '+965', 'flag' => '🇰🇼'],
    ['name' => 'عُمان', 'code' => '+968', 'flag' => '🇴🇲'],
    ['name' => 'اليمن', 'code' => '+967', 'flag' => '🇾🇪'],
    ['name' => 'الجزائر', 'code' => '+213', 'flag' => '🇩🇿'],
    ['name' => 'تونس', 'code' => '+216', 'flag' => '🇹🇳'],
    ['name' => 'المغرب', 'code' => '+212', 'flag' => '🇲🇦'],
    ['name' => 'ليبيا', 'code' => '+218', 'flag' => '🇱🇾'],
    ['name' => 'السودان', 'code' => '+249', 'flag' => '🇸🇩'],
    ['name' => 'موريتانيا', 'code' => '+222', 'flag' => '🇲🇷'],
    ['name' => 'الصومال', 'code' => '+252', 'flag' => '🇸🇴'],
    ['name' => 'جيبوتي', 'code' => '+253', 'flag' => '🇩🇯'],
    ['name' => 'جزر القمر', 'code' => '+269', 'flag' => '🇰🇲'],
];
@endphp

@extends('layouts.app')
@section('title', 'إنشاء حساب جديد')

@push('styles')
<style>
  /* الهوية (آمنة) */
  .bg-brand-dark{ background:#4a2c2a; }
  .bg-brand-primary{ background:#cd8985; }
  .text-brand-primary{ color:#cd8985; }
  .text-brand-text{ color:#4a2c2a; }
  .focus\:ring-brand-primary:focus{ --tw-ring-color:#cd8985; }
  .border-brand-primary{ border-color:#cd8985; }

  /* ====== سكوﭬ للصفحة حتى ما يأثر على الهيدر ====== */
  .register-scope{
    background:#f9f5f1;
    background-image:
      radial-gradient(circle at 10% 20%, rgba(205,137,133,.05) 0%, transparent 20%),
      radial-gradient(circle at 90% 80%, rgba(205,137,133,.05) 0%, transparent 20%);
  }

  .register-card{
    background:#fff;
    border:1px solid #f3e5e3;
    box-shadow:0 10px 30px rgba(0,0,0,.05);
  }

  /* مجموعة إدخال الهاتف (داخل السكوﭬ فقط) */
  .register-scope .phone-input-group{
    display:flex; align-items:center; gap:0;
    border:2px solid #e5e7eb; border-radius:12px;
    background:#fff; transition:.25s; overflow:hidden; direction:ltr;
  }
  .register-scope .phone-input-group:focus-within{
    border-color:#cd8985; box-shadow:0 0 0 3px rgba(205,137,133,.18);
  }

  .register-scope .country-code-btn{
    height:48px; min-width:112px;
    padding:.75rem 1rem; background:#f9f5f1; border-right:1px solid #e5e7eb;
    display:flex; align-items:center; justify-content:center; gap:.5rem;
    font-size:.92rem; cursor:pointer; transition:.2s;
  }
  .register-scope .country-code-btn:hover{ background:#f3e5e3; }

  .register-scope .phone-input{
    height:48px; flex:1; padding:.75rem 1rem; font-size:1rem;
    border:0; outline:0; background:transparent; direction:ltr; text-align:left;
  }

  .register-scope .country-list{
    position:absolute; left:0; right:0; margin-top:.5rem; max-height:260px; overflow-y:auto;
    background:#fff; border:1px solid #e5e7eb; border-radius:12px; z-index:9999;
    box-shadow:0 14px 28px rgba(0,0,0,.12); direction:rtl;
  }
  .register-scope .country-list a{
    display:flex; align-items:center; gap:.75rem; padding:.7rem 1rem; font-size:.92rem;
    color:#4a2c2a; cursor:pointer; transition:.15s; border-bottom:1px solid #f3e5e3;
  }
  .register-scope .country-list a:last-child{ border-bottom:0; }
  .register-scope .country-list a:hover{ background:#f9f5f1; }
  .register-scope .country-list::-webkit-scrollbar{ width:8px; }
  .register-scope .country-list::-webkit-scrollbar-thumb{ background:#eadbcd; border-radius:999px; }

  /* الحقول العامة داخل السكوﭬ */
  .register-scope .form-field input[type="text"],
  .register-scope .form-field input[type="password"],
  .register-scope .form-field input[type="tel"]{ transition:.2s; }
  .register-scope .form-field input:focus{ background:#fff9f9; }

  /* كلمة المرور: أيقونة العرض فقط */
  .register-scope .password-wrapper{ position:relative; }
  .register-scope .password-toggle{
    position:absolute; inset-inline-end:1rem; top:50%; transform:translateY(-50%);
    color:#6b7280; cursor:pointer; transition:.2s;
  }
  .register-scope .password-toggle:hover{ color:#cd8985; }
  .register-scope .pad-for-toggle{ padding-inline-end:2.5rem; }

  /* رمز الدعوة (ستايل مميز) */
  .register-scope .referral-chip{
    display:inline-flex; align-items:center; gap:.35rem;
    background:linear-gradient(135deg,#cd8985,#be6661);
    color:#fff; font-size:.72rem; font-weight:700;
    padding:.2rem .6rem; border-radius:999px; letter-spacing:.2px;
    box-shadow:0 6px 14px rgba(205,137,133,.35);
  }
  .register-scope .referral-input{
    background:#fff; border:2px solid #f0deda; transition:.2s;
  }
  .register-scope .referral-input:focus{
    border-color:#cd8985; box-shadow:0 0 0 3px rgba(205,137,133,.18);
  }
  .register-scope .referral-icon{
    position:absolute; inset-block:0; inset-inline-start:.75rem;
    display:flex; align-items:center; color:#b28a86;
  }
  [dir="rtl"] .register-scope .referral-icon{
    inset-inline-start:auto; inset-inline-end:.75rem;
  }

  /* Responsive */
  @media (max-width:640px){
    .register-scope .container{ padding-inline:1rem; }
    .register-scope .py-8{ padding-block:2rem; }
    .register-scope .px-6{ padding-inline:1.5rem; }
  }

  /* ====== Dark Mode (مقيد بالسكوﭬ) ====== */
  html.dark .register-scope{
    background:#0b0f14;
    background-image:
      radial-gradient(circle at 10% 20%, rgba(205,137,133,.05) 0%, transparent 22%),
      radial-gradient(circle at 90% 80%, rgba(205,137,133,.05) 0%, transparent 22%);
  }
  html.dark .register-card{
    background:#0f172a; border-color:#1f2937;
    box-shadow:0 10px 30px rgba(0,0,0,.22);
  }
  html.dark .register-scope .text-brand-text{ color:#e5e7eb; }

  html.dark .register-scope .form-field input[type="text"],
  html.dark .register-scope .form-field input[type="password"],
  html.dark .register-scope .form-field input[type="tel"]{
    background:#0f172a; border-color:#334155; color:#e5e7eb;
  }
  html.dark .register-scope .form-field input:focus{
    border-color:#cd8985; box-shadow:0 0 0 3px rgba(205,137,133,.25); background:#0b1220;
  }

  html.dark .register-scope .phone-input-group{ background:#0f172a; border-color:#1f2937; }
  html.dark .register-scope .phone-input-group:focus-within{ box-shadow:0 0 0 3px rgba(205,137,133,.22); }
  html.dark .register-scope .country-code-btn{ background:#111827; border-right-color:#1f2937; color:#e5e7eb; }
  html.dark .register-scope .country-code-btn:hover{ background:#0f172a; }
  html.dark .register-scope .phone-input::placeholder{ color:#94a3b8; }

  html.dark .register-scope .country-list{
    background:#0f172a; border-color:#1f2937; box-shadow:0 14px 28px rgba(0,0,0,.35);
  }
  html.dark .register-scope .country-list a{ color:#e5e7eb; border-bottom-color:#1f2937; }
  html.dark .register-scope .country-list a:hover{ background:#111827; }

  html.dark .register-scope label,
  html.dark .register-scope .text-gray-600{ color:#d1d5db !important; }
  html.dark .register-scope .text-gray-500{ color:#94a3b8 !important; }
  html.dark .register-scope .text-gray-400{ color:#9ca3af !important; }

  html.dark .register-scope .referral-input{ background:#0f172a; border-color:#be6661aa; }
  html.dark .register-scope .referral-icon{ color:#e5c7c3; }
</style>
@endpush

@section('content')
<div class="register-scope min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8" dir="rtl">
  <div class="max-w-md w-full space-y-8">
    <!-- Logo and Title -->
    <div class="text-center mb-10">
      <div class="flex justify-center mb-4">
        <div class="w-20 h-20 rounded-full bg-brand-secondary flex items-center justify-center">
          <img src="{{ asset('logo.png') }}" alt="Tofof Logo" class="w-16 h-16">
        </div>
      </div>
      <h2 class="text-3xl font-extrabold text-brand-text mb-2">إنشاء حساب جديد</h2>
      <p class="text-gray-600">انضمي إلى عالم طفوف واستمتعي بتجربة تسوق فريدة</p>
    </div>

    <!-- Form Card -->
    <div class="register-card rounded-2xl shadow-xl overflow-hidden">
      <div class="py-8 px-6 md:px-8">
        <form method="POST" action="{{ route('register') }}"
              x-data="{
                countryMenuOpen:false,
                countries: {{ json_encode($countries) }},
                selectedCountry: {{ json_encode($countries[0]) }},
                localPhone: '{{ old('local_phone_number', '') }}',
                showPassword:false,
                showConfirmPassword:false,

                init(){
                  this.$watch('localPhone', (v)=>{
                    if(this.selectedCountry.code==='+964' && v?.startsWith('0')){
                      this.$nextTick(()=>{ this.localPhone = v.substring(1); });
                    }
                  });
                },
                toggleCountryMenu(){ this.countryMenuOpen = !this.countryMenuOpen; },
                selectCountry(c){ this.selectedCountry = c; this.countryMenuOpen = false; }
              }"
              x-init="init()"
              @click.away="countryMenuOpen=false">
          @csrf

          <!-- الاسم الكامل -->
          <div class="mb-6 form-field">
            <label for="name" class="block text-gray-700 font-medium mb-2 dark:text-gray-200">الاسم الكامل</label>
            <div class="relative">
              <div class="absolute inset-y-0 !right-0 flex items-center pr-3 pointer-events-none">
                <i class="bi bi-person text-gray-400 dark:text-gray-500"></i>
              </div>
              <input id="name" type="text"
                     class="w-full px-4 py-3 pr-10 border-2 border-gray-200 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-primary focus:border-transparent @error('name') border-red-500 @enderror"
                     name="name" value="{{ old('name') }}" required autocomplete="name" autofocus placeholder="أدخلي اسمك الكامل">
            </div>
            @error('name')
              <p class="text-red-500 text-xs mt-2 flex items-center">
                <i class="bi bi-exclamation-circle ml-1"></i>{{ $message }}
              </p>
            @enderror
          </div>

          <!-- رقم الهاتف مع اختيار الدولة -->
          <div class="mb-6 relative form-field">
            <label for="local_phone_number" class="block text-gray-700 font-medium mb-2 dark:text-gray-200">رقم الهاتف</label>

            <div class="phone-input-group">
              <button type="button" class="country-code-btn" @click="toggleCountryMenu()">
                <span x-text="selectedCountry.flag" class="text-xl"></span>
                <span class="font-semibold text-gray-700 dark:text-gray-100" x-text="selectedCountry.code"></span>
                <i class="bi bi-chevron-down text-xs transition-transform duration-300" :class="{'rotate-180': countryMenuOpen}"></i>
              </button>

              <input id="local_phone_number" type="tel" class="phone-input dark:text-gray-100 dark:placeholder-gray-400"
                     name="local_phone_number" x-model="localPhone" required autocomplete="tel"
                     :placeholder="selectedCountry.code === '+964' ? 'مثال: 7712345678' : 'أدخل رقم الهاتف'"
                     :maxlength="selectedCountry.code === '+964' ? 10 : 15"
                     :pattern="selectedCountry.code === '+964' ? '7[0-9]{9}' : null"
                     title="للرقم العراقي، أدخل 10 أرقام تبدأ بـ 7.">
            </div>

            <input type="hidden" name="phone_number" :value="selectedCountry.code.replace('+','') + localPhone">

            <p class="text-xs text-gray-500 mt-2 flex items-center">
              <i class="bi bi-info-circle ml-1"></i>
              سيتم إرسال رمز تحقق عبر واتساب إلى هذا الرقم لتفعيل حسابك.
            </p>
            <div x-show="selectedCountry.code === '+964'" class="text-xs text-blue-600 mt-1 flex items-start" style="display:none;">
              <i class="bi bi-lightbulb ml-1 mt-0.5"></i>
              <span>ملاحظة: أدخلي الرقم المكوّن من 10 أرقام بدون الصفر في البداية (مثال: 7712345678).</span>
            </div>

            <div x-show="countryMenuOpen"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="country-list" style="display:none;">
              <template x-for="country in countries" :key="country.code">
                <a href="#" role="option" tabindex="0" @click.prevent="selectCountry(country)">
                  <span class="text-xl" x-text="country.flag"></span>
                  <span class="flex-grow text-right" x-text="country.name"></span>
                  <span class="text-gray-500 font-medium dark:text-gray-400" x-text="country.code"></span>
                </a>
              </template>
            </div>

            @error('phone_number')
              <p class="text-red-500 text-xs mt-2 flex items-center">
                <i class="bi bi-exclamation-circle ml-1"></i>{{ $message }}
              </p>
            @enderror
          </div>

          <!-- كلمة المرور -->
          <div class="mb-6 form-field">
            <label for="password" class="block text-gray-700 font-medium mb-2 dark:text-gray-200">كلمة المرور</label>
            <div class="password-wrapper">
              <input :type="showPassword ? 'text' : 'password'" id="password"
                     class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-primary focus:border-transparent pad-for-toggle @error('password') border-red-500 @enderror"
                     name="password" required autocomplete="new-password" placeholder="أدخلي كلمة المرور">
              <span class="password-toggle" @click="showPassword = !showPassword">
                <i class="bi text-lg" :class="showPassword ? 'bi-eye-slash' : 'bi-eye'"></i>
              </span>
            </div>
            @error('password')
              <p class="text-red-500 text-xs mt-2 flex items-center">
                <i class="bi bi-exclamation-circle ml-1"></i>{{ $message }}
              </p>
            @enderror
          </div>

          <!-- تأكيد كلمة المرور -->
          <div class="mb-8 form-field">
            <label for="password-confirm" class="block text-gray-700 font-medium mb-2 dark:text-gray-200">تأكيد كلمة المرور</label>
            <div class="password-wrapper">
              <input :type="showConfirmPassword ? 'text' : 'password'" id="password-confirm"
                     class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-primary focus:border-transparent pad-for-toggle"
                     name="password_confirmation" required autocomplete="new-password" placeholder="أعيدي كتابة كلمة المرور">
              <span class="password-toggle" @click="showConfirmPassword = !showConfirmPassword">
                <i class="bi text-lg" :class="showConfirmPassword ? 'bi-eye-slash' : 'bi-eye'"></i>
              </span>
            </div>
          </div>

          {{-- رمز الدعوة --}}
          <div class="mb-6 form-field referral-wrap">
            <div class="flex items-center justify-between mb-2">
              <label for="referral_code" class="block text-gray-700 font-medium dark:text-gray-200">رمز الدعوة</label>
              <span class="referral-chip">اختياري</span>
            </div>

            <div class="relative">
              <span class="referral-icon">
                <i class="bi bi-gift"></i>
              </span>
              <input id="referral_code" type="text"
                     class="referral-input w-full px-4 py-3 pr-10 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-primary focus:border-transparent @error('referral_code') border-red-500 @enderror"
                     name="referral_code" value="{{ old('referral_code') }}" autocomplete="off"
                     placeholder="أدخلي رمز الدعوة إن وجد">
            </div>

            @error('referral_code')
              <p class="text-red-500 text-xs mt-2 flex items-center">
                <i class="bi bi-exclamation-circle ml-1"></i>{{ $message }}
              </p>
            @enderror
          </div>

          <!-- زر إنشاء الحساب -->
          <div class="mb-6">
            <button type="submit"
                    class="w-full bg-brand-dark text-white font-bold py-3 px-4 rounded-full hover:bg-brand-primary transition duration-300 transform hover:-translate-y-0.5 shadow-lg hover:shadow-xl flex items-center justify-center">
              <i class="bi bi-person-plus ml-2"></i>
              إنشاء الحساب
            </button>
          </div>

          <!-- Divider -->
          <div class="relative mb-6">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-gray-200 dark:border-gray-700"></div>
            </div>
            <div class="relative flex justify-center text-sm">
              <span class="px-4 register-card text-gray-500 dark:bg-[#0f172a] dark:text-gray-400">أو</span>
            </div>
          </div>

          <!-- رابط تسجيل الدخول -->
          <div class="text-center mb-2">
            <p class="text-gray-600 mb-3 dark:text-gray-300">لديك حساب بالفعل؟</p>
            <a class="w-full block border-2 border-brand-primary text-brand-primary font-bold py-3 px-4 rounded-full hover:bg-brand-primary hover:text-white transition duration-300"
               href="{{ route('login') }}">
              تسجيل الدخول
            </a>
          </div>
        </form>
      </div>
    </div>

  </div>
</div>
@endsection
