@extends('layouts.app')

@section('title', __('pages.faq_title'))

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

  .divider {
    height: 1px;
    background: #000000;
    opacity: 0.1;
  }

  /* ===========================
     دعم الوضع الليلي (مُسَوَّر)
     يطبّق فقط داخل .about-wrap
  ============================ */
  html.dark .about-wrap.tofof-bg { background-color: #0b0f14; }
  
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
    <h1 class="text-4xl font-bold mb-8 text-center p-4 rounded-lg" style="background-color:#6d0e16; color:#f7f7f7;">{{ __('pages.faq_title') }}</h1>

    <!-- مقدمة -->
    <p class="text-xl mb-10 text-center">
      {{ __('pages.faq_intro') }}
    </p>

    <!-- قائمة الأسئلة (شبكة) -->
    <div class="grid md:grid-cols-2 gap-6 mb-12">
      
      <div class="feature p-6">
        <h2 class="text-xl font-bold mb-3 brand-primary">{{ __('pages.faq_q1') }}</h2>
        <div class="divider mb-3"></div>
        <p>{{ __('pages.faq_a1') }}</p>
      </div>

      <div class="feature p-6">
        <h2 class="text-xl font-bold mb-3 brand-primary">{{ __('pages.faq_q2') }}</h2>
        <div class="divider mb-3"></div>
        <p>{{ __('pages.faq_a2') }}</p>
      </div>

      <div class="feature p-6">
        <h2 class="text-xl font-bold mb-3 brand-primary">{{ __('pages.faq_q3') }}</h2>
        <div class="divider mb-3"></div>
        <p>{{ __('pages.faq_a3') }}</p>
      </div>

      <div class="feature p-6">
        <h2 class="text-xl font-bold mb-3 brand-primary">{{ __('pages.faq_q4') }}</h2>
        <div class="divider mb-3"></div>
        <p class="mb-3">{{ __('pages.payment_methods_list') }}</p>
        <ul class="pretty-list space-y-1">
          <li>{{ __('pages.payment_options') }}</li>
          <li>{{ __('pages.faq_zaincash') }}</li>
          <li>{{ __('pages.faq_mastercard') }}</li>
          <li>{{ __('pages.faq_prepaid') }}</li>
        </ul>
      </div>

    </div>

    <!-- الخاتمة -->
    <div class="text-center pt-8 border-t" style="border-color: rgba(0,0,0,0.1);">
      <p class="text-lg">{{ __('pages.faq_contact_cta') }}</p>
      <div class="mt-4">
        <a href="{{ route('page.contact-us') }}" class="link-brand">{{ __('pages.faq_contact_link') }}</a>
      </div>
    </div>

  </div>
</div>
@endsection
