@extends('layouts.app')

@section('title', 'تأكيد رقم الهاتف')

@php
  $phoneForVerification = session('phone_for_verification');
  $oldOtp = preg_replace('/\D+/', '', old('otp', ''));
  $otpDigits = array_pad(str_split(substr($oldOtp, 0, 6)), 6, '');
@endphp

@push('styles')
<style>
  /* ===== Match current login page ===== */
  .otp-scope{
    --c-primary:#6d0e16;
    --c-hover:#c9101d;
    --c-focus-ring:rgba(227,19,34,.16);
    --c-text:#1d2432;
    background:#f5f7fb !important;
    min-height: 100vh;
  }

  .otp-card{
    background: #fff;
    border: 1px solid #e4e8f0;
    border-radius: 0;
    box-shadow: 0 18px 42px rgba(17,22,38,.14);
    overflow: hidden;
    position: relative;
  }

  .otp-shell{ position:relative; }
  .otp-shell::before,
  .otp-shell::after{
    content:'';
    position:absolute;
    width:74px;
    height:74px;
    border:1px solid rgba(227,19,34,.22);
    pointer-events:none;
  }
  .otp-shell::before{ top:-14px; inset-inline-start:-14px; border-inline-end:0; border-bottom:0; }
  .otp-shell::after{ bottom:-14px; inset-inline-end:-14px; border-inline-start:0; border-top:0; }

  .otp-head{
    background: transparent;
    text-align: right;
  }
  .otp-body{ position: relative; }

  .otp-phone-chip{
    background:#f3f5f9;
    border:1px solid #e2e6ef;
    color:#4b5563;
    border-radius: 8px;
    padding: .55rem .85rem;
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    font-size: .9rem;
    margin-top: .45rem;
  }

  .otp-input{
    border: 1px solid #e2e6ef;
    border-radius: 0;
    background:#f3f5f9;
    color: var(--c-text);
    font-weight: 800;
    text-align: center;
  }

  .otp-input:focus{
    outline: none;
    box-shadow:
      0 0 0 2px var(--c-focus-ring);
    border-color: var(--c-primary) !important;
    background:#fff;
  }

  .otp-alert{
    border-radius: 12px;
    border-width: 1px;
  }

  .otp-wa i{ line-height: 1; }

  .otp-btn{
    background: var(--c-primary) !important;
    color: #fff !important;
    border-radius: 0.95rem;
    box-shadow: 0 4px 18px rgba(109,14,22,.32);
  }
  .otp-btn:hover{
    background: var(--c-hover) !important;
    box-shadow: 0 6px 22px rgba(201,16,29,.28);
  }

  /* ===== Dark Mode ===== */
  html.dark .otp-scope{
    --c-text:#f2f4f8;
    --c-focus-ring:rgba(227,19,34,.16);
    background:#06070a !important;
  }
  html.dark .otp-card{
    background: linear-gradient(160deg,#10131a 0%,#0a0c11 100%) !important;
    border-color: #1d212b;
    box-shadow: 0 24px 50px rgba(0,0,0,.48);
  }
  html.dark .otp-shell::before,
  html.dark .otp-shell::after{
    border-color: rgba(227,19,34,.55);
  }
  html.dark .otp-title{ color: #f2f4f8 !important; }
  html.dark .otp-sub{ color: #7d8592 !important; }
  html.dark .otp-phone-chip{ background:#1a1d24; border-color:#262b37; color:#d8dce4; }

  html.dark .otp-input{
    background:#1a1d24 !important;
    border-color:#262b37 !important;
    color:#f2f4f8 !important;
  }
  html.dark .otp-input::placeholder{ color: #9ca3af !important; }
  html.dark .otp-input:focus{
    box-shadow: 0 0 0 2px rgba(227,19,34,.16);
    border-color: #e31322 !important;
    background:#1a1d24 !important;
  }

  html.dark .otp-btn{
    background: #be6661 !important;
    color:#fff !important;
  }
  html.dark .otp-btn:hover{ background:#cd8985 !important; }

  html.dark .otp-muted{ color:#9ca3af !important; }

  html.dark .otp-resend:disabled{
    color:#6b7280 !important;
  }

  /* Alerts in dark */
  html.dark .otp-alert-success{
    background: rgba(16,185,129,.12);
    border-color: rgba(16,185,129,.35);
    color:#34d399;
  }
  html.dark .otp-alert-error{
    background: rgba(239,68,68,.12);
    border-color: rgba(239,68,68,.35);
    color:#f87171;
  }
  html.dark .otp-alert-info{
    background: rgba(59,130,246,.12);
    border-color: rgba(59,130,246,.35);
    color:#93c5fd;
  }
</style>
@endpush

@section('content')
<div class="otp-scope">
<div class="container mx-auto px-4 py-10" dir="rtl">
  <div class="max-w-md mx-auto otp-shell">
    <div class="otp-card">

      {{-- Header --}}
      <div class="otp-head py-6 px-6 md:px-8">
        <h2 class="otp-title text-[2.05rem] leading-tight font-extrabold text-right text-[#1c2230]">تأكيد رقم الهاتف</h2>
        <p class="otp-sub text-right text-sm text-[#7b8492] mt-1">أدخل رمز التحقق المرسل عبر واتساب.</p>
        @if($phoneForVerification)
          <div class="text-right">
            <span class="otp-phone-chip" dir="ltr">
              <i class="bi bi-phone"></i>
              {{ $phoneForVerification }}
            </span>
          </div>
        @endif
      </div>

      {{-- Body --}}
      <div class="otp-body px-6 pb-6 md:px-8 md:pb-8"
           x-data="{
              timer: 60,
              canResend: false,
              message: '',
              messageType: '',
                otpDigits: ['{{ $otpDigits[0] }}','{{ $otpDigits[1] }}','{{ $otpDigits[2] }}','{{ $otpDigits[3] }}','{{ $otpDigits[4] }}','{{ $otpDigits[5] }}'],
                otpCombined: '',

              startTimer() {
                  this.canResend = false;
                  let interval = setInterval(() => {
                      this.timer--;
                      if (this.timer === 0) {
                          clearInterval(interval);
                          this.canResend = true;
                          this.timer = 60;
                      }
                  }, 1000);
              },

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

                if (!pasted) {
                  return;
                }

                event.preventDefault();

                for (let i = 0; i < 6; i++) {
                  this.otpDigits[i] = pasted[i] || '';
                }

                this.updateOtpCombined();
                const focusIndex = Math.min(pasted.length, 5);
                this.$refs['otp' + focusIndex].focus();
              },

              async resendCode() {
                  if (!this.canResend) return;

                  this.canResend = false;
                  this.message = 'جاري إرسال الرمز...';
                  this.messageType = 'info';

                  try {
                      const response = await fetch('{{ route('otp.verification.resend') }}', {
                          method: 'POST',
                          headers: {
                              'X-CSRF-TOKEN': '{{ csrf_token() }}',
                              'Accept': 'application/json',
                          }
                      });

                      const data = await response.json();

                      if (!response.ok) {
                          throw new Error(data.message || 'حدث خطأ ما');
                      }

                      this.message = data.message;
                      this.messageType = 'success';
                      this.startTimer();

                  } catch (error) {
                      this.message = error.message || 'حدث خطأ. يرجى المحاولة مرة أخرى.';
                      this.messageType = 'error';
                      this.canResend = true;
                  }
              }
           }"
              x-init="startTimer(); updateOtpCombined()"
      >

        @if (session('status'))
          <div class="otp-alert otp-alert-success bg-green-50 border border-green-400 text-green-700 px-4 py-3 mb-4" role="alert">
            {{ session('status') }}
          </div>
        @endif

        <div x-show="message"
             :class="{
               'otp-alert otp-alert-success bg-green-50 border-green-400 text-green-700': messageType === 'success',
               'otp-alert otp-alert-error bg-red-50 border-red-400 text-red-700': messageType === 'error',
               'otp-alert otp-alert-info bg-blue-50 border-blue-400 text-blue-700': messageType === 'info'
             }"
             class="border px-4 py-3 mb-4"
             role="alert"
             x-text="message">
        </div>

        <form method="POST" action="{{ route('otp.verification.verify') }}">
          @csrf
           <input type="hidden" name="otp" x-model="otpCombined">

          <div class="mb-4">
            <label for="otp" class="block text-[#646d7b] text-sm font-semibold mb-2 dark:text-[#abb1bc]">رمز التحقق</label>
             <div class="grid grid-cols-6 gap-2" dir="ltr" @paste="onOtpPaste($event)">
          <input x-ref="otp0" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code"
            class="otp-input w-full h-12 text-lg @error('otp') border-red-500 @enderror"
            x-model="otpDigits[0]" @input="onOtpInput(0, $event)" @keydown="onOtpKeydown(0, $event)" autofocus>
          <input x-ref="otp1" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*"
            class="otp-input w-full h-12 text-lg @error('otp') border-red-500 @enderror"
            x-model="otpDigits[1]" @input="onOtpInput(1, $event)" @keydown="onOtpKeydown(1, $event)">
          <input x-ref="otp2" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*"
            class="otp-input w-full h-12 text-lg @error('otp') border-red-500 @enderror"
            x-model="otpDigits[2]" @input="onOtpInput(2, $event)" @keydown="onOtpKeydown(2, $event)">
          <input x-ref="otp3" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*"
            class="otp-input w-full h-12 text-lg @error('otp') border-red-500 @enderror"
            x-model="otpDigits[3]" @input="onOtpInput(3, $event)" @keydown="onOtpKeydown(3, $event)">
          <input x-ref="otp4" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*"
            class="otp-input w-full h-12 text-lg @error('otp') border-red-500 @enderror"
            x-model="otpDigits[4]" @input="onOtpInput(4, $event)" @keydown="onOtpKeydown(4, $event)">
          <input x-ref="otp5" type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*"
            class="otp-input w-full h-12 text-lg @error('otp') border-red-500 @enderror"
            x-model="otpDigits[5]" @input="onOtpInput(5, $event)" @keydown="onOtpKeydown(5, $event)">
             </div>
             <p class="text-xs text-gray-500 mt-2 dark:text-[#737b88]">أدخل كل رقم في مربع مستقل أو الصق الرمز كاملًا.</p>
            @error('otp')
              <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
          </div>

          <div class="mb-4">
            <button type="submit"
                    class="otp-btn w-full text-white font-extrabold py-3 px-4 transition duration-300">
              تحقق
            </button>
          </div>
        </form>

        <div class="text-center text-sm text-gray-600 mt-4 otp-muted">
          <p x-show="!canResend">
            يمكنك طلب رمز جديد خلال <span x-text="timer" class="font-extrabold"></span> ثانية.
          </p>
          <button @click="resendCode()" :disabled="!canResend" x-show="canResend"
                  class="otp-resend font-extrabold text-[#6d0e16] hover:underline disabled:text-gray-400 disabled:cursor-not-allowed">
            إعادة إرسال الرمز
          </button>
        </div>

        {{-- زر واتساب --}}
        <div class="mt-6 text-center">
          <p class="otp-muted text-sm text-gray-600 mb-2">بحاجة لمساعدة؟ تواصل معنا على الواتساب:</p>
          <a href="https://wa.me/9647744969024" target="_blank"
             class="otp-wa inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-sm font-extrabold rounded-md shadow">
            <i class="bi bi-whatsapp text-lg ml-2"></i>
            تواصل عبر واتساب
          </a>
        </div>

      </div>
    </div>
  </div>
</div>
</div>
@endsection
