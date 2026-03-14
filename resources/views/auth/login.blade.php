@php
// Array of Arab countries with their codes and flags
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
    ['name' => 'البحرين', 'code' => '+973', 'flag' => '🇧🇭'],
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
@section('title', 'تسجيل الدخول')

@push('styles')
<style>
  /* هوية عامة بسيطة (ما تأثر على الهيدر) */
  .max-w-md{width:100%}
  .bg-brand-dark{background:#4a2c2a}
  .bg-brand-primary{background:#cd8985}
  .text-brand-primary{color:#cd8985}
  .text-brand-text{color:#4a2c2a}
  .focus\:ring-brand-primary:focus{--tw-ring-color:#cd8985}
  .border-brand-primary{border-color:#cd8985}

  /* ============ سكوپ الصفحة فقط ============ */
  .auth-scope{
    /* خلفية الصفحة (لايت) */
    background:#f9f5f1;
    background-image:
      radial-gradient(circle at 10% 20%, rgba(205,137,133,.05) 0%, transparent 20%),
      radial-gradient(circle at 90% 80%, rgba(205,137,133,.05) 0%, transparent 20%);
  }

  /* كارت النموذج داخل السكوپ فقط */
  .auth-card{
    background:#fff;
    border:1px solid #f3e5e3;
    box-shadow:0 10px 30px rgba(0,0,0,.05);
  }

  /* إدخال الهاتف داخل السكوپ */
  .auth-scope .phone-input-group{
    display:flex; align-items:center;
    border:2px solid #e5e7eb; border-radius:12px;
    transition:.25s; direction:ltr; overflow:hidden; background:#fff;
  }
  .auth-scope .phone-input-group:focus-within{border-color:#cd8985; box-shadow:0 0 0 3px rgba(205,137,133,.18)}

  .auth-scope .country-code-btn{
    padding:.75rem 1rem; background:#f9f5f1; border-right:1px solid #e5e7eb;
    display:flex; align-items:center; gap:.5rem; font-size:.92rem; min-width:112px;
    justify-content:center; cursor:pointer; transition:.2s; height:48px; border-radius:0;
  }
  .auth-scope .country-code-btn:hover{background:#f3e5e3}

  .auth-scope .phone-input{
    border:0; outline:0; flex:1; padding:.75rem 1rem; font-size:1rem;
    direction:ltr; text-align:left; background:transparent; height:48px;
  }

  .auth-scope .country-list{
    position:absolute; left:0; right:0; margin-top:.5rem; max-height:260px; overflow-y:auto;
    background:#fff; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 14px 28px rgba(0,0,0,.12);
    z-index:9999; direction:rtl;
  }
  .auth-scope .country-list a{
    display:flex; align-items:center; gap:.75rem; padding:.7rem 1rem; font-size:.92rem; cursor:pointer;
    color:#4a2c2a; transition:.15s; border-bottom:1px solid #f3e5e3;
  }
  .auth-scope .country-list a:last-child{border-bottom:0}
  .auth-scope .country-list a:hover{background:#f9f5f1}
  .auth-scope .country-list::-webkit-scrollbar{width:8px}
  .auth-scope .country-list::-webkit-scrollbar-thumb{background:#eadbcd; border-radius:999px}

  /* كلمة المرور: أيقونة العرض فقط (داخل السكوپ) */
  .auth-scope .password-wrapper{position:relative}
  .auth-scope .password-toggle{
    position:absolute; left:1rem; top:50%; transform:translateY(-50%);
    cursor:pointer; color:#6b7280; transition:.2s;
  }
  .auth-scope .password-toggle:hover{color:#cd8985}

  /* Responsive */
  @media (max-width:640px){
    .auth-scope .container{padding-inline:1rem}
    .auth-scope .py-8{padding-block:2rem}
    .auth-scope .px-6{padding-inline:1.5rem}
  }

  /* —— Dark Mode (مقيّد بالسكوپ) —— */
  html.dark .auth-scope{
    background:#0b0f14;
    background-image:
      radial-gradient(circle at 10% 20%, rgba(205,137,133,.05) 0%, transparent 22%),
      radial-gradient(circle at 90% 80%, rgba(205,137,133,.05) 0%, transparent 22%);
  }
  html.dark .auth-card{background:#0f172a; border-color:#1f2937; box-shadow:0 10px 30px rgba(0,0,0,.22)}
  html.dark .auth-scope .text-brand-text{color:#e5e7eb}
  html.dark .auth-scope .text-gray-600{color:#d1d5db !important}
  html.dark .auth-scope .text-gray-500{color:#94a3b8 !important}
  html.dark .auth-scope .text-gray-400{color:#9ca3af !important}

  html.dark .auth-scope .phone-input-group{background:#0f172a; border-color:#1f2937}
  html.dark .auth-scope .phone-input-group:focus-within{box-shadow:0 0 0 3px rgba(205,137,133,.22)}
  html.dark .auth-scope .country-code-btn{background:#111827; border-right-color:#1f2937; color:#e5e7eb}
  html.dark .auth-scope .country-code-btn:hover{background:#0f172a}
  html.dark .auth-scope .phone-input{color:#e5e7eb}
  html.dark .auth-scope .phone-input::placeholder{color:#94a3b8}

  html.dark .auth-scope .country-list{background:#0f172a; border-color:#1f2937; box-shadow:0 14px 28px rgba(0,0,0,.35)}
  html.dark .auth-scope .country-list a{color:#e5e7eb; border-bottom-color:#1f2937}
  html.dark .auth-scope .country-list a:hover{background:#111827}
  html.dark .auth-scope .country-list::-webkit-scrollbar-thumb{background:#374151}

  /* Inputs داخل الكارت فقط */
  html.dark .auth-card input[type="text"],
  html.dark .auth-card input[type="password"],
  html.dark .auth-card input[type="tel"]{
    background:#0f172a !important;
    border-color:#334155 !important;
    color:#e5e7eb !important;
  }
  html.dark .auth-card input[type="text"]::placeholder,
  html.dark .auth-card input[type="password"]::placeholder,
  html.dark .auth-card input[type="tel"]::placeholder{
    color:#94a3b8 !important;
  }
  html.dark .auth-card input[type="text"]:focus,
  html.dark .auth-card input[type="password"]:focus,
  html.dark .auth-card input[type="tel"]:focus{
    border-color:#cd8985 !important;
    box-shadow:0 0 0 3px rgba(205,137,133,.22) !important;
    background:#0b1220 !important;
  }

  html.dark .auth-scope .password-toggle{ color:#9ca3af; }
  html.dark .auth-scope .password-toggle:hover{ color:#cd8985; }

  html.dark .auth-scope input[type="checkbox"]{ background:#111827; border-color:#334155; }
  html.dark .auth-scope input[type="checkbox"]:checked{ background:#cd8985; border-color:#cd8985; }

  html.dark .auth-card label{ color:#e5e7eb !important; }
  html.dark .auth-card .text-gray-600{ color:#d1d5db !important; }
  html.dark .auth-card .text-gray-500{ color:#94a3b8 !important; }

  /* Divider chip "أو" (داخل السكوپ فقط) */
  .auth-scope .divider-chip{ background:#fff; }
  html.dark .auth-scope .divider-chip{ background:#0f172a !important; color:#94a3b8 !important; }

  html.dark .auth-scope .bg-brand-dark:hover{ background:#be6661; }
</style>
@endpush

@section('content')
<div class="auth-scope min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
  <div class="max-w-md w-full space-y-8">
    <!-- Logo and Title -->
    <div class="text-center mb-10">
      <div class="flex justify-center mb-4">
        <div class="w-20 h-20 rounded-full bg-brand-secondary flex items-center justify-center">
          <img src="{{ asset('logo.png') }}" alt="Tofof Logo" class="w-16 h-16">
        </div>
      </div>
      <h2 class="text-3xl font-extrabold text-brand-text mb-2">مرحباً بعودتكم</h2>
      <p class="text-gray-600">سجّل دخولك لحسابك في طفوف</p>
    </div>

    <!-- Form Card -->
    <div class="auth-card bg-white rounded-2xl shadow-xl overflow-hidden">
      <div class="py-8 px-6 md:px-8">
        <form method="POST" action="{{ route('login') }}"
              x-data="{
                showPassword:false,
                countryMenuOpen:false,
                selectedCountry: {{ json_encode($countries[0]) }},
                localNumber: '{{ old('local_phone_number') ?? '' }}'
              }"
              @click.away="countryMenuOpen=false">
          @csrf

          {{-- Phone Number --}}
          <div class="mb-6 relative">
            <label for="local_phone_number" class="block text-gray-700 font-medium mb-2 dark:text-gray-200">
              رقم الهاتف
            </label>

            <div class="phone-input-group">
              <button type="button" @click="countryMenuOpen = !countryMenuOpen" class="country-code-btn">
                <span x-text="selectedCountry.flag" class="text-xl"></span>
                <span class="font-semibold text-gray-700 dark:text-gray-100" x-text="selectedCountry.code"></span>
                <i class="bi bi-chevron-down text-xs transition-transform duration-300" :class="{'rotate-180': countryMenuOpen}"></i>
              </button>

              <input id="local_phone_number" type="tel"
                     class="phone-input dark:text-gray-100 dark:placeholder-gray-400"
                     name="local_phone_number" x-model="localNumber"
                     required autocomplete="tel" autofocus
                     placeholder="مثال: 7701234567">
            </div>

            <input type="hidden" name="phone_number" :value="selectedCountry.code.replace('+','') + localNumber">

            <div x-show="countryMenuOpen"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="country-list" style="display:none;">
              @foreach($countries as $country)
                <a href="#"
                   @click.prevent="selectedCountry = {{ json_encode($country) }}; countryMenuOpen = false"
                   title="{{ $country['name'] }}">
                  <span class="text-xl">{{ $country['flag'] }}</span>
                  <span class="flex-grow text-right">{{ $country['name'] }}</span>
                  <span class="text-gray-500 font-medium dark:text-gray-400">{{ $country['code'] }}</span>
                </a>
              @endforeach
            </div>

            @error('phone_number')
              <p class="text-red-500 text-xs mt-2 flex items-center">
                <i class="bi bi-exclamation-circle ml-1"></i>{{ $message }}
              </p>
            @enderror
          </div>

          {{-- Password (eye only) --}}
          <div class="mb-6">
            <label for="password" class="block text-gray-700 font-medium mb-2 dark:text-gray-200">كلمة المرور</label>
            <div class="password-wrapper">
              <input :type="showPassword ? 'text' : 'password'"
                     id="password"
                     class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-brand-primary focus:border-transparent @error('password') border-red-500 @enderror"
                     name="password" required placeholder="أدخل كلمة المرور">
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

          {{-- Remember & Forgot --}}
<div class="flex items-center justify-between mb-8">
    <label for="remember" class="flex items-center cursor-pointer">
        <input id="remember" type="checkbox" name="remember"
               class="h-4 w-4 text-brand-primary focus:ring-brand-primary border-gray-300 rounded">
        <span class="mr-2 block text-sm text-gray-700 dark:text-gray-200">تذكرني</span>
    </label>

    @if (! env('OTP_DISABLED', false))
        {{-- OTP شغّال → نعرض رابط استعادة كلمة السر --}}
        <a href="{{ route('password.reset.phone.form') }}"
           class="text-sm text-brand-primary hover:text-brand-dark font-medium">
            هل نسيت كلمة السر؟
        </a>
    @else
        {{-- OTP مطفي → ما نعرض رابط، نعرض فقط ملاحظة بسيطة --}}
        <span class="text-sm text-brand-primary hover:text-brand-dark font-medium">
            إذا نسيت كلمة المرور<br>تواصل معنا عبر الواتساب
        </span>
    @endif
</div>

          {{-- Login Button --}}
          <div class="mb-6">
            <button type="submit"
                    class="w-full bg-brand-dark text-white font-bold py-3 px-4 rounded-full hover:bg-brand-primary transition duration-300 transform hover:-translate-y-0.5 shadow-lg hover:shadow-xl flex items-center justify-center">
              <i class="bi bi-box-arrow-in-right ml-2"></i>
              تسجيل الدخول
            </button>
          </div>

          {{-- Divider --}}
          <div class="relative mb-6">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-gray-200 dark:border-gray-700"></div>
            </div>
            <div class="relative flex justify-center text-sm">
              <span class="px-4 divider-chip text-gray-500">أو</span>
            </div>
          </div>

          {{-- Register Link --}}
          <div class="text-center mb-6">
            <p class="text-gray-600 dark:text-gray-300 mb-3">ليس لديك حساب؟</p>
            <a class="w-full block border-2 border-brand-primary text-brand-primary font-bold py-3 px-4 rounded-full hover:bg-brand-primary hover:text-white transition duration-300"
               href="{{ route('register') }}">
              إنشاء حساب جديد
            </a>
          </div>

          {{-- WhatsApp Contact --}}
          <div class="mt-8 text-center pt-6 border-t border-gray-100 dark:border-gray-700">
            <p class="text-sm text-gray-600 dark:text-gray-300 mb-3">بحاجة لمساعدة؟ تواصل معنا على الواتساب:</p>
            <a href="https://wa.me/9647757778099" target="_blank"
               class="inline-flex items-center px-5 py-3 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-full shadow transition duration-300 transform hover:-translate-y-0.5">
              <i class="bi bi-whatsapp text-xl ml-2"></i>
              تواصل معنا عبر واتساب
            </a>
          </div>
        </form>
      </div>
    </div>

  </div>
</div>
@endsection