@php
// قائمة الدول
$countries = [
  ['name' => 'العراق', 'code' => '+964', 'flag' => 'iq'],
  ['name' => 'مصر', 'code' => '+20', 'flag' => 'eg'],
  ['name' => 'السعودية', 'code' => '+966', 'flag' => 'sa'],
  ['name' => 'الإمارات', 'code' => '+971', 'flag' => 'ae'],
  ['name' => 'تركيا', 'code' => '+90', 'flag' => 'tr'],
  ['name' => 'الأردن', 'code' => '+962', 'flag' => 'jo'],
  ['name' => 'سوريا', 'code' => '+963', 'flag' => 'sy'],
  ['name' => 'لبنان', 'code' => '+961', 'flag' => 'lb'],
  ['name' => 'فلسطين', 'code' => '+970', 'flag' => 'ps'],
  ['name' => 'قطر', 'code' => '+974', 'flag' => 'qa'],
  ['name' => 'البحرين', 'code' => '+973', 'flag' => 'bh'],
  ['name' => 'الكويت', 'code' => '+965', 'flag' => 'kw'],
  ['name' => 'عُمان', 'code' => '+968', 'flag' => 'om'],
  ['name' => 'اليمن', 'code' => '+967', 'flag' => 'ye'],
  ['name' => 'الجزائر', 'code' => '+213', 'flag' => 'dz'],
  ['name' => 'تونس', 'code' => '+216', 'flag' => 'tn'],
  ['name' => 'المغرب', 'code' => '+212', 'flag' => 'ma'],
  ['name' => 'ليبيا', 'code' => '+218', 'flag' => 'ly'],
  ['name' => 'السودان', 'code' => '+249', 'flag' => 'sd'],
  ['name' => 'موريتانيا', 'code' => '+222', 'flag' => 'mr'],
  ['name' => 'الصومال', 'code' => '+252', 'flag' => 'so'],
];
@endphp

@extends('layouts.app')
@section('title', 'إعادة تعيين كلمة المرور')

@push('styles')
<style>
.auth-scope{
  --c-primary:#6d0e16;
  --c-bg:#06070a;
  --c-field-bg:#1a1d24;
  --c-field-border:#262b37;
  --c-text:#f2f4f8;
}

.auth-scope{background:var(--c-bg) !important;min-height:100vh;}
.auth-shell{position:relative;}
.auth-shell::before,.auth-shell::after{content:'';position:absolute;width:74px;height:74px;border:1px solid rgba(227,19,34,.55);pointer-events:none;}
.auth-shell::before{top:-14px;inset-inline-start:-14px;border-inline-end:0;border-bottom:0;}
.auth-shell::after{bottom:-14px;inset-inline-end:-14px;border-inline-start:0;border-top:0;}

