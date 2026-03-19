@php
// Array of Arab countries with their codes and flags
$locale = app()->getLocale();
$isArabic = $locale === 'ar';

$countriesRaw = [
  ['ar' => 'العراق', 'en' => 'Iraq', 'code' => '+964', 'flag' => 'iq'],
  ['ar' => 'مصر', 'en' => 'Egypt', 'code' => '+20', 'flag' => 'eg'],
  ['ar' => 'السعودية', 'en' => 'Saudi Arabia', 'code' => '+966', 'flag' => 'sa'],
  ['ar' => 'الإمارات', 'en' => 'United Arab Emirates', 'code' => '+971', 'flag' => 'ae'],
  ['ar' => 'تركيا', 'en' => 'Turkey', 'code' => '+90', 'flag' => 'tr'],
  ['ar' => 'الأردن', 'en' => 'Jordan', 'code' => '+962', 'flag' => 'jo'],
  ['ar' => 'سوريا', 'en' => 'Syria', 'code' => '+963', 'flag' => 'sy'],
  ['ar' => 'لبنان', 'en' => 'Lebanon', 'code' => '+961', 'flag' => 'lb'],
  ['ar' => 'فلسطين', 'en' => 'Palestine', 'code' => '+970', 'flag' => 'ps'],
  ['ar' => 'قطر', 'en' => 'Qatar', 'code' => '+974', 'flag' => 'qa'],
  ['ar' => 'البحرين', 'en' => 'Bahrain', 'code' => '+973', 'flag' => 'bh'],
  ['ar' => 'الكويت', 'en' => 'Kuwait', 'code' => '+965', 'flag' => 'kw'],
  ['ar' => 'عُمان', 'en' => 'Oman', 'code' => '+968', 'flag' => 'om'],
  ['ar' => 'اليمن', 'en' => 'Yemen', 'code' => '+967', 'flag' => 'ye'],
  ['ar' => 'الجزائر', 'en' => 'Algeria', 'code' => '+213', 'flag' => 'dz'],
  ['ar' => 'تونس', 'en' => 'Tunisia', 'code' => '+216', 'flag' => 'tn'],
  ['ar' => 'المغرب', 'en' => 'Morocco', 'code' => '+212', 'flag' => 'ma'],
  ['ar' => 'ليبيا', 'en' => 'Libya', 'code' => '+218', 'flag' => 'ly'],
  ['ar' => 'السودان', 'en' => 'Sudan', 'code' => '+249', 'flag' => 'sd'],
  ['ar' => 'موريتانيا', 'en' => 'Mauritania', 'code' => '+222', 'flag' => 'mr'],
  ['ar' => 'الصومال', 'en' => 'Somalia', 'code' => '+252', 'flag' => 'so'],
];

$countries = array_map(function ($country) use ($isArabic) {
  return [
    'name' => $isArabic ? $country['ar'] : $country['en'],
    'code' => $country['code'],
    'flag' => $country['flag'],
  ];
}, $countriesRaw);
@endphp

@extends('layouts.app')
@section('title', 'تسجيل الدخول')

@push('styles')
<style>
/* ===== هوية البصرة — صفحة تسجيل الدخول ===== */

/* متغيرات الألوان */
.auth-scope{
  --c-primary:   #6d0e16;
  --c-hover:     #a61c20;
  --c-light:     #fff1f2;
  --c-border:    #f2d6d7;
  --c-focus-ring:rgba(109,14,22,.18);
  --c-bg:        #fff8f8;
  --c-text:      #2f1113;
}

/* خلفية الصفحة */
.auth-scope{
  background:var(--c-bg);
  background-image:
    radial-gradient(ellipse at 15% 10%, rgba(109,14,22,.09) 0%, transparent 35%),
    radial-gradient(ellipse at 85% 90%, rgba(166,28,32,.07) 0%, transparent 35%);
  min-height:100vh;
}

