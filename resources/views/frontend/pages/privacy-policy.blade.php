@extends('layouts.app')

@section('title', __('pages.privacy_title'))

@push('styles')
<style>
  .tofof-bg {
    position: relative;
    overflow: hidden;
    background-color: #ffffff;
  }
  .content-wrapper { position: relative; z-index: 1; }

  .brand-primary { color: #6d0e16; }
  .brand-accent  { color: #cd8985; }
  .brand-muted   { color: #6b6b6b; }
  .brand-card    { 
    background: #f7f7f7; 
    box-shadow: 
      0 20px 50px rgba(0,0,0,0.2), 
      0 10px 20px rgba(0,0,0,0.1),
      inset 0 1px 1px rgba(255,255,255,0.8);
    border: 1px solid #e0e0e0;
  }
  .brand-border  { border-color: #000000; }

  .link-brand {
    background-color: #6d0e16;
    color: #f7f7f7 !important;
    padding: 0.2rem 0.6rem;
    border-radius: 0.4rem;
    text-decoration: none;
    transition: all .2s ease;
    display: inline-block;
  }
  .link-brand:hover {
    background-color: #8b121c;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(109,14,22,0.3);
  }

  .pretty-list {
    list-style: disc inside;
    padding-inline-start: 0;
    margin: 0;
  }

  .feature {
    background: #fff;
    border: 1px solid #eadbcd;
    border-radius: .75rem;
  }

  .note {
    background: #fdf7f6;
    border: 1px solid #f3d6d3;
    border-radius: .75rem;
  }

  .divider {
    height: 1px;
    background: linear-gradient(to right, transparent, #000000, transparent);
    opacity: 0.2;
  }

  /* ===========================
     دعم الوضع الليلي (مُسَوَّر)
     يطبّق فقط داخل .about-wrap
  ============================ */
  html.dark .about-wrap.tofof-bg { background-color: #0b0f14; }
  html.dark .about-wrap.tofof-bg::before {
    opacity: .12;
    background-image:
      radial-gradient(circle at 15% 50%, #1f2937 2px, transparent 0),
      radial-gradient(circle at 85% 30%, #374151 2px, transparent 0),
      radial-gradient(circle at 25% 90%, #334155 2px, transparent 0),
      radial-gradient(circle at 75% 70%, #475569 2px, transparent 0);
  }

  html.dark .about-wrap .brand-card {
    background: #0f172a !important;
    border: 1px solid #1f2937 !important;
    box-shadow: 0 10px 26px rgba(0,0,0,.25);
  }

  html.dark .about-wrap .brand-primary { color: #f0b0ad !important; }
  html.dark .about-wrap .brand-muted   { color: #cbd5e1 !important; }

  html.dark .about-wrap h1,
  html.dark .about-wrap h2,
  html.dark .about-wrap h3,
  html.dark .about-wrap p,
  html.dark .about-wrap li { color: #e5e7eb; }

  html.dark .about-wrap .feature {
    background: #0f172a !important;
    border-color: #1f2937 !important;
    box-shadow: 0 8px 22px rgba(0,0,0,.22);
  }

  html.dark .about-wrap .note {
    background: #111827 !important;
    border-color: #374151 !important;
  }

  html.dark .about-wrap .divider {
    background: linear-gradient(to right, transparent, #374151, transparent);
  }

  html.dark .about-wrap .link-brand {
    background-color: #4d0a10 !important;
    color: #f7f7f7 !important;
  }
</style>
@endpush

@section('content')
<div class="min-h-screen tofof-bg about-wrap px-4 py-16">
  <div class="content-wrapper max-w-5xl mx-auto brand-card p-10 rounded-lg shadow-md leading-relaxed">

    <!-- العنوان -->
    <h1 class="text-4xl font-bold mb-8 text-center p-4 rounded-lg" style="background-color:#6d0e16; color:#f7f7f7;">{{ __('pages.privacy_title') }}</h1>

    <!-- مقدمة -->
    <p class="text-lg mb-8 rtl:text-right">
      {{ __('pages.privacy_intro') }}
    </p>

    <!-- الأقسام المتعددة -->
    <div class="grid md:grid-cols-2 gap-6 mb-10">
      
      <!-- 1. المعلومات التي نجمعها -->
      <div class="feature p-6">
        <h2 class="text-xl font-bold mb-3">{{ __('pages.privacy_collect_title') }}</h2>
        <div class="divider mb-3" style="height: 2px; opacity: 0.2;"></div>
        <ul class="pretty-list space-y-1">
          <li>{{ __('pages.privacy_collect_1') }}</li>
          <li>{{ __('pages.privacy_collect_2') }}</li>
          <li>{{ __('pages.privacy_collect_3') }}</li>
          <li>{{ __('pages.privacy_collect_4') }}</li>
          <li>{{ __('pages.privacy_collect_5') }}</li>
        </ul>
      </div>

      <!-- 2. لماذا نجمع المعلومات -->
      <div class="feature p-6">
        <h2 class="text-xl font-bold mb-3">{{ __('pages.privacy_why_title') }}</h2>
        <div class="divider mb-3" style="height: 2px; opacity: 0.2;"></div>
        <ul class="pretty-list space-y-1">
          <li>{{ __('pages.privacy_why_1') }}</li>
          <li>{{ __('pages.privacy_why_2') }}</li>
          <li>{{ __('pages.privacy_why_3') }}</li>
        </ul>
      </div>

      <!-- 3. ماذا لا نفعل -->
      <div class="feature p-6">
        <h2 class="text-xl font-bold mb-3">{{ __('pages.privacy_not_title') }}</h2>
        <div class="divider mb-3" style="height: 2px; opacity: 0.2;"></div>
        <ul class="pretty-list space-y-1">
          <li>{{ __('pages.privacy_not_1') }}</li>
          <li>{{ __('pages.privacy_not_2') }}</li>
          <li>{{ __('pages.privacy_not_3') }}</li>
        </ul>
      </div>

      <!-- 4. حماية البيانات -->
      <div class="feature p-6">
        <h2 class="text-xl font-bold mb-3">{{ __('pages.privacy_protect_title') }}</h2>
        <div class="divider mb-3" style="height: 2px; opacity: 0.2;"></div>
        <ul class="pretty-list space-y-1">
          <li>{{ __('pages.privacy_protect_1') }}</li>
          <li>{{ __('pages.privacy_protect_2') }}</li>
          <li>{{ __('pages.privacy_protect_3') }}</li>
          <li>{{ __('pages.privacy_protect_4') }}</li>
        </ul>
      </div>
    </div>

    <!-- حقوق المستخدم -->
    <div class="note p-6 mb-10">
      <h2 class="text-2xl font-semibold mb-3">{{ __('pages.privacy_rights_title') }}</h2>
      <p class="mb-3">{{ __('pages.privacy_rights_intro') }}</p>
      <ul class="pretty-list space-y-2 text-lg rtl:pr-6">
        <li>{{ __('pages.privacy_rights_1') }}</li>
        <li>{{ __('pages.privacy_rights_2') }}</li>
        <li>{{ __('pages.privacy_rights_3') }}</li>
      </ul>
    </div>

    <!-- الخاتمة -->
    <div class="text-center pt-6 border-t" style="border-color: rgba(0,0,0,0.1);">
      <p class="text-xl mb-4">{{ __('pages.privacy_outro') }}</p>
      <p>
        {{ __('pages.privacy_contact_cta') }} 
        <a href="{{ route('page.contact-us') }}" class="link-brand">{{ __('pages.payment_contact_link') }}</a>.
      </p>
    </div>

  </div>
</div>
@endsection
