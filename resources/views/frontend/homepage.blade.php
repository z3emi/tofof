@php
    $sortedCategories = $categories;
@endphp
@php
    $favoriteProductIds = $favoriteProductIds ?? [];
@endphp
@php
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
    
    .home-scope {
        
        --primary-color: #6d0e16;
        --primary-hover: #a61c20;
        --secondary-color: #ffffff;
        --accent-color: #ea7a7e;

        
        --bg: #ffffff;
        --bg-soft: #ffffff;
        --surface: #ffffff;
        --card-bg: #ffffff;
        --text: #111111;
        --text-soft: #333333;
        --muted: #666666;
        --border: #e5e5e5;

        
        --new-badge-color: #4CAF50;
        --bestseller-badge-color: #FF9800;
        --sale-badge-color: #E53935;

        
        --hero-start: rgba(255, 255, 255, 0.8);
        --hero-end: rgba(247, 247, 247, 0.8);

        
        --cat-grad-from: #ffffff;
        --cat-grad-to: #ffffff;

        
    }

    
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
        
        --secondary-color: #1a1a1a;
    }

    
    .home-scope {
        background: var(--bg);
        color: var(--text);
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    
    html.dark .home-scope .bg-white { background-color: var(--surface) !important; }
    html.dark .home-scope .bg-gray-50 { background-color: var(--bg-soft) !important; }
    html.dark .home-scope .text-[#333] { color: var(--text) !important; }
    html.dark .home-scope .text-gray-700 { color: var(--text) !important; }
    html.dark .home-scope .text-gray-500 { color: var(--muted) !important; }

    
    .home-scope .hero-title{ font-size:3.5rem; font-weight:800; color:var(--text); line-height:1.2; margin-bottom:1.5rem; transition: color 0.3s ease; }
    .home-scope .hero-title span{ background:linear-gradient(135deg, var(--primary-color), var(--primary-hover)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
    @media (max-width:1024px){ .home-scope .hero-title{ font-size:2.5rem; } }
    .home-scope .hero-subtitle{ font-size:1.25rem; color:var(--text-soft); margin-bottom:2rem; opacity:.9; line-height:1.6; transition: color 0.3s ease; }
    .home-scope .btn-hero-primary{ background:linear-gradient(135deg, var(--primary-color), var(--primary-hover)); color:#fff; padding:.6rem 1.7rem; border-radius:50px; font-weight:600; font-size:0.95rem; text-decoration:none; display:inline-block; transition:.3s; box-shadow:0 4px 15px rgba(205,137,133,.3); }
    .home-scope .btn-hero-primary:hover{ transform:translateY(-3px); box-shadow:0 8px 25px rgba(205,137,133,.4); }

    
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
        padding: 1rem 2rem 4rem 2rem; 
        color: #fff;
        user-select: none; 
    }
    .header-slider-content .hero-title,
    .header-slider-content .hero-subtitle {
        color: #fff;
        text-shadow: 0 2px 8px rgba(0,0,0,0.5);
    }

    
    @media (max-width: 640px) {
        .header-slider-content {
            padding: 1rem 0.5rem 1.2rem 0.5rem; 
        }
        .header-slider-content .hero-title {
            font-size: 1.5rem; 
            line-height: 1.25;
            margin-bottom: 0.5rem; 
        }
        .header-slider-content .hero-subtitle {
            font-size: 0.9rem; 
            margin-bottom: 1rem; 
            line-height: 1.5;
        }
        .header-slider-content .btn-hero-primary {
            font-size: 0.75rem;
            padding: 0.4rem 1.1rem;
        }
    }

    
    .home-scope .promo-slider{ position:relative; overflow:visible; border-radius:16px; margin: 0.75rem auto; background:var(--surface); transition: background 0.3s ease; }
    @media (min-width: 1024px) {
        .home-scope .promo-slider {
            max-width: 1300px; 
        }
    }
    .home-scope .slider-wrapper{ position:relative; width:100%; height:100%; overflow:hidden; border-radius:16px; }
    .home-scope .slider-container{ display:flex; transition:transform .5s ease; height:100%; }
    .home-scope .slide{
        position:relative;
        min-width:100%;
        display:flex;
        align-items:center;
        overflow:hidden;
        height: auto; 
        min-height: 80px; 
        max-height: 480px;
    }
    
    .promo-slider.shrinked .slide {
        
        aspect-ratio: auto;
        min-height: clamp(92px, 18vw, 170px);
        height: clamp(92px, 18vw, 170px);
        max-height: 170px;
    }

    
    .hero-slider .slide {
        width: 100%;
        aspect-ratio: 1920 / 700;
        position: relative;
        overflow: hidden;
    }
    
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
        position:relative; 
        width:100%; 
        height:auto; 
        object-fit: contain; 
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

    
    .home-scope .section-header h2{ color: var(--text); transition: color 0.3s ease; }
    .home-scope .section-cats { background: linear-gradient(to bottom right, var(--cat-grad-from), var(--cat-grad-to)); position:relative; overflow:hidden; transition: background 0.3s ease; }
    
    
    .home-scope .category-name {
        color: var(--text); 
        transition: color 0.3s ease;
    }

    
    
    .home-scope .product-sale-badge{
        position:absolute;
        top:.6rem;
        right:.6rem;
        z-index:15;
        background:#6d0e16;      
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


.home-scope .product-image-slider{
    position: relative;
    width: 100%;
    height: 100%;
}


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

    
    .home-scope .no-scrollbar::-webkit-scrollbar{ display:none; }
    .home-scope .no-scrollbar{ -ms-overflow-style:none; scrollbar-width:none; }
    .home-scope .floating-element{ position:absolute; border-radius:50%; background:linear-gradient(135deg, var(--primary-color), var(--accent-color)); opacity:.08; z-index:0; animation:float 6s ease-in-out infinite; transition: opacity 0.3s ease; }
    html.dark .home-scope .floating-element { opacity: 0.04; }
    @keyframes float{ 0%{ transform:translateY(0); } 50%{ transform:translateY(-20px);} 100%{ transform:translateY(0); } }
    .home-scope .floating-1{ width:80px;height:80px; top:10%; right:5%; animation-delay:0s; }
    .home-scope .floating-2{ width:60px;height:60px; bottom:15%; left:8%; animation-delay:1s; }
    .home-scope .floating-3{ width:40px;height:40px; top:40%; right:15%; animation-delay:2s; }

    
    .home-scope .testimonials-section {
        --testimonials-bg-a: #fcf9f9;
        --testimonials-bg-b: #ffffff;
        --testimonials-stroke: #efe2e3;
        --testimonials-title: var(--primary-color);
        background: #ffffff;
        border-top: 1px solid rgba(195, 33, 38, 0.1);
        border-bottom: 1px solid rgba(195, 33, 38, 0.08);
    }
    .home-scope .testimonials-title {
        color: var(--testimonials-title);
        font-weight: 800;
        letter-spacing: -0.02em;
    }
    .home-scope .testimonials-subtitle {
        color: #7a6a6c;
    }
    .home-scope .testimonials-marquee {
        position: relative;
        overflow: hidden;
        border-radius: 22px;
        padding: 5px 0;
        cursor: pointer;
        outline: none;
    }
    .home-scope .testimonials-marquee::before,
    .home-scope .testimonials-marquee::after {
        content: "";
        position: absolute;
        top: 0;
        bottom: 0;
        width: 56px;
        z-index: 2;
        pointer-events: none;
    }
    .home-scope .testimonials-marquee::before {
        right: 0;
        background: linear-gradient(270deg, rgba(255, 255, 255, 0.72), rgba(255, 255, 255, 0));
    }
    .home-scope .testimonials-marquee::after {
        left: 0;
        background: linear-gradient(90deg, rgba(255, 255, 255, 0.72), rgba(255, 255, 255, 0));
    }
    .home-scope .testimonials-track {
        display: flex;
        gap: 10px;
        width: max-content;
        direction: ltr;
        will-change: transform;
        transform: translate3d(0, 0, 0);
    }
    .home-scope .testimonial-card {
        width: min(84vw, 320px);
        min-height: 168px;
        border-radius: 16px;
        border: 1px solid var(--testimonials-stroke);
        background: #f2f3f5;
        box-shadow: 0 8px 22px rgba(58, 20, 20, 0.08);
        padding: 13px;
        direction: rtl;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .home-scope .testimonial-card-head {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .home-scope .testimonial-avatar {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #efdfdf;
        box-shadow: 0 0 0 2px #fff;
    }
    .home-scope .testimonial-name {
        color: var(--primary-color);
        font-weight: 800;
        font-size: 1.25rem;
        line-height: 1.2;
        margin: 0;
    }
    .home-scope .testimonial-role {
        color: #8a7d7f;
        font-size: 0.82rem;
        margin: 2px 0 5px;
    }
    .home-scope .testimonial-stars {
        display: inline-flex;
        gap: 2px;
        color: #f6c54a;
        font-size: 0.84rem;
    }
    .home-scope .testimonial-text {
        color: #54494b;
        font-size: 1rem;
        line-height: 1.6;
        margin-top: 8px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    @media (max-width: 768px) {
        .home-scope .testimonials-marquee::before,
        .home-scope .testimonials-marquee::after {
            width: 12px;
        }
        .home-scope .testimonials-track {
            gap: 8px;
        }
        .home-scope .testimonial-card {
            width: min(70vw, 220px);
            min-height: 126px;
            border-radius: 12px;
            padding: 8px;
        }
        .home-scope .testimonial-avatar {
            width: 36px;
            height: 36px;
            border-width: 2px;
        }
        .home-scope .testimonial-name {
            font-size: 0.9rem;
        }
        .home-scope .testimonial-role {
            font-size: 0.66rem;
        }
        .home-scope .testimonial-text {
            font-size: 0.75rem;
            margin-top: 4px;
            -webkit-line-clamp: 2;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .home-scope .testimonials-track {
            transform: none !important;
        }
    }

    
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

    
    html.dark .home-scope .product-card { background-color:#1f2937; }
    html.dark .home-scope .product-title { color:#f9fafb; }
    html.dark .home-scope .btn-fav { background-color:#374151; color:#9ca3af; }
    html.dark .home-scope .btn-fav:hover { background-color:#4b5563; }
    html.dark .home-scope .btn-fav.favorited { background-color:rgba(205,137,133,.2); color:#f9a8d4; }
</style>
<style>
  
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
  

  
  .slider-dots{
    background: rgba(15, 23, 42, .35) !important; 
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

  
  .slider-dots .dot.active{
    width: 24px !important; 
    background: #ffffff !important;
    border-color: rgba(255,255,255,.65);
    opacity: 1;
  }

  
  .slider-dots .dot:hover{ transform: translateY(-1px); }

  
  @media (max-width: 640px){
        .home-scope .promo-slider { margin-bottom: 30px !important; }
        .slider-dots{
            bottom: -16px !important;
            left: 50% !important;
            right: auto !important;
            top: auto !important;
            padding: 4px 8px !important;
            gap: 6px !important;
            background: rgba(0,0,0,0.18) !important;
            box-shadow: none !important;
            border-radius: 999px !important;
            min-width: auto;
            max-width: none;
            width: auto;
            height: auto;
            transform: translateX(-50%) !important;
            opacity: .96;
            justify-content: center;
            align-items: center;
            flex-direction: row;
        }
        .slider-dots .dot{
            width: 5px !important;
            height: 5px !important;
            border-radius: 999px !important;
            background: #fff !important;
            opacity: 0.55;
            margin: 0 !important;
            border: none !important;
            box-shadow: none !important;
            transition: width 0.2s, transform 0.2s, background 0.2s, opacity 0.2s;
        }
        .slider-dots .dot.active{
            width: 15px !important;
            height: 5px !important;
            background: #6d0e16 !important;
            opacity: 1;
            transform: scale(1.05);
        }
  }

  
  

  
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
  
.section-cats .category-name {
  text-align: center;
  white-space: normal;      
  word-break: break-word;   
  line-height: 1.3;
  max-width: 110px;         
  min-height: 2.6em;        
  display: flex;
  align-items: center;
  justify-content: center;
  text-wrap: balance;
}


.section-cats .flex.flex-col.items-center {
  min-width: 110px;
}



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



.section-cats .w-28.h-28 {
  width: 7rem !important;
  height: 7rem !important;
  border-radius: 50%;
}
@media (max-width: 640px) {
  .section-cats .w-28.h-28 {
    width: 4.2rem !important; 
    height: 4.2rem !important;
  }
}


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

.section-cats .overflow-x-auto {
  height: auto !important;      
  overflow-y: visible !important;
}


@keyframes pulseSide {
  0% { transform: translateY(-50%) scale(1); opacity: 0.4; }
  50% { transform: translateY(-50%) scale(1.2); opacity: 0.8; }
  100% { transform: translateY(-50%) scale(1); opacity: 0.4; }
}
.pulse-arrow {
  position: absolute;
  top: 40%; 
  z-index: 20;
  pointer-events: none;
  color: var(--primary-color);
  font-size: 1.2rem;
  animation: pulseSide 2s infinite ease-in-out;
  display: none; 
}
@media (max-width: 640px) {
  .pulse-arrow { display: block; }
}
.pulse-arrow-left { left: 5px; }
.pulse-arrow-right { right: 5px; }


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



@if(($heroSlides ?? collect())->isNotEmpty())
<section class="container mx-auto px-4 mt-4">
    <div class="promo-slider hero-slider" x-data="carouselSlider({{ $heroSlides->count() }})">
        <div class="slider-wrapper h-full">
            <div class="slider-container h-full" x-ref="sliderContainer">
                @foreach($heroSlides as $slide)
                    <div class="slide">
                        @if($slide->click_type === 'image')
                            <a href="{{ $slide->button_url ?? '#' }}" class="absolute inset-0 z-[10]" aria-label="{{ $slide->alt_text ?: $slide->title }}"></a>
                        @endif

                        <img src="{{ $slide->effective_image_url }}"
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
                            @if($slide->click_type !== 'image' && (!empty($slide->button_text) || !empty($slide->button_text_en)))
                                <div class="-mt-2 w-full flex justify-center">
                                    <a href="{{ $slide->button_url ?? '#' }}" 
                                       class="inline-block bg-white text-[#6d0e16] px-6 py-2 rounded-full font-bold text-base hover:bg-gray-100 transition shadow-lg mx-auto">
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
</section>
@endif


@php
    $primaryCategories2 = $primaryCategories2
        ?? \App\Models\PrimaryCategory::query()
            ->active()
            ->withCount('products')
            ->ordered()
            ->get();
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
                  setTimeout(() => {
                      this.updateButtons();
                      this.startAutoScroll();
                  }, 100);
              }
         }"
         x-init="init()">
<div class="w-full text-center relative z-10">


        <div class="relative">
            
            
            <button
                class="brand-nav hidden md:flex absolute left-4 pos-mid z-[10001]"
                :class="{'brand-nav-active': canGoLeft}"
                x-show="canGoLeft"
                x-cloak
                x-transition.opacity.duration.200ms
                type="button" aria-label="{{ __('common.prev') }}"
                @click="goLeft()">
                <i class="bi bi-chevron-left text-2xl"></i>
            </button>

            
            <button
                class="brand-nav hidden md:flex absolute right-4 pos-mid z-[10001]"
                :class="{'brand-nav-active': canGoRight}"
                x-show="canGoRight"
                x-cloak
                x-transition.opacity.duration.200ms
                type="button" aria-label="{{ __('common.next') }}"
                @click="goRight()">
                <i class="bi bi-chevron-right text-2xl"></i>
            </button>

            

<div class="overflow-x-auto no-scrollbar js-auto-bounce" x-ref="catScroll">
    
    <div class="flex flex-row gap-[14px] md:gap-12 items-start w-max py-1 auto-bounce-track" x-ref="catTrack">
        
        <div class="pulse-arrow pulse-arrow-left" x-show="canGoLeft" x-cloak><i class="bi bi-chevron-left"></i></div>
        <div class="pulse-arrow pulse-arrow-right" x-show="canGoRight" x-cloak><i class="bi bi-chevron-right"></i></div>

@foreach($primaryCategories2 as $pc)
    @php
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


<a href="{{ route('shop', ['brand' => $pc->slug]) }}"
   class="flex flex-col items-center min-w-[68px] md:min-w-[110px] group text-center transition-all duration-300">
            <div class="w-28 h-28 rounded-full overflow-hidden border-4 border-white shadow-lg bg-white relative">
                <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                @if($thumb)
                    <img src="{{ $thumb }}" class="w-full h-full object-cover object-center"
                         alt="{{ $pc->name_translated }}" width="112" height="112">
                @else
                    <div class="w-full h-full grid place-items-center text-[#6d0e16] bg-white">
                        <i class="bi bi-tags" style="font-size:1.6rem;"></i>
                    </div>
                @endif
            </div>
            <h3 class="category-name mt-1 md:mt-2 text-base font-semibold group-hover:text-[#6d0e16]">{{ $pc->name_translated }}</h3>
        </a>
    @endif
@endforeach
            </div>
        </div>
        </div>
    </div>

    
    <style>
        .section-cats { --icon-size: 112px; --row-py: 16px; --btn-dy: 0px; }
        .pos-mid { top: calc(var(--row-py) + (var(--icon-size) / 2) + var(--btn-dy)); transform: translateY(-50%); }
        .edge-hit { --edge-w: clamp(48px, 10vw, 120px); width: var(--edge-w); background: transparent; border: 0; padding: 0; cursor: pointer; }
        .section-cats .overflow-x-auto { scroll-snap-type: x mandatory; overflow-y: visible !important; }
        .section-cats .overflow-x-auto .flex>a { scroll-snap-align: start; }
        .section-cats .category-name { text-align:center; white-space:normal; word-break:break-word; line-height:1.35; min-height:2.7em; max-width:110px; margin-inline:auto; }
        .section-cats .w-28.h-28 { width:7rem!important; height:7rem!important; border-radius:50%; }
        @media (max-width: 640px) { .section-cats .w-28.h-28 { width:4.2rem!important; height:4.2rem!important; } }
        
        
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

        
        .brand-nav-active {
            color: #6d0e16;
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

        
        .brand-nav i {
            font-size: 1.4rem;
            transition: all 0.3s ease;
        }

        .brand-nav-active:hover i {
            transform: scale(1.15);
        }

        
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



@if(($promoPrimarySlides ?? collect())->isNotEmpty())
<section class="container mx-auto px-4">

    <div class="promo-slider promo-primary-slider" x-data="carouselSlider(3)">
    <div class="slider-wrapper">
      <div class="slider-container" x-ref="sliderContainer">
        @foreach($promoPrimarySlides as $slide)
            <div class="slide">
                @if($slide->click_type === 'image')
                    <a href="{{ $slide->button_url ?? '#' }}" class="absolute inset-0 z-[10]" aria-label="{{ $slide->alt_text ?: $slide->title }}"></a>
                @endif
              <img src="{{ $slide->effective_image_url }}"
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
                @if($slide->click_type !== 'image' && ($slide->button_text || $slide->button_text_en))
                    <div class="-mt-2">
                        <a href="{{ $slide->button_url ?: '#' }}"
                           class="inline-block bg-white text-[#6d0e16] px-5 py-1.5 rounded-full font-bold text-sm hover:bg-gray-100 transition">
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
</section>
@endif


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

    

    
    <div class="overflow-x-auto no-scrollbar js-auto-bounce" x-ref="catScroll">
        
        <div class="flex flex-row gap-[14px] md:gap-12 items-start w-max py-1 auto-bounce-track" x-ref="catTrack">
            
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
                            <div class="w-full h-full grid place-items-center text-[#6d0e16] bg-white">
                                <i class="bi bi-tags" style="font-size:1.6rem;"></i>
                            </div>
                        @endif
                    </div>
                    <h3 class="category-name mt-1 md:mt-2 text-base font-semibold group-hover:text-[#6d0e16]">{{ $category->name_translated }}</h3>
                </a>
            @endforeach

        </div>
    </div>

    
    
    <button type="button" x-cloak class="cat-side-nav-glass inline-flex absolute top-1/2 left-4 -translate-y-1/2 z-[5]"
            :class="{'cat-nav-active': showLeftButton}"
            x-show="!isMobile && showLeftButton"
            x-transition.opacity.duration.200ms
            aria-label="{{ __('common.prev') }}"
            @click="goLeft()">
        <i class="bi bi-chevron-left text-base md:text-lg"></i>
    </button>
    
    <button type="button" x-cloak class="cat-side-nav-glass inline-flex absolute top-1/2 right-4 -translate-y-1/2 z-[5]"
            :class="{'cat-nav-active': showRightButton}"
            x-show="!isMobile && showRightButton"
            x-transition.opacity.duration.200ms
            aria-label="{{ __('common.next') }}"
            @click="goRight()">
        <i class="bi bi-chevron-right text-base md:text-lg"></i>
    </button>
    
    
    <style>
        .section-cats .overflow-x-auto { scroll-snap-type: x mandatory; overflow-y: visible !important; }
        .section-cats .overflow-x-auto .flex>a { scroll-snap-align: start; }
        .section-cats .w-28.h-28 { width:7rem!important; height:7rem!important; border-radius:50%; }
        @media (max-width: 640px) { .section-cats .w-28.h-28 { width:4.2rem!important; height:4.2rem!important; } }
        .section-cats .category-name { text-align:center; white-space:normal; word-break:break-word; line-height:1.35; min-height:2.7em; max-width:110px; margin-inline:auto; }
        @media (max-width: 640px) { .section-cats .category-name { font-size: 0.75rem!important; max-width: 75px!important; min-height: 2.2em; } }
        
        
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

        
        .cat-nav-active {
            color: #6d0e16;
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

        
        .cat-side-nav-glass i {
            font-size: 1.3rem;
            transition: all 0.3s ease;
        }

        .cat-nav-active:hover i {
            transform: scale(1.15);
        }
        
        
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




@if($newProducts->isNotEmpty())
<section class="py-6 lg:py-10 bg-white relative" dir="rtl">
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
                            
                            @php
                                $isAvailable = ($product->stock_qty ?? $product->stock_quantity ?? 0) > 0;
                                $isOnSale = $product->isOnSale();
                                $discountPercentage = ($isOnSale && $product->price > 0)
                                    ? round((($product->price - $product->sale_price) / $product->price) * 100)
                                    : null;
                                $secondImage = optional($product->images->get(1))->image_path;
                            @endphp

                            
                            @if(!$isAvailable)
                                <div class="absolute inset-0 bg-black/60 z-10 flex items-center justify-center pointer-events-none">
                                    <span class="text-white font-bold tracking-wider text-sm border border-white/50 rounded-full px-3 py-1">
                                        {{ __('common.out_of_stock') }}
                                    </span>
                                </div>
                            @endif

                            
                            @if($isOnSale && $isAvailable && $discountPercentage > 0)
                                <div class="product-sale-badge">-{{ $discountPercentage }}%</div>
                            @endif

                            
                            <div class="product-image-slider">
                                
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
                
            @endforeach
        </div>
    </div>
</section>
@endif

@if(($promoSecondarySlides ?? collect())->isNotEmpty())
<section class="container mx-auto px-4">
    <div class="promo-slider promo-primary-slider" x-data="carouselSlider(2)">
        <div class="slider-wrapper">
            <div class="slider-container" x-ref="sliderContainer">
                @foreach($promoSecondarySlides as $slide)
                    <div class="slide">
                        @if($slide->click_type === 'image')
                            <a href="{{ $slide->button_url ?? '#' }}" class="absolute inset-0 z-[10]" aria-label="{{ $slide->alt_text ?: $slide->title }}"></a>
                        @endif
                        <img src="{{ $slide->effective_image_url }}"
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
                            @if($slide->click_type !== 'image' && ($slide->button_text || $slide->button_text_en))
                                <div class="-mt-2">
                                    <a href="{{ $slide->button_url ?: '#' }}" class="inline-block bg-white text-[#6d0e16] px-5 py-1.5 rounded-full font-bold text-sm hover:bg-gray-100 transition">
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
</section>
@endif


@if($saleProducts->isNotEmpty())
<section class="py-12 bg-white relative" dir="rtl">
<div class="container mx-auto px-4 relative z-10">
        <div class="flex justify-between items-center mb-6">
            <div class="section-header relative">
                <h2 class="text-3xl font-bold" style="color: var(--text);">{{ __('home.featured_offers') }}</h2>
            </div>
            <a href="{{ route('shop', ['on_sale' => 'true']) }}" class="text-sm font-semibold hover:underline" style="color: var(--primary-color);">{{ __('common.view_all') }} <i class="bi bi-arrow-left-short"></i></a>
        </div>
        <div class="products-grid">
            @foreach($saleProducts->take(14) as $product)
                
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
                            
                            @php
                                $isAvailable = ($product->stock_qty ?? $product->stock_quantity ?? 0) > 0;
                                $isOnSale = $product->isOnSale();
                                $discountPercentage = ($isOnSale && $product->price > 0)
                                    ? round((($product->price - $product->sale_price) / $product->price) * 100)
                                    : null;
                                $secondImage = optional($product->images->get(1))->image_path;
                            @endphp

                            
                            @if(!$isAvailable)
                                <div class="absolute inset-0 bg-black/60 z-10 flex items-center justify-center pointer-events-none">
                                    <span class="text-white font-bold tracking-wider text-sm border border-white/50 rounded-full px-3 py-1">
                                        {{ __('common.out_of_stock') }}
                                    </span>
                                </div>
                            @endif

                            
                            @if($isOnSale && $isAvailable && $discountPercentage > 0)
                                <div class="product-sale-badge">-{{ $discountPercentage }}%</div>
                            @endif

                            
                            <div class="product-image-slider">
                                
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
                
            @endforeach
        </div>
    </div>
</section>
@endif


@if($bestSellingProducts->isNotEmpty())
<section class="py-12 bg-white relative" dir="rtl">
<div class="container mx-auto px-4 relative z-10">
        <div class="flex justify-between items-center mb-6">
            <div class="section-header relative">
                <h2 class="text-3xl font-bold" style="color: var(--text);">{{ __('home.best_selling') }}</h2>
            </div>
            <a href="{{ route('shop') }}" class="text-sm font-semibold hover:underline" style="color: var(--primary-color);">{{ __('common.view_all') }} <i class="bi bi-arrow-left-short"></i></a>
        </div>
        <div class="products-grid">
            @foreach($bestSellingProducts->take(14) as $product)
                
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
                            
                            @php
                                $isAvailable = ($product->stock_qty ?? $product->stock_quantity ?? 0) > 0;
                                $isOnSale = $product->isOnSale();
                                $discountPercentage = ($isOnSale && $product->price > 0)
                                    ? round((($product->price - $product->sale_price) / $product->price) * 100)
                                    : null;
                                $secondImage = optional($product->images->get(1))->image_path;
                            @endphp

                            
                            @if(!$isAvailable)
                                <div class="absolute inset-0 bg-black/60 z-10 flex items-center justify-center pointer-events-none">
                                    <span class="text-white font-bold tracking-wider text-sm border border-white/50 rounded-full px-3 py-1">
                                        {{ __('common.out_of_stock') }}
                                    </span>
                                </div>
                            @endif

                            
                            @if($isOnSale && $isAvailable && $discountPercentage > 0)
                                <div class="product-sale-badge">-{{ $discountPercentage }}%</div>
                            @endif

                            
                            <div class="product-image-slider">
                                
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
                
            @endforeach
        </div>
    </div>
</section>
@endif

@php
    $homepageTestimonials = ($featuredReviews ?? collect())->values();
    $halfTestimonials = (int) ceil($homepageTestimonials->count() / 2);
    $topTestimonials = $homepageTestimonials->take($halfTestimonials);
    $bottomTestimonials = $homepageTestimonials->slice($halfTestimonials);
    if ($bottomTestimonials->isEmpty()) {
        $bottomTestimonials = $topTestimonials;
    }
@endphp

@if($homepageTestimonials->isNotEmpty())
<section class="testimonials-section py-6 md:py-8" dir="rtl">
    <div class="container mx-auto px-4">
        <div class="mb-4 md:mb-5 text-center">
            <h2 class="testimonials-title text-xl md:text-3xl">آراء العملاء</h2>
        </div>

        <div class="space-y-4 md:space-y-5">
            <div class="testimonials-marquee" data-marquee data-speed-multiplier="0.88" data-start-offset="-120" role="button" tabindex="0" aria-label="إيقاف أو تشغيل حركة آراء العملاء">
                <div class="testimonials-track testimonials-track-to-right">
                    @foreach($topTestimonials as $review)
                        @php
                            $testimonialName = $review->user?->name ?: 'عميل متجر توفف';
                            $testimonialAvatar = $review->user?->avatar_url ?: asset('storage/avatars/default.png');
                            $testimonialProduct = $review->product?->name_ar ?: ($review->product?->name_en ?: 'عميل موثوق');
                        @endphp
                        <article class="testimonial-card">
                            <header class="testimonial-card-head">
                                <img src="{{ $testimonialAvatar }}" alt="{{ $testimonialName }}" class="testimonial-avatar" onerror="this.onerror=null;this.src='{{ asset('storage/avatars/default.png') }}';">
                                <div>
                                    <h3 class="testimonial-name">{{ $testimonialName }}</h3>
                                    <p class="testimonial-role">{{ $testimonialProduct }}</p>
                                    <div class="testimonial-stars" aria-label="{{ (int) $review->rating }} من 5">
                                        @for($i=1; $i<=5; $i++)
                                            <i class="bi {{ $i <= (int) $review->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                                        @endfor
                                    </div>
                                </div>
                            </header>
                            <p class="testimonial-text">{{ \Illuminate\Support\Str::limit((string) $review->comment, 180) }}</p>
                        </article>
                    @endforeach
                </div>
            </div>

            <div class="testimonials-marquee" data-marquee role="button" tabindex="0" aria-label="إيقاف أو تشغيل حركة آراء العملاء">
                <div class="testimonials-track testimonials-track-to-right">
                    @foreach($bottomTestimonials as $review)
                        @php
                            $testimonialName = $review->user?->name ?: 'عميل متجر توفف';
                            $testimonialAvatar = $review->user?->avatar_url ?: asset('storage/avatars/default.png');
                            $testimonialProduct = $review->product?->name_ar ?: ($review->product?->name_en ?: 'عميل موثوق');
                        @endphp
                        <article class="testimonial-card">
                            <header class="testimonial-card-head">
                                <img src="{{ $testimonialAvatar }}" alt="{{ $testimonialName }}" class="testimonial-avatar" onerror="this.onerror=null;this.src='{{ asset('storage/avatars/default.png') }}';">
                                <div>
                                    <h3 class="testimonial-name">{{ $testimonialName }}</h3>
                                    <p class="testimonial-role">{{ $testimonialProduct }}</p>
                                    <div class="testimonial-stars" aria-label="{{ (int) $review->rating }} من 5">
                                        @for($i=1; $i<=5; $i++)
                                            <i class="bi {{ $i <= (int) $review->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                                        @endfor
                                    </div>
                                </div>
                            </header>
                            <p class="testimonial-text">{{ \Illuminate\Support\Str::limit((string) $review->comment, 180) }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
@endif

@push('styles')
<style>
  
  .home-blog .post-card{
    border:1px solid #ebe8e6; border-radius:.75rem; background:#fff;
    overflow:hidden; transition:transform .25s, box-shadow .25s;
  }
  .home-blog .post-card:hover{ transform:translateY(-3px); box-shadow:0 10px 20px rgba(0,0,0,.08); }
  .home-blog .post-image{
    width:100%; aspect-ratio:1/1; object-fit:cover; display:block; 
  }
  .home-blog .post-body{ padding:.9rem }
  .home-blog .post-title{ font-size:1rem; line-height:1.35; margin:.35rem 0 }
  .home-blog .post-excerpt{ font-size:.875rem }

  
  html.dark .home-blog .post-card{ background:#0f172a; border-color:#1f2937; box-shadow:0 8px 22px rgba(0,0,0,.22) }
  html.dark .home-blog .post-card p, html.dark .home-blog .post-card h3{ color:#e5e7eb }
  html.dark .home-blog .post-card .text-gray-500,
  html.dark .home-blog .post-card .text-gray-600{ color:#9ca3af!important }
</style>
@endpush
@push('styles')
<style>
  
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
        <a href="{{ url('/blog') }}" class="text-sm font-semibold text-[#6d0e16] hover:underline">{{ __('home.view_all_articles') }}</a>
    </div>

    
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

</div> 

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('carouselSlider', (initialSlides = 1) => ({
            currentSlide: 0,
            slides: initialSlides,
            startX: 0, currentX: 0, startY: 0, isDragging: false, sliderWidth: 0, autoSlideInterval: null,
            rtl: document.documentElement.dir === 'rtl',
            io: null,

            init() {
                this.$el.style.touchAction = 'pan-y'; // Prevent browser from interfering with horizontal drag
                this.sliderWidth = this.$el.offsetWidth;
                this.slides = this.$refs.sliderContainer?.children?.length || this.slides;
                this.goToSlide(this.currentSlide);
                this.startAutoSlide();

                this.$el.addEventListener('touchstart', this.handleTouchStart.bind(this), { passive: true });
                this.$el.addEventListener('touchmove', this.handleTouchMove.bind(this), { passive: false });
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
                window.addEventListener('resize', () => { this.sliderWidth = this.$el.offsetWidth; this.goToSlide(this.currentSlide); });
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
            handleTouchStart(e) { 
                this.startX = e.touches[0].clientX; 
                this.startY = e.touches[0].clientY;
                this.currentX = this.startX; 
                this.isDragging = true; 
                this.pauseAutoSlide(); 
            },
            handleTouchMove(e) { 
                if (!this.isDragging) return; 
                const dx = Math.abs(this.startX - e.touches[0].clientX);
                const dy = Math.abs(this.startY - e.touches[0].clientY);
                if (dx > dy && e.cancelable) {
                    e.preventDefault();
                }
                
                this.currentX = e.touches[0].clientX; 
                this.updateDragPosition(); 
            },
            handleTouchEnd() { if (!this.isDragging) return; this.isDragging = false; this.handleSwipe(); this.resumeAutoSlide(); },
            handleMouseDown(e) { this.startX = e.clientX; this.currentX = this.startX; this.isDragging = true; this.$el.style.cursor = 'grabbing'; this.pauseAutoSlide(); },
            handleMouseMove(e) { 
                if (!this.isDragging) return; 
                this.currentX = e.clientX; 
                this.updateDragPosition(); 
            },
            handleMouseUp() { if (!this.isDragging) return; this.isDragging = false; this.$el.style.cursor = 'grab'; this.handleSwipe(); this.resumeAutoSlide(); },
            pauseAutoSlide() { if (this.autoSlideInterval) { clearInterval(this.autoSlideInterval); this.autoSlideInterval = null; } },
            resumeAutoSlide() { this.startAutoSlide(); },
            updateDragPosition() {
                const diff = this.startX - this.currentX;
                let percentDiff = (diff / this.sliderWidth) * 100;
                
                const direction = this.rtl ? 1 : -1;
                const expectedTranslate = (direction * this.currentSlide * 100) - percentDiff;
                this.$refs.sliderContainer.style.transition = 'none';
                this.$refs.sliderContainer.style.transform = `translateX(${expectedTranslate}%)`;
            },
            handleSwipe() {
                const diff = this.startX - this.currentX;
                const threshold = Math.min(40, this.sliderWidth / 10);
                
                let newSlide = this.currentSlide;
                if (Math.abs(diff) > threshold) {
                    if ((diff > 0 && !this.rtl) || (diff < 0 && this.rtl)) { 
                        newSlide = this.currentSlide + 1; 
                    } else { 
                        newSlide = this.currentSlide - 1; 
                    }
                }
                if (newSlide >= this.slides) newSlide = 0; // Wrap to start
                if (newSlide < 0) newSlide = this.slides - 1; // Wrap to end
                
                this.currentSlide = newSlide;
                this.goToSlide(this.currentSlide);
            },
            goToSlide(index) {
                this.currentSlide = index;
                const direction = this.rtl ? 1 : -1;
                this.$refs.sliderContainer.style.transition = 'transform 0.4s cubic-bezier(0.25, 1, 0.5, 1)';
                this.$refs.sliderContainer.style.transform = `translateX(${direction * (this.currentSlide * 100)}%)`;
                setTimeout(() => { if (!this.isDragging) this.$refs.sliderContainer.style.transition = ''; }, 400);
            }
        }));
    });

    document.addEventListener('DOMContentLoaded', () => {
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        document.querySelectorAll('[data-marquee]').forEach((marquee) => {
            const track = marquee.querySelector('.testimonials-track');
            if (!track) {
                return;
            }

            const seedNodes = Array.from(track.children).map((node) => node.cloneNode(true));
            if (seedNodes.length === 0) {
                return;
            }

            const isLeftTrack = track.classList.contains('testimonials-track-to-left');
            const direction = isLeftTrack ? -1 : 1;
            const speedMultiplier = Number(marquee.dataset.speedMultiplier || '1') || 1;
            const startOffset = Number(marquee.dataset.startOffset || '0') || 0;
            let isPausedByHover = false;
            let rafId = null;
            let lastTs = 0;
            let offset = 0;
            let resizeTimeout = null;
            let tempPauseTimeout = null;

            const getSpeed = () => (window.innerWidth <= 768 ? 34 : 46) * speedMultiplier; // px / second
            const getGap = () => {
                const style = window.getComputedStyle(track);
                const gapValue = parseFloat(style.columnGap || style.gap || '0');
                return Number.isFinite(gapValue) ? gapValue : 0;
            };

            const getForwardStep = () => {
                const first = track.children[0];
                const second = track.children[1];
                if (!first) {
                    return 0;
                }

                if (!second) {
                    return first.getBoundingClientRect().width + getGap();
                }

                return second.getBoundingClientRect().left - first.getBoundingClientRect().left;
            };

            const getBackwardStep = () => {
                const count = track.children.length;
                const last = track.children[count - 1];
                const prev = track.children[count - 2];
                if (!last) {
                    return 0;
                }

                if (!prev) {
                    return last.getBoundingClientRect().width + getGap();
                }

                return last.getBoundingClientRect().left - prev.getBoundingClientRect().left;
            };

            const ensureFilledTrack = () => {
                track.innerHTML = '';

                seedNodes.forEach((node) => {
                    track.appendChild(node.cloneNode(true));
                });

                const minWidth = marquee.clientWidth * 3.4;
                let safety = 0;
                while (track.scrollWidth < minWidth && safety < 8) {
                    seedNodes.forEach((node) => {
                        track.appendChild(node.cloneNode(true));
                    });
                    safety++;
                }

                offset = startOffset;

                if (isLeftTrack) {
                    recycleForLeft();
                } else {
                    recycleForRight();
                }

                track.style.transform = 'translate3d(0,0,0)';
                track.style.transform = `translate3d(${offset}px,0,0)`;
            };

            const isPaused = () => isPausedByHover || marquee.classList.contains('is-paused');

            const pauseTemporarily = (duration = 2000) => {
                marquee.classList.add('is-paused');

                if (tempPauseTimeout) {
                    clearTimeout(tempPauseTimeout);
                }

                tempPauseTimeout = setTimeout(() => {
                    marquee.classList.remove('is-paused');
                    tempPauseTimeout = null;
                }, duration);
            };

            const recycleForLeft = () => {
                let threshold = getForwardStep();
                while (threshold > 0 && -offset >= threshold) {
                    const first = track.firstElementChild;
                    if (!first) {
                        break;
                    }

                    track.appendChild(first);
                    offset += threshold;
                    threshold = getForwardStep();
                }
            };

            const recycleForRight = () => {
                let threshold = getBackwardStep();
                while (threshold > 0 && offset >= threshold) {
                    const last = track.lastElementChild;
                    if (!last) {
                        break;
                    }

                    track.insertBefore(last, track.firstElementChild);
                    offset -= threshold;
                    threshold = getBackwardStep();
                }
            };

            const animate = (ts) => {
                if (!lastTs) {
                    lastTs = ts;
                }

                const dt = (ts - lastTs) / 1000;
                lastTs = ts;

                if (!prefersReducedMotion && !isPaused()) {
                    offset += direction * getSpeed() * dt;

                    if (isLeftTrack) {
                        recycleForLeft();
                    } else {
                        recycleForRight();
                    }

                    track.style.transform = `translate3d(${offset}px,0,0)`;
                }

                rafId = window.requestAnimationFrame(animate);
            };

            ensureFilledTrack();

            marquee.addEventListener('mouseenter', () => {
                isPausedByHover = true;
            });

            marquee.addEventListener('mouseleave', () => {
                isPausedByHover = false;
            });

            marquee.addEventListener('click', () => {
                pauseTemporarily(2000);
            });

            marquee.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    pauseTemporarily(2000);
                }
            });

            window.addEventListener('resize', () => {
                if (resizeTimeout) {
                    clearTimeout(resizeTimeout);
                }
                resizeTimeout = setTimeout(() => {
                    ensureFilledTrack();
                }, 120);
            });

            rafId = window.requestAnimationFrame(animate);

            marquee.addEventListener('remove', () => {
                if (tempPauseTimeout) {
                    clearTimeout(tempPauseTimeout);
                    tempPauseTimeout = null;
                }
                if (rafId) {
                    cancelAnimationFrame(rafId);
                }
            });
        });
    });
</script>
@endpush

@endsection
