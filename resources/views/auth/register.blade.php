@php
// قائمة الدول كما في صفحة تسجيل الدخول
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
@section('title', 'إنشاء حساب جديد')

@push('styles')
<style>
/* ===== هوية البصرة — صفحة إنشاء الحساب ===== */

.register-scope{
  --c-primary:   #6d0e16;
  --c-hover:     #a61c20;
  --c-light:     #fff1f2;
  --c-border:    #f2d6d7;
  --c-focus-ring:rgba(109,14,22,.18);
  --c-bg:        #fff8f8;
  --c-text:      #2f1113;
}

.register-scope{
  background:var(--c-bg);
  background-image:
    radial-gradient(ellipse at 15% 10%, rgba(109,14,22,.09) 0%, transparent 35%),
    radial-gradient(ellipse at 85% 90%, rgba(166,28,32,.07) 0%, transparent 35%);
  min-height:100vh;
}

/* الكارت */
.register-card{
  background:#fff;
  border:1px solid var(--c-border);
  border-radius:20px;
  box-shadow:0 8px 40px rgba(109,14,22,.10), 0 1px 4px rgba(0,0,0,.04);
  position:relative;
  overflow:hidden;
}
/* تم حذف الخط الجانبي الأحمر */

/* ترويسة */
.register-card-header{
  background:#6d0e16 !important;
  padding:2rem 2rem 1.5rem;
  text-align:center;
  position:relative;
}
.register-card-header::after{
  content:'';
  position:absolute;
  bottom:-1px; left:0; right:0;
  height:24px;
  background:#fff;
  border-radius:50% 50% 0 0 / 100% 100% 0 0;
}
.register-logo-ring{
  width:72px; height:72px;
  border-radius:50%;
  background:rgba(255,255,255,.15);
  border:3px solid rgba(255,255,255,.4);
  display:flex; align-items:center; justify-content:center;
  margin:0 auto 1rem;
}
.register-logo-ring img{ width:52px; height:52px; object-fit:contain; }
.register-card-header h2{ color:#fff; font-size:1.4rem; font-weight:800; margin:0 0 .25rem; }
.register-card-header p{ color:rgba(255,255,255,.82); font-size:.88rem; margin:0; }

/* حقول الإدخال */
.register-scope .r-field{
  width:100%; padding:.75rem 1rem;
  border:2px solid #e5e7eb; border-radius:12px;
  font-size:1rem; transition:.22s;
  background:#fff; color:var(--c-text);
  outline:none;
}
.register-scope .r-field:focus{
  border-color:var(--c-primary);
  box-shadow:0 0 0 3px var(--c-focus-ring);
  background:#fff9f9;
}
.register-scope .r-field.error{ border-color:#ef4444; }

/* مجموعة الهاتف */
.register-scope .phone-input-group{
  display:flex; align-items:center;
  border:2px solid #e5e7eb; border-radius:12px;
  overflow:hidden; direction:ltr; background:#fff; transition:.22s;
}
.register-scope .phone-input-group:focus-within{
  border-color:var(--c-primary);
  box-shadow:0 0 0 3px var(--c-focus-ring);
}
.register-scope .country-code-btn{
  height:48px; min-width:110px; padding:.5rem .75rem;
  background:var(--c-light); border-inline-end:2px solid #e5e7eb;
  display:flex; align-items:center; justify-content:center; gap:.4rem;
  direction:rtl;
  font-size:.88rem; cursor:pointer; transition:.2s; flex-shrink:0;
}
.register-scope .country-code-btn:hover{ background:#fde8ea; }
.register-scope .phone-input{
  flex:1; height:48px; padding:.5rem 1rem;
  border:0; outline:0; font-size:1rem;
  background:transparent; direction:ltr; text-align:left;
}

/* قائمة الدول */
.register-scope .country-list{
  position:absolute; left:0; right:0; top:calc(100% + 6px);
  max-height:240px; overflow-y:auto;
  background:#fff; border:1px solid #e5e7eb; border-radius:12px;
  box-shadow:0 12px 32px rgba(0,0,0,.14); z-index:9999; direction:rtl;
}
.register-scope .country-list a{
  display:flex; align-items:center; gap:.65rem;
  padding:.65rem 1rem; font-size:.9rem; cursor:pointer;
  color:var(--c-text); transition:.12s;
  border-bottom:1px solid #fce9e9;
}
.register-scope .country-list a:last-child{ border-bottom:0; }
.register-scope .country-list a:hover{ background:var(--c-light); }
.register-scope .country-list::-webkit-scrollbar{ width:6px; }
.register-scope .country-list::-webkit-scrollbar-thumb{ background:#f2c0c2; border-radius:999px; }

/* أيقونة عرض كلمة المرور */
.register-scope .password-wrapper{ position:relative; }
.register-scope .r-field.pe-12{ padding-right:3rem !important; }
.register-scope .password-toggle{
  position:absolute; inset-inline-end:1rem; top:50%;
  transform:translateY(-50%); cursor:pointer;
  color:#9ca3af; transition:.2s; line-height:1; z-index:10;
}
.register-scope .password-toggle:hover{ color:var(--c-primary); }

/* تثبيت محاذاة الأيقونات/الأعلام مع النص العربي */
.register-scope .icon-inline,
.register-scope .country-code-btn i,
.register-scope .country-list .dial-code,
.register-scope .country-list .country-name,
.register-scope .reg-btn-primary i,
.register-scope .reg-btn-outline i,
.register-scope .password-toggle i,
.register-scope .text-xs i{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  line-height:1;
  vertical-align:middle;
}
.register-scope .country-code-btn img.flag,
.register-scope .country-list img.flag{
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
.register-scope .country-list .country-name{ text-align:right; }
.register-scope .country-list .dial-code{ white-space:nowrap; }

/* رمز الدعوة */
.register-scope .referral-chip{
  display:inline-flex; align-items:center; gap:.3rem;
  background:linear-gradient(135deg,#6d0e16,#a61c20);
  color:#fff; font-size:.7rem; font-weight:700;
  padding:.18rem .55rem; border-radius:999px;
  box-shadow:0 4px 10px rgba(109,14,22,.30);
}
.register-scope .r-field-referral{
  background:#fff; border:2px solid var(--c-border);
}
.register-scope .r-field-referral:focus{
  border-color:var(--c-primary);
  box-shadow:0 0 0 3px var(--c-focus-ring);
  background:#fff9f9;
}

/* زر الإرسال */
.reg-btn-primary{
  width:100%; padding:.85rem 1rem;
  background:linear-gradient(135deg, #6d0e16 0%, #a61c20 100%);
  color:#fff; font-weight:700; font-size:1rem;
  border:none; border-radius:12px; cursor:pointer;
  box-shadow:0 4px 18px rgba(109,14,22,.32);
  transition:.22s; display:flex; align-items:center; justify-content:center; gap:.5rem;
}
.reg-btn-primary:hover{
  background:#4a0910 !important;
  box-shadow:0 6px 22px rgba(109,14,22,.42);
  transform:translateY(-1px);
}
.reg-btn-outline{
  width:100%; padding:.8rem 1rem;
  background:transparent; color:var(--c-primary);
  font-weight:700; font-size:1rem;
  border:2px solid var(--c-primary); border-radius:12px;
  cursor:pointer; transition:.22s;
  display:flex; align-items:center; justify-content:center; gap:.5rem;
  text-decoration:none;
}
.reg-btn-outline:hover{
  background:var(--c-primary); color:#fff;
  box-shadow:0 4px 16px rgba(109,14,22,.28);
  transform:translateY(-1px);
}

/* فاصل */
.reg-divider{ display:flex; align-items:center; gap:.75rem; color:#9ca3af; font-size:.85rem; }
.reg-divider::before,.reg-divider::after{ content:''; flex:1; height:1px; background:#e5e7eb; }

/* تسمية الحقل */
.r-label{ display:block; font-size:.9rem; font-weight:600; color:#374151; margin-bottom:.45rem; }

/* ===== Dark Mode ===== */
html.dark .register-scope{
  --c-bg:#0d0d0f;
  --c-text:#f1f5f9;
  --c-focus-ring:rgba(166,28,32,.30);
  background:#0d0d0f;
  background-image:
    radial-gradient(ellipse at 15% 10%, rgba(109,14,22,.20) 0%, transparent 35%),
    radial-gradient(ellipse at 85% 90%, rgba(166,28,32,.15) 0%, transparent 35%);
}
html.dark .register-card{
  background:#161b27; border-color:#1e2a3a;
  box-shadow:0 8px 40px rgba(0,0,0,.40);
}
/* تم حذف الخط الجانبي الأحمر في الوضع الليلي */
html.dark .register-card-header::after{ background:#161b27; }

html.dark .register-scope .r-field,
html.dark .register-scope .r-field-referral{
  background:#1e2a3a; border-color:#2a3a52; color:#f1f5f9;
}
html.dark .register-scope .r-field:focus,
html.dark .register-scope .r-field-referral:focus{
  border-color:#a61c20; background:#1a2234;
  box-shadow:0 0 0 3px rgba(166,28,32,.25);
}
html.dark .register-scope .r-field::placeholder,
html.dark .register-scope .r-field-referral::placeholder{ color:#64748b; }

html.dark .register-scope .phone-input-group{ background:#1e2a3a; border-color:#2a3a52; }
html.dark .register-scope .phone-input-group:focus-within{
  border-color:#a61c20; box-shadow:0 0 0 3px rgba(166,28,32,.25);
}
html.dark .register-scope .country-code-btn{
  background:#111827; border-inline-end-color:#2a3a52; color:#e2e8f0;
}
html.dark .register-scope .country-code-btn:hover{ background:#0f172a; }
html.dark .register-scope .phone-input{ color:#f1f5f9; }
html.dark .register-scope .phone-input::placeholder{ color:#64748b; }

html.dark .register-scope .country-list{
  background:#161b27; border-color:#2a3a52; box-shadow:0 14px 40px rgba(0,0,0,.50);
}
html.dark .register-scope .country-list a{ color:#e2e8f0; border-bottom-color:#1e2a3a; }
html.dark .register-scope .country-list a:hover{ background:#1e2a3a; }
html.dark .register-scope .country-list::-webkit-scrollbar-thumb{ background:#374151; }

html.dark .register-scope .r-label{ color:#d1d5db; }
html.dark .register-scope .password-toggle{ color:#6b7280; }
html.dark .register-scope .password-toggle:hover{ color:#f87171; }
html.dark .register-scope .text-gray-500{ color:#94a3b8 !important; }
html.dark .register-scope .text-gray-600{ color:#cbd5e1 !important; }
html.dark .reg-divider{ color:#4b5563; }
html.dark .reg-divider::before,.dark .reg-divider::after{ background:#2a3a52; }
html.dark .reg-btn-outline{ color:#f87171; border-color:#f87171; }
html.dark .reg-btn-outline:hover{ background:#a61c20; color:#fff; border-color:#a61c20; }

/* ===== Final look override (reference-inspired) ===== */
.register-scope{background:#06070a !important;}
.register-shell{position:relative;}
.register-shell::before,.register-shell::after{content:'';position:absolute;width:74px;height:74px;border:1px solid rgba(227,19,34,.55);pointer-events:none;}
.register-shell::before{top:-14px;inset-inline-start:-14px;border-inline-end:0;border-bottom:0;}
.register-shell::after{bottom:-14px;inset-inline-end:-14px;border-inline-start:0;border-top:0;}
.register-card{background:linear-gradient(160deg,#10131a 0%,#0a0c11 100%) !important;border:1px solid #1d212b !important;border-radius:0 !important;box-shadow:0 24px 50px rgba(0,0,0,.48) !important;}
.register-card-header{background:transparent !important;padding:2rem 2.1rem .7rem !important;text-align:right !important;}
.register-card-header::after,.register-logo-ring{display:none !important;}
.register-card-header h2{color:#f2f4f8 !important;font-size:2.05rem !important;}
.register-card-header p{color:#7d8592 !important;font-size:.95rem;margin-top:.3rem;}
.register-form{padding:1rem 2.1rem 2rem !important;}
.r-label{display:flex !important;align-items:center;justify-content:space-between;color:#abb1bc !important;font-size:.8rem !important;text-transform:uppercase;letter-spacing:.12em;}
.r-label-ar{font-size:.74rem;letter-spacing:0;text-transform:none;color:#8d94a1;}
.register-scope .phone-input-group,.register-scope .r-field,.register-scope .r-field-referral{background:#1a1d24 !important;border:1px solid #262b37 !important;border-radius:0 !important;color:#f2f4f8 !important;}
.register-scope .phone-input-group:focus-within,.register-scope .r-field:focus,.register-scope .r-field-referral:focus{border-color:#e31322 !important;box-shadow:0 0 0 2px rgba(227,19,34,.16) !important;}
.register-scope .country-code-btn{height:54px !important;background:#151922 !important;border-inline-end:1px solid #262b37 !important;color:#d2d7df !important;direction:ltr !important;}
.register-scope .phone-input,.register-scope .r-field{height:54px !important;color:#f2f4f8 !important;}
.register-scope .phone-input::placeholder,.register-scope .r-field::placeholder{color:#737b88 !important;}
.register-scope .country-list{background:#12151c !important;border-color:#2a303d !important;border-radius:0 !important;width:min(560px, calc(100vw - 2rem)) !important;left:50% !important;right:auto !important;transform:translateX(-50%);}
.register-scope .country-list a{color:#d8dce4 !important;border-bottom-color:#212734 !important;}
.register-scope .country-list a:hover{background:#181d27 !important;}
/* زر إنشاء الحساب لون ثابت */
.reg-btn-primary{background:#6d0e16 !important;}
.reg-btn-primary:hover{background:#c9101d !important;}
.reg-foot{color:#8f96a3;text-align:center;text-transform:uppercase;letter-spacing:.08em;font-size:.9rem;margin-top:1.2rem;}
.reg-foot a{color:#fff;text-decoration:none;margin-inline-start:.4rem;}
.reg-foot a:hover{color:#e31322;}
html:not(.dark) .register-scope{background:#f5f7fb !important;}
html:not(.dark) .register-card{background:#fff !important;border-color:#e4e8f0 !important;box-shadow:0 18px 42px rgba(17,22,38,.14) !important;}
html:not(.dark) .register-card-header h2{color:#1c2230 !important;}
html:not(.dark) .r-label{color:#646d7b !important;}
html:not(.dark) .r-label-ar{color:#7b8492 !important;}
html:not(.dark) .register-scope .phone-input-group,
html:not(.dark) .register-scope .r-field,
html:not(.dark) .register-scope .r-field-referral{background:#f3f5f9 !important;border-color:#e2e6ef !important;color:#1d2432 !important;}
html:not(.dark) .register-scope .phone-input,
html:not(.dark) .register-scope .r-field{color:#1d2432 !important;}
html:not(.dark) .register-scope .country-list{background:#fff !important;border-color:#d8ddea !important;}
html:not(.dark) .register-scope .country-list a{color:#1f2533 !important;border-bottom-color:#edf1f7 !important;}
html:not(.dark) .reg-foot a{color:#202737;}
</style>
@endpush

@section('content')
<div class="register-scope flex items-center justify-center py-10 px-4" dir="rtl">
  <div class="w-full max-w-md register-shell">

    <!-- الكارت الرئيسي -->
    <div class="register-card mb-6"
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

      <!-- ترويسة الكارت -->
      <div class="register-card-header">
        <div class="register-logo-ring">
          <img src="{{ asset('logo.png') }}" alt="طفوف">
        </div>
        <h2>إنشاء حساب</h2>
        <p>إنشاء حساب جديد</p>
      </div>

      <!-- النموذج -->
      <div class="p-6 md:p-8 register-form">
        <form method="POST" action="{{ route('register') }}">
          @csrf

          <!-- الاسم الكامل -->
          <div class="mb-5">
            <label class="r-label" for="name">الاسم الكامل</label>
            <input id="name" type="text"
                   class="r-field @error('name') error @enderror"
                   name="name" value="{{ old('name') }}"
                   required autocomplete="name" autofocus
                   placeholder="أدخل اسمك الكامل">
            @error('name')
              <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                <i class="bi bi-exclamation-circle"></i>{{ $message }}
              </p>
            @enderror
          </div>

          <!-- رقم الهاتف -->
          <div class="mb-5 relative">
            <label class="r-label" for="local_phone_number">رقم الهاتف</label>
            <div class="phone-input-group">
              <button type="button" class="country-code-btn" @click="toggleCountryMenu()">
                <img :src="'{{ request()->root() }}/flags/' + selectedCountry.flag + '.svg'" class="flag w-6 h-4 object-contain rounded-sm shadow-sm" :alt="selectedCountry.name + ' flag'">
                <span class="font-bold dial-code" x-text="selectedCountry.code"></span>
                <i class="bi bi-chevron-down text-xs icon-inline transition-transform duration-200" :class="{'rotate-180': countryMenuOpen}"></i>
              </button>
              <input id="local_phone_number" type="tel"
                     class="phone-input"
                     name="local_phone_number" x-model="localPhone"
                     required autocomplete="tel"
                     :placeholder="selectedCountry.code === '+964' ? '7712345678' : 'رقم الهاتف'"
                     :maxlength="selectedCountry.code === '+964' ? 10 : 15">
            </div>
            <input type="hidden" name="phone_number" :value="selectedCountry.code.replace('+','') + localPhone">

            <p class="text-xs text-gray-500 mt-1.5 flex items-center gap-1">
              <i class="bi bi-whatsapp text-green-500"></i>
              سيتم إرسال رمز تحقق عبر واتساب لتفعيل حسابك.
            </p>

            <div x-show="countryMenuOpen"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-1"
                 class="country-list" style="display:none;">
              <template x-for="country in countries" :key="country.code">
                <a href="#" @click.prevent="selectCountry(country)">
                  <img :src="'{{ request()->root() }}/flags/' + country.flag + '.svg'" class="flag w-6 h-4 object-contain rounded-sm shadow-sm" :alt="country.name + ' flag'">
                  <span class="country-name flex-grow" x-text="country.name"></span>
                  <span class="dial-code text-gray-400 text-sm font-medium" x-text="country.code"></span>
                </a>
              </template>
            </div>

            @error('phone_number')
              <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                <i class="bi bi-exclamation-circle"></i>{{ $message }}
              </p>
            @enderror
          </div>

          <!-- كلمة المرور -->
          <div class="mb-5">
            <label class="r-label" for="password">كلمة المرور</label>
            <div class="password-wrapper">
              <input :type="showPassword ? 'text' : 'password'" id="password"
                     class="r-field pe-12 @error('password') error @enderror"
                     name="password" required autocomplete="new-password"
                     placeholder="أدخل كلمة المرور">
              <span class="password-toggle" @click="showPassword = !showPassword">
                <i class="bi text-xl icon-inline" :class="showPassword ? 'bi-eye-slash' : 'bi-eye'"></i>
              </span>
            </div>
            @error('password')
              <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                <i class="bi bi-exclamation-circle"></i>{{ $message }}
              </p>
            @enderror
          </div>

          <!-- تأكيد كلمة المرور -->
          <div class="mb-5">
            <label class="r-label" for="password-confirm">تأكيد كلمة المرور</label>
            <div class="password-wrapper">
              <input :type="showConfirmPassword ? 'text' : 'password'" id="password-confirm"
                     class="r-field pe-12"
                     name="password_confirmation" required autocomplete="new-password"
                     placeholder="أعد كتابة كلمة المرور">
              <span class="password-toggle" @click="showConfirmPassword = !showConfirmPassword">
                <i class="bi text-xl icon-inline" :class="showConfirmPassword ? 'bi-eye-slash' : 'bi-eye'"></i>
              </span>
            </div>
          </div>

          <!-- رمز الدعوة -->
          <div class="mb-6">
            <div class="flex items-center justify-between mb-1.5">
              <label class="r-label mb-0" for="referral_code">رمز الدعوة</label>
              <span class="referral-chip"><i class="bi bi-gift text-xs"></i> اختياري</span>
            </div>
            <input id="referral_code" type="text"
                   class="r-field r-field-referral @error('referral_code') error @enderror"
                   name="referral_code" value="{{ old('referral_code') }}"
                   autocomplete="off" placeholder="أدخل رمز الدعوة إن وجد">
            @error('referral_code')
              <p class="text-red-500 text-xs mt-1.5 flex items-center gap-1">
                <i class="bi bi-exclamation-circle"></i>{{ $message }}
              </p>
            @enderror
          </div>

          <!-- زر إنشاء الحساب -->
          <button type="submit" class="reg-btn-primary mb-4">
            <i class="bi bi-person-check icon-inline"></i>
            إنشاء حساب
          </button>

          <p class="reg-foot">لديك حساب بالفعل? <a href="{{ route('login') }}">تسجيل دخول</a></p>
        </form>
      </div>
    </div>

    <!-- واتساب -->
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
