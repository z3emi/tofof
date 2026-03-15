@extends('layouts.app')

@section('title', 'تأكيد رقم الهاتف')

@push('styles')
<style>
  /* ===== Card Glow / Subtle accents ===== */
  .otp-card{
    background: #ffffff;
    border-radius: 16px;
    box-shadow:
      0 14px 28px rgba(0,0,0,.08),
      0 6px 12px rgba(0,0,0,.04),
      inset 0 0 0 1px #eadbcd;
    overflow: hidden;
    position: relative;
    isolation: isolate;
  }
  .otp-card::before{
    content:"";
    position:absolute; inset:0;
    background:
      radial-gradient(120% 60% at 110% -10%, rgba(205,137,133,.06), transparent 50%),
      radial-gradient(80% 40% at -10% 10%, rgba(255,255,255,.9), transparent 45%);
    pointer-events:none;
    z-index:0;
  }
  .otp-head{
    position: relative;
    background: linear-gradient(90deg, rgba(190,102,97,.08), rgba(209,163,164,.08));
    border-bottom: 1px solid #eadbcd;
  }
  .otp-body{ position: relative; z-index: 1; }

  /* Inputs focus ring to brand */
  .otp-input:focus{
    outline: none;
    box-shadow:
      0 0 0 2px rgba(190,102,97,.25),
      0 2px 8px rgba(190,102,97,.15);
    border-color: #cd8985 !important;
  }

  /* Alert blocks rounded + border */
  .otp-alert{
    border-radius: 12px;
    border-width: 1px;
  }

  /* WhatsApp button icon alignment fix */
  .otp-wa i{ line-height: 1; }

  /* ===== Dark Mode ===== */
  html.dark .otp-card{
    background: #0f172a !important;
    box-shadow:
      0 14px 28px rgba(0,0,0,.35),
      inset 0 0 0 1px #1f2937 !important;
  }
  html.dark .otp-card::before{
    background:
      radial-gradient(120% 60% at 110% -10%, rgba(205,137,133,.08), transparent 50%),
      radial-gradient(80% 40% at -10% 10%, rgba(255,255,255,.05), transparent 45%);
  }
  html.dark .otp-head{
    background: linear-gradient(90deg, rgba(190,102,97,.12), rgba(209,163,164,.12));
    border-bottom-color: #1f2937;
  }
  html.dark .otp-title{ color: #e5e7eb !important; }
  html.dark .otp-sub{ color: #cbd5e1 !important; }

  html.dark .otp-input{
    background: #111827 !important;
    border-color: #374151 !important;
    color: #f9fafb !important;
  }
  html.dark .otp-input::placeholder{ color: #9ca3af !important; }
  html.dark .otp-input:focus{
    box-shadow:
      0 0 0 2px rgba(190,102,97,.35),
      0 2px 8px rgba(190,102,97,.22);
    border-color: #be6661 !important;
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
<div class="container mx-auto px-4 py-16">
  <div class="max-w-md mx-auto">
    <div class="otp-card">

      {{-- Header --}}
      <div class="otp-head py-6 px-6 md:px-8">
        <h2 class="otp-title text-2xl font-extrabold text-center text-brand-text">تأكيد رقم الهاتف</h2>
        <p class="otp-sub text-center text-sm text-gray-600 mt-2">لقد أرسلنا رمز تحقق مكون من 6 أرقام إلى رقم هاتفك عبر واتساب.</p>
      </div>

      {{-- Body --}}
      <div class="otp-body py-6 px-6 md:px-8"
           x-data="{
              timer: 60,
              canResend: false,
              message: '',
              messageType: '',

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
           x-init="startTimer()"
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
          <div class="mb-4">
            <label for="otp" class="block text-gray-700 text-sm font-bold mb-2 dark:text-gray-200">رمز التحقق</label>
            <input id="otp" type="text"
                   class="otp-input w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-brand-primary @error('otp') border-red-500 @enderror"
                   name="otp" required autofocus inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code"
                   placeholder="أدخلي الرمز هنا">
            @error('otp')
              <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
          </div>

          <div class="mb-4">
            <button type="submit"
                    class="otp-btn w-full bg-brand-primary text-white font-extrabold py-3 px-4 rounded-md hover:bg-brand-dark transition duration-300">
              تحقق
            </button>
          </div>
        </form>

        <div class="text-center text-sm text-gray-600 mt-4 otp-muted">
          <p x-show="!canResend">
            يمكنك طلب رمز جديد خلال <span x-text="timer" class="font-extrabold"></span> ثانية.
          </p>
          <button @click="resendCode()" :disabled="!canResend" x-show="canResend"
                  class="otp-resend font-extrabold text-brand-primary hover:underline disabled:text-gray-400 disabled:cursor-not-allowed">
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
@endsection
