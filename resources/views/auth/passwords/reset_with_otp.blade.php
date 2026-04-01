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

.auth-field{width:100%;height:54px !important;padding:.5rem 1rem;background:var(--c-field-bg) !important;border:1px solid var(--c-field-border) !important;border-radius:0 !important;color:var(--c-text) !important;outline:none;}
.auth-field:focus{border-color:#e31322 !important;box-shadow:0 0 0 2px rgba(227,19,34,.16) !important;}
.auth-field::placeholder{color:#737b88 !important;}

.password-wrapper{position:relative;}
.auth-field.pe-12{padding-inline-end:3rem !important;}
.password-toggle{position:absolute;inset-inline-end:1rem;top:50%;transform:translateY(-50%);cursor:pointer;color:#9ca3af;transition:.2s;line-height:1;z-index:10;}
.password-toggle:hover{color:#e31322;}

.auth-phone-chip{margin-top:.45rem;color:#8f96a3;font-size:.76rem;}

.auth-btn-primary{width:100%;padding:.85rem 1rem;background:#6d0e16 !important;color:#fff;font-weight:700;font-size:1rem;border:none;border-radius:0;cursor:pointer;transition:.22s;display:flex;align-items:center;justify-content:center;gap:.5rem;}
.auth-btn-primary:hover{background:#c9101d !important;}

.auth-foot{color:#8f96a3;text-align:center;text-transform:uppercase;letter-spacing:.08em;font-size:.9rem;margin-top:1.2rem;}
.auth-foot a{color:#fff;text-decoration:none;margin-inline-start:.4rem;}
.auth-foot a:hover{color:#e31322;}

.auth-alert{margin-bottom:1.25rem;border:1px solid rgba(239,68,68,.35);background:rgba(127,29,29,.2);color:#fecaca;padding:.85rem 1rem;font-size:.85rem;}
.auth-success{margin-bottom:1.25rem;border:1px solid rgba(34,197,94,.35);background:rgba(20,83,45,.2);color:#bbf7d0;padding:.85rem 1rem;font-size:.85rem;}

.otp-input{
  width: 100%;
  height: 54px !important;
  text-align: center;
  font-size: 1.25rem;
  font-weight: 800;
  background: var(--c-field-bg) !important;
  border: 1px solid var(--c-field-border) !important;
  color: var(--c-text) !important;
  outline: none;
  transition: all 0.2s;
}
.otp-input:focus{
  border-color: #e31322 !important;
  box-shadow: 0 0 0 2px rgba(227,19,34,.16) !important;
  background: var(--c-field-bg) !important;
}

html:not(.dark) .auth-scope{background:#f5f7fb !important;}
html:not(.dark) .auth-card{background:#fff !important;border-color:#e4e8f0 !important;box-shadow:0 18px 42px rgba(17,22,38,.14) !important;}
html:not(.dark) .auth-card-header h2{color:#1c2230 !important;}
html:not(.dark) .auth-card-header p{color:#6f7785 !important;}
html:not(.dark) .auth-label{color:#646d7b !important;}
html:not(.dark) .auth-field, html:not(.dark) .otp-input{background:#f3f5f9 !important;border-color:#e2e6ef !important;color:#1d2432 !important;}
html:not(.dark) .auth-foot a{color:#202737;}
html:not(.dark) .auth-phone-chip{color:#6b7280;}
html:not(.dark) .auth-alert{border-color:#fecaca;background:#fef2f2;color:#b91c1c;}
html:not(.dark) .auth-success{border-color:#86efac;background:#f0fdf4;color:#166534;}
</style>
@endpush

@section('content')
<div class="auth-scope flex items-center justify-center py-10 px-4" dir="rtl">
  <div class="w-full max-w-md auth-shell">

    <div class="auth-card mb-6"
         x-data="{
           showPassword:false,
           showConfirmPassword:false,
           otpDigits: ['', '', '', '', '', ''],
           otpCombined: '',
           updateOtpCombined() {
             this.otpCombined = this.otpDigits.join('');
           },
           onOtpInput(index, event) {
             const value = (event.target.value || '').replace(/\D/g, '').slice(-1);
             this.otpDigits[index] = value;
             event.target.value = value;
             this.updateOtpCombined();
             if (value && index < 5) {
               this.$refs['otp' + (index + 1)].focus();
             }
           },
           onOtpKeydown(index, event) {
             if (event.key === 'Backspace' && !this.otpDigits[index] && index > 0) {
               this.$refs['otp' + (index - 1)].focus();
             }
           },
           onOtpPaste(event) {
             const pasted = (event.clipboardData || window.clipboardData)
               .getData('text')
               .replace(/\D/g, '')
               .slice(0, 6);
             if (!pasted) return;
             event.preventDefault();
             for (let i = 0; i < 6; i++) {
               this.otpDigits[i] = pasted[i] || '';
             }
             this.updateOtpCombined();
             const focusIndex = Math.min(pasted.length, 5);
             this.$refs['otp' + focusIndex].focus();
           }
         }">

      <div class="auth-card-header">
        <h2>تحديث كلمة المرور</h2>
        <p>أدخل رمز التحقق وكلمة المرور الجديدة</p>
      </div>

      <div class="p-6 md:p-8 auth-form">
        @if (session('status'))
          <div class="auth-success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
          <div class="auth-alert">
            <ul class="list-disc pr-5 space-y-1">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form method="POST" action="{{ route('password.update.with.otp') }}">
          @csrf
          <input type="hidden" name="phone_number" value="{{ session('phone_number_for_reset') }}">

          <input type="hidden" name="otp" x-model="otpCombined">

          <div class="mb-5">
            <label class="auth-label">رمز التحقق</label>
            <div class="grid grid-cols-6 gap-2" dir="ltr" @paste="onOtpPaste($event)">
              <input x-ref="otp0" type="text" maxlength="1" inputmode="numeric"
                     class="otp-input @error('otp') border-red-500 @enderror"
                     x-model="otpDigits[0]" @input="onOtpInput(0, $event)" @keydown="onOtpKeydown(0, $event)" autofocus>
              <input x-ref="otp1" type="text" maxlength="1" inputmode="numeric"
                     class="otp-input @error('otp') border-red-500 @enderror"
                     x-model="otpDigits[1]" @input="onOtpInput(1, $event)" @keydown="onOtpKeydown(1, $event)">
              <input x-ref="otp2" type="text" maxlength="1" inputmode="numeric"
                     class="otp-input @error('otp') border-red-500 @enderror"
                     x-model="otpDigits[2]" @input="onOtpInput(2, $event)" @keydown="onOtpKeydown(2, $event)">
              <input x-ref="otp3" type="text" maxlength="1" inputmode="numeric"
                     class="otp-input @error('otp') border-red-500 @enderror"
                     x-model="otpDigits[3]" @input="onOtpInput(3, $event)" @keydown="onOtpKeydown(3, $event)">
              <input x-ref="otp4" type="text" maxlength="1" inputmode="numeric"
                     class="otp-input @error('otp') border-red-500 @enderror"
                     x-model="otpDigits[4]" @input="onOtpInput(4, $event)" @keydown="onOtpKeydown(4, $event)">
              <input x-ref="otp5" type="text" maxlength="1" inputmode="numeric"
                     class="otp-input @error('otp') border-red-500 @enderror"
                     x-model="otpDigits[5]" @input="onOtpInput(5, $event)" @keydown="onOtpKeydown(5, $event)">
            </div>
            <p class="text-xs text-gray-500 mt-2 dark:text-[#737b88]">أدخل الرمز المكون من 6 أرقام المرسل إليك.</p>
          </div>

          <div class="mb-5">
            <label class="auth-label" for="password">كلمة المرور الجديدة</label>
            <div class="password-wrapper">
              <input :type="showPassword ? 'text' : 'password'" id="password" name="password"
                     class="auth-field pe-12 @error('password') border-red-500 @enderror"
                     required placeholder="أدخل كلمة المرور الجديدة">
              <span class="password-toggle" @click="showPassword = !showPassword">
                <i class="bi text-xl" :class="showPassword ? 'bi-eye-slash' : 'bi-eye'"></i>
              </span>
            </div>
          </div>

          <div class="mb-6">
            <label class="auth-label" for="password-confirm">تأكيد كلمة المرور</label>
            <div class="password-wrapper">
              <input :type="showConfirmPassword ? 'text' : 'password'" id="password-confirm"
                     class="auth-field pe-12" name="password_confirmation" required
                     placeholder="أعد كتابة كلمة المرور">
              <span class="password-toggle" @click="showConfirmPassword = !showConfirmPassword">
                <i class="bi text-xl" :class="showConfirmPassword ? 'bi-eye-slash' : 'bi-eye'"></i>
              </span>
            </div>
          </div>

          <button type="submit" class="auth-btn-primary">
            <i class="bi bi-shield-check"></i>
            تحديث كلمة المرور
          </button>

          <p class="auth-phone-chip">الرقم: {{ session('phone_number_for_reset') }}</p>
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
