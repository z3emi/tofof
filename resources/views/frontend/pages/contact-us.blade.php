@extends('layouts.app')

@section('title', 'اتصل بنا – طفوف')

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
    background: #000000;
    opacity: 0.1;
  }

  /* --- دعم الوضع الليلي --- */
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
  html.dark .about-wrap h4,
  html.dark .about-wrap p,
  html.dark .about-wrap li,
  html.dark .about-wrap label { color: #e5e7eb !important; }
  
  html.dark .about-wrap .feature {
    background: #0f172a !important;
    border-color: #1f2937 !important;
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

  /* تخصيص للنموذج في الوضع الليلي */
  html.dark .about-wrap .form-input {
      background-color: #1e293b;
      border-color: #334155;
      color: #e5e7eb;
  }
  html.dark .about-wrap .form-input:focus {
      border-color: #f0b0ad;
      ring-color: #f0b0ad;
      background-color: #0f172a;
  }
  html.dark .about-wrap .form-label {
      color: #cbd5e1;
  }
  html.dark .about-wrap .form-button {
      background-color: #4d0a10 !important;
      color: #f7f7f7 !important;
  }
</style>
@endpush

@section('content')
<div class="min-h-screen tofof-bg about-wrap px-4 py-16" dir="rtl">
  <div class="content-wrapper max-w-5xl mx-auto brand-card p-6 md:p-10 rounded-lg shadow-md leading-relaxed">

    <!-- العنوان -->
    <h1 class="text-4xl font-bold mb-8 text-center p-4 rounded-lg" style="background-color:#6d0e16; color:#f7f7f7;">اتصل بنا</h1>

    {{-- رسائل النجاح --}}
    @if (session('success'))
        <div class="max-w-2xl mx-auto mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-md text-center text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- رسائل الأخطاء --}}
    @if ($errors->any())
        <div class="max-w-2xl mx-auto mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-md text-sm">
            <p class="font-semibold mb-1">يرجى تصحيح الأخطاء التالية:</p>
            <ul class="list-disc list-inside text-right">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <p class="text-xl mb-10 text-center max-w-2xl mx-auto">
      إذا عندك أي سؤال، استفسار، أو تحتاج مساعدة، فريق طفوف دايمًا قريب منك 📬
    </p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
        
        <div class="feature p-6">
            <h2 class="text-2xl font-bold mb-4 brand-primary">📬 طرق التواصل الرسمية</h2>
            <div class="divider mb-4"></div>
            <ul class="space-y-4">
                
                <li class="flex items-center">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp" class="w-6 h-6 ml-3">
                    <a href="https://wa.me/9647744969024" target="_blank" class="link-brand">
                        راسـلـنـا واتـسـاب
                    </a>
                </li>
                
                <li class="flex items-center">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/a/a5/Instagram_icon.png" alt="Instagram" class="w-6 h-6 ml-3">
                    <a href="https://www.instagram.com/tofof_watches" target="_blank" class="link-brand">
                        تـابـعـنـا إنـسـتـغـرام
                    </a>
                </li>
                
                <li class="flex items-center">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/5/51/Facebook_f_logo_%282019%29.svg" alt="Facebook" class="w-6 h-6 ml-3">
                    <a href="https://www.facebook.com/p/%D8%B7%D9%81%D9%88%D9%81-%D9%84%D9%84%D8%B3%D8%A7%D8%B9%D8%A7%D8%AA-100091444293851/" target="_blank" class="link-brand">
                        صـفـحـة فـيـسـبـوك
                    </a>
                </li>
            </ul>
        </div>

        <div class="feature p-6">
            <h2 class="text-2xl font-bold mb-4 brand-primary">🕒 ساعات الدعم</h2>
            <div class="divider mb-4"></div>
            <div class="flex items-start">
                <div class="p-3 bg-red-50 rounded-full ml-4">
                  <svg class="w-8 h-8 brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-1">الرد على الرسائل</h3>
                    <p class="text-lg">متوفرين 24/7</p>
                    <p class="text-sm opacity-70">(يشمل أيام العطل الرسمية)</p>
                </div>
            </div>
        </div>
    </div>


    <div class="mt-10 text-center border-t pt-8" style="border-color: rgba(0,0,0,0.1);">
        <p class="text-lg opacity-80">
            💡 <strong>ملاحظة:</strong> دائمًا نحب نسمع منك، سواء كان اقتراح أو ملاحظة – كل مداخلة منك تساعدنا نكون أفضل 🤍
        </p>
    </div>

  </div>
</div>
@endsection