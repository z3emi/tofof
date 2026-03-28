@php
    // ترتيب الأقسام تنازلياً حسب عدد المنتجات
    $sortedCategories = $categories;

    try {
        if ($categories instanceof \Illuminate\Support\Collection) {
            $sortedCategories = $categories
                ->sortByDesc(function ($cat) {
                    // يفضّل استخدام withCount('products') في الكنترولر
                    // هنا نتعامل مرنًا:
                    if (isset($cat->products_count)) {
                        return (int) $cat->products_count;
                    }
                    return method_exists($cat, 'products') ? (int) $cat->products()->count() : 0;
                })
                ->values();
        }
    } catch (\Throwable $e) {
        // إبقاء الترتيب كما هو إذا حدث خطأ
    }
@endphp
@php
    // This defines the variable as an empty array if it doesn't exist, preventing errors.
    $favoriteProductIds = $favoriteProductIds ?? [];
@endphp
@php
    // Convert hex to rgb for rgba
    if (!function_exists('hexToRgb')) {
        function hexToRgb($hex) {
            $hex = str_replace("#", "", $hex);
            if(strlen($hex) == 3) {
                $r = hexdec(substr($hex,0,1).substr($hex,0,1));
                $g = hexdec(substr($hex,1,1).substr($hex,1,1));
                $b = hexdec(substr($hex,2,1).substr($hex,2,1));
            } else {
                $r = hexdec(substr($hex,0,2));
                $g = hexdec(substr($hex,2,2));
                $b = hexdec(substr($hex,4,2));
            }
            return "$r, $g, $b";
        }
    }
@endphp

@extends('layouts.app')

