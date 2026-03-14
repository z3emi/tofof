@php
// قائمة الدول
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
@section('title', 'إعادة تعيين كلمة السر')

@push('styles')
<style>
  /* ——— العامة ——— */
  .bg-white{background:#fff; border:1px solid #f3e5e3; box-shadow:0 10px 30px rgba(0,0,0,.05)}
  body{
    background:#f9f5f1;
    background-image:
      radial-gradient(circle at 10% 20%, rgba(205,137,133,.05) 0%, transparent 20%),
      radial-gradient(circle at 90% 80%, rgba(205,137,133,.05) 0%, transparent 20%);
  }
  button{transition:.3s}

  /* ——— مجموعة الهاتف ——— */
  .phone-input-group{
    display:flex; align-items:center; direction:ltr; overflow:hidden;
    border:2px solid #e5e7eb; border-radius:12px; background:#fff; transition:.25s;
  }
  .phone-input-group:focus-within{border-color:#cd8985; box-shadow:0 0 0 3px rgba(205,137,133,.2)}
  .country-code-btn{
    min-width:110px; height:48px; padding:.75rem 1rem; background:#f9f5f1;
    border-right:1px solid #e5e7eb; display:flex; align-items:center; gap:.5rem;
    justify-content:center; cursor:pointer; transition:.2s; font-size:.92rem;
  }
  .country-code-btn:hover{background:#f3e5e3}
  .phone-input{
    flex:1; height:48px; padding:.75rem 1rem; border:0; outline:0; background:transparent;
    direction:ltr; text-align:left; font-size:1rem;
  }
  .country-list{
    position:absolute; left:0; right:0; margin-top:.5rem; max-height:260px; overflow-y:auto;
    background:#fff; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 14px 28px rgba(0,0,0,.12);
    z-index:9999; direction:rtl;
  }
  .country-list a{
    display:flex; align-items:center; gap:.75rem; padding:.7rem 1rem; border-bottom:1px solid #f3e5e3;
    color:#4a2c2a; cursor:pointer; transition:.15s; font-size:.92rem;
  }
  .country-list a:last-child{border-bottom:0}
  .country-list a:hover{background:#f9f5f1}

  /* ——— Responsive ——— */
  @media (max-width:640px){ .container{padding-inline:1rem} .py-8{padding-block:2rem} .px-6{padding-inline:1.5rem} }

  /* ——— RTL تعديلات ——— */
  [dir="rtl"] .phone-input{direction:ltr; text-align:left}
  [dir="rtl"] .country-code-btn{border-right:none; border-left:1px solid #e5e7eb; border-radius:0 12px 12px 0}
  [dir="rtl"] .country-list{right:0; left:auto}
  [dir="rtl"] .country-list a{flex-direction:row-reverse}

  /* ——— Dark Mode ——— */
  html.dark body{
    background:#0b0f14;
    background-image:
      radial-gradient(circle at 10% 20%, rgba(205,137,133,.05) 0%, transparent 22%),
      radial-gradient(circle at 90% 80%, rgba(205,137,133,.05) 0%, transparent 22%);
  }
  html.dark .bg-white{background:#0f172a; border-color:#1f2937; box-shadow:0 10px 30px rgba(0,0,0,.22)}
  html.dark .text-gray-600{color:#d1d5db !important}
  html.dark .text-gray-500{color:#94a3b8 !important}

  html.dark .phone-input-group{background:#0f172a; border-color:#1f2937}
  html.dark .phone-input-group:focus-within{box-shadow:0 0 0 3px rgba(205,137,133,.22)}
  html.dark .country-code-btn{background:#111827; border-color:#1f2937; color:#e5e7eb}
  html.dark .country-code-btn:hover{background:#0f172a}
  html.dark .phone-input{color:#e5e7eb}
  html.dark .phone-input::placeholder{color:#94a3b8}

  html.dark .country-list{background:#0f172a; border-color:#1f2937; box-shadow:0 14px 28px rgba(0,0,0,.35)}
  html.dark .country-list a{color:#e5e7eb; border-bottom-color:#1f2937}
  html.dark .country-list a:hover{background:#111827}
  html.dark .country-list::-webkit-scrollbar-thumb{background:#374151}

  /* فاصل "أو" */
  .divider-chip{background:#fff; color:#6b7280}
  html.dark .divider-chip{background:#0f172a !important; color:#94a3b8 !important}

  /* صندوق الأخطاء */
  .error-alert{background:#fef2f2; border-right:4px solid #ef4444}
  html.dark .error-alert{background:#1f2937; border-right-color:#ef4444}
  html.dark .error-alert h3{color:#fecaca}
  html.dark .error-alert ul li{color:#fca5a5}
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
      <h2 class="text-3xl font-extrabold text-brand-text mb-2 dark:text-gray-100">إعادة تعيين كلمة السر</h2>
      <p class="text-gray-600">أدخلي رقم هاتفك المسجل لإرسال رمز التحقق</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
      <div class="py-8 px-6 md:px-8">
        @if ($errors->any())
          <div class="mb-6 error-alert p-4 rounded-md">
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

        <form method="POST" action="{{ route('password.send.otp') }}"
              x-data="{
                countryMenuOpen:false,
                countries: {{ json_encode($countries) }},
                selectedCountry: {{ json_encode($countries[0]) }},
                localPhone: '{{ old('local_phone_number', '') }}',
                init(){
                  this.$watch('localPhone', v=>{
                    if(this.selectedCountry.code==='+964' && v.startsWith('0')){
                      this.$nextTick(()=>{ this.localPhone = v.substring(1) })
                    }
                  })
                },
                toggleCountryMenu(){ this.countryMenuOpen=!this.countryMenuOpen },
                selectCountry(c){ this.selectedCountry=c; this.countryMenuOpen=false }
              }"
              x-init="init()"
              @click.away="countryMenuOpen=false">
          @csrf

          <div class="mb-6 relative">
            <label for="local_phone_number" class="block text-gray-700 font-medium mb-2 dark:text-gray-200">رقم الهاتف</label>
            <div class="phone-input-group">
              <button type="button" class="country-code-btn" @click="toggleCountryMenu()">
                <span x-text="selectedCountry.flag"></span>
                <span class="font-semibold text-gray-700 dark:text-gray-100" x-text="selectedCountry.code"></span>
                <i class="bi bi-chevron-down text-xs transition-transform duration-300" :class="{'rotate-180': countryMenuOpen}"></i>
              </button>
              <input id="local_phone_number" type="tel" class="phone-input dark:text-gray-100 dark:placeholder-gray-400"
                     name="local_phone_number" x-model="localPhone" required autocomplete="tel" autofocus
                     :placeholder="selectedCountry.code === '+964' ? 'مثال: 7712345678' : 'أدخل رقم الهاتف'"
                     :maxlength="selectedCountry.code === '+964' ? 10 : 15"
                     :pattern="selectedCountry.code === '+964' ? '7[0-9]{9}' : null"
                     title="للرقم العراقي، أدخل 10 أرقام تبدأ بالرقم 7.">
            </div>

            <!-- الرقم الكامل المخفي -->
            <input type="hidden" name="phone_number" :value="selectedCountry.code.replace('+','') + localPhone">

            <p class="text-xs text-gray-500 mt-2 flex items-center">
              <i class="bi bi-info-circle ml-1"></i>
              سيتم إرسال رمز تحقق عبر واتساب إلى هذا الرقم.
            </p>

            <div x-show="selectedCountry.code === '+964'" class="text-xs text-blue-600 mt-1 flex items-start" style="display:none;">
              <i class="bi bi-lightbulb ml-1 mt-0.5"></i>
              <span>ملاحظة: أدخلي الرقم المكوّن من 10 أرقام بدون الصفر في البداية (مثال: 7712345678).</span>
            </div>

            <div x-show="countryMenuOpen" class="country-list"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 style="display:none;">
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

          <div class="mb-6">
            <button type="submit"
                    class="w-full bg-brand-dark text-white font-bold py-3 px-4 rounded-full hover:bg-brand-primary transition duration-300 transform hover:-translate-y-0.5 shadow-lg hover:shadow-xl flex items-center justify-center">
              <i class="bi bi-shield-lock ml-2"></i>
              إرسال رمز التحقق
            </button>
          </div>
        </form>

        <!-- Divider -->
        <div class="relative mb-6">
          <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-200 dark:border-gray-700"></div>
          </div>
          <div class="relative flex justify-center text-sm">
            <span class="px-4 divider-chip">أو</span>
          </div>
        </div>

        <!-- رابط العودة -->
        <div class="text-center">
          <p class="text-gray-600 dark:text-gray-300 mb-3">تذكرت كلمة السر؟</p>
          <a class="w-full block border-2 border-brand-primary text-brand-primary font-bold py-3 px-4 rounded-full hover:bg-brand-primary hover:text-white transition duration-300"
             href="{{ route('login') }}">
            العودة لتسجيل الدخول
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