/* شريط عمودي ملوّن على يمين/يسار الكارت */
.auth-card{
  background:#fff;
  border:1px solid var(--c-border);
  border-radius:20px;
  box-shadow:0 8px 40px rgba(109,14,22,.10), 0 1px 4px rgba(0,0,0,.04);
  position:relative;
  overflow:hidden;
}
/* تم حذف الخط الجانبي الأحمر */

/* ترويسة الكارت (حزام ملوّن + لوجو) */
.auth-card-header{
  background:#6d0e16 !important;
  padding:2rem 2rem 1.5rem;
  text-align:center;
  position:relative;
}
.auth-card-header::after{
  content:'';
  position:absolute;
  bottom:-1px; left:0; right:0;
  height:24px;
  background:#fff;
  border-radius:50% 50% 0 0 / 100% 100% 0 0;
}
.auth-logo-ring{
  width:72px; height:72px;
  border-radius:50%;
  background:rgba(255,255,255,.15);
  border:3px solid rgba(255,255,255,.4);
  display:flex; align-items:center; justify-content:center;
  margin:0 auto 1rem;
  backdrop-filter:blur(4px);
}
.auth-logo-ring img{ width:52px; height:52px; object-fit:contain; }
.auth-card-header h2{ color:#fff; font-size:1.5rem; font-weight:800; margin:0 0 .25rem; }
.auth-card-header p{ color:rgba(255,255,255,.82); font-size:.9rem; margin:0; }

/* حقول الإدخال */
.auth-scope .auth-field{
  width:100%; padding:.75rem 1rem;
  border:2px solid #e5e7eb; border-radius:12px;
  font-size:1rem; transition:.22s;
  background:#fff; color:var(--c-text);
  outline:none;
}
.auth-scope .auth-field:focus{
  border-color:var(--c-primary);
  box-shadow:0 0 0 3px var(--c-focus-ring);
  background:#fff9f9;
}
.auth-scope .auth-field.error{ border-color:#ef4444; }

/* مجموعة رقم الهاتف */
.auth-scope .phone-input-group{
  display:flex; align-items:center;
  border:2px solid #e5e7eb; border-radius:12px;
  overflow:hidden; direction:ltr; background:#fff; transition:.22s;
}
.auth-scope .phone-input-group:focus-within{
  border-color:var(--c-primary);
  box-shadow:0 0 0 3px var(--c-focus-ring);
}
.auth-scope .country-code-btn{
  height:48px; min-width:110px; padding:.5rem .75rem;
  background:var(--c-light); border-inline-end:2px solid #e5e7eb;
  display:flex; align-items:center; justify-content:center; gap:.4rem;
  direction:rtl;
  font-size:.88rem; cursor:pointer; transition:.2s; flex-shrink:0;
}
.auth-scope .country-code-btn:hover{ background:#fde8ea; }
.auth-scope .phone-input{
  flex:1; height:48px; padding:.5rem 1rem;
  border:0; outline:0; font-size:1rem;
  background:transparent; direction:ltr; text-align:left;
}

/* قائمة الدول */
.auth-scope .country-list{
  position:absolute; left:0; right:0; top:calc(100% + 6px);
  max-height:240px; overflow-y:auto;
  background:#fff; border:1px solid #e5e7eb; border-radius:12px;
  box-shadow:0 12px 32px rgba(0,0,0,.14); z-index:9999; direction:rtl;
}
.auth-scope .country-list a{
  display:flex; align-items:center; gap:.65rem;
  padding:.65rem 1rem; font-size:.9rem; cursor:pointer;
  color:var(--c-text); transition:.12s;
  border-bottom:1px solid #fce9e9;
}
.auth-scope .country-list a:last-child{ border-bottom:0; }
.auth-scope .country-list a:hover{ background:var(--c-light); }
.auth-scope .country-list::-webkit-scrollbar{ width:6px; }
.auth-scope .country-list::-webkit-scrollbar-thumb{ background:#f2c0c2; border-radius:999px; }

/* أيقونة إظهار / إخفاء كلمة المرور */
.auth-scope .password-wrapper{ position:relative; }
.auth-scope .auth-field.pe-12{ padding-right:3rem !important; }
.auth-scope .password-toggle{
  position:absolute; inset-inline-end:1rem; top:50%;
  transform:translateY(-50%); cursor:pointer;
  color:#9ca3af; transition:.2s; line-height:1; z-index:10;
}
.auth-scope .password-toggle:hover{ color:var(--c-primary); }

/* تثبيت محاذاة الأيقونات/الأعلام مع النص العربي */
.auth-scope .icon-inline,
.auth-scope .country-code-btn i,
.auth-scope .country-list .dial-code,
.auth-scope .country-list .country-name,
.auth-scope .auth-btn-primary i,
.auth-scope .auth-btn-outline i,
.auth-scope .password-toggle i,
.auth-scope .text-xs i{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  line-height:1;
  vertical-align:middle;
}
.auth-scope .country-code-btn img.flag,
.auth-scope .country-list img.flag{
  display:block !important;
  width:24px !important;
  height:16px !important;
  min-width:24px !important;
  min-height:16px !important;
  object-fit:contain !important;
  background:#fff;
  line-height:0 !important;
  vertical-align:top !important;
  border-radius:2px;
  box-shadow:0 1px 2px rgba(0,0,0,.25);
  flex-shrink:0;
}
.auth-scope .country-list .country-name{ text-align:right; }
.auth-scope .country-list .dial-code{ white-space:nowrap; }

/* زر الإرسال */
.auth-btn-primary{
  width:100%; padding:.85rem 1rem;
  background:#6d0e16 !important;
  color:#fff; font-weight:700; font-size:1rem;
  border:none; border-radius:12px; cursor:pointer;
  box-shadow:0 4px 18px rgba(109,14,22,.32);
  transition:.22s; display:flex; align-items:center; justify-content:center; gap:.5rem;
}
.auth-btn-primary:hover{
  background:#4a0910 !important;
  box-shadow:0 6px 22px rgba(109,14,22,.42);
  transform:translateY(-1px);
}
.auth-btn-primary:active{ transform:translateY(0); }

/* زر الإجراء الثانوي (حدود) */
.auth-btn-outline{
  width:100%; padding:.8rem 1rem;
  background:transparent;
  color:var(--c-primary); font-weight:700; font-size:1rem;
  border:2px solid var(--c-primary); border-radius:12px; cursor:pointer;
  transition:.22s; display:flex; align-items:center; justify-content:center; gap:.5rem;
  text-decoration:none;
}
.auth-btn-outline:hover{
  background:var(--c-primary); color:#fff;
  box-shadow:0 4px 16px rgba(109,14,22,.28);
  transform:translateY(-1px);
}

/* فاصل "أو" */
.auth-divider{ display:flex; align-items:center; gap:.75rem; color:#9ca3af; font-size:.85rem; }
.auth-divider::before, .auth-divider::after{
  content:''; flex:1; height:1px; background:#e5e7eb;
}

/* تسمية الحقل */
.auth-label{
  display:block; font-size:.9rem; font-weight:600;
  color:#374151; margin-bottom:.45rem;
}

/* تشيك بوكس تسجيل الدخول */
.auth-scope input[type="checkbox"]{
  accent-color:var(--c-primary);
}

/* ===== Dark Mode ===== */
html.dark .auth-scope{
  --c-bg:#0d0d0f;
  --c-text:#f1f5f9;
  --c-focus-ring:rgba(166,28,32,.30);
  background:#0d0d0f;
  background-image:
    radial-gradient(ellipse at 15% 10%, rgba(109,14,22,.20) 0%, transparent 35%),
    radial-gradient(ellipse at 85% 90%, rgba(166,28,32,.15) 0%, transparent 35%);
}
html.dark .auth-card{
  background:#161b27; border-color:#1e2a3a;
  box-shadow:0 8px 40px rgba(0,0,0,.40), 0 1px 4px rgba(0,0,0,.20);
}
/* تم حذف الخط الجانبي الأحمر في الوضع الليلي */
html.dark .auth-card-header::after{ background:#161b27; }

html.dark .auth-scope .auth-field{
  background:#1e2a3a; border-color:#2a3a52; color:#f1f5f9;
}
html.dark .auth-scope .auth-field:focus{
  border-color:#a61c20; background:#1a2234;
  box-shadow:0 0 0 3px rgba(166,28,32,.25);
}
html.dark .auth-scope .auth-field::placeholder{ color:#64748b; }

html.dark .auth-scope .phone-input-group{ background:#1e2a3a; border-color:#2a3a52; }
html.dark .auth-scope .phone-input-group:focus-within{
  border-color:#a61c20; box-shadow:0 0 0 3px rgba(166,28,32,.25);
}
html.dark .auth-scope .country-code-btn{
  background:#111827; border-inline-end-color:#2a3a52; color:#e2e8f0;
}
html.dark .auth-scope .country-code-btn:hover{ background:#0f172a; }
html.dark .auth-scope .phone-input{ color:#f1f5f9; }
html.dark .auth-scope .phone-input::placeholder{ color:#64748b; }

html.dark .auth-scope .country-list{
  background:#161b27; border-color:#2a3a52; box-shadow:0 14px 40px rgba(0,0,0,.50);
}
html.dark .auth-scope .country-list a{ color:#e2e8f0; border-bottom-color:#1e2a3a; }
html.dark .auth-scope .country-list a:hover{ background:#1e2a3a; }
html.dark .auth-scope .country-list::-webkit-scrollbar-thumb{ background:#374151; }

html.dark .auth-scope .auth-label{ color:#d1d5db; }
html.dark .auth-scope .password-toggle{ color:#6b7280; }
html.dark .auth-scope .password-toggle:hover{ color:#f87171; }

html.dark .auth-divider{ color:#4b5563; }
html.dark .auth-divider::before, html.dark .auth-divider::after{ background:#2a3a52; }

html.dark .auth-scope .text-gray-600{ color:#cbd5e1 !important; }
html.dark .auth-scope .text-gray-500{ color:#94a3b8 !important; }
html.dark .auth-btn-outline{ color:#f87171; border-color:#f87171; }
html.dark .auth-btn-outline:hover{ background:#a61c20; color:#fff; border-color:#a61c20; }

/* ===== Final look override (reference-inspired) ===== */
.auth-scope{background:#06070a !important;}
.auth-shell{position:relative;}
.auth-shell::before,.auth-shell::after{content:'';position:absolute;width:74px;height:74px;border:1px solid rgba(227,19,34,.55);pointer-events:none;}
.auth-shell::before{top:-14px;inset-inline-start:-14px;border-inline-end:0;border-bottom:0;}
.auth-shell::after{bottom:-14px;inset-inline-end:-14px;border-inline-start:0;border-top:0;}
.auth-card{background:linear-gradient(160deg,#10131a 0%,#0a0c11 100%) !important;border:1px solid #1d212b !important;border-radius:0 !important;box-shadow:0 24px 50px rgba(0,0,0,.48) !important;overflow:visible !important;}
.auth-card-header{background:transparent !important;padding:2rem 2.1rem .7rem !important;text-align:right !important;}
.auth-card-header::after,.auth-logo-ring{display:none !important;}
.auth-card-header h2{color:#f2f4f8 !important;font-size:2.05rem !important;}
.auth-card-header p{color:#7d8592 !important;font-size:.95rem;margin-top:.3rem;}
.auth-form{padding:1rem 2.1rem 2rem !important;}
.auth-label{display:flex !important;align-items:center;justify-content:space-between;color:#abb1bc !important;font-size:.8rem !important;text-transform:uppercase;letter-spacing:.12em;}
.auth-label-ar{font-size:.74rem;letter-spacing:0;text-transform:none;color:#8d94a1;}
.auth-scope .phone-input-group,.auth-scope .auth-field{background:#1a1d24 !important;border:1px solid #262b37 !important;border-radius:0 !important;color:#f2f4f8 !important;}
.auth-scope .phone-input-group:focus-within,.auth-scope .auth-field:focus{border-color:#e31322 !important;box-shadow:0 0 0 2px rgba(227,19,34,.16) !important;}
.auth-scope .country-code-btn{height:54px !important;background:#151922 !important;border-inline-end:1px solid #262b37 !important;color:#d2d7df !important;direction:ltr !important;}
.auth-scope .phone-input{height:54px !important;color:#f2f4f8 !important;}
.auth-scope .phone-input::placeholder,.auth-scope .auth-field::placeholder{color:#737b88 !important;}
.auth-scope .country-list{background:#12151c !important;border-color:#2a303d !important;border-radius:0 !important;width:min(640px, calc(100vw - 1rem)) !important;left:50% !important;right:auto !important;transform:translateX(-50%);}
.auth-scope .country-list a{color:#d8dce4 !important;border-bottom-color:#212734 !important;}
.auth-scope .country-list a:hover{background:#181d27 !important;}
.auth-help{color:#9da3ad;font-size:.84rem;}
.auth-help a{color:#e31322 !important;text-decoration:none;}
/* زر تسجيل الدخول لون ثابت */
.auth-btn-primary{background:#6d0e16 !important;}
.auth-btn-primary:hover{background:#c9101d !important;}
.auth-foot{color:#8f96a3;text-align:center;text-transform:uppercase;letter-spacing:.08em;font-size:.9rem;margin-top:1.2rem;}
.auth-foot a{color:#fff;text-decoration:none;margin-inline-start:.4rem;}
.auth-foot a:hover{color:#e31322;}
html:not(.dark) .auth-scope{background:#f5f7fb !important;}
html:not(.dark) .auth-card{background:#fff !important;border-color:#e4e8f0 !important;box-shadow:0 18px 42px rgba(17,22,38,.14) !important;}
html:not(.dark) .auth-card-header h2{color:#1c2230 !important;}
html:not(.dark) .auth-label{color:#646d7b !important;}
html:not(.dark) .auth-label-ar{color:#7b8492 !important;}
html:not(.dark) .auth-scope .phone-input-group,
html:not(.dark) .auth-scope .auth-field{background:#f3f5f9 !important;border-color:#e2e6ef !important;color:#1d2432 !important;}
html:not(.dark) .auth-scope .phone-input,
html:not(.dark) .auth-scope .auth-field{color:#1d2432 !important;}
html:not(.dark) .auth-scope .country-list{background:#fff !important;border-color:#d8ddea !important;}
html:not(.dark) .auth-scope .country-list a{color:#1f2533 !important;border-bottom-color:#edf1f7 !important;}
html:not(.dark) .auth-scope .country-list a:hover{background:#eef2f8 !important;color:#111827 !important;}
html:not(.dark) .auth-foot a{color:#202737;}
</style>
@endpush

@section('content')
<div class="auth-scope flex items-center justify-center py-10 px-4" dir="rtl">
  <div class="w-full max-w-md auth-shell">

    <!-- الكارت الرئيسي -->
    <div class="auth-card mb-6"
         x-data="{
           showPassword:false,
           countryMenuOpen:false,
           selectedCountry: {{ json_encode($countries[0]) }},
           localNumber: '{{ old('local_phone_number') ?? '' }}',
           init(){
             this.$watch('localNumber', (v)=>{
               if(this.selectedCountry.code==='+964' && v?.startsWith('0')){
                 this.$nextTick(()=>{ this.localNumber = v.substring(1); });
               }
             });
           }
         }"
         x-init="init()"
         @click.away="countryMenuOpen=false">

      <!-- ترويسة الكارت -->
      <div class="auth-card-header">
        <div class="auth-logo-ring">
          <img src="{{ asset('logo.png') }}" alt="طفوف">
        </div>
        <h2>تسجيل الدخول</h2>
        <p>أهلاً بعودتك</p>
      </div>

      <!-- النموذج -->
      <div class="p-6 md:p-8 auth-form">
        <form method="POST" action="{{ route('login') }}">
          @csrf

          {{-- رقم الهاتف --}}
          <div class="mb-5 relative">
            <label class="auth-label">رقم الهاتف</label>
            <div class="phone-input-group">
              <button type="button" @click="countryMenuOpen = !countryMenuOpen" class="country-code-btn">
                <img :src="'{{ request()->root() }}/flags/' + selectedCountry.flag + '.svg'" class="flag w-6 h-4 object-contain rounded-sm shadow-sm" :alt="selectedCountry.name + ' flag'">
                <span class="font-bold dial-code" x-text="selectedCountry.code"></span>
                <i class="bi bi-chevron-down text-xs icon-inline transition-transform duration-200" :class="{'rotate-180': countryMenuOpen}"></i>
              </button>
              <input id="local_phone_number" type="tel"
                     class="phone-input"
                     name="local_phone_number" x-model="localNumber"
                inputmode="numeric" pattern="[0-9]*"
                @input="localNumber = ($event.target.value || '').replace(/\D+/g, '')"
                :maxlength="selectedCountry.code === '+964' ? 10 : 15"
                     required autocomplete="tel" autofocus
                     placeholder="7701234567">
            </div>
            <input type="hidden" name="phone_number" :value="selectedCountry.code.replace('+','') + localNumber">

            <!-- قائمة الدول -->
            <div x-show="countryMenuOpen"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-1"
                 class="country-list" style="display:none;">
              @foreach($countries as $country)
                <a href="#"
                   @click.prevent="selectedCountry = {{ json_encode($country) }}; countryMenuOpen = false">
                  <img src="{{ request()->root() }}/flags/{{ $country['flag'] }}.svg" class="flag w-6 h-4 object-contain rounded-sm shadow-sm" alt="{{ $country['name'] }} flag">
                  <span class="country-name flex-grow">{{ $country['name'] }}</span>
                  <span class="dial-code text-gray-400 text-sm font-medium">{{ $country['code'] }}</span>
                </a>
              @endforeach
            </div>

            @error('phone_number')
              <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                <i class="bi bi-exclamation-circle"></i>{{ $message }}
              </p>
            @enderror
          </div>

          {{-- كلمة المرور --}}
          <div class="mb-5">
            <label class="auth-label" for="password">كلمة المرور</label>
            <div class="password-wrapper">
              <input :type="showPassword ? 'text' : 'password'"
                     id="password" name="password"
                     class="auth-field pe-12 @error('password') error @enderror"
                     required placeholder="أدخل كلمة المرور">
              <span class="password-toggle" @click="showPassword = !showPassword">
                <i class="bi text-xl" :class="showPassword ? 'bi-eye-slash' : 'bi-eye'"></i>
              </span>
            </div>
            @error('password')
              <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                <i class="bi bi-exclamation-circle"></i>{{ $message }}
              </p>
            @enderror
          </div>

          {{-- تذكرني + نسيت كلمة السر --}}
          <div class="flex items-center justify-between mb-6 auth-help">
            <label class="flex items-center gap-2 cursor-pointer text-sm">
              <input id="remember" type="checkbox" name="remember" class="w-4 h-4 rounded">
              تذكرني
            </label>
            @if (! env('OTP_DISABLED', false))
              <a href="{{ route('password.reset.phone.form') }}"
                 class="text-sm font-semibold hover:underline" style="color:var(--c-primary)">
                نسيت كلمة السر؟
              </a>
            @else
              <span class="text-sm text-gray-500">تواصل معنا عبر الواتساب</span>
            @endif
          </div>

          {{-- زر الدخول --}}
          <button type="submit" class="auth-btn-primary mb-4">
            <i class="bi bi-box-arrow-in-right icon-inline"></i>
            تسجيل الدخول
          </button>

          <p class="auth-foot">ليس لديك حساب؟ <a href="{{ route('register') }}">إنشاء حساب</a></p>
        </form>
      </div>
    </div>

    {{-- واتساب --}}
    <div class="text-center">
      <p class="text-sm text-gray-500 mb-2">بحاجة لمساعدة؟</p>
      <a href="https://wa.me/9647744969024" target="_blank"
         class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-semibold text-white shadow transition hover:-translate-y-0.5"
         style="background:#25d366">
        <i class="bi bi-whatsapp text-lg icon-inline"></i>
        تواصل معنا عبر واتساب
      </a>
    </div>

  </div>
</div>
@endsection