@push('styles')
<style>
    /* =========================
        THEME VARIABLES (scoped)
    ========================= */
    .home-scope {
        /* Brand palette */
        --primary-color: #c32126;
        --primary-hover: #a61c20;
        --secondary-color: #ffffff;
        --accent-color: #ea7a7e;

        /* Base */
        --bg: #ffffff;
        --bg-soft: #ffffff;
        --surface: #ffffff;
        --card-bg: #ffffff;
        --text: #111111;
        --text-soft: #333333;
        --muted: #666666;
        --border: #e5e5e5;

        /* Badges */
        --new-badge-color: #4CAF50;
        --bestseller-badge-color: #FF9800;
        --sale-badge-color: #E53935;

        /* Hero gradient */
        --hero-start: rgba(255, 255, 255, 0.8);
        --hero-end: rgba(247, 247, 247, 0.8);

        /* Section gradient */
        --cat-grad-from: #ffffff;
        --cat-grad-to: #ffffff;

        /* Slider overlay (now per-slide) */
    }

    /* =========================
        DARK THEME OVERRIDES (scoped)
    ========================= */
    html.dark .home-scope {
        --bg: #0a0a0a;
        --bg-soft: #121212;
        --surface: #111111;
        --card-bg: #111111;
        --text: #ffffff;
        --text-soft: #e5e5e5;
        --muted: #bfbfbf;
        --border: #262626;
        --hero-start: rgba(10, 10, 10, 0.72);
        --hero-end: rgba(18, 18, 18, 0.72);
        --cat-grad-from: #0a0a0a;
        --cat-grad-to: #121212;
        /* Dark overrides for sliders now per-slide */
        --secondary-color: #1a1a1a;
    }

    /* =========================
        GLOBAL (scoped)
    ========================= */
    .home-scope {
        background: var(--bg);
        color: var(--text);
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    /* Overrides (scoped) */
    html.dark .home-scope .bg-white { background-color: var(--surface) !important; }
    html.dark .home-scope .bg-gray-50 { background-color: var(--bg-soft) !important; }
    html.dark .home-scope .text-[#333] { color: var(--text) !important; }
    html.dark .home-scope .text-gray-700 { color: var(--text) !important; }
    html.dark .home-scope .text-gray-500 { color: var(--muted) !important; }

    /* ---------- Hero Styles (reused in slider) ---------- */
    .home-scope .hero-title{ font-size:3.5rem; font-weight:800; color:var(--text); line-height:1.2; margin-bottom:1.5rem; transition: color 0.3s ease; }
    .home-scope .hero-title span{ background:linear-gradient(135deg, var(--primary-color), var(--primary-hover)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
    @media (max-width:1024px){ .home-scope .hero-title{ font-size:2.5rem; } }
    .home-scope .hero-subtitle{ font-size:1.25rem; color:var(--text-soft); margin-bottom:2rem; opacity:.9; line-height:1.6; transition: color 0.3s ease; }
    .home-scope .btn-hero-primary{ background:linear-gradient(135deg, var(--primary-color), var(--primary-hover)); color:#fff; padding:.6rem 1.7rem; border-radius:50px; font-weight:600; font-size:0.95rem; text-decoration:none; display:inline-block; transition:.3s; box-shadow:0 4px 15px rgba(205,137,133,.3); }
    .home-scope .btn-hero-primary:hover{ transform:translateY(-3px); box-shadow:0 8px 25px rgba(205,137,133,.4); }

    /* ---------- NEW HEADER SLIDER STYLES ---------- */
    .header-slider-overlay {
        position: absolute;
        inset: 0;
        z-index: 1;
    }
    .header-slider-content {
        position: relative;
        z-index: 2;
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 1rem 2rem 4rem 2rem; /* Increased bottom padding to push content UP */
        color: #fff;
        user-select: none; /* NEW */
    }
    .header-slider-content .hero-title,
    .header-slider-content .hero-subtitle {
        color: #fff;
        text-shadow: 0 2px 8px rgba(0,0,0,0.5);
    }

    /* MOBILE FIX: Reduce spacing and font sizes on mobile for the slider */
    @media (max-width: 640px) {
        .header-slider-content {
            padding: 1rem 0.5rem 1.2rem 0.5rem; /* Reduced from 2.5rem bottom and 1rem sides */
        }
        .header-slider-content .hero-title {
            font-size: 1.5rem; /* Reduced from 2rem to fit the smaller height */
            line-height: 1.25;
            margin-bottom: 0.5rem; /* Reduced margin */
        }
        .header-slider-content .hero-subtitle {
            font-size: 0.9rem; /* Smaller subtitle */
            margin-bottom: 1rem; /* Reduced margin */
            line-height: 1.5;
        }
        .header-slider-content .btn-hero-primary {
            font-size: 0.75rem;
            padding: 0.4rem 1.1rem;
        }
    }

    /* ---------- Promo Slider (Generalized for all sliders) ---------- */
    .home-scope .promo-slider{ position:relative; overflow:hidden; border-radius:16px; margin: 0.75rem auto; background:var(--surface); transition: background 0.3s ease; }
    @media (min-width: 1024px) {
        .home-scope .promo-slider {
            max-width: 1300px; /* تم تكبير عرض السلايدر في الكمبيوتر ليكون أوسع وأبرز */
        }
    }
    .home-scope .slider-wrapper{ position:relative; width:100%; height:100%; }
    .home-scope .slider-container{ display:flex; transition:transform .5s ease; height:100%; }
    .home-scope .slide{
        position:relative;
        min-width:100%;
        display:flex;
        align-items:center;
        overflow:hidden;
        height: auto; /* الارتفاع سيتبع الصورة تلقائياً لتكون 'ذكية' */
        min-height: 80px; 
        max-height: 480px;
    }
    /* توحيد نسبة العرض إلى الارتفاع للسلايدرات الصغيرة أيضاً */
    .promo-slider.shrinked .slide {
        /* Keep secondary promo sliders visually compact across viewports */
        aspect-ratio: auto;
        min-height: clamp(92px, 18vw, 170px);
        height: clamp(92px, 18vw, 170px);
        max-height: 170px;
    }

    /* ستايل خاص بالسلايدر الرئيسي (Hero) لتوحيد الأبعاد في الهاتف والكمبيوتر بنفس النسبة */
    .hero-slider .slide {
        width: 100%;
        aspect-ratio: 1920 / 700;
        position: relative;
        overflow: hidden;
    }
    /* ستايل خاص بالسلايدرات الترويجية (أقل ارتفاعاً وأصغر عرضاً) */
    .promo-primary-slider .slide {
        width: 100%;
        aspect-ratio: 1920 / 540;
        position: relative;
        overflow: hidden;
    }
    .hero-slider .slide-bg, .promo-primary-slider .slide-bg {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    @media (max-width: 768px) {
        .hero-slider .slide {
            aspect-ratio: 1920 / 700;
        }
        .promo-primary-slider .slide {
            aspect-ratio: 1920 / 540;
        }
    }
    .home-scope .slide-bg{ 
        position:relative; /* جعل الصورة هي المحرك للارتفاع */
        width:100%; 
        height:auto; 
        object-fit: contain; /* لضمان ظهور الصورة كاملة */
        z-index:0; 
        display: block;
    }
    html.dark .home-scope .slide-bg{ opacity: 0.8; }
    .home-scope .slide-overlay{ position:absolute; inset:0; z-index:1; }
    .home-scope .slide-content{
        position:absolute; inset:0; z-index:2; color:#fff; height:100%; width:100%;
        display:flex; flex-direction:column; justify-content:center; align-items:center !important; text-align:center !important;
        padding: 0.5rem;
        user-select: none;
    }
        html[dir="rtl"] .home-scope .slide-content{ align-items:center !important; padding: 1.25rem 0 1.25rem 0 !important; text-align:center !important; }
        /* اجبار محاذاة النص والزر في السلايدر للوسط دائماً */
        /* تعميم محاذاة المنتصف على جميع السلايدرات */
        .slide-content, .slider-content, .slider-section .slide-content, .slider-wrapper .slide-content, .home-scope .slide-content {
            display: flex !important;
            flex-direction: column !important;
            justify-content: center !important;
            align-items: center !important;
            text-align: center !important;
        }
        .slide-content > *, .slider-content > *, .slider-section .slide-content > *, .slider-wrapper .slide-content > *, .home-scope .slide-content > * {
            text-align: center !important;
            align-self: center !important;
            max-width: 100% !important;
        }
        /* تصغير نصوص وأزرار السلايدر لتناسب الارتفاع الأقل */
        .home-scope .slide-content h2 {
            font-size: clamp(1rem, 1.8vw, 1.9rem) !important;
            line-height: 1.25;
            margin-bottom: 0.4rem !important;
        }
        .home-scope .slide-content p {
            font-size: clamp(0.78rem, 1.1vw, 1rem) !important;
            line-height: 1.45;
            margin-bottom: 0.6rem !important;
        }
        .home-scope .slide-content a {
            font-size: clamp(0.72rem, 0.95vw, 0.9rem) !important;
            padding: 0.35rem 0.95rem !important;
            border-radius: 999px;
        }
        .btn-hero-primary, .btn-primary {
            display: inline-flex !important;
            justify-content: center !important;
            align-items: center !important;
            margin-left: auto !important;
            margin-right: auto !important;
        }
    @media (max-width: 768px) {
        .home-scope .slide-content h2 {
            font-size: clamp(0.9rem, 4.5vw, 1.2rem) !important;
        }
        .home-scope .slide-content p {
            font-size: clamp(0.7rem, 3.2vw, 0.85rem) !important;
            margin-bottom: 0.45rem !important;
        }
        .home-scope .slide-content a {
            font-size: clamp(0.66rem, 2.8vw, 0.78rem) !important;
            padding: 0.28rem 0.75rem !important;
        }
        .home-scope .slide-content { padding: 1rem; align-items: center !important; text-align: center !important; }

        .promo-slider.shrinked .slide {
            height: auto;
            min-height: 60px;
            max-height: 140px;
        }
    }

    .home-scope .slider-dots{ position:absolute; bottom:15px; left:50%; transform:translateX(-50%); display:flex; gap:8px; z-index:3; }
    .home-scope .dot{ width:10px; height:10px; border-radius:50%; background:rgba(255,255,255,.5); cursor:pointer; transition:.3s; }
    .home-scope .dot.active{ background:#fff; transform:scale(1.2); }
    .home-scope .slider-nav{ position:absolute; top:50%; transform:translateY(-50%); z-index:3; background:rgba(255,255,255,.2); color:#fff; border:none; width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:.3s; font-size:1.2rem; }
    .home-scope .slider-nav:hover{ background:rgba(255,255,255,.3); }
    .home-scope .slider-prev{ left:10px; } .home-scope .slider-next{ right:10px; }

    /* ---------- Sections ---------- */
    .home-scope .section-header h2{ color: var(--text); transition: color 0.3s ease; }
    .home-scope .section-cats { background: linear-gradient(to bottom right, var(--cat-grad-from), var(--cat-grad-to)); position:relative; overflow:hidden; transition: background 0.3s ease; }
    
    /* FIX: Category names color */
    .home-scope .category-name {
        color: var(--text); /* Use theme's main text color */
        transition: color 0.3s ease;
    }

    /* ---------- Product Card (old styles kept) ---------- */
    /* شارة نسبة الخصم للمنتجات (نفس فكرة المفضلة) */
    .home-scope .product-sale-badge{
        position:absolute;
        top:.6rem;
        right:.6rem;
        z-index:15;
        background:#c32126;      /* لون البراند */
        color:#fff;
        font-weight:800;
        font-size:.75rem;
        padding:.35rem .6rem;
        border-radius:999px;
        box-shadow:0 6px 14px rgba(0,0,0,.18);
    }

    .home-scope .product-card{
        transition:.3s; box-shadow:0 4px 12px rgba(0,0,0,.05); border-radius:12px; background:var(--card-bg);
        overflow:hidden; border:2px solid transparent; position:relative; display:flex; flex-direction:column;
        transition: background 0.3s ease, box-shadow 0.3s ease, transform 0.3s ease;
    }
    .home-scope .product-card:hover{ box-shadow:0 15px 30px rgba(0,0,0,.12); transform:translateY(-8px); }

    .home-scope .product-image-container{
    aspect-ratio:1/1;
    overflow:hidden;
    position:relative;
    border-radius:12px 12px 0 0;
}

/* إلغاء منطق 200% + 50% واستعمال طبقات متراكبة */
.home-scope .product-image-slider{
    position: relative;
    width: 100%;
    height: 100%;
}

/* كل صورة تغطي الكارد كامل وتتحرك بس بالـ transform */
.home-scope .product-image-slider img{
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.35s ease;
}
    .home-scope .product-dots { position: absolute; bottom: 8px; left: 50%; transform: translateX(-50%); display: flex; gap: 6px; z-index: 10; }
    .home-scope .product-dot { width: 8px; height: 8px; background-color: rgba(255, 255, 255, 0.6); border-radius: 50%; transition: background-color 0.3s ease, transform 0.3s ease; }
    .home-scope .product-dot.active { background-color: var(--primary-color); transform: scale(1.2); }

    .home-scope .products-grid{ display:grid; grid-template-columns:repeat(6,minmax(0,1fr)); gap:1.5rem; }
    @media (max-width:1280px){ .home-scope .products-grid{ grid-template-columns:repeat(5,1fr); gap:1.2rem; } }
    @media (max-width:1024px){ .home-scope .products-grid{ grid-template-columns:repeat(3,1fr); gap:1rem; } }
    @media (max-width:640px){  .home-scope .products-grid{ grid-template-columns:repeat(2,1fr); gap:1rem; } }

    /* ---------- Helpers ---------- */
    .home-scope .no-scrollbar::-webkit-scrollbar{ display:none; }
    .home-scope .no-scrollbar{ -ms-overflow-style:none; scrollbar-width:none; }
    .home-scope .floating-element{ position:absolute; border-radius:50%; background:linear-gradient(135deg, var(--primary-color), var(--accent-color)); opacity:.08; z-index:0; animation:float 6s ease-in-out infinite; transition: opacity 0.3s ease; }
    html.dark .home-scope .floating-element { opacity: 0.04; }
    @keyframes float{ 0%{ transform:translateY(0); } 50%{ transform:translateY(-20px);} 100%{ transform:translateY(0); } }
    .home-scope .floating-1{ width:80px;height:80px; top:10%; right:5%; animation-delay:0s; }
    .home-scope .floating-2{ width:60px;height:60px; bottom:15%; left:8%; animation-delay:1s; }
    .home-scope .floating-3{ width:40px;height:40px; top:40%; right:15%; animation-delay:2s; }

    /* =========================
        SHOP PRODUCT CARD OVERRIDES (make home = shop)
        ========================= */
    .home-scope .product-card { background:#fff; border-radius:14px; border:2px solid transparent; box-shadow:0 4px 12px rgba(0,0,0,.05); transform:translateY(0); }
    .home-scope .product-card:hover { transform:translateY(-6px); box-shadow:0 16px 30px rgba(0,0,0,.10); }
    .home-scope .product-content-link { display:flex; flex-direction:column; flex-grow:1; text-decoration:none; color:inherit; }
    .home-scope .product-info { padding:12px; display:flex; flex-direction:column; gap:8px; text-align:center; flex-grow:1; }
    .home-scope .product-title { font-weight:700; color:#2d2a2a; line-height:1.35; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; min-height:2.6em; }
    .home-scope .price { color:#000; font-weight:800; font-size:1rem; }
    html.dark .home-scope .price { color: #fff !important; }
    .home-scope .old { text-decoration:line-through; color:#e53935; font-size:.85rem; }
    .home-scope .product-actions { display:flex; gap:8px; margin-top:auto; padding: 0 10px 10px; position:relative; z-index:2; }
    .home-scope .btn-primary { background:#6d0e16 !important; color:#fff; border-radius:10px; font-weight:700; transition:.2s; }
    .home-scope .btn-primary:hover{ background:#500a10 !important; }
    .home-scope .product-actions .btn-primary { flex-grow:1; flex-shrink:1; min-width:0; overflow:hidden; height:44px; display:inline-flex; align-items:center; justify-content:center; padding:0 .75rem; white-space:nowrap; font-size:.9rem; }
    @media (max-width:390px) { .home-scope .product-actions .btn-primary { font-size:.75rem; padding:0 .4rem; } }
    .home-scope .product-actions .btn-primary:only-child { flex-grow:unset; width:100%; }
    .home-scope .btn-fav { width:44px; height:44px; border-radius:999px; background-color:#e5e7eb; color:#4b5563; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0; transition:.2s; font-size:1.1rem; border:none; cursor:pointer; }
    .home-scope .btn-fav:hover { background-color:#d1d5db; }
    .home-scope .btn-fav.favorited { background-color:#fee2e2; color:#ef4444; }
    .home-scope .no-products-message { background-color:#fff; border:1px solid #f3f4f6; }

    /* دارك مود مثل المتجر (scoped) */
    html.dark .home-scope .product-card { background-color:#1f2937; }
    html.dark .home-scope .product-title { color:#f9fafb; }
    html.dark .home-scope .btn-fav { background-color:#374151; color:#9ca3af; }
    html.dark .home-scope .btn-fav:hover { background-color:#4b5563; }
    html.dark .home-scope .btn-fav.favorited { background-color:rgba(205,137,133,.2); color:#f9a8d4; }
</style>
<style>
  /* زر "منتهي الكمية" — غيّر لون الخلفية فقط */
  .home-scope .product-card .btn-primary[disabled],
  .home-scope .product-card .btn-primary:disabled {
    background-color: #9CA3AF !important;
    border-color: #9CA3AF !important;
  }
  .home-scope .product-card .btn-primary[disabled]:hover,
  .home-scope .product-card .btn-primary:disabled:hover {
    background-color: #9CA3AF !important;
    border-color: #9CA3AF !important;
  }
</style>

@endpush
@push('styles')
<style>
  /* Removed hardcoded brand overlay to respect admin settings */

  /* =========================
     2) dots – ستايل زجاجي موحّد لكل السلايدرات
     ========================= */
  .slider-dots{
    background: rgba(15, 23, 42, .35) !important; /* زجاجي خفيف */
    border: 1px solid rgba(255,255,255,.08);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    padding: 6px 10px !important;
    border-radius: 999px;
    box-shadow: 0 6px 18px rgba(0,0,0,.18);
    gap: 10px !important;
  }
  html.dark .slider-dots{
    background: rgba(0,0,0,.45) !important;
    border-color: rgba(255,255,255,.06);
  }

  /* نقطة خاملة */
  .slider-dots .dot{
    width: 10px !important;
    height: 10px !important;
    border-radius: 999px !important;
    background: rgba(255,255,255,.55) !important;
    border: 1px solid rgba(255,255,255,.35);
    transition: width .22s ease, background-color .22s ease, transform .22s ease, opacity .22s ease;
    box-shadow: inset 0 0 0 1px rgba(255,255,255,.12);
    opacity: .85;
  }

  /* نقطة مفعّلة – بيضاوية ناعمة */
  .slider-dots .dot.active{
    width: 24px !important; /* يعطيها شكل كبسولة */
    background: #ffffff !important;
    border-color: rgba(255,255,255,.65);
    opacity: 1;
  }

  /* لمسة تفاعل بسيطة */
  .slider-dots .dot:hover{ transform: translateY(-1px); }

  /* =========================
     3) موبايل – أصغر/أخف وما تتداخل مع النص
     ========================= */
  @media (max-width: 640px){
        .slider-dots{
            bottom: 10px !important;
            left: 10px !important;
            right: auto !important;
            top: auto !important;
            padding: 4px 3px !important;
            gap: 4px !important;
            background: rgba(0,0,0,0.18) !important;
            box-shadow: none !important;
            border-radius: 999px !important;
            min-width: auto;
            max-width: none;
            width: auto;
            height: auto;
            transform: none !important;
            opacity: .96;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        .slider-dots .dot{
            width: 3.5px !important;
            height: 3.5px !important;
            border-radius: 999px !important;
            background: #fff !important;
            opacity: 0.55;
            margin: 0 !important;
            border: none !important;
            box-shadow: none !important;
            transition: transform 0.2s, background 0.2s, opacity 0.2s;
        }
        .slider-dots .dot.active{
            width: 5px !important;
            height: 5px !important;
            background: #6d0e16 !important;
            opacity: 1;
            transform: scale(1.05);
        }
  }

  /* اختياري: لو بعده يتقاطع مع الأزرار بالموبايل، زيد المسافة للأسفل */
  /* @media (max-width: 640px){
      .slider-dots{ bottom: 6px !important; }
    } */

  /* =========================
     4) اتّساق النقاط داخل بطاقات المنتجات (لو حاب)
     ========================= */
  .product-dots .product-dot{
    width: 6px; height: 6px; opacity:.8;
    transition: width .22s ease, opacity .22s ease;
  }
  .product-dots .product-dot.active{
    width: 12px; opacity:1; background: var(--primary-color);
  }
  @media (max-width: 640px){
    .product-dots{ transform: scale(.9); }
  }
  /* ===== توحيد تباعد وأحجام الفئات ===== */
.section-cats .category-name {
  text-align: center;
  white-space: normal;      /* يخلي النص يلف سطر ثاني */
  word-break: break-word;   /* لو كلمة طويلة تنكسر */
  line-height: 1.3;
  max-width: 110px;         /* نفس عرض الدائرة */
  min-height: 2.6em;        /* يضمن مساحة لسطرين */
  display: flex;
  align-items: center;
  justify-content: center;
  text-wrap: balance;
}

/* الأيقونات/الدائرة تبقى بنفس الحجم */
.section-cats .flex.flex-col.items-center {
  min-width: 110px;
}
/* ====== فئات المتجر: نفس ارتفاع + الأيقونة تبقى دائرية ====== */

/* ====== تعديل: منع السكول الداخلي ====== */
.section-cats .flex.flex-col.items-center {
  width: 140px;
  min-width: 140px;
  min-height: auto;
  height: auto;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start;
}
@media (max-width: 640px) {
  .section-cats .flex.flex-col.items-center {
    width: auto !important;
    min-width: 65px !important;
    min-height: auto !important;
  }
}


/* الأيقونة تبقى دائرية وتصغر في الجوال */
.section-cats .w-28.h-28 {
  width: 7rem !important;
  height: 7rem !important;
  border-radius: 50%;
}
@media (max-width: 640px) {
  .section-cats .w-28.h-28 {
    width: 4.2rem !important; /* أصغر للجوال */
    height: 4.2rem !important;
  }
}

/* العنوان يلتف ويأخذ سطرين بدون قص ويصغر في الجوال */
.section-cats .category-name {
  max-width: 100%;
  margin-top: .75rem;
  text-align: center;
  white-space: normal;
  word-break: break-word;
  line-height: 1.4;
  min-height: 2.8em;
}
@media (max-width: 640px) {
  .section-cats .category-name {
    font-size: 0.75rem !important;
    min-height: 2.2em;
    margin-top: 0.5rem;
  }
}
/* منع أي scroll عمودي داخل صندوق الفئات */
.section-cats .overflow-x-auto {
  height: auto !important;      /* خليه يتحدد تلقائي */
  overflow-y: visible !important;/* يلغي أي سحب عمودي */
}

/* الأسهم النابضة التجميلية */
@keyframes pulseSide {
  0% { transform: translateY(-50%) scale(1); opacity: 0.4; }
  50% { transform: translateY(-50%) scale(1.2); opacity: 0.8; }
  100% { transform: translateY(-50%) scale(1); opacity: 0.4; }
}
.pulse-arrow {
  position: absolute;
  top: 40%; /* مستوى الصورة تقريباً */
  z-index: 20;
  pointer-events: none;
  color: var(--primary-color);
  font-size: 1.2rem;
  animation: pulseSide 2s infinite ease-in-out;
  display: none; /* مخفي افتراضياً */
}
@media (max-width: 640px) {
  .pulse-arrow { display: block; }
}
.pulse-arrow-left { left: 5px; }
.pulse-arrow-right { right: 5px; }

/* تعطيل snap للشريط المتحرك تلقائيا حتى لا يثبت مكانه */
.section-cats .overflow-x-auto.js-auto-bounce {
    scroll-snap-type: none !important;
    scroll-behavior: auto !important;
}
.section-cats .overflow-x-auto.js-auto-bounce .flex > a {
    scroll-snap-align: none !important;
}
.section-cats .auto-bounce-track {
    will-change: transform;
}

</style>
@endpush

@section('content')
<div class="home-scope">

{{-- Header Image Slider --}}
{{-- Header Image Slider (Matching Second Slider Style) --}}
@if(($heroSlides ?? collect())->isNotEmpty())
<section class="container mx-auto px-4 mt-4">
    <div class="promo-slider hero-slider"
        x-data="{
            currentSlide: 0,
            slides: {{ $heroSlides->count() }},
            startX: 0, currentX: 0, isDragging: false, sliderWidth: 0, autoSlideInterval: null,
            rtl: document.documentElement.dir === 'rtl',
            io: null,
            init() {
                this.sliderWidth = this.$el.offsetWidth;
                this.goToSlide(this.currentSlide);
                this.startAutoSlide();
                this.$el.addEventListener('touchstart', this.handleTouchStart.bind(this), { passive: true });
                this.$el.addEventListener('touchmove', this.handleTouchMove.bind(this), { passive: true });
                this.$el.addEventListener('touchend', this.handleTouchEnd.bind(this), { passive: true });
                this.$el.addEventListener('mousedown', this.handleMouseDown.bind(this));
                this.$el.addEventListener('mousemove', this.handleMouseMove.bind(this));
                this.$el.addEventListener('mouseup', this.handleMouseUp.bind(this));
                this.$el.addEventListener('mouseleave', this.handleMouseUp.bind(this));
                this.$el.addEventListener('alpine:destroy', () => this.cleanup());
                try {
                    this.io = new IntersectionObserver((entries) => {
                        entries.forEach(e => {
                            if (e.isIntersecting) { this.resumeAutoSlide(); } else { this.pauseAutoSlide(); }
                        });
                    }, { threshold: 0.15 });
                    this.io.observe(this.$el);
                } catch(_) {}
                window.addEventListener('resize', () => { this.sliderWidth = this.$el.offsetWidth; });
            },
            startAutoSlide() {
                if (this.autoSlideInterval) return;
                this.autoSlideInterval = setInterval(() => {
                    if (!this.isDragging) {
                        this.currentSlide = (this.currentSlide + 1) % this.slides;
                        this.goToSlide(this.currentSlide);
                    }
                }, 5000);
            },
            cleanup() { 
                if (this.autoSlideInterval) { clearInterval(this.autoSlideInterval); this.autoSlideInterval = null; }
                if (this.io) { try { this.io.disconnect(); } catch(_) {} }
            },
            handleTouchStart(e) { this.startX = e.touches[0].clientX; this.isDragging = true; this.pauseAutoSlide(); },
            handleTouchMove(e) { if (!this.isDragging) return; this.currentX = e.touches[0].clientX; },
            handleTouchEnd() { if (!this.isDragging) return; this.isDragging = false; this.handleSwipe(); this.resumeAutoSlide(); },
            handleMouseDown(e) { this.startX = e.clientX; this.isDragging = true; this.$el.style.cursor = 'grabbing'; this.pauseAutoSlide(); },
            handleMouseMove(e) { if (!this.isDragging) return; this.currentX = e.clientX; },
            handleMouseUp() { if (!this.isDragging) return; this.isDragging = false; this.$el.style.cursor = 'grab'; this.handleSwipe(); this.resumeAutoSlide(); },
            pauseAutoSlide() { if (this.autoSlideInterval) { clearInterval(this.autoSlideInterval); this.autoSlideInterval = null; } },
            resumeAutoSlide() { this.startAutoSlide(); },
            handleSwipe() {
                const diff = this.startX - this.currentX;
                const threshold = this.sliderWidth / 4;
                if (Math.abs(diff) > threshold) {
                    if ((diff > 0 && !this.rtl) || (diff < 0 && this.rtl)) { this.currentSlide = (this.currentSlide + 1) % this.slides; }
                    else { this.currentSlide = (this.currentSlide - 1 + this.slides) % this.slides; }
                }
                this.goToSlide(this.currentSlide);
            },
            goToSlide(index) {
                this.currentSlide = index;
                const direction = this.rtl ? 1 : -1;
                this.$refs.sliderContainer.style.transition = 'transform 0.5s ease';
                this.$refs.sliderContainer.style.transform = `translateX(${direction * (this.currentSlide * 100)}%)`;
                setTimeout(() => { this.$refs.sliderContainer.style.transition = ''; }, 500);
            }
        }">
        <div class="slider-wrapper h-full">
            <div class="slider-container h-full" x-ref="sliderContainer">
                @foreach($heroSlides as $slide)
                    <div class="slide">
                        <img src="{{ $slide->image_path ?? $slide->background_image_url }}"
                             class="slide-bg"
                                alt="{{ $slide->alt_text ?: ($slide->title ?: __('home.slider_image')) }}"
                             width="1920"
                             height="700">
                        @if(!empty($slide->show_overlay))
                            @php
                                $sRgb = hexToRgb($slide->overlay_color ?? '#000000');
                                $sStrength = $slide->overlay_strength ?? 0.5;
                            @endphp
                            <div class="slide-overlay" style="background: linear-gradient(90deg, rgba({{ $sRgb }}, {{ $sStrength }}), rgba({{ $sRgb }}, {{ $sStrength * 0.45 }}));"></div>
                        @endif
                        <div class="slide-content">
                            @if(!empty($slide->title) || !empty($slide->title_en))
                                <h2 class="text-2xl md:text-5xl font-extrabold mb-2 w-full text-center">
                                    @if(app()->getLocale() === 'en' && !empty($slide->title_en))
                                        {{ $slide->title_en }}
                                    @else
                                        {{ $slide->title }}
                                    @endif
                                </h2>
                            @endif
                            @if(!empty($slide->subtitle) || !empty($slide->subtitle_en))
                                <p class="mb-6 text-sm md:text-lg opacity-90 max-w-xl w-full mx-auto text-center">
                                    @if(app()->getLocale() === 'en' && !empty($slide->subtitle_en))
                                        {{ $slide->subtitle_en }}
                                    @else
                                        {{ $slide->subtitle }}
                                    @endif
                                </p>
                            @endif
                            @if(!empty($slide->button_text) || !empty($slide->button_text_en))
                                <div class="-mt-2 w-full flex justify-center">
                                    <a href="{{ $slide->button_url ?? '#' }}" 
                                       class="inline-block bg-white text-[#c32126] px-6 py-2 rounded-full font-bold text-base hover:bg-gray-100 transition shadow-lg mx-auto">
                                        @if(app()->getLocale() === 'en' && !empty($slide->button_text_en))
                                            {{ $slide->button_text_en }}
                                        @else
                                            {{ $slide->button_text }}
                                        @endif
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="slider-dots">
                <template x-for="(slide, index) in slides" :key="index">
                    <div class="dot"
                         role="button" tabindex="0"
                         :aria-label="`{{ __('home.slide_label') }} ${index+1}`"
                         :class="{ 'active': currentSlide === index }"
                         @click="goToSlide(index)"
                         @keydown.enter.space="goToSlide(index)"></div>
                </template>
            </div>
        </div>
    </div>
</section>
@endif

{{-- Categories Section (Using PrimaryCategory model) --}}
@php
    // تحميل الفئات لو ما ممرّرتها من الكنترولر
    $primaryCategories2 = $primaryCategories2
        ?? \App\Models\PrimaryCategory::query()
            ->active()
            ->withCount('products')
            ->ordered()
            ->get();

    // ترتيب تنازلي حسب عدد المنتجات (نفس منطقك فوق)
    try {
        if ($primaryCategories2 instanceof \Illuminate\Support\Collection) {
            $primaryCategories2 = $primaryCategories2
                ->sortByDesc(function ($pc) {
                    if (isset($pc->products_count)) return (int) $pc->products_count;
                    return method_exists($pc, 'products') ? (int) $pc->products()->count() : 0;
                })
                ->values();
        }
    } catch (\Throwable $e) {}
@endphp

@if($primaryCategories2->count())
<section class="pt-2 pb-0 lg:pt-4 lg:pb-0 section-cats relative overflow-hidden"
         x-data="{
              el:null, track:null, canGoLeft:false, canGoRight:true, step:320,
              autoDir:1, autoTimer:null, autoPaused:false, resumeTimer:null,
              autoTickMs:16, autoStepPx:1, manualPauseMs:5000, ticks:0,
              
              isRTL() { return getComputedStyle(this.el).direction === 'rtl'; },
              
              pauseAutoScroll() {
                  this.autoPaused = true;
                  if (this.resumeTimer) { clearTimeout(this.resumeTimer); this.resumeTimer = null; }
              },
              resumeAutoScroll() {
                  if (this.resumeTimer) { clearTimeout(this.resumeTimer); this.resumeTimer = null; }
                  this.autoPaused = false;
              },
              pauseAutoTemporarily(ms = this.manualPauseMs) {
                  this.pauseAutoScroll();
                  this.resumeTimer = setTimeout(() => this.resumeAutoScroll(), ms);
              },
              startAutoScroll() {
                  if (this.autoTimer || !this.el) return;
                  this.autoTimer = setInterval(() => {
                      if (!this.el || this.autoPaused) return;

                      let prev = this.el.scrollLeft;
                      let applyStep = (this.isRTL() ? -1 : 1) * this.autoDir * this.autoStepPx;
                      this.el.scrollLeft += applyStep;
                      
                      if (Math.abs(this.el.scrollLeft - prev) < 0.5) {
                          this.autoDir *= -1;
                      }
                      
                      if (this.ticks % 10 === 0) this.updateButtons();
                      this.ticks++;
                  }, this.autoTickMs);
              },
              updateButtons() {
                  if (!this.el || !this.track) return;
                  const elRect = this.el.getBoundingClientRect();
                  const trackRect = this.track.getBoundingClientRect();
                  this.canGoLeft = Math.round(trackRect.left) < Math.round(elRect.left) - 2;
                  this.canGoRight = Math.round(trackRect.right) > Math.round(elRect.right) + 2;
              },
              goLeft() {
                  this.pauseAutoTemporarily();
                  this.el.scrollBy({ left: -this.step, behavior: 'smooth' });
                  setTimeout(() => this.updateButtons(), 400);
              },
              goRight() {
                  this.pauseAutoTemporarily();
                  this.el.scrollBy({ left: this.step, behavior: 'smooth' });
                  setTimeout(() => this.updateButtons(), 400);
              },
              init(){
                  this.el = this.$refs.catScroll; if(!this.el) return;
                  this.track = this.$refs.catTrack; if(!this.track) return;
                  this.track.style.transform = ''; // clear any old css transforms
                  
                  this.$nextTick(()=>{
                      const card=this.track.querySelector('a');
                      this.step = card ? Math.max(240, Math.floor(card.getBoundingClientRect().width+10)) : 300;
                      this.updateButtons();
                  });

                  this.el.addEventListener('scroll', () => { this.updateButtons(); }, {passive:true});
                  this.el.addEventListener('pointerdown', () => this.pauseAutoTemporarily(), {passive:true});
                  this.el.addEventListener('touchstart', () => this.pauseAutoTemporarily(), {passive:true});
                  this.el.addEventListener('touchmove', () => this.pauseAutoTemporarily(), {passive:true});
                  this.el.addEventListener('wheel', () => this.pauseAutoTemporarily(), {passive:true});
                  this.el.addEventListener('mouseenter', () => this.pauseAutoTemporarily());
                  this.el.addEventListener('mousemove', () => this.pauseAutoTemporarily());
                  this.el.addEventListener('mouseleave', () => this.pauseAutoTemporarily());

                  window.addEventListener('resize', () => { this.updateButtons(); });
                  
                  // Initialize buttons and let browser set the correct scroll position first
                  setTimeout(() => {
                      this.updateButtons();
                      this.startAutoScroll();
                  }, 100);
              }
         }"
         x-init="init()">

    {{-- خلفيات خفيفة (نفس الستايل) --}}
    <!-- Removed blurred blobs for unified white background -->

    <div class="w-full text-center relative z-10">


        <div class="relative">
            {{-- أزرار التنقل (نفس الأزرار) --}}
            {{-- الزر الأيسر: يرجع/للخلف (prev) --}}
            <button
                class="brand-nav hidden md:flex absolute left-4 pos-mid z-[10001]"
                :class="{'brand-nav-active': canGoLeft, 'brand-nav-disabled': !canGoLeft}"
                :disabled="!canGoLeft"
                type="button" aria-label="{{ __('common.prev') }}"
                @click="goLeft()">
                <i class="bi bi-chevron-left text-2xl"></i>
            </button>

            {{-- الزر الأيمن: يتقدم/للأمام (next) --}}
            <button
                class="brand-nav hidden md:flex absolute right-4 pos-mid z-[10001]"
                :class="{'brand-nav-active': canGoRight, 'brand-nav-disabled': !canGoRight}"
                :disabled="!canGoRight"
                type="button" aria-label="{{ __('common.next') }}"
                @click="goRight()">
                <i class="bi bi-chevron-right text-2xl"></i>
            </button>

            {{-- تلاشي ديكوري --}}
            <div class="pointer-events-none hidden md:block absolute inset-y-0 left-0 w-24 z-[9998]"
                 style="background:linear-gradient(90deg, var(--bg), transparent)"></div>
            <div class="pointer-events-none hidden md:block absolute inset-y-0 right-0 w-24 z-[9998]"
                 style="background:linear-gradient(270deg, var(--bg), transparent)"></div>


            {{-- الشريط القابل للتمرير --}}
{{-- تم حذف "px-16" من هنا --}}
<div class="overflow-x-auto no-scrollbar js-auto-bounce" x-ref="catScroll">
    {{-- تم إضافة "pr-16" هنا لإضافة فراغ في نهاية القائمة فقط --}}
    <div class="flex flex-row gap-[5px] md:gap-8 items-start w-max py-1 auto-bounce-track" x-ref="catTrack">
        {{-- العربي: canPrev يرجع لليمين، canNext يتقدم لليسار --}}
        <div class="pulse-arrow pulse-arrow-left" x-show="canGoLeft" x-cloak><i class="bi bi-chevron-left"></i></div>
        <div class="pulse-arrow pulse-arrow-right" x-show="canGoRight" x-cloak><i class="bi bi-chevron-right"></i></div>

@foreach($primaryCategories2 as $pc)
    @php
        // اعتبره رئيسي إذا ما عنده parent_id أو علاقة parent
        $isTopLevel = true;
        if (isset($pc->parent_id) && $pc->parent_id)  { $isTopLevel = false; }
        if (isset($pc->parent)    && $pc->parent)     { $isTopLevel = false; }
    @endphp

    @if($isTopLevel)
        @php
            $thumb = $pc->image
                ? asset('storage/'.$pc->image)
                : ($pc->icon ? asset('storage/'.$pc->icon) : null);
        @endphp

{{-- هذا هو السطر الصحيح --}}
<a href="{{ route('shop', ['brand' => $pc->slug]) }}"
   class="flex flex-col items-center min-w-[68px] md:min-w-[110px] group text-center transition-all duration-300">
            <div class="w-28 h-28 rounded-full overflow-hidden border-4 border-white shadow-lg bg-white relative">
                <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                @if($thumb)
                    <img src="{{ $thumb }}" class="w-full h-full object-cover object-center"
                         alt="{{ $pc->name_translated }}" width="112" height="112">
                @else
                    <div class="w-full h-full grid place-items-center text-[#c32126] bg-white">
                        <i class="bi bi-tags" style="font-size:1.6rem;"></i>
                    </div>
                @endif
            </div>
            <h3 class="category-name mt-1 md:mt-2 text-base font-semibold group-hover:text-[#c32126]">{{ $pc->name_translated }}</h3>
        </a>
    @endif
@endforeach
            </div>
        </div>
        </div>
    </div>

    {{-- نفس تنسيقات الأزرار / المواضع المستعملة في سكشن الأقسام --}}
    <style>
        .section-cats { --icon-size: 112px; --row-py: 16px; --btn-dy: 0px; }
        .pos-mid { top: calc(var(--row-py) + (var(--icon-size) / 2) + var(--btn-dy)); transform: translateY(-50%); }
        .edge-hit { --edge-w: clamp(48px, 10vw, 120px); width: var(--edge-w); background: transparent; border: 0; padding: 0; cursor: pointer; }
        .section-cats .overflow-x-auto { scroll-snap-type: x mandatory; overflow-y: visible !important; }
        .section-cats .overflow-x-auto .flex>a { scroll-snap-align: start; }
        .section-cats .category-name { text-align:center; white-space:normal; word-break:break-word; line-height:1.35; min-height:2.7em; max-width:110px; margin-inline:auto; }
        .section-cats .w-28.h-28 { width:7rem!important; height:7rem!important; border-radius:50%; }
        @media (max-width: 640px) { .section-cats .w-28.h-28 { width:4.2rem!important; height:4.2rem!important; } }
        
        /* تنسيق أزرار التنقل للفئات الأولى (Primary Categories) */
        .brand-nav {
            position: absolute;
            height: 48px;
            width: 48px;
            border-radius: 9999px;
            align-items: center;
            justify-content: center;
            border: 1.5px solid rgba(234, 219, 205, 0.9);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(12px) saturate(140%);
            -webkit-backdrop-filter: blur(12px) saturate(140%);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }

        .brand-nav::before {
            content: "";
            position: absolute;
            inset: -6px;
            border-radius: inherit;
            backdrop-filter: blur(12px) saturate(140%);
            -webkit-backdrop-filter: blur(12px) saturate(140%);
            z-index: -1;
            pointer-events: none;
            transition: all 0.3s ease;
        }

        /* حالة الزر النشط (يوجد تنقل) */
        .brand-nav-active {
            color: #c32126;
            background: rgba(255, 255, 255, 0.4);
            border-color: rgba(234, 219, 205, 0.9);
        }

        .brand-nav-active:hover {
            background: rgba(255, 255, 255, 0.8);
            box-shadow: 0 16px 32px rgba(0, 0, 0, 0.15);
        }

        .brand-nav-active:active {
            background: rgba(255, 255, 255, 0.9);
        }

        /* حالة الزر المعطل (لا يوجد تنقل) */
        .brand-nav-disabled {
            color: #999;
            background: rgba(200, 200, 200, 0.25);
            border-color: rgba(180, 180, 180, 0.4);
            pointer-events: none;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .brand-nav-disabled::before {
            background: rgba(180, 180, 180, 0.1);
        }

        .brand-nav-disabled:hover {
            background: rgba(200, 200, 200, 0.25);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.05);
            transform: none;
        }

        /* الأيقونة */
        .brand-nav i {
            font-size: 1.4rem;
            transition: all 0.3s ease;
        }

        .brand-nav-active:hover i {
            transform: scale(1.15);
        }

        /* الوضع المظلم (Dark Mode) */
        html.dark .brand-nav-active {
            color: #f0b0ad;
            background: rgba(15, 23, 42, 0.4);
            border-color: rgba(31, 41, 55, 0.9);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
        }

        html.dark .brand-nav-active::before {
            background: rgba(15, 23, 42, 0.2);
        }

        html.dark .brand-nav-active:hover {
            background: rgba(15, 23, 42, 0.5);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.4);
        }

        html.dark .brand-nav-disabled {
            color: #666;
            background: rgba(100, 100, 100, 0.2);
            border-color: rgba(80, 80, 80, 0.4);
        }

        html.dark .brand-nav-disabled::before {
            background: rgba(80, 80, 80, 0.1);
        }
    </style>
</section>
@endif


{{-- Promo Slider 1 --}}
@if(($promoPrimarySlides ?? collect())->isNotEmpty())
<section class="container mx-auto px-4">

    <div class="promo-slider promo-primary-slider"
       x-data="{
        currentSlide: 0,
        slides: 3, /* default; will auto-detect */
        startX: 0, currentX: 0, isDragging: false, sliderWidth: 0, autoSlideInterval: null,
        rtl: document.documentElement.dir === 'rtl',
        io:null,

        init() {
          this.sliderWidth = this.$el.offsetWidth;

          /* NEW: dynamic slides */
          this.slides = this.$refs.sliderContainer?.children?.length || this.slides;

          this.goToSlide(this.currentSlide);
          this.startAutoSlide();

          this.$el.addEventListener('touchstart', this.handleTouchStart.bind(this), { passive: true });
          this.$el.addEventListener('touchmove', this.handleTouchMove.bind(this), { passive: true });
          this.$el.addEventListener('touchend', this.handleTouchEnd.bind(this), { passive: true });

          this.$el.addEventListener('mousedown', this.handleMouseDown.bind(this));
          this.$el.addEventListener('mousemove', this.handleMouseMove.bind(this));
          this.$el.addEventListener('mouseup', this.handleMouseUp.bind(this));
          this.$el.addEventListener('mouseleave', this.handleMouseUp.bind(this));

          /* OLD (kept): */
          this.$watch && this.$watch('$destroy', () => { this.cleanup(); });

          /* NEW: destroy + visibility */
          this.$el.addEventListener('alpine:destroy', () => this.cleanup());
          try {
            this.io = new IntersectionObserver((entries) => {
              entries.forEach(e => e.isIntersecting ? this.resumeAutoSlide() : this.pauseAutoSlide());
            }, { threshold: 0.15 });
            this.io.observe(this.$el);
          } catch(_) {}

          window.addEventListener('resize', () => { this.sliderWidth = this.$el.offsetWidth; });
        },

        startAutoSlide() {
          if (this.autoSlideInterval) return;
          this.autoSlideInterval = setInterval(() => {
            if (!this.isDragging) {
              this.currentSlide = (this.currentSlide + 1) % this.slides;
              this.goToSlide(this.currentSlide);
            }
          }, 5000);
        },

        cleanup() { if (this.autoSlideInterval) { clearInterval(this.autoSlideInterval); this.autoSlideInterval = null; } if(this.io){ try{ this.io.disconnect(); }catch(_){}} },

        handleTouchStart(e) { this.startX = e.touches[0].clientX; this.isDragging = true; this.pauseAutoSlide(); },
        handleTouchMove(e) { if (!this.isDragging) return; this.currentX = e.touches[0].clientX; },
        handleTouchEnd() { if (!this.isDragging) return; this.isDragging = false; this.handleSwipe(); this.resumeAutoSlide(); },

        handleMouseDown(e) { this.startX = e.clientX; this.isDragging = true; this.$el.style.cursor = 'grabbing'; this.pauseAutoSlide(); },
        handleMouseMove(e) { if (!this.isDragging) return; this.currentX = e.clientX; },
        handleMouseUp() { if (!this.isDragging) return; this.isDragging = false; this.$el.style.cursor = 'grab'; this.handleSwipe(); this.resumeAutoSlide(); },

        pauseAutoSlide() { if (this.autoSlideInterval) { clearInterval(this.autoSlideInterval); this.autoSlideInterval = null; } },
        resumeAutoSlide() { this.startAutoSlide(); },

        handleSwipe() {
          const diff = this.startX - this.currentX;
          const threshold = this.sliderWidth / 4;
          if (Math.abs(diff) > threshold) {
            if ((diff > 0 && !this.rtl) || (diff < 0 && this.rtl)) { this.currentSlide = (this.currentSlide + 1) % this.slides; }
            else { this.currentSlide = (this.currentSlide - 1 + this.slides) % this.slides; }
          }
          this.goToSlide(this.currentSlide);
        },

        goToSlide(index) {
          this.currentSlide = index;
          const direction = this.rtl ? 1 : -1;
          this.$refs.sliderContainer.style.transition = 'transform 0.5s ease';
          this.$refs.sliderContainer.style.transform = `translateX(${direction * (this.currentSlide * 100)}%)`;
          setTimeout(() => { this.$refs.sliderContainer.style.transition = ''; }, 500);
        }
      }">
    <div class="slider-wrapper">
      <div class="slider-container" x-ref="sliderContainer">
        @foreach($promoPrimarySlides as $slide)
            <div class="slide">
              <img src="{{ $slide->background_image_url }}"
                   class="slide-bg"
                    alt="{{ $slide->alt_text ?: ($slide->title ?: __('home.slider_image')) }}"
                   width="1920"
                   height="540">
              @if($slide->show_overlay)
                @php
                    $spRgb = hexToRgb($slide->overlay_color ?? "#000000");
                    $spStrength = $slide->overlay_strength ?? 0.5;
                @endphp
                <div class="slide-overlay" style="background: linear-gradient(90deg, rgba({{ $spRgb }}, {{ $spStrength }}), rgba({{ $spRgb }}, {{ $spStrength * 0.45 }}));"></div>
              @endif
              <div class="slide-content">
                @if($slide->title || $slide->title_en)
                    <h2 class="text-2xl md:text-4xl font-extrabold mb-2">
                        @if(app()->getLocale() === 'en' && !empty($slide->title_en))
                            {{ $slide->title_en }}
                        @else
                            {{ $slide->title }}
                        @endif
                    </h2>
                @endif
                @if($slide->subtitle || $slide->subtitle_en)
                    <p class="mb-4 text-sm md:text-base">
                        @if(app()->getLocale() === 'en' && !empty($slide->subtitle_en))
                            {{ $slide->subtitle_en }}
                        @else
                            {{ $slide->subtitle }}
                        @endif
                    </p>
                @endif
                @if($slide->button_text || $slide->button_text_en)
                    <div class="-mt-2">
                        <a href="{{ $slide->button_url ?: '#' }}"
                           class="inline-block bg-white text-[#c32126] px-5 py-1.5 rounded-full font-bold text-sm hover:bg-gray-100 transition">
                          @if(app()->getLocale() === 'en' && !empty($slide->button_text_en))
                              {{ $slide->button_text_en }}
                          @else
                              {{ $slide->button_text }}
                          @endif
                        </a>
                    </div>
                @endif
              </div>
            </div>
        @endforeach
      </div>

      <div class="slider-dots">
        <template x-for="(slide, index) in slides" :key="index">
          <div class="dot"
               role="button" tabindex="0"
               :aria-label="`Slide ${index+1}`"
               :class="{ 'active': currentSlide === index }"
               @click="goToSlide(index)"
               @keydown.enter.space="goToSlide(index)"></div>
        </template>
      </div>
    </div>
  </div>
</section>
@endif

{{-- Brands Section (Using Category model) --}}
<section class="pt-2 pb-0 md:pt-4 md:pb-0 section-cats relative overflow-hidden"
         x-data="{
             el:null, track:null, showLeftButton:true, showRightButton:true, isMobile: false, step: 320,
             autoDir:1, autoTimer:null, autoPaused:false, resumeTimer:null,
             autoTickMs:16, autoStepPx:1, manualPauseMs:5000, ticks:0,
             
             isRTL() { return getComputedStyle(this.el).direction === 'rtl'; },
             
             pauseAutoScroll() {
                 this.autoPaused = true;
                 if (this.resumeTimer) { clearTimeout(this.resumeTimer); this.resumeTimer = null; }
             },
             resumeAutoScroll() {
                 if (this.resumeTimer) { clearTimeout(this.resumeTimer); this.resumeTimer = null; }
                 this.autoPaused = false;
             },
             pauseAutoTemporarily(ms = this.manualPauseMs) {
                 this.pauseAutoScroll();
                 this.resumeTimer = setTimeout(() => this.resumeAutoScroll(), ms);
             },
             startAutoScroll() {
                 if (this.autoTimer || !this.el) return;
                 this.autoTimer = setInterval(() => {
                     if (!this.el || this.autoPaused) return;

                     let prev = this.el.scrollLeft;
                     let applyStep = (this.isRTL() ? -1 : 1) * this.autoDir * this.autoStepPx;
                     this.el.scrollLeft += applyStep;
                     
                     if (Math.abs(this.el.scrollLeft - prev) < 0.5) {
                         this.autoDir *= -1;
                     }
                     
                     if (this.ticks % 10 === 0) this.updateButtons();
                     this.ticks++;
                 }, this.autoTickMs);
             },
             updateButtons() {
                 if (!this.el || !this.track) return;
                 const elRect = this.el.getBoundingClientRect();
                 const trackRect = this.track.getBoundingClientRect();
                 this.showLeftButton = Math.round(trackRect.left) < Math.round(elRect.left) - 2;
                 this.showRightButton = Math.round(trackRect.right) > Math.round(elRect.right) + 2;
             },
             goLeft() {
                 this.pauseAutoTemporarily();
                 this.el.scrollBy({ left: -this.step, behavior: 'smooth' });
                 setTimeout(() => this.updateButtons(), 400);
             },
             goRight() {
                 this.pauseAutoTemporarily();
                 this.el.scrollBy({ left: this.step, behavior: 'smooth' });
                 setTimeout(() => this.updateButtons(), 400);
             },
             init(){
                 this.isMobile = window.innerWidth < 768;
                 this.el = this.$refs.catScroll; if(!this.el) return;
                 this.track = this.$refs.catTrack; if(!this.track) return;
                 this.track.style.transform = '';
                 
                 this.$nextTick(() => {
                     const card = this.track.querySelector('a');
                     if (card) this.step = Math.max(240, Math.floor(card.getBoundingClientRect().width + 10));
                     this.updateButtons();
                 });
                 
                 this.el.addEventListener('scroll', () => { this.updateButtons(); }, {passive:true});
                 this.el.addEventListener('pointerdown', () => this.pauseAutoTemporarily(), {passive:true});
                 this.el.addEventListener('touchstart', () => this.pauseAutoTemporarily(), {passive:true});
                 this.el.addEventListener('touchmove', () => this.pauseAutoTemporarily(), {passive:true});
                 this.el.addEventListener('wheel', () => { this.pauseAutoTemporarily(); }, {passive:true});
                 this.el.addEventListener('mouseenter', () => this.pauseAutoTemporarily());
                 this.el.addEventListener('mousemove', () => this.pauseAutoTemporarily());
                 this.el.addEventListener('mouseleave', () => this.pauseAutoTemporarily());

                 window.addEventListener('resize', () => {
                     this.isMobile = window.innerWidth < 768;
                     this.updateButtons();
                 });

                 setTimeout(() => {
                     this.updateButtons();
                     this.startAutoScroll();
                 }, 100);
             }
         }"
         x-init="init()">

    {{-- Header --}}

    
    <div class="overflow-x-auto no-scrollbar js-auto-bounce" x-ref="catScroll">
        {{-- تم استخدام !important هنا للتغلب على أي كود آخر --}}
        <div class="flex flex-row gap-[2px] md:gap-8 items-start w-max py-1 auto-bounce-track" x-ref="catTrack">
            {{-- الأسهم النابضة: تتبع نفس منطق الأزرار (showLeft لليسار و showRight لليمين) --}}
            <div class="pulse-arrow pulse-arrow-left" x-show="showLeftButton" x-cloak><i class="bi bi-chevron-left"></i></div>
            <div class="pulse-arrow pulse-arrow-right" x-show="showRightButton" x-cloak><i class="bi bi-chevron-right"></i></div>

            
            @php
                $rootCategories = collect($sortedCategories ?? $categories ?? [])
                    ->filter(fn($c) => empty($c->parent_id))
                    ->values();
            @endphp

            @foreach($rootCategories as $category)
                @php
                    $thumb = $category->image ? asset('storage/'.$category->image) : (!empty($category->icon) ? asset('storage/'.$category->icon) : null);
                @endphp
                <a href="{{ route('shop', ['category' => $category->slug]) }}" class="flex flex-col items-center min-w-[68px] md:min-w-[110px] group text-center transition-all duration-300">
                    <div class="w-28 h-28 rounded-full overflow-hidden border-4 border-white shadow-lg bg-white relative">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        @if($thumb)
                            <img src="{{ $thumb }}" class="w-full h-full object-cover object-center" alt="{{ $category->name_translated }}" width="112" height="112">
                        @else
                            <div class="w-full h-full grid place-items-center text-[#c32126] bg-white">
                                <i class="bi bi-tags" style="font-size:1.6rem;"></i>
                            </div>
                        @endif
                    </div>
                    <h3 class="category-name mt-1 md:mt-2 text-base font-semibold group-hover:text-[#c32126]">{{ $category->name_translated }}</h3>
                </a>
            @endforeach

        </div>
    </div>

    {{-- Buttons --}}
    {{-- الزر الأيسر: يرجع/للخلف (prev) --}}
    <button type="button" x-cloak class="cat-side-nav-glass inline-flex absolute top-1/2 left-4 -translate-y-1/2 z-[5]"
            :class="{'cat-nav-active': showLeftButton, 'cat-nav-disabled': !showLeftButton}"
            :disabled="!showLeftButton"
            x-show="!isMobile"
            x-transition
            aria-label="{{ __('common.prev') }}"
            @click="goLeft()">
        <i class="bi bi-chevron-left text-base md:text-lg"></i>
    </button>
    {{-- الزر الأيمن: يتقدم/للأمام (next) --}}
    <button type="button" x-cloak class="cat-side-nav-glass inline-flex absolute top-1/2 right-4 -translate-y-1/2 z-[5]"
            :class="{'cat-nav-active': showRightButton, 'cat-nav-disabled': !showRightButton}"
            :disabled="!showRightButton"
            x-show="!isMobile"
            x-transition
            aria-label="{{ __('common.next') }}"
            @click="goRight()">
        <i class="bi bi-chevron-right text-base md:text-lg"></i>
    </button>
    
    {{-- Styles --}}
    <style>
        .section-cats .overflow-x-auto { scroll-snap-type: x mandatory; overflow-y: visible !important; }
        .section-cats .overflow-x-auto .flex>a { scroll-snap-align: start; }
        .section-cats .w-28.h-28 { width:7rem!important; height:7rem!important; border-radius:50%; }
        @media (max-width: 640px) { .section-cats .w-28.h-28 { width:4.2rem!important; height:4.2rem!important; } }
        .section-cats .category-name { text-align:center; white-space:normal; word-break:break-word; line-height:1.35; min-height:2.7em; max-width:110px; margin-inline:auto; }
        @media (max-width: 640px) { .section-cats .category-name { font-size: 0.75rem!important; max-width: 75px!important; min-height: 2.2em; } }
        
        /* تنسيق أزرار التنقل للبراندات (Brands Section) */
        .cat-side-nav-glass {
            position: absolute;
            height: 48px;
            width: 48px;
            border-radius: 9999px;
            align-items: center;
            justify-content: center;
            border: 1.5px solid rgba(234, 219, 205, 0.9);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(12px) saturate(140%);
            -webkit-backdrop-filter: blur(12px) saturate(140%);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }
        
        .cat-side-nav-glass::after {
            content: "";
            position: absolute;
            inset: -6px;
            border-radius: inherit;
            backdrop-filter: blur(12px) saturate(140%);
            -webkit-backdrop-filter: blur(12px) saturate(140%);
            z-index: -1;
            pointer-events: none;
            transition: all 0.3s ease;
        }

        /* حالة الزر النشط (يوجد تنقل) */
        .cat-nav-active {
            color: #c32126;
            background: rgba(255, 255, 255, 0.4);
            border-color: rgba(234, 219, 205, 0.9);
        }

        .cat-nav-active:hover {
            background: rgba(255, 255, 255, 0.8);
            box-shadow: 0 16px 32px rgba(0, 0, 0, 0.15);
        }

        .cat-nav-active:active {
            background: rgba(255, 255, 255, 0.9);
        }

        /* حالة الزر المعطل (لا يوجد تنقل) */
        .cat-nav-disabled {
            color: #999;
            background: rgba(200, 200, 200, 0.25);
            border-color: rgba(180, 180, 180, 0.4);
            pointer-events: none;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .cat-nav-disabled::after {
            background: rgba(180, 180, 180, 0.1);
        }

        .cat-nav-disabled:hover {
            background: rgba(200, 200, 200, 0.25);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.05);
            transform: none;
        }

        /* الأيقونة */
        .cat-side-nav-glass i {
            font-size: 1.3rem;
            transition: all 0.3s ease;
        }

        .cat-nav-active:hover i {
            transform: scale(1.15);
        }
        
        /* الوضع المظلم (Dark Mode) */
        html.dark .cat-nav-active {
            color: #f0b0ad;
            background: rgba(15, 23, 42, 0.4);
            border-color: rgba(31, 41, 55, 0.9);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
        }
        
        html.dark .cat-nav-active::after {
            background: rgba(15, 23, 42, 0.2);
        }
        
        html.dark .cat-nav-active:hover {
            background: rgba(15, 23, 42, 0.5);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.4);
        }

        html.dark .cat-nav-disabled {
            color: #666;
            background: rgba(100, 100, 100, 0.2);
            border-color: rgba(80, 80, 80, 0.4);
        }

        html.dark .cat-nav-disabled::after {
            background: rgba(80, 80, 80, 0.1);
        }
    </style>
</section>



{{-- New Products Section --}}
@if($newProducts->isNotEmpty())
<section class="py-6 lg:py-10 bg-white relative" dir="rtl">
    <!-- Removed floating-3 -->
    <div class="container mx-auto px-4 relative z-10">
        <div class="flex justify-between items-center mb-6">
            <div class="section-header relative">
                <h2 class="text-3xl font-bold" style="color: var(--text);">{{ __('home.new_in_store') }}</h2>
            </div>
            <a href="{{ route('shop') }}" class="font-semibold hover:underline" style="color: var(--primary-color);">
                {{ __('common.view_all') }} <i class="bi bi-arrow-left-short"></i>
            </a>
        </div>

        <div class="products-grid">
            @foreach($newProducts->take(14) as $product)
                {{-- ===== Shop-identical Product Card (بدون شريط) ===== --}}
                <div class="product-card"
                     x-data="{
                         showAlt:false,
                         hasTwoImages: {{ optional($product->images)->count() > 1 ? 'true' : 'false' }},
                         rtl: document.documentElement.dir === 'rtl',
                         added:false, loadingAdd:false,
                         isFavorite: {{ in_array($product->id, $favoriteProductIds ?? []) ? 'true' : 'false' }},
                         loadingFav:false,
                         touchStartX:0,touchStartY:0,isSwiping:false,gestureDetermined:false,
                         handleTouchStart(e){ this.touchStartX=e.touches[0].clientX; this.touchStartY=e.touches[0].clientY; this.isSwiping=false; this.gestureDetermined=false; },
                         handleTouchMove(e){
                             if(this.gestureDetermined)return;
                             const dx=Math.abs(e.touches[0].clientX-this.touchStartX);
                             const dy=Math.abs(e.touches[0].clientY-this.touchStartY);
                             if(dx>10||dy>10){
                                 if(dx>dy){ this.isSwiping=true; e.preventDefault(); }
                                 this.gestureDetermined=true;
                             }
                         },
                         handleTouchEnd(e,linkEl){ if(this.isSwiping){ if(this.hasTwoImages){ this.showAlt=!this.showAlt; } } else { const dx=Math.abs(e.changedTouches[0].clientX-this.touchStartX); const dy=Math.abs(e.changedTouches[0].clientY-this.touchStartY); if(dx<10&&dy<10){ window.location.href=linkEl.href; } } },
                         toggleWishlist() {
                             if(this.loadingFav) return;
                             this.loadingFav=true;
                             fetch('{{ route('wishlist.toggle.async', $product->id) }}', {
                                 method:'POST',
                                 headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json','X-Requested-With':'XMLHttpRequest'}
                             })
                             .then(r=>r.json())
                             .then(d=>{
                                 if(d.success){
                                     this.isFavorite=!this.isFavorite;
                                     window.dispatchEvent(new CustomEvent('wishlist-updated',{detail:{count:d.wishlistCount}}))
                                 } else { alert(d.message || 'Error'); }
                             })
                             .catch(()=>alert('{{ __('common.connection_error') }}'))
                             .finally(()=>this.loadingFav=false);
                         },
                         addToCart() {
                             if(this.loadingAdd) return;
                             this.loadingAdd=true;
                             fetch('{{ route('cart.store') }}', {
                                 method:'POST',
                                 headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json','Content-Type':'application/json'},
                                 body:JSON.stringify({product_id:{{ $product->id }},quantity:1})
                             })
                             .then(r=>r.json())
                             .then(d=>{
                                 if(d.success){
                                     this.added=true;
                                     window.dispatchEvent(new CustomEvent('cart-updated',{detail:{cartCount:d.cartCount}}));
                                     setTimeout(()=>this.added=false,1800);
                                 } else { alert(d.message || 'Error'); }
                             })
                             .catch(()=>alert('{{ __('common.connection_error') }}'))
                             .finally(()=>this.loadingAdd=false);
                         }
                     }"
                     @mouseover="hasTwoImages ? showAlt=true : null"
                     @mouseout="hasTwoImages ? showAlt=false : null">

                    <a href="{{ route('product.detail', $product) }}"
                       class="product-content-link"
                       @touchstart="handleTouchStart($event)"
                       @touchmove="handleTouchMove($event)"
                       @touchend="handleTouchEnd($event, $el)">

                        <div class="product-image-container">
                            {{-- شارة منتهي الكمية + شارة نسبة الخصم --}}
                            @php
                                $isAvailable = ($product->stock_qty ?? $product->stock_quantity ?? 0) > 0;
                                $isOnSale = $product->isOnSale();
                                $discountPercentage = ($isOnSale && $product->price > 0)
                                    ? round((($product->price - $product->sale_price) / $product->price) * 100)
                                    : null;
                                $secondImage = optional($product->images->get(1))->image_path;
                            @endphp

                            {{-- منتهي الكمية --}}
                            @if(!$isAvailable)
                                <div class="absolute inset-0 bg-black/60 z-10 flex items-center justify-center pointer-events-none">
                                    <span class="text-white font-bold tracking-wider text-sm border border-white/50 rounded-full px-3 py-1">
                                        {{ __('common.out_of_stock') }}
                                    </span>
                                </div>
                            @endif

                            {{-- شارة نسبة الخصم --}}
                            @if($isOnSale && $isAvailable && $discountPercentage > 0)
                                <div class="product-sale-badge">-{{ $discountPercentage }}%</div>
                            @endif

                            {{-- ✅ سلايد بدون أي شريط جانبي --}}
                            <div class="product-image-slider">
                                {{-- الصورة الأولى --}}
                                @if ($product->firstImage)
                                    <img src="{{ asset('storage/'.$product->firstImage->image_path) }}"
                                         alt="{{ $product->name_translated }}" loading="lazy" width="600" height="600"
                                         :style="{
                                            transform: showAlt && hasTwoImages
                                                ? (rtl ? 'translateX(105%)' : 'translateX(-105%)')
                                                : 'translateX(0)',
                                            transition: 'transform 0.35s ease'
                                         }">
                                @else
                                    <img src="https://placehold.co/600x600?text=No+Image"
                                         alt="No image" loading="lazy" width="600" height="600"
                                         :style="{
                                            transform: showAlt && hasTwoImages
                                                ? (rtl ? 'translateX(105%)' : 'translateX(-105%)')
                                                : 'translateX(0)',
                                            transition: 'transform 0.35s ease'
                                         }">
                                @endif

                                {{-- الصورة الثانية (تعكس الاتجاه) --}}
                                @if ($secondImage)
                                    <img src="{{ asset('storage/'.$secondImage) }}"
                                         alt="{{ $product->name_translated }} (alt)" loading="lazy" width="600" height="600"
                                         :style="{
                                            transform: showAlt && hasTwoImages
                                                ? 'translateX(0)'
                                                : (rtl ? 'translateX(-105%)' : 'translateX(105%)'),
                                            transition: 'transform 0.35s ease'
                                         }">
                                @elseif ($product->firstImage)
                                    <img src="{{ asset('storage/' . $product->firstImage->image_path) }}"
                                         alt="{{ $product->name_translated }}" loading="lazy" width="600" height="600"
                                         :style="{
                                            transform: showAlt && hasTwoImages
                                                ? 'translateX(0)'
                                                : (rtl ? 'translateX(-105%)' : 'translateX(105%)'),
                                            transition: 'transform 0.35s ease'
                                         }">
                                @else
                                    <img src="https://placehold.co/600x600?text=No+Image"
                                         alt="No image" loading="lazy" width="600" height="600"
                                         :style="{
                                            transform: showAlt && hasTwoImages
                                                ? 'translateX(0)'
                                                : (rtl ? 'translateX(-105%)' : 'translateX(105%)'),
                                            transition: 'transform 0.35s ease'
                                         }">
                                @endif
                            </div>

                            <template x-if="hasTwoImages">
                                <div class="product-dots">
                                    <div class="product-dot" :class="{ 'active': !showAlt }"></div>
                                    <div class="product-dot" :class="{ 'active': showAlt }"></div>
                                </div>
                            </template>
                        </div>

                        <div class="product-info">
                            <h3 class="product-title">{{ $product->name_translated }}</h3>

                            @php
                                $avg = round((float) ($product->average_rating ?? 0), 1);
                                $count = (int) ($product->reviews_count ?? 0);
                            @endphp
                            <div class="flex items-center justify-center gap-2" title="{{ __('common.rating') }} {{ $avg }}">
                                <div class="flex">
                                    @for($i=1;$i<=5;$i++)
                                        @php $full=$i<=floor($avg); $half=!$full && ($i-$avg)<=0.5; @endphp
                                        <i class="bi {{ $full ? 'bi-star-fill' : ($half ? 'bi-star-half' : 'bi-star') }} text-yellow-500 text-sm"></i>
                                    @endfor
                                </div>
                                @if($count > 0)
                                    <span class="text-xs text-gray-500">({{ $count }})</span>
                                @endif
                            </div>

                            <div class="flex items-baseline justify-center gap-2">
                                @if($product->isOnSale())
                                    <div class="price">{{ number_format($product->sale_price,0) }} {{ __('common.currency') }}</div>
                                    <div class="old">{{ number_format($product->price,0) }} {{ __('common.currency') }}</div>
                                @else
                                    <div class="price">{{ number_format($product->price,0) }} {{ __('common.currency') }}</div>
                                @endif
                            </div>
                        </div>
                    </a>
                    <div class="product-actions">
                        @auth
                        <button @click.stop="toggleWishlist()"
                            @touchend.stop
                            class="btn-fav"
                            :class="{'favorited':isFavorite, 'opacity-50 pointer-events-none': loadingFav}"
                            :disabled="loadingFav">
                            <i class="bi" :class="isFavorite ? 'bi-heart-fill' : 'bi-heart'"></i>
                        </button>
                        @endauth

                        @if ($isAvailable)
                            <button @click.stop="addToCart()"
                                @touchend.stop
                                class="btn-primary">
                                <span x-show="!added && !loadingAdd"><i class="bi bi-cart-plus"></i> {{ __('common.add_to_cart') }}</span>
                                <span x-show="loadingAdd"><i class="bi bi-arrow-repeat animate-spin"></i></span>
                                <span x-show="added"><i class="bi bi-check-lg"></i> {{ __('common.added_to_cart') }}</span>
                            </button>
                        @else
                            <button class="btn-primary" disabled>
                                {{ __('common.out_of_stock') }}
                            </button>
                        @endif
                    </div>
                </div>
                {{-- ===== /Shop-identical Product Card ===== --}}
            @endforeach
        </div>
    </div>
</section>
@endif
{{-- Promo Slider 2 --}}
@if(($promoSecondarySlides ?? collect())->isNotEmpty())
<section class="container mx-auto px-4">
    <div class="promo-slider promo-primary-slider"
         x-data="{
         currentSlide: 0,
         slides: 2, /* default; will auto-detect */
         startX: 0,
         currentX: 0,
         isDragging: false,
         sliderWidth: 0,
         autoSlideInterval: null,
         rtl: document.documentElement.dir === 'rtl',
         io:null,

         init() {
           this.sliderWidth = this.$el.offsetWidth;

           /* NEW: dynamic slides */
           this.slides = this.$refs.sliderContainer?.children?.length || this.slides;

           this.goToSlide(this.currentSlide);
           this.startAutoSlide();

           this.$el.addEventListener('touchstart', this.handleTouchStart.bind(this), { passive: true });
           this.$el.addEventListener('touchmove', this.handleTouchMove.bind(this), { passive: true });
           this.$el.addEventListener('touchend', this.handleTouchEnd.bind(this), { passive: true });

           this.$el.addEventListener('mousedown', this.handleMouseDown.bind(this));
           this.$el.addEventListener('mousemove', this.handleMouseMove.bind(this));
           this.$el.addEventListener('mouseup', this.handleMouseUp.bind(this));
           this.$el.addEventListener('mouseleave', this.handleMouseUp.bind(this));

           /* OLD (kept): */
           this.$watch && this.$watch('$destroy', () => { this.cleanup(); });

           /* NEW: destroy + visibility */
           this.$el.addEventListener('alpine:destroy', () => this.cleanup());
           try {
             this.io = new IntersectionObserver((entries) => {
               entries.forEach(e => e.isIntersecting ? this.resumeAutoSlide() : this.pauseAutoSlide());
             }, { threshold: 0.15 });
             this.io.observe(this.$el);
           } catch(_) {}

         },

         startAutoSlide() {
           if (this.autoSlideInterval) return;
           this.autoSlideInterval = setInterval(() => {
             if (!this.isDragging) {
               this.currentSlide = (this.currentSlide + 1) % this.slides;
               this.goToSlide(this.currentSlide);
             }
           }, 5000);
         },

         cleanup() { if (this.autoSlideInterval) { clearInterval(this.autoSlideInterval); this.autoSlideInterval = null; } if(this.io){ try{ this.io.disconnect(); }catch(_){}} },

         handleTouchStart(e) { this.startX = e.touches[0].clientX; this.isDragging = true; this.pauseAutoSlide(); },
         handleTouchMove(e) { if (!this.isDragging) return; this.currentX = e.touches[0].clientX; },
         handleTouchEnd() { if (!this.isDragging) return; this.isDragging = false; this.handleSwipe(); this.resumeAutoSlide(); },

         handleMouseDown(e) { this.startX = e.clientX; this.isDragging = true; this.$el.style.cursor = 'grabbing'; this.pauseAutoSlide(); },
         handleMouseMove(e) { if (!this.isDragging) return; this.currentX = e.clientX; },
         handleMouseUp() { if (!this.isDragging) return; this.isDragging = false; this.$el.style.cursor = 'grab'; this.handleSwipe(); this.resumeAutoSlide(); },

         pauseAutoSlide() { if (this.autoSlideInterval) { clearInterval(this.autoSlideInterval); this.autoSlideInterval = null; } },
         resumeAutoSlide() { this.startAutoSlide(); },

         handleSwipe() {
           const diff = this.startX - this.currentX;
           const threshold = this.sliderWidth / 4;
           if (Math.abs(diff) > threshold) {
             if ((diff > 0 && !this.rtl) || (diff < 0 && this.rtl)) { this.currentSlide = (this.currentSlide + 1) % this.slides; }
             else { this.currentSlide = (this.currentSlide - 1 + this.slides) % this.slides; }
           }
           this.goToSlide(this.currentSlide);
         },

         goToSlide(index) {
           this.currentSlide = index;
           const direction = this.rtl ? 1 : -1;
           this.$refs.sliderContainer.style.transition = 'transform 0.5s ease';
           this.$refs.sliderContainer.style.transform = `translateX(${direction * (this.currentSlide * 100)}%)`;
           setTimeout(() => { this.$refs.sliderContainer.style.transition = ''; }, 500);
         }
       }">
        <div class="slider-wrapper">
            <div class="slider-container" x-ref="sliderContainer">
                @foreach($promoSecondarySlides as $slide)
                    <div class="slide">
                        <img src="{{ $slide->background_image_url }}"
                             class="slide-bg"
                             alt="{{ $slide->alt_text ?: ($slide->title ?: __('home.slider_image')) }}"
                             width="1920"
                             height="540">
                        <div class="slide-overlay"></div>
                        <div class="slide-content">
                            @if($slide->title || $slide->title_en)
                                <h2 class="text-2xl md:text-3xl font-bold mb-2">
                                    @if(app()->getLocale() === 'en' && !empty($slide->title_en))
                                        {{ $slide->title_en }}
                                    @else
                                        {{ $slide->title }}
                                    @endif
                                </h2>
                            @endif
                            @if($slide->subtitle || $slide->subtitle_en)
                                <p class="mb-4">
                                    @if(app()->getLocale() === 'en' && !empty($slide->subtitle_en))
                                        {{ $slide->subtitle_en }}
                                    @else
                                        {{ $slide->subtitle }}
                                    @endif
                                </p>
                            @endif
                            @if($slide->button_text || $slide->button_text_en)
                                <div class="-mt-2">
                                    <a href="{{ $slide->button_url ?: '#' }}" class="inline-block bg-white text-[#c32126] px-5 py-1.5 rounded-full font-bold text-sm hover:bg-gray-100 transition">
                                        @if(app()->getLocale() === 'en' && !empty($slide->button_text_en))
                                            {{ $slide->button_text_en }}
                                        @else
                                            {{ $slide->button_text }}
                                        @endif
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="slider-dots">
                <template x-for="(slide, index) in slides" :key="index">
                    <div class="dot"
                         role="button" tabindex="0"
                         :aria-label="`Slide ${index+1}`"
                         :class="{ 'active': currentSlide === index }"
                         @click="goToSlide(index)"
                         @keydown.enter.space="goToSlide(index)"></div>
                </template>
            </div>
        </div>
    </div>
</section>
@endif

{{-- Sale Products Section --}}
@if($saleProducts->isNotEmpty())
<section class="py-12 bg-white relative" dir="rtl">
    <!-- Removed floating-1 -->
    <div class="container mx-auto px-4 relative z-10">
        <div class="flex justify-between items-center mb-6">
            <div class="section-header relative">
                <h2 class="text-3xl font-bold" style="color: var(--text);">{{ __('home.featured_offers') }}</h2>
            </div>
            <a href="{{ route('shop', ['on_sale' => 'true']) }}" class="text-sm font-semibold hover:underline" style="color: var(--primary-color);">{{ __('common.view_all') }} <i class="bi bi-arrow-left-short"></i></a>
        </div>
        <div class="products-grid">
            @foreach($saleProducts->take(14) as $product)
                {{-- ===== Shop-identical Product Card ===== --}}
                <div class="product-card"
                     x-data="{
                         showAlt:false,
                         hasTwoImages: {{ optional($product->images)->count() > 1 ? 'true' : 'false' }},
                         rtl: document.documentElement.dir === 'rtl',
                         added:false, loadingAdd:false,
                         isFavorite: {{ in_array($product->id, $favoriteProductIds ?? []) ? 'true' : 'false' }},
                         loadingFav:false,
                         touchStartX:0,touchStartY:0,isSwiping:false,gestureDetermined:false,
                         handleTouchStart(e){ this.touchStartX=e.touches[0].clientX; this.touchStartY=e.touches[0].clientY; this.isSwiping=false; this.gestureDetermined=false; },
                         handleTouchMove(e){ if(this.gestureDetermined)return; const dx=Math.abs(e.touches[0].clientX-this.touchStartX); const dy=Math.abs(e.touches[0].clientY-this.touchStartY); if(dx>10||dy>10){ if(dx>dy){ this.isSwiping=true; e.preventDefault(); } this.gestureDetermined=true; } },
                         handleTouchEnd(e,linkEl){ if(this.isSwiping){ if(this.hasTwoImages){ this.showAlt=!this.showAlt; } } else { const dx=Math.abs(e.changedTouches[0].clientX-this.touchStartX); const dy=Math.abs(e.changedTouches[0].clientY-this.touchStartY); if(dx<10&&dy<10){ window.location.href=linkEl.href; } } },
                         toggleWishlist() {
                             if(this.loadingFav) return;
                             this.loadingFav=true;
                             fetch('{{ route('wishlist.toggle.async', $product->id) }}', {
                                 method:'POST',
                                 headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json','X-Requested-With':'XMLHttpRequest'}
                             })
                             .then(r=>r.json())
                             .then(d=>{
                                 if(d.success){
                                     this.isFavorite=!this.isFavorite;
                                     window.dispatchEvent(new CustomEvent('wishlist-updated',{detail:{count:d.wishlistCount}}))
                                 } else { alert(d.message || 'Error'); }
                             })
                             .catch(()=>alert('{{ __('common.connection_error') }}'))
                             .finally(()=>this.loadingFav=false);
                         },
                         addToCart() {
                             if(this.loadingAdd) return;
                             this.loadingAdd=true;
                             fetch('{{ route('cart.store') }}', {
                                 method:'POST',
                                 headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json','Content-Type':'application/json'},
                                 body:JSON.stringify({product_id:{{ $product->id }},quantity:1})
                             })
                             .then(r=>r.json())
                             .then(d=>{
                                 if(d.success){
                                     this.added=true;
                                     window.dispatchEvent(new CustomEvent('cart-updated',{detail:{cartCount:d.cartCount}}));
                                     setTimeout(()=>this.added=false,1800);
                                 } else { alert(d.message || 'Error'); }
                             })
                             .catch(()=>alert('{{ __('common.connection_error') }}'))
                             .finally(()=>this.loadingAdd=false);
                         }
                     }"
                     @mouseover="hasTwoImages ? showAlt=true : null"
                     @mouseout="hasTwoImages ? showAlt=false : null">

                    <a href="{{ route('product.detail', $product) }}"
                       class="product-content-link"
                       @touchstart="handleTouchStart($event)"
                       @touchmove="handleTouchMove($event)"
                       @touchend="handleTouchEnd($event, $el)">

                        <div class="product-image-container">
                            {{-- شارة منتهي الكمية + شارة نسبة الخصم --}}
                            @php
                                $isAvailable = ($product->stock_qty ?? $product->stock_quantity ?? 0) > 0;
                                $isOnSale = $product->isOnSale();
                                $discountPercentage = ($isOnSale && $product->price > 0)
                                    ? round((($product->price - $product->sale_price) / $product->price) * 100)
                                    : null;
                                $secondImage = optional($product->images->get(1))->image_path;
                            @endphp

                            {{-- منتهي الكمية --}}
                            @if(!$isAvailable)
                                <div class="absolute inset-0 bg-black/60 z-10 flex items-center justify-center pointer-events-none">
                                    <span class="text-white font-bold tracking-wider text-sm border border-white/50 rounded-full px-3 py-1">
                                        {{ __('common.out_of_stock') }}
                                    </span>
                                </div>
                            @endif

                            {{-- شارة نسبة الخصم --}}
                            @if($isOnSale && $isAvailable && $discountPercentage > 0)
                                <div class="product-sale-badge">-{{ $discountPercentage }}%</div>
                            @endif

                            {{-- ✅ سلايد بدون أي شريط جانبي --}}
                            <div class="product-image-slider">
                                {{-- الصورة الأولى --}}
                                @if ($product->firstImage)
                                    <img src="{{ asset('storage/'.$product->firstImage->image_path) }}"
                                         alt="{{ $product->name_translated }}" loading="lazy" width="600" height="600"
                                         :style="{
                                            transform: showAlt && hasTwoImages
                                                ? (rtl ? 'translateX(105%)' : 'translateX(-105%)')
                                                : 'translateX(0)',
                                            transition: 'transform 0.35s ease'
                                         }">
                                @else
                                    <img src="https://placehold.co/600x600?text=No+Image"
                                         alt="No image" loading="lazy" width="600" height="600"
                                         :style="{
                                            transform: showAlt && hasTwoImages
                                                ? (rtl ? 'translateX(105%)' : 'translateX(-105%)')
                                                : 'translateX(0)',
                                            transition: 'transform 0.35s ease'
                                         }">
                                @endif

                                {{-- الصورة الثانية (تعكس الاتجاه) --}}
                                @if ($secondImage)
                                    <img src="{{ asset('storage/'.$secondImage) }}"
                                         alt="{{ $product->name_translated }} (alt)" loading="lazy" width="600" height="600"
                                         :style="{
                                            transform: showAlt && hasTwoImages
                                                ? 'translateX(0)'
                                                : (rtl ? 'translateX(-105%)' : 'translateX(105%)'),
                                            transition: 'transform 0.35s ease'
                                         }">
                                @elseif ($product->firstImage)
                                    <img src="{{ asset('storage/' . $product->firstImage->image_path) }}"
                                         alt="{{ $product->name_translated }}" loading="lazy" width="600" height="600"
                                         :style="{
                                            transform: showAlt && hasTwoImages
                                                ? 'translateX(0)'
                                                : (rtl ? 'translateX(-105%)' : 'translateX(105%)'),
                                            transition: 'transform 0.35s ease'
                                         }">
                                @else
                                    <img src="https://placehold.co/600x600?text=No+Image"
                                         alt="No image" loading="lazy" width="600" height="600"
                                         :style="{
                                            transform: showAlt && hasTwoImages
                                                ? 'translateX(0)'
                                                : (rtl ? 'translateX(-105%)' : 'translateX(105%)'),
                                            transition: 'transform 0.35s ease'
                                         }">
                                @endif
                            </div>

                            <template x-if="hasTwoImages">
                                <div class="product-dots">
                                    <div class="product-dot" :class="{ 'active': !showAlt }"></div>
                                    <div class="product-dot" :class="{ 'active': showAlt }"></div>
                                </div>
                            </template>
                        </div>


                        <div class="product-info">
                            <h3 class="product-title">{{ $product->name_translated }}</h3>

                            @php
                                $avg = round((float) ($product->average_rating ?? 0), 1);
                                $count = (int) ($product->reviews_count ?? 0);
                            @endphp
                            <div class="flex items-center justify-center gap-2" title="{{ __('common.rating') }} {{ $avg }}">
                                <div class="flex">
                                    @for($i=1;$i<=5;$i++)
                                        @php $full=$i<=floor($avg); $half=!$full && ($i-$avg)<=0.5; @endphp
                                        <i class="bi {{ $full ? 'bi-star-fill' : ($half ? 'bi-star-half' : 'bi-star') }} text-yellow-500 text-sm"></i>
                                    @endfor
                                </div>
                                @if($count > 0)
                                    <span class="text-xs text-gray-500">({{ $count }})</span>
                                @endif
                            </div>

                            <div class="flex items-baseline justify-center gap-2">
                                @if($product->isOnSale())
                                    <div class="price">{{ number_format($product->sale_price,0) }} {{ __('common.currency') }}</div>
                                    <div class="old">{{ number_format($product->price,0) }} {{ __('common.currency') }}</div>
                                @else
                                    <div class="price">{{ number_format($product->price,0) }} {{ __('common.currency') }}</div>
                                @endif
                            </div>

                        </div>
                    </a>
                    <div class="product-actions">
                        @auth
                        <button @click.stop="toggleWishlist()"
                            @touchend.stop
                            class="btn-fav"
                            :class="{'favorited':isFavorite, 'opacity-50 pointer-events-none': loadingFav}"
                            :disabled="loadingFav">
                            <i class="bi" :class="isFavorite ? 'bi-heart-fill' : 'bi-heart'"></i>
                        </button>
                        @endauth

                        @if ($isAvailable)
                            <button @click.stop="addToCart()"
                                @touchend.stop
                                class="btn-primary">
                                <span x-show="!added && !loadingAdd"><i class="bi bi-cart-plus"></i> {{ __('common.add_to_cart') }}</span>
                                <span x-show="loadingAdd"><i class="bi bi-arrow-repeat animate-spin"></i></span>
                                <span x-show="added"><i class="bi bi-check-lg"></i> {{ __('common.added_to_cart') }}</span>
                            </button>
                        @else
                            <button class="btn-primary" disabled>
                                {{ __('common.out_of_stock') }}
                            </button>
                        @endif
                    </div>
                </div>
                {{-- ===== /Shop-identical Product Card ===== --}}
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Best Selling Products Section --}}
@if($bestSellingProducts->isNotEmpty())
<section class="py-12 bg-white relative" dir="rtl">
    <!-- Removed floating-2 -->
    <div class="container mx-auto px-4 relative z-10">
        <div class="flex justify-between items-center mb-6">
            <div class="section-header relative">
                <h2 class="text-3xl font-bold" style="color: var(--text);">{{ __('home.best_selling') }}</h2>
            </div>
            <a href="{{ route('shop') }}" class="text-sm font-semibold hover:underline" style="color: var(--primary-color);">{{ __('common.view_all') }} <i class="bi bi-arrow-left-short"></i></a>
        </div>
        <div class="products-grid">
            @foreach($bestSellingProducts->take(14) as $product)
                {{-- ===== Shop-identical Product Card ===== --}}
                <div class="product-card"
                     x-data="{
                         showAlt:false,
                         hasTwoImages: {{ optional($product->images)->count() > 1 ? 'true' : 'false' }},
                         rtl: document.documentElement.dir === 'rtl',
                         added:false, loadingAdd:false,
                         isFavorite: {{ in_array($product->id, $favoriteProductIds ?? []) ? 'true' : 'false' }},
                         loadingFav:false,
                         touchStartX:0,touchStartY:0,isSwiping:false,gestureDetermined:false,
                         handleTouchStart(e){ this.touchStartX=e.touches[0].clientX; this.touchStartY=e.touches[0].clientY; this.isSwiping=false; this.gestureDetermined=false; },
                         handleTouchMove(e){ if(this.gestureDetermined)return; const dx=Math.abs(e.touches[0].clientX-this.touchStartX); const dy=Math.abs(e.touches[0].clientY-this.touchStartY); if(dx>10||dy>10){ if(dx>dy){ this.isSwiping=true; e.preventDefault(); } this.gestureDetermined=true; } },
                         handleTouchEnd(e,linkEl){ if(this.isSwiping){ if(this.hasTwoImages){ this.showAlt=!this.showAlt; } } else { const dx=Math.abs(e.changedTouches[0].clientX-this.touchStartX); const dy=Math.abs(e.changedTouches[0].clientY-this.touchStartY); if(dx<10&&dy<10){ window.location.href=linkEl.href; } } },
                         toggleWishlist() {
                             if(this.loadingFav) return;
                             this.loadingFav=true;
                             fetch('{{ route('wishlist.toggle.async', $product->id) }}', {
                                 method:'POST',
                                 headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json','X-Requested-With':'XMLHttpRequest'}
                             })
                             .then(r=>r.json())
                             .then(d=>{
                                 if(d.success){
                                     this.isFavorite=!this.isFavorite;
                                     window.dispatchEvent(new CustomEvent('wishlist-updated',{detail:{count:d.wishlistCount}}))
                                 } else { alert(d.message || 'Error'); }
                             })
                             .catch(()=>alert('{{ __('common.connection_error') }}'))
                             .finally(()=>this.loadingFav=false);
                         },
                         addToCart() {
                             if(this.loadingAdd) return;
                             this.loadingAdd=true;
                             fetch('{{ route('cart.store') }}', {
                                 method:'POST',
                                 headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json','Content-Type':'application/json'},
                                 body:JSON.stringify({product_id:{{ $product->id }},quantity:1})
                             })
                             .then(r=>r.json())
                             .then(d=>{
                                 if(d.success){
                                     this.added=true;
                                     window.dispatchEvent(new CustomEvent('cart-updated',{detail:{cartCount:d.cartCount}}));
                                     setTimeout(()=>this.added=false,1800);
                                 } else { alert(d.message || 'Error'); }
                             })
                             .catch(()=>alert('{{ __('common.connection_error') }}'))
                             .finally(()=>this.loadingAdd=false);
                         }
                     }"
                     @mouseover="hasTwoImages ? showAlt=true : null"
                     @mouseout="hasTwoImages ? showAlt=false : null">

                    <a href="{{ route('product.detail', $product) }}"
                       class="product-content-link"
                       @touchstart="handleTouchStart($event)"
                       @touchmove="handleTouchMove($event)"
                       @touchend="handleTouchEnd($event, $el)">

                        <div class="product-image-container">
                            {{-- شارة منتهي الكمية + شارة نسبة الخصم --}}
                            @php
                                $isAvailable = ($product->stock_qty ?? $product->stock_quantity ?? 0) > 0;
                                $isOnSale = $product->isOnSale();
                                $discountPercentage = ($isOnSale && $product->price > 0)
                                    ? round((($product->price - $product->sale_price) / $product->price) * 100)
                                    : null;
                                $secondImage = optional($product->images->get(1))->image_path;
                            @endphp

                            {{-- منتهي الكمية --}}
                            @if(!$isAvailable)
                                <div class="absolute inset-0 bg-black/60 z-10 flex items-center justify-center pointer-events-none">
                                    <span class="text-white font-bold tracking-wider text-sm border border-white/50 rounded-full px-3 py-1">
                                        {{ __('common.out_of_stock') }}
                                    </span>
                                </div>
                            @endif

                            {{-- شارة نسبة الخصم --}}
                            @if($isOnSale && $isAvailable && $discountPercentage > 0)
                                <div class="product-sale-badge">-{{ $discountPercentage }}%</div>
                            @endif

                            {{-- ✅ سلايد بدون أي شريط جانبي --}}
                            <div class="product-image-slider">
                                {{-- الصورة الأولى --}}
                                @if ($product->firstImage)
                                    <img src="{{ asset('storage/'.$product->firstImage->image_path) }}"
                                         alt="{{ $product->name_translated }}" loading="lazy" width="600" height="600"
                                         :style="{
                                            transform: showAlt && hasTwoImages
                                                ? (rtl ? 'translateX(105%)' : 'translateX(-105%)')
                                                : 'translateX(0)',
                                            transition: 'transform 0.35s ease'
                                         }">
                                @else
                                    <img src="https://placehold.co/600x600?text=No+Image"
                                         alt="No image" loading="lazy" width="600" height="600"
                                         :style="{
                                            transform: showAlt && hasTwoImages
                                                ? (rtl ? 'translateX(105%)' : 'translateX(-105%)')
                                                : 'translateX(0)',
                                            transition: 'transform 0.35s ease'
                                         }">
                                @endif

                                {{-- الصورة الثانية (تعكس الاتجاه) --}}
                                @if ($secondImage)
                                    <img src="{{ asset('storage/'.$secondImage) }}"
                                         alt="{{ $product->name_translated }} (alt)" loading="lazy" width="600" height="600"
                                         :style="{
                                            transform: showAlt && hasTwoImages
                                                ? 'translateX(0)'
                                                : (rtl ? 'translateX(-105%)' : 'translateX(105%)'),
                                            transition: 'transform 0.35s ease'
                                         }">
                                @elseif ($product->firstImage)
                                    <img src="{{ asset('storage/' . $product->firstImage->image_path) }}"
                                         alt="{{ $product->name_translated }}" loading="lazy" width="600" height="600"
                                         :style="{
                                            transform: showAlt && hasTwoImages
                                                ? 'translateX(0)'
                                                : (rtl ? 'translateX(-105%)' : 'translateX(105%)'),
                                            transition: 'transform 0.35s ease'
                                         }">
                                @else
                                    <img src="https://placehold.co/600x600?text=No+Image"
                                         alt="No image" loading="lazy" width="600" height="600"
                                         :style="{
                                            transform: showAlt && hasTwoImages
                                                ? 'translateX(0)'
                                                : (rtl ? 'translateX(-105%)' : 'translateX(105%)'),
                                            transition: 'transform 0.35s ease'
                                         }">
                                @endif
                            </div>

                            <template x-if="hasTwoImages">
                                <div class="product-dots">
                                    <div class="product-dot" :class="{ 'active': !showAlt }"></div>
                                    <div class="product-dot" :class="{ 'active': showAlt }"></div>
                                </div>
                            </template>
                        </div>

                        <div class="product-info">
                            <h3 class="product-title">{{ $product->name_translated }}</h3>

                            @php
                                $avg = round((float) ($product->average_rating ?? 0), 1);
                                $count = (int) ($product->reviews_count ?? 0);
                            @endphp
                            <div class="flex items-center justify-center gap-2" title="{{ __('common.rating') }} {{ $avg }}">
                                <div class="flex">
                                    @for($i=1;$i<=5;$i++)
                                        @php $full=$i<=floor($avg); $half=!$full && ($i-$avg)<=0.5; @endphp
                                        <i class="bi {{ $full ? 'bi-star-fill' : ($half ? 'bi-star-half' : 'bi-star') }} text-yellow-500 text-sm"></i>
                                    @endfor
                                </div>
                                @if($count > 0)
                                    <span class="text-xs text-gray-500">({{ $count }})</span>
                                @endif
                            </div>

                            <div class="flex items-baseline justify-center gap-2">
                                @if($product->isOnSale())
                                    <div class="price">{{ number_format($product->sale_price,0) }} {{ __('common.currency') }}</div>
                                    <div class="old">{{ number_format($product->price,0) }} {{ __('common.currency') }}</div>
                                @else
                                    <div class="price">{{ number_format($product->price,0) }} {{ __('common.currency') }}</div>
                                @endif
                            </div>
                        </div>
                    </a>

                    <div class="product-actions">
                        @auth
                        <button @click.stop="toggleWishlist()"
                            @touchend.stop
                            class="btn-fav"
                            :class="{'favorited':isFavorite, 'opacity-50 pointer-events-none': loadingFav}"
                            :disabled="loadingFav">
                            <i class="bi" :class="isFavorite ? 'bi-heart-fill' : 'bi-heart'"></i>
                        </button>
                        @endauth

                        @if ($isAvailable)
                            <button @click.stop="addToCart()"
                                @touchend.stop
                                class="btn-primary">
                                <span x-show="!added && !loadingAdd"><i class="bi bi-cart-plus"></i> {{ __('common.add_to_cart') }}</span>
                                <span x-show="loadingAdd"><i class="bi bi-arrow-repeat animate-spin"></i></span>
                                <span x-show="added"><i class="bi bi-check-lg"></i> {{ __('common.added_to_cart') }}</span>
                            </button>
                        @else
                            <button class="btn-primary" disabled>
                                {{ __('common.out_of_stock') }}
                            </button>
                        @endif
                    </div>
                </div>
                {{-- ===== /Shop-identical Product Card ===== --}}
            @endforeach
        </div>
    </div>