.auth-card{background:linear-gradient(160deg,#10131a 0%,#0a0c11 100%) !important;border:1px solid #1d212b !important;border-radius:0 !important;box-shadow:0 24px 50px rgba(0,0,0,.48) !important;overflow:visible;}
.auth-card-header{background:transparent !important;padding:2rem 2.1rem .7rem !important;text-align:right !important;}
.auth-card-header h2{color:#f2f4f8 !important;font-size:2.05rem !important;font-weight:800;margin:0;}
.auth-card-header p{color:#7d8592 !important;font-size:.95rem;margin-top:.3rem;}
.auth-form{padding:1rem 2.1rem 2rem !important;}

.auth-label{display:block;color:#abb1bc !important;font-size:.8rem !important;text-transform:uppercase;letter-spacing:.12em;margin-bottom:.5rem;}

.phone-input-group{display:flex;align-items:center;direction:ltr;background:var(--c-field-bg) !important;border:1px solid var(--c-field-border) !important;border-radius:0 !important;overflow:hidden;}
.phone-input-group:focus-within{border-color:#e31322 !important;box-shadow:0 0 0 2px rgba(227,19,34,.16) !important;}

.country-code-btn{height:54px !important;min-width:110px;padding:.5rem .75rem;background:#151922 !important;border-inline-end:1px solid var(--c-field-border) !important;color:#d2d7df !important;display:flex;align-items:center;justify-content:center;gap:.4rem;direction:ltr !important;font-size:.88rem;cursor:pointer;}
.country-code-btn img.flag,.country-list img.flag{display:block !important;width:24px !important;height:16px !important;min-width:24px !important;min-height:16px !important;object-fit:contain !important;background:#fff;line-height:0 !important;vertical-align:top !important;border-radius:2px;box-shadow:0 1px 2px rgba(0,0,0,.25);flex-shrink:0;}

.phone-input{flex:1;height:54px !important;padding:.5rem 1rem;border:0;outline:0;font-size:1rem;background:transparent;color:var(--c-text) !important;direction:ltr;text-align:left;}
.phone-input::placeholder{color:#737b88 !important;}

.country-list{position:absolute;left:50%;right:auto;transform:translateX(-50%);width:min(640px, calc(100vw - 1rem));top:calc(100% + 6px);max-height:240px;overflow-y:auto;background:#12151c !important;border:1px solid #2a303d !important;border-radius:0 !important;box-shadow:0 12px 32px rgba(0,0,0,.4);z-index:9999;direction:rtl;}
.country-list a{display:flex;align-items:center;gap:.65rem;padding:.65rem 1rem;font-size:.9rem;color:#d8dce4 !important;border-bottom:1px solid #212734 !important;transition:.12s;}
.country-list a:last-child{border-bottom:0;}
.country-list a:hover{background:#181d27 !important;}

.auth-note{color:#8f96a3;font-size:.76rem;margin-top:.45rem;}

.auth-btn-primary{width:100%;padding:.85rem 1rem;background:#6d0e16 !important;color:#fff;font-weight:700;font-size:1rem;border:none;border-radius:0;cursor:pointer;transition:.22s;display:flex;align-items:center;justify-content:center;gap:.5rem;}
.auth-btn-primary:hover{background:#c9101d !important;}

.auth-foot{color:#8f96a3;text-align:center;text-transform:uppercase;letter-spacing:.08em;font-size:.9rem;margin-top:1.2rem;}
.auth-foot a{color:#fff;text-decoration:none;margin-inline-start:.4rem;}
.auth-foot a:hover{color:#e31322;}

.auth-alert{margin-bottom:1.25rem;border:1px solid rgba(239,68,68,.35);background:rgba(127,29,29,.2);color:#fecaca;padding:.85rem 1rem;font-size:.85rem;}

html:not(.dark) .auth-scope{background:#f5f7fb !important;}
html:not(.dark) .auth-card{background:#fff !important;border-color:#e4e8f0 !important;box-shadow:0 18px 42px rgba(17,22,38,.14) !important;}
html:not(.dark) .auth-card-header h2{color:#1c2230 !important;}
html:not(.dark) .auth-card-header p{color:#6f7785 !important;}
html:not(.dark) .auth-label{color:#646d7b !important;}
html:not(.dark) .phone-input-group{background:#f3f5f9 !important;border-color:#e2e6ef !important;}
html:not(.dark) .country-code-btn{background:#e9edf5 !important;color:#2b3445 !important;border-inline-end-color:#d9deea !important;}
html:not(.dark) .phone-input{color:#1d2432 !important;}
html:not(.dark) .country-list{background:#fff !important;border-color:#d8ddea !important;}
html:not(.dark) .country-list a{color:#1f2533 !important;border-bottom-color:#edf1f7 !important;}
html:not(.dark) .auth-foot a{color:#202737;}
html:not(.dark) .auth-note{color:#6b7280;}
html:not(.dark) .auth-alert{border-color:#fecaca;background:#fef2f2;color:#b91c1c;}
</style>
@endpush

@section('content')
<div class="auth-scope flex items-center justify-center py-10 px-4" dir="rtl">
  <div class="w-full max-w-md auth-shell">

    <div class="auth-card mb-6"
         x-data="{
           countryMenuOpen:false,
           countries: {{ json_encode($countries) }},
           selectedCountry: {{ json_encode($countries[0]) }},
           localPhone: '{{ old('local_phone_number', '') }}',
           init(){
             this.$watch('localPhone', v => {
               if(this.selectedCountry.code === '+964' && v.startsWith('0')){
                 this.$nextTick(() => { this.localPhone = v.substring(1) })
               }
             })
           },
           toggleCountryMenu(){ this.countryMenuOpen = !this.countryMenuOpen },
           selectCountry(c){ this.selectedCountry = c; this.countryMenuOpen = false }
         }"
         x-init="init()"
         @click.away="countryMenuOpen=false">

      <div class="auth-card-header">
        <h2>إعادة تعيين كلمة المرور</h2>
        <p>أدخل رقم هاتفك المسجل لإرسال رمز التحقق</p>
      </div>

      <div class="p-6 md:p-8 auth-form">
        @if ($errors->any())
          <div class="auth-alert">
            <ul class="list-disc pr-5 space-y-1">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form method="POST" action="{{ route('password.send.otp') }}">
          @csrf

          <div class="mb-6 relative">
            <label for="local_phone_number" class="auth-label">رقم الهاتف</label>
            <div class="phone-input-group">
              <button type="button" class="country-code-btn" @click="toggleCountryMenu()">
                <img :src="'{{ request()->root() }}/flags/' + selectedCountry.flag + '.svg'" class="flag w-6 h-4 object-contain rounded-sm shadow-sm" :alt="selectedCountry.name + ' flag'">
                <span class="font-semibold" x-text="selectedCountry.code"></span>
                <i class="bi bi-chevron-down text-xs transition-transform duration-300" :class="{'rotate-180': countryMenuOpen}"></i>
              </button>

              <input id="local_phone_number" type="tel" class="phone-input"
                     name="local_phone_number" x-model="localPhone" required autocomplete="tel" autofocus
                     :placeholder="selectedCountry.code === '+964' ? 'مثال: 7712345678' : 'أدخل رقم الهاتف'"
                     :maxlength="selectedCountry.code === '+964' ? 10 : 15"
                     :pattern="selectedCountry.code === '+964' ? '7[0-9]{9}' : null"
                     title="للرقم العراقي، أدخل 10 أرقام تبدأ بالرقم 7.">
            </div>

            <input type="hidden" name="phone_number" :value="selectedCountry.code.replace('+','') + localPhone">

            <p class="auth-note">سيتم إرسال رمز تحقق عبر واتساب إلى هذا الرقم.</p>
            <p x-show="selectedCountry.code === '+964'" class="auth-note" style="display:none;">
              ملاحظة: أدخل الرقم المكوّن من 10 أرقام بدون الصفر في البداية (مثال: 7712345678).
            </p>

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
                  <img :src="'{{ request()->root() }}/flags/' + country.flag + '.svg'" class="flag w-6 h-4 object-contain rounded-sm shadow-sm" :alt="country.name + ' flag'">
                  <span class="flex-grow text-right" x-text="country.name"></span>
                  <span class="font-medium" x-text="country.code"></span>
                </a>
              </template>
            </div>

            @error('phone_number')
              <p class="text-red-500 text-xs mt-2 flex items-center gap-1">
                <i class="bi bi-exclamation-circle"></i>{{ $message }}
              </p>
            @enderror
          </div>

          <button type="submit" class="auth-btn-primary">
            <i class="bi bi-shield-lock"></i>
            إرسال رمز التحقق
          </button>

          <p class="auth-foot">تذكرت كلمة المرور؟ <a href="{{ route('login') }}">تسجيل الدخول</a></p>
        </form>
      </div>
    </div>

    <div class="text-center">
      <p class="text-sm text-gray-500 mb-2">بحاجة إلى مساعدة؟</p>
      <a href="https://wa.me/9647744969024" target="_blank"
         class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-semibold text-white shadow transition hover:-translate-y-0.5"
         style="background:#25d366">
        <i class="bi bi-whatsapp text-lg"></i>
        تواصل معنا عبر واتساب
      </a>
    </div>

  </div>
</div>
@endsection
