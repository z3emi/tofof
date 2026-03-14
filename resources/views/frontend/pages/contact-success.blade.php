@extends('layouts.app')

@section('title', 'تم استلام رسالتك – طفوف')

@push('styles')
<style>
  .tofof-bg {
      position: relative;
      overflow: hidden;
      background-color: #f9f5f1;
  }
  .tofof-bg::before {
      content: '';
      position: absolute; inset: 0;
      background-image:
        radial-gradient(circle at 15% 50%, #eadbcd 2px, transparent 0),
        radial-gradient(circle at 85% 30%, #dcaca9 2px, transparent 0),
        radial-gradient(circle at 25% 90%, #cd8985 2px, transparent 0),
        radial-gradient(circle at 75% 70%, #be6661 2px, transparent 0);
      background-size: 50px 50px;
      opacity: 0.18;
      z-index: 0;
  }
  .content-wrapper { position: relative; z-index: 1; }

  .brand-primary { color: #be6661; }
  .brand-muted   { color: #6b6b6b; }

  html.dark .tofof-bg { background-color: #0b0f14; }
  html.dark .tofof-bg::before {
      opacity: .12;
      background-image:
        radial-gradient(circle at 15% 50%, #1f2937 2px, transparent 0),
        radial-gradient(circle at 85% 30%, #374151 2px, transparent 0),
        radial-gradient(circle at 25% 90%, #334155 2px, transparent 0),
        radial-gradient(circle at 75% 70%, #475569 2px, transparent 0);
  }
  html.dark .tofof-bg .card {
      background: #0f172a;
      border-color: #1f2937;
      color: #e5e7eb;
  }
</style>
@endpush

@section('content')
<div class="min-h-screen tofof-bg px-4 py-16" dir="rtl">
    <div class="content-wrapper max-w-xl mx-auto card bg-white border border-[#eadbcd] rounded-lg shadow-md p-8 text-center">

        <h1 class="text-3xl md:text-4xl font-bold brand-primary mb-4">
            تم استلام رسالتك 🤍
        </h1>

        <p class="text-lg brand-muted mb-6">
            شكرًا لأنك تواصلت ويانا 💌  
            استلمنا رسالتك بنجاح، وراح يراجعها فريق الدعم ويتواصل معك خلال أقرب وقت ممكن.
        </p>

        @if (session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-md text-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex flex-col sm:flex-row gap-3 justify-center mt-4">
            <a href="{{ route('homepage') }}"
               class="inline-flex justify-center px-6 py-2 rounded-md text-white text-base font-medium"
               style="background-color:#be6661;">
                العودة إلى الرئيسية
            </a>

            <a href="{{ route('page.contact-us') }}"
               class="inline-flex justify-center px-6 py-2 rounded-md border text-base font-medium"
               style="border-color:#be6661; color:#be6661;">
                إرسال رسالة جديدة
            </a>
        </div>
    </div>
</div>
@endsection