</section>
@endif

@push('styles')
<style>
  /* ===== Home Blog (6 posts, 2 per row on mobile, square image) ===== */
  .home-blog .post-card{
    border:1px solid #ebe8e6; border-radius:.75rem; background:#fff;
    overflow:hidden; transition:transform .25s, box-shadow .25s;
  }
  .home-blog .post-card:hover{ transform:translateY(-3px); box-shadow:0 10px 20px rgba(0,0,0,.08); }
  .home-blog .post-image{
    width:100%; aspect-ratio:1/1; object-fit:cover; display:block; /* صورة مربعة */
  }
  .home-blog .post-body{ padding:.9rem }
  .home-blog .post-title{ font-size:1rem; line-height:1.35; margin:.35rem 0 }
  .home-blog .post-excerpt{ font-size:.875rem }

  /* Dark */
  html.dark .home-blog .post-card{ background:#0f172a; border-color:#1f2937; box-shadow:0 8px 22px rgba(0,0,0,.22) }
  html.dark .home-blog .post-card p, html.dark .home-blog .post-card h3{ color:#e5e7eb }
  html.dark .home-blog .post-card .text-gray-500,
  html.dark .home-blog .post-card .text-gray-600{ color:#9ca3af!important }
</style>
@endpush
@push('styles')
<style>
  /* Desktop-only shrink for blog cards */
  @media (min-width: 1024px) {
    .home-blog .post-card { border-radius: .6rem; }
    .home-blog .post-body { padding: .65rem; }
    .home-blog .post-title { font-size: .95rem; line-height: 1.3; margin: .3rem 0; }
    .home-blog .post-excerpt { font-size: .82rem; }
    .home-blog .post-card:hover { transform: translateY(-2px); box-shadow: 0 8px 18px rgba(0,0,0,.07); }
  }
</style>
@endpush

@php
    // اجلب حتى 6 مقالات
    $homePosts = ($homePosts ?? null)
        ?: (isset($posts) && is_iterable($posts) ? collect($posts) : null);

    if (blank($homePosts) || ($homePosts instanceof \Illuminate\Support\Collection && $homePosts->isEmpty())) {
        try {
            $homePosts = \App\Models\Post::with(['category','author'])
                ->when(\Schema::hasColumn('posts','published_at'), fn($q)=>$q->whereNotNull('published_at'))
                ->orderByDesc(\Schema::hasColumn('posts','published_at') ? 'published_at' : 'created_at')
                ->take(6)->get();
        } catch (\Throwable $e) { $homePosts = collect(); }
    }
    $homePosts = $homePosts->take(6);
@endphp

@if($homePosts->isNotEmpty())
<section class="home-blog container mx-auto px-4 py-12">
    <div class="flex items-end justify-between mb-6">
        <h2 class="text-2xl md:text-3xl font-extrabold">{{ __('home.from_blog') }}</h2>
        <a href="{{ url('/blog') }}" class="text-sm font-semibold text-[#c32126] hover:underline">{{ __('home.view_all_articles') }}</a>
    </div>

    {{-- شبكة: هاتف = عمودين، من md وفوق = 3 أعمدة (إجمالي 6 عناصر) --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
        @foreach ($homePosts as $post)
            <article class="post-card">
                <a href="{{ route('blog.show', $post->slug) }}">
                    <img
                        src="{{ $post->image ? asset('storage/' . $post->image) : 'https://placehold.co/600x600/F3E5E3/BE6661?text=Tofof' }}"
                        alt="{{ $post->title }}"
                        class="post-image"
                        width="600" height="600">
                </a>
                <div class="post-body">
                    @if(optional($post->category)->name)
                        <p class="text-[11px] text-gray-500 mb-1">{{ $post->category->name }}</p>
                    @endif

                    <h3 class="post-title font-bold">
                        <a href="{{ route('blog.show', $post->slug) }}" class="link-brand">{{ $post->title }}</a>
                    </h3>

                    @if(!empty($post->excerpt))
                        <p class="post-excerpt text-gray-600 mb-2"
                           style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                            {{ $post->excerpt }}
                        </p>
                    @endif

                    <div class="text-[11px] text-gray-400">
                        @if(optional($post->author)->name) <span>{{ __('common.by') }} {{ $post->author->name }}</span> @endif
                        @php $date = $post->published_at ?? $post->created_at; @endphp
                        @if($date) &bull; <span>{{ \Illuminate\Support\Carbon::parse($date)->format('d M, Y') }}</span> @endif
                    </div>
                </div>
            </article>
        @endforeach
    </div>
</section>
@endif

</div> {{-- إغلاق home-scope --}}

@endsection
