@extends('layouts.app')

@section('title', 'عن طفوف – من نحن')

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

  /* ===========================
     دعم الوضع الليلي (مُسَوَّر)
     يطبّق فقط داخل .about-wrap
  ============================ */
  html.dark .about-wrap.tofof-bg { background-color: #0b0f14; }
  html.dark .about-wrap.tofof-bg::before {
    opacity: .12;
    /* نفس النمط لكن أغمق */
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
  html.dark .about-wrap .brand-accent  { color: #e8c2c0 !important; }
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
    <h1 class="text-4xl font-bold mb-8 text-center p-4 rounded-lg" style="background-color:#6d0e16; color:#f7f7f7;">من نحن</h1>

    <!-- المقدمة -->
    <p class="text-lg brand-muted mb-6 rtl:text-right">
      متجر عراقي متخصص بالساعات الاصلية والنادرة والفاخرة – ساعات ، محافظ، ونظارات شمسية أصلية 100% من أشهر الماركات العالمية، وبضمان رسمي موثوق.
    </p>

    <!-- الرؤية -->
    <div class="mb-8">
      <h2 class="text-2xl font-semibold mb-3">انطلقنا برؤية واضحة</h2>
      <div class="divider mb-4" style="height: 2px; opacity: 0.2;"></div>
      <p class="text-lg">
      أن الساعة ليست مجرّد قطعة … بل تعبير كامل عن الشخصية يبدأ من الثقة وينتهي بالرضا الحقيقي.
      </p>
    </div>

    <!-- ليش طفوف؟ -->
    <div class="mb-10">
      <h2 class="text-2xl font-semibold mb-4">ليش طفوف أفضل اختيار؟</h2>

      <div class="grid md:grid-cols-2 gap-4">
        <div class="feature p-5">
          <h3 class="text-xl font-semibold mb-2">ساعات أصلية 100% ومضمونة</h3>
          <p>
            نوفّر الساعات من مصادرها الرسمية فقط، مع ضمان شامل للجودة.
          </p>
        </div>

        <div class="feature p-5">
          <h3 class="text-xl font-semibold mb-2">معلومات وتفاصيل المنتج مع كل طلب</h3>
          <p>
            كل منتج يحتوى على كامل المرفقات و دليل مفصل عن الماركة والمواصفات حتى تضمن أفضل اختيار.
          </p>
        </div>

        <div class="feature p-5 md:col-span-2">
          <h3 class="text-xl font-semibold mb-2">توصيل سريع خلال 24 ساعة</h3>
          <p>
            توصيل لكل محافظات العراق خلال يوم واحد فقط، مع خيارات دفع مرنة.
          </p>
        </div>
      </div>
    </div>

    <!-- طرق الطلب -->
    <div class="note p-6 mb-10">
      <h2 class="text-2xl font-semibold mb-3">عدة طرق للطلب</h2>
      <ul class="pretty-list space-y-2 text-lg rtl:pr-6">
        <li>من خلال المتجر.</li>
        <li>من خلال صفحات التواصل الاجتماعي.</li>
        <li>
          عبر الرقم المباشر:
          <a href="https://wa.me/9647757778099" class="link-brand">واتساب مباشر</a>
        </li>
      </ul>
    </div>

    <!-- الخاتمة -->
    <p class="text-xl text-center">
      مع <span class="font-bold" style="color:#6d0e16;">Tofof</span>… الستايل صار أسهل، أذكى، وأكثر ثقة.
    </p>

  </div>
</div>
@endsection
