@php
    use App\Models\Setting;
    use App\Models\Category;
    use App\Models\PrimaryCategory;
    use Illuminate\Support\Facades\App;
    use Illuminate\Support\Facades\Cache;
    use Illuminate\Support\Str;

    // Apply locale from session/cookie directly in blade context
    try {
        $sessionLocale = (string)(session('locale') ?? request()->cookie('app_locale') ?? config('app.locale', 'ar'));
    } catch (\Exception $e) {
        $sessionLocale = 'ar';
    }
    $availableLocales = ['ar', 'en'];
    if (!in_array($sessionLocale, $availableLocales)) { $sessionLocale = 'ar'; }
    App::setLocale($sessionLocale);

    $locale = app()->getLocale();
    $dir = in_array($locale, ['ar']) ? 'rtl' : 'ltr';

    try {
        \Illuminate\Support\Facades\Log::info('BLADE LOCALE: ' . $locale . ' | Session: ' . (session()->has('locale') ? session('locale') : 'NONE'));
    } catch (\Exception $e) {}

    try {
        $seo = Setting::whereIn('key', [
            'site_title','meta_description',
            'site_title_ar','site_title_en',
            'meta_description_ar','meta_description_en',
            'site_url'
        ])->pluck('value','key');
    } catch (\Exception $e) {
        $seo = collect();
    }

    $siteTitle = $locale === 'ar'
        ? ($seo['site_title_ar'] ?? $seo['site_title'] ?? 'طفوف | اكسسوارات فاخرة أصلية')
        : ($seo['site_title_en'] ?? $seo['site_title'] ?? 'Tofof | Premium Authentic Accessories');

    $siteName = trim(explode('|', $siteTitle)[0]);

    $metaDescription = $locale === 'ar'
        ? ($seo['meta_description_ar'] ?? $seo['meta_description'] ?? 'اكتشف أرقى الاكسسوارات الأصلية مع طفوف. ساعات، محافظ، ونظارات شمسية بضمان كامل.')
        : ($seo['meta_description_en'] ?? $seo['meta_description'] ?? 'Discover premium authentic accessories at Tofof. Watches, wallets, sunglasses and more.');

    $canonical = $seo['site_url'] ?? null;

    try {
        $categories = Cache::remember('global_categories', now()->addHours(6), function () {
            return Category::whereNull('parent_id')
                ->with('children')
                ->withCount('products')
                ->get()
                ->each(function ($category) {
                    $total = (int)$category->products_count;
                    if ($category->children) {
                        foreach ($category->children as $child) {
                            $childTotal = (int)$child->products_count;
                            if ($child->children) {
                                foreach ($child->children as $grandChild) {
                                    $childTotal += (int)$grandChild->products_count;
                                    $grandChild->total_products_count = (int)$grandChild->products_count;
                                }
                            }
                            $child->total_products_count = $childTotal;
                            $total += $childTotal;
                        }
                    }
                    $category->total_products_count = $total;
                });
        });
    } catch (\Exception $e) {
        $categories = collect();
    }

    $showDashboardLink = false;
    try {
        if (auth()->check()) {
            $u = auth()->user();
            $hasSuper = method_exists($u, 'hasRole') && $u->hasRole('super-admin');
            $hasNonUserRole = $u->roles && $u->roles->where('name', '!=', 'user')->isNotEmpty();
        $hasAnyPerms = method_exists($u, 'canAccessAdminPanel')
          ? $u->canAccessAdminPanel()
          : (method_exists($u, 'permissions') && $u->permissions()->exists());
            $showDashboardLink = $hasSuper || $hasNonUserRole || $hasAnyPerms;
        }
    } catch (\Exception $e) {
        $showDashboardLink = false;
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}" dir="{{ $dir }}" :class="{ 'dark': isDark }">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="theme-color" content="#6d0e16">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-title" content="{{ $siteName }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', $siteTitle)</title>
    <meta name="description" content="@yield('meta_description', $metaDescription)">
    <link rel="canonical" href="{{ $canonical ? rtrim($canonical,'/') : url()->current() }}" />

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo.png') }}">

    <script>
      window.__tofofDeferredInstallPrompt = null;
      window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        window.__tofofDeferredInstallPrompt = e;
      });
    </script>

    <meta property="og:site_name" content="{{ $locale === 'ar' ? ($seo['site_title_ar'] ?? $seo['site_title'] ?? 'طفوف') : ($seo['site_title_en'] ?? $seo['site_title'] ?? 'Tofof') }}">
    <meta property="og:title" content="@yield('title', $siteTitle)">
    <meta property="og:description" content="@yield('meta_description', $metaDescription)">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">

    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@graph": [
        {
          "@type": "Organization",
          "@id": "{{ $canonical ?? url('/') }}/#organization",
          "name": "{{ $locale === 'ar' ? ($seo['site_title_ar'] ?? $seo['site_title'] ?? 'طفوف') : ($seo['site_title_en'] ?? $seo['site_title'] ?? 'Tofof') }}",
          "alternateName": "{{ $siteTitle }}",
          "url": "{{ $canonical ?? url('/') }}/",
          "logo": "{{ asset('logo.png') }}",
          "image": "{{ asset('logo.png') }}",
          "telephone": "+9647744969024",
          "sameAs": [
            "https://www.instagram.com/tofof_watches",
            "https://www.facebook.com/p/%D8%B7%D9%81%D9%88%D9%81-%D9%84%D9%84%D8%B3%D8%A7%D8%B9%D8%A7%D8%AA-100091444293851/"
          ]
        },
        {
          "@type": "WebSite",
          "@id": "{{ $canonical ?? url('/') }}/#website",
          "url": "{{ $canonical ?? url('/') }}/",
          "name": "{{ $locale === 'ar' ? ($seo['site_title_ar'] ?? $seo['site_title'] ?? 'طفوف') : ($seo['site_title_en'] ?? $seo['site_title'] ?? 'Tofof') }}",
          "alternateName": "{{ $siteTitle }}",
          "publisher": {
            "@id": "{{ $canonical ?? url('/') }}/#organization"
          },
          "inLanguage": "{{ $locale === 'ar' ? 'ar-IQ' : 'en-US' }}"
        }
      ]
    }
    </script>

    <script>
      (function () {
        const saved = localStorage.getItem('theme');
        if (saved === 'dark') {
          document.documentElement.classList.add('dark');
        } else {
          document.documentElement.classList.remove('dark');
        }
      })();
    </script>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        "brand-bg": "#FFFFFF",
                        "brand-primary": "#D1A3A4",
                        "brand-secondary": "#F3E5E3",
                        "brand-dark": "#34282C",
                        "brand-text": "#34282C",
                        "brand-accent": "#6d0e16",
                        "brand-gray": "#6B7280",
                    },
                    animation: {
                        'heartbeat': 'heartbeat 1.5s ease-in-out infinite',
                        'bounce-slow': 'bounce 2s infinite',
                        'ping-once': 'ping 1s cubic-bezier(0, 0, 0.2, 1)',
                        'pulse-glow': 'pulse-glow 2s infinite ease-in-out',
                    },
                    keyframes: {
                        heartbeat: {
                            '0%, 100%': { transform: 'scale(1)' },
                            '50%': { transform: 'scale(1.2)' },
                        },
                        'pulse-glow': {
                          '0%, 100%': { opacity: 1, boxShadow: '0 0 0 0 rgba(190, 102, 97, 0.7)' },
                          '50%': { opacity: 0.8, boxShadow: '0 0 0 10px rgba(190, 102, 97, 0)' }
                        }
                    }
                },
            },
        };
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<style>
/* ===== الفوتر حامل شفاف ===== */
footer.footer-mobile {
  background: transparent !important;
  border: 0 !important;
  box-shadow: none !important;
  height: auto !important;
  padding: 0 !important;
}

/* ترك مساحة أسفل الصفحة للـnav */
@media (max-width: 1023px){
  body{ padding-bottom: calc(84px + env(safe-area-inset-bottom)) !important; }
}

/* wrap داخل الفوتر */
footer.footer-mobile .glass-nav-wrap{
  padding: 12px 16px;
}

/* ===== Glass Nav ===== */
.glass-nav{
  width: 100%; height: 64px; border-radius: 24px; overflow:hidden; position:relative;
  background: linear-gradient(180deg, rgba(255,255,255,.55), rgba(255,255,255,.35));
  border: 1px solid rgba(255,255,255,.35);
  box-shadow: 0 8px 26px rgba(0,0,0,.12);
  backdrop-filter: blur(18px); -webkit-backdrop-filter: blur(18px);
}
html.dark .glass-nav{
  background: linear-gradient(180deg, rgba(15,23,42,.55), rgba(15,23,42,.35));
  border-color: rgba(148,163,184,.18);
  box-shadow: 0 10px 28px rgba(0,0,0,.45);
}

/* Blobs */
.glass-nav::before, .glass-nav::after{
  content:""; position:absolute; width:220px; height:220px; border-radius:50%;
  filter: blur(30px); opacity:.35; pointer-events:none; animation: floatBlob 10s ease-in-out infinite;
}
.glass-nav::before{ right:-80px; bottom:-120px;
  background: radial-gradient(closest-side, rgba(205,137,133,.55), transparent 70%); animation-delay:-3s;
}
.glass-nav::after{ left:-90px; top:-130px;
  background: radial-gradient(closest-side, rgba(190,102,97,.45), transparent 70%);
}
@keyframes floatBlob{
  0%{transform:translate(0,0) scale(1)}
  50%{transform:translate(20px,-14px) scale(1.05)}
  100%{transform:translate(0,0) scale(1)}
}

/* Items */
.glass-items{ display:grid; grid-template-columns: repeat(5, 1fr); height:100%; position:relative; z-index:2; }
.glass-item{
  display:flex; flex-direction:column; align-items:center; justify-content:center; gap:4px;
  font-weight:600; font-size:11px; color:#6b7280; transition: transform .18s ease, color .18s ease;
  position: relative;
  z-index: 2;
}
.glass-item .icon{ font-size:20px; line-height:1; transition: transform .28s cubic-bezier(.22,.61,.36,1); }
.glass-item .glass-label {
  transition: opacity .24s ease, transform .24s ease;
  opacity: .84;
  transform: none;
}
.glass-item:active{ transform: translateY(1px) scale(.98); }
.glass-item.active{ color:#6d0e16; }
.glass-item.active .icon { transform: none; }
.glass-item.active .glass-label {
  opacity: 1;
  transform: none;
}
html.dark .glass-item{ color:#cbd5e1; } 
html.dark .glass-item.active{ color:#f0b0ad; }

/* تحسين الحجم للأيباد والتابلت */
@media (min-width: 768px) and (max-width: 1023px) {
  .glass-nav { height: 74px; border-radius: 28px; }
  .glass-item { font-size: 14px; gap: 6px; }
  .glass-item .icon { font-size: 26px; }
}

.glass-indicator {
  position: absolute;
  inset-inline-start: 0;
  top: 6px;
  bottom: 6px;
  width: 20%;
  border-radius: 18px;
  background: transparent;
  box-shadow: none;
  transform: translateX(calc(var(--glass-index, 0) * 100%));
  transition: transform .34s cubic-bezier(.22,.61,.36,1);
  pointer-events: none;
  z-index: 1;
}

html.dark .glass-indicator {
  background: transparent;
  box-shadow: none;
}

.glass-items.no-animate .glass-indicator,
.glass-items.no-animate .glass-item .icon,
.glass-items.no-animate .glass-item .glass-label {
  transition: none !important;
}

/* ===== معالجة RTL للـ Footer Mobile ===== */
html[dir="rtl"] .glass-nav::before {
  right: auto;
  left: -80px;
}

html[dir="rtl"] .glass-nav::after {
  left: auto;
  right: -90px;
}

html[dir="rtl"] .glass-indicator {
  inset-inline-start: auto;
  inset-inline-end: 0;
  transform: translateX(calc(var(--glass-index, 0) * -100%));
}

@media (max-width: 767px) {
  html[dir="rtl"] .glass-item .glass-label {
    direction: rtl;
  }
}
</style>

    <style>
        html, body { margin: 0 !important; padding: 0 !important; }
        html {
          scroll-behavior: auto;
          scrollbar-gutter: stable;
        }
        @media (hover: hover) and (pointer: fine) {
          html {
            scroll-behavior: smooth;
          }
        }
        body { font-family: "Tajawal", "Cairo", sans-serif; background-color: #f7f7f7; color: #1a1a1a; }
        .dark body { background-color: #0a0a0a !important; color: #ffffff !important; }

        html::-webkit-scrollbar {
          width: 12px;
        }
        html::-webkit-scrollbar-track {
          background: transparent;
        }
        html::-webkit-scrollbar-thumb {
          background: linear-gradient(180deg, rgba(109,14,22,0.78), rgba(205,137,133,0.62));
          border-radius: 999px;
          border: 3px solid transparent;
          background-clip: content-box;
          transition: opacity .25s ease, background-color .25s ease;
        }
        html::-webkit-scrollbar-thumb:hover {
          background: linear-gradient(180deg, rgba(109,14,22,0.95), rgba(205,137,133,0.82));
          background-clip: content-box;
        }
        html.dark::-webkit-scrollbar-thumb {
          background: linear-gradient(180deg, rgba(240,176,173,0.85), rgba(109,14,22,0.7));
          background-clip: content-box;
        }

        body > .sticky.top-0.z-40 {
          top: 0 !important;
          margin-top: 0 !important;
        }

        #mobileHeader,
        #desktopNav {
          margin-top: 0 !important;
        }

        header { margin-bottom: 0 !important; }
        main, section { margin-top: 0 !important; padding-top: 0 !important; }

        .footer-mobile { background-color: #FFFFFF; border-top: 1px solid #E5E7EB; box-shadow: 0 -2px 10px rgba(0,0,0,0.1); }
        .dark .footer-mobile { background-color: #0f172a; border-top-color: #1f2937; box-shadow: 0 -2px 10px rgba(0,0,0,0.4); }
        .footer-mobile a { transition: all 0.3s ease; position: relative; overflow: hidden; }
        .dark .footer-mobile a { color: #e5e7eb; }
        .footer-mobile a.active { color: #6d0e16; font-weight: bold; }
        .dark .footer-mobile a.active { color: #f0b0ad; }
        .footer-mobile a::after { content: ''; position: absolute; bottom: 0; left: 0; width: 100%; height: 3px; background-color: #6d0e16; transform: scaleX(0); transition: transform 0.3s ease; }
        .dark .footer-mobile a::after { background-color: #f0b0ad; }
        .footer-mobile a.active::after { transform: scaleX(1); }
        .footer-mobile .icon { font-size: 1.5rem; margin-bottom: 0.25rem; }
        /* حذف تأثير الأيقونات */
        .footer-mobile a:hover .icon { transform: none; }

        .badge, .dark .badge, .dark .badge-cart { position: absolute; top: -5px; right: -5px; background-color: #d59e06 !important; color: #fff !important; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.1); z-index: 10; }

        /* ===== معالجة RTL للـ Footer Mobile Underline ===== */
        html[dir="rtl"] .footer-mobile a::after {
          left: auto;
          right: 0;
        }

        .mobile-nav-item.active { background: linear-gradient(to top, rgba(190, 102, 97, 0.1), transparent); }
        
        .nav-consultation {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 9999px;
            animation: pulse-glow 2s infinite ease-in-out;
        }
        header.mobile-rounded {
          border-bottom-left-radius: 30px;
          border-bottom-right-radius: 30px;
        }

        /* تحسين الهيدر للأيباد */
        @media (min-width: 768px) and (max-width: 1023px) {
          #mobileHeader .text-lg { font-size: 1.4rem; }
          #mobileHeader .w-8.h-8 { width: 2.5rem; height: 2.5rem; }
          #mobileHeader .w-9.h-9 { width: 3rem; height: 3rem; }
          #mobileHeader .bi { font-size: 1.4rem; }
          #mobileHeader .container { padding-left: 2rem; padding-right: 2rem; }
        }

    </style>
<style>
  [x-cloak] { display: none !important; }

  .store-badge-row {
    display: flex;
    flex-wrap: nowrap;
    gap: 0.7rem;
    justify-content: center;
    align-items: center;
  }
  .store-badge-btn {
    min-width: 160px;
    height: 52px;
    border-radius: 12px;
    border: 1px solid #333;
    background: #000;
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
    padding: 0.4rem 0.9rem;
    cursor: pointer;
    text-decoration: none;
    transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
  }
  .store-badge-btn:hover {
    transform: translateY(-2px);
    background: #1a1a1a;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
  }
  .store-badge-btn .store-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  .store-badge-btn .store-icon i {
    font-size: 1.7rem;
    color: #fff;
    line-height: 1;
  }
  .store-badge-btn .store-icon svg {
    width: 26px;
    height: 26px;
  }
  .store-badge-btn .text-wrap {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    line-height: 1.15;
  }
  .store-badge-btn .mini {
    font-size: 0.55rem;
    opacity: 0.85;
    letter-spacing: 0.04em;
    font-weight: 500;
    color: #fff;
    text-transform: uppercase;
  }
  .store-badge-btn .label {
    font-size: 1rem;
    font-weight: 700;
    color: #fff;
    white-space: nowrap;
  }
  .footer-bottom-bar {
    border-top: 1px solid #e5e5e5;
    padding-top: 1rem;
    padding-bottom: 0.75rem;
  }
  .dark .footer-bottom-bar {
    border-top-color: #1f2937;
  }
  .footer-bottom-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.9rem;
    align-items: center;
  }
  .footer-bottom-center {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    gap: 0.85rem;
    text-align: center;
    color: #6b7280;
    font-size: 1.02rem;
    font-weight: 700;
  }
  .footer-divider {
    color: #c5c7cc;
  }
  .footer-payment-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.62rem;
    flex-wrap: nowrap;
  }
  .payment-pill {
    background: transparent;
    border-radius: 14px;
    border: 0;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: auto;
    transition: transform 0.2s ease;
  }
  .payment-pill:hover {
    transform: translateY(-1px);
  }
  .payment-pill img {
    height: 21px;
    width: auto;
    object-fit: contain;
    display: block;
    filter: none;
  }
  .dark .payment-pill img {
    filter: none;
  }
  .dark .payment-pill img[alt="Zain Cash"] {
    filter: none;
  }
  .dark .payment-pill.pill-has-bg {
    background: #ffffff;
    border-radius: 10px;
    padding: 3px 8px;
  }
  @media (min-width: 1024px) {
    .footer-bottom-grid {
      grid-template-columns: 1fr auto 1fr;
      gap: 1.25rem;
    }
    .footer-bottom-bar {
      padding-bottom: 0.45rem;
    }
    .footer-left-col {
      justify-self: start;
    }
    .footer-center-col {
      justify-self: center;
    }
    .footer-right-col {
      justify-self: end;
    }
    .footer-bottom-center {
      font-size: 1.02rem;
    }
  }
  @media (max-width: 767px) {
    .store-badge-row {
      flex-wrap: nowrap;
      justify-content: center;
    }
    .store-badge-btn {
      min-width: 178px;
      height: 52px;
      padding: 0.5rem 0.68rem;
    }
    .footer-bottom-bar {
      padding-bottom: 0.75rem;
    }
    .footer-payment-row {
      flex-wrap: wrap;
    }
  }
  .ios-install-modal {
    background: rgba(0, 0, 0, 0.65);
    -webkit-backdrop-filter: blur(3px);
    backdrop-filter: blur(3px);
  }
  .ios-install-card {
    border-radius: 20px;
    background: #ffffff;
    border: 1px solid #ececec;
    box-shadow: 0 24px 52px rgba(0, 0, 0, 0.26);
  }
  html.dark .ios-install-card {
    background: #111827;
    border-color: #374151;
  }
  /* ===== إخفاء الفوتر الرئيسي عند فتح التطبيق كـ PWA ===== */
  @media (display-mode: standalone) {
    footer:not(.footer-mobile) {
      display: none !important;
    }
  }
</style>
    {{-- STYLES FOR CATEGORIES COMPONENT --}}
    <style>
      :root {
        --brand: #6d0e16;
        --brand-dark: #6d0e16;
        --line: #e5e5e5;
        --soft: #ffffff;
        --text: #1a1a1a;
        --text-light: #5f5f5f;
        --bg-light: #f7f7f7;
        --border: #e5e5e5;
        --shadow: rgba(0, 0, 0, 0.1);
        --transition: all 0.3s ease;
      }
      
      html.dark {
        --brand: #6d0e16;
        --brand-dark: #6d0e16;
        --line: #2a2a2a;
        --soft: #111111;
        --text: #ffffff;
        --text-light: #d6d6d6;
        --bg-light: #0a0a0a;
        --border: #2a2a2a;
        --shadow: rgba(0, 0, 0, 0.3);
      }

      .category-tree-container * {
        font-family: "Cairo", sans-serif !important;
        letter-spacing: 0 !important;
      }
      
      .category-tree { position: relative; }
      .category-node { margin-bottom: 1rem; position: relative; }
      
      .category-card { background: var(--bg-light); border-radius: 14px; box-shadow: 0 8px 18px var(--shadow); overflow: hidden; transition: var(--transition); border: 1px solid var(--border); display: flex; align-items: center; position: relative; }
      .category-card:hover { transform: translateY(-3px); box-shadow: 0 12px 22px rgba(0, 0, 0, 0.12); }
      .dark .category-card:hover { box-shadow: 0 12px 22px rgba(0, 0, 0, 0.3); }
      .category-card::before { content: ''; position: absolute; right: 0; top: 0; bottom: 0; width: 6px; background: linear-gradient(180deg, var(--brand) 0%, #e8b8b6 100%); border-top-right-radius: 14px; border-bottom-right-radius: 14px; }
      .dark .category-card::before { background: linear-gradient(180deg, var(--brand) 0%, var(--brand-dark) 100%); }
      
      .category-link { display: flex; align-items: center; padding: 1.25rem; flex: 1; text-decoration: none; color: inherit; }
      .category-icon { width: 60px; height: 60px; border-radius: 12px; overflow: hidden; flex-shrink: 0; margin-left: 1rem; background: var(--soft); display: flex; align-items: center; justify-content: center; border: 1px solid var(--line); }
      .icon-image { width: 100%; height: 100%; object-fit: cover; }
      .icon-placeholder { font-size: 1.5rem; color: var(--brand); }
      .category-name { margin: 0 0 0.5rem 0; font-size: 1.1rem; font-weight: 700; color: var(--text); }
      .category-meta { display: flex; flex-wrap: wrap; gap: 0.35rem; }
      .meta-item { display: inline-flex; align-items: center; gap: 0.35rem; height: 28px; padding: 0 0.65rem; background: var(--soft); border-radius: 999px; font-size: 0.8rem; color: var(--brand-dark); font-weight: 600; border: 1px solid var(--line); }
      
      .expand-btn { width: 38px; height: 38px; border-radius: 999px; background: var(--bg-light); border: 1px solid var(--line); color: var(--brand); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: var(--transition); box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05); margin: 0 1rem; }
      .expand-btn:hover { background: #fff3f3; border-color: #f3c6c3; color: var(--brand-dark); }
      .dark .expand-btn:hover { background: rgba(205, 137, 133, 0.1); border-color: var(--brand); color: var(--brand-dark); }
      .expand-btn i { transition: transform 0.3s ease; }
      .rotate-180 { transform: rotate(180deg); }

      .subcategories { margin-top: 1rem; margin-right: 1.5rem; position: relative; }
      .subcategory-list { list-style: none; padding: 0; margin: 0; }
      .subcategory-node { margin-bottom: 1rem; position: relative; }
      .subcategory-card { background: var(--bg-light); border-radius: 12px; box-shadow: 0 6px 14px var(--shadow); transition: var(--transition); border: 1px solid var(--border); display: flex; align-items: center; position: relative; }
      .subcategory-card::before { content: ''; position: absolute; right: 0; top: 0; bottom: 0; width: 5px; background: linear-gradient(180deg, #e8b8b6 0%, #f3c6c3 100%); border-top-right-radius: 12px; border-bottom-right-radius: 12px; }
      .dark .subcategory-card::before { background: linear-gradient(180deg, var(--brand) 0%, #6d0e16 100%); opacity:0.7; }
      .subcategory-link { display: flex; align-items: center; padding: 1rem; flex: 1; text-decoration: none; color: inherit; }
      .subcategory-icon { width: 50px; height: 50px; border-radius: 10px; flex-shrink: 0; margin-left: 0.75rem; background: var(--soft); display: flex; align-items: center; justify-content: center; border: 1px solid var(--line); }
      .subcategory-name { font-size: 1rem; font-weight: 700; color: var(--text); }

      .sub-subcategories { margin-top: 0.75rem; margin-right: 2.5rem; position: relative; }
      .sub-subcategory-list { list-style: none; padding: 0; margin: 0; }
      .sub-subcategory-node { margin-bottom: 0.75rem; }
      .sub-subcategory-card { background: var(--bg-light); border-radius: 10px; box-shadow: 0 4px 10px var(--shadow); border: 1px solid var(--border); display: flex; align-items: center; position: relative; }
      .sub-subcategory-card::before { content: ''; position: absolute; right: 0; top: 0; bottom: 0; width: 4px; background: linear-gradient(180deg, #f3c6c3 0%, #f9e6e3 100%); border-top-right-radius: 10px; border-bottom-right-radius: 10px; }
      .dark .sub-subcategory-card::before { background: linear-gradient(180deg, #6d0e16 0%, #6d0e16 100%); opacity:0.6; }
      .sub-subcategory-link { display: flex; align-items: center; padding: 0.75rem; flex: 1; text-decoration: none; color: inherit; }
      .sub-subcategory-icon { width: 40px; height: 40px; border-radius: 8px; flex-shrink: 0; margin-left: 0.5rem; background: var(--soft); display: flex; align-items: center; justify-content: center; border: 1px solid var(--line); }
      .sub-subcategory-name { font-size: 0.9rem; font-weight: 700; color: var(--text); }
    </style>

{{-- Live Search styles --}}
<style>
  /* ===== Blur Backdrop البحث — فقط خلف المحتوى وليس الهيدر ===== */
  .search-blur-backdrop {
    backdrop-filter: blur(16px) saturate(1.5);
    -webkit-backdrop-filter: blur(16px) saturate(1.5);
    background: rgba(0, 0, 0, 0.25);
    /* z-30 أقل من header (z-40) → الهيدر يبقى صافياً */
  }
  html.dark .search-blur-backdrop {
    background: rgba(0, 0, 0, 0.45);
  }

  .search-result-item {
    display:flex; align-items:center; gap:1rem;
    padding:.75rem 1rem; text-decoration:none;
    border-bottom:1px solid #f3e5e3; transition:background-color .2s ease;
  }
  .search-result-item:last-child{ border-bottom:none; }
  .search-result-img{ width:50px; height:50px; object-fit:cover; border-radius:.5rem; flex-shrink:0; background:#f3f4f6; }

  /* موبايل فقط */
  @media (max-width: 1023px){
    #mobileHeader{
      border-radius: 0;
      transition: border-radius .25s ease, box-shadow .25s ease;
    }
    #mobileHeader.rounded{
      border-top-left-radius: 0;
      border-top-right-radius: 0;
      border-bottom-left-radius: 28px;
      border-bottom-right-radius: 28px;
    }
  }
  #mobileHeader {
    transition: border-bottom-left-radius .25s ease, border-bottom-right-radius .25s ease, box-shadow .25s ease;
  }
  #mobileHeader.fast-off {
    transition: none !important;
  }
</style>

@php
    $hideDesktopFooterOnRoutes = ['passwords.*','verify.otp','profile.*', 'password.reset.phone.form', 'password.reset.with.otp.form', 'password.reset.with.otp', 'wallet.*', 'passwords.*', 'login', 'register', 'checkout.*', 'cart.*', 'orders.*', 'profile.addresses.*', 'profile.orders*', 'categories.index', 'wishlist'];
    $hideDesktopFooter = request()->routeIs($hideDesktopFooterOnRoutes);
@endphp

@if($hideDesktopFooter)
<style>
  footer:not(.footer-mobile){ display: none !important; }
</style>
@endif

    <style>
      @media (min-width: 1024px){
        #desktopNav {
          transition: opacity .3s ease, transform .3s ease;
          will-change: opacity, transform;
        }
        #desktopNav.fade-out {
          opacity: 0;
          transform: translateY(-10px);
          pointer-events: none;
        }

        .desktop-liquid-shell {
          border-radius: 0;
          background: #D4AF37;
          border: none;
          -webkit-backdrop-filter: blur(32px);
          backdrop-filter: blur(32px);
          box-shadow: 0 10px 30px rgba(0,0,0,.18);
        }

        .desktop-liquid-nav a,
        .desktop-liquid-nav button {
          color: #ffffff !important;
          font-weight: 700;
        }

        .desktop-liquid-nav a.active-link,
        .desktop-liquid-nav button.active-link {
          color: #ffffff !important;
        }
        .desktop-liquid-nav a.active-link span {
          width: 100% !important;
        }

        .desktop-liquid-nav a:hover,
        .desktop-liquid-nav button:hover {
          color: #fff0c0 !important;
        }

        .desktop-liquid-nav > a span {
          background-color: currentColor !important;
        }

        html.dark .desktop-liquid-shell {
          border-radius: 0;
          background: #D4AF37;
          border: none;
          box-shadow: 0 10px 30px rgba(0,0,0,.18);
        }

        html.dark .desktop-liquid-nav > a,
        html.dark .desktop-liquid-nav > div > button {
          color: #ffffff;
        }

        html.dark .desktop-liquid-nav > a:hover,
        html.dark .desktop-liquid-nav > div > button:hover {
          color: #fff0c0;
        }
      }
    </style>

    {{-- ==================================================================== --}}
    {{-- ====== START: STYLES FOR POPUPS, GLASS EFFECT, & SIDEBAR ====== --}}
    {{-- ==================================================================== --}}
    <style>
        .search-popup, .notification-popup {
          position: fixed;
          z-index: 9990 !important;
          background: rgba(255, 255, 255, 0.94) !important;
          -webkit-backdrop-filter: blur(32px);
          backdrop-filter: blur(32px);
          border: 1px solid rgba(255, 255, 255, 0.5);
          border-radius: 1.25rem;
          box-shadow: 0 20px 50px rgba(0,0,0,0.12);
          max-height: 70vh;
          overflow-y: auto;
        }

        .dark .search-popup, .dark .notification-popup {
          background: rgba(17, 24, 39, 0.94) !important;
          -webkit-backdrop-filter: blur(32px);
          backdrop-filter: blur(32px);
          border-color: rgba(255, 255, 255, 0.1);
          box-shadow: 0 20px 50px rgba(0,0,0,0.3);
        }

        .search-popup .search-result-item { border-bottom-color: rgba(190, 102, 97, 0.2) !important; }
        .dark .search-popup .search-result-item { border-bottom-color: rgba(240, 176, 173, 0.2) !important; }
        .search-popup .search-result-item:hover,
        .search-popup .search-result-item.highlighted { background-color: rgba(190, 102, 97, 0.1) !important; }
        .dark .search-popup .search-result-item:hover,
        .dark .search-popup .search-result-item.highlighted { background-color: rgba(240, 176, 173, 0.15) !important; }
        .search-popup .search-result-item:first-child { border-top-left-radius: 1rem; border-top-right-radius: 1rem; }
        .search-popup .search-result-item:last-child { border-bottom: 0 !important; border-bottom-left-radius: 1rem; border-bottom-right-radius: 1rem; }

        .notification-popup div, .notification-popup a { background: transparent !important; }
        .notification-popup a:hover { background-color: rgba(190, 102, 97, 0.1) !important; }
        .dark .notification-popup a:hover { background-color: rgba(240, 176, 173, 0.15) !important; }
        .notification-popup .border-b { border-color: rgba(190, 102, 97, 0.2) !important; }
        .dark .notification-popup .border-b { border-color: rgba(240, 176, 173, 0.2) !important; }
        .notification-popup [class*="bg-gray-50"] { background: rgba(190, 102, 97, 0.05) !important; }
        .dark .notification-popup [class*="bg-gray-900"] { background: rgba(240, 176, 173, 0.08) !important; }

        /* ===== Sidebar Glass Effect ===== */
        .sidebar-glass {
          background: rgba(249, 245, 241, 0.75); /* Light mode bg #0a0a0a */
          -webkit-backdrop-filter: blur(20px);
          backdrop-filter: blur(20px);
          border-left: 1px solid rgba(234, 220, 205, 0.4);
        }
        .dark .sidebar-glass {
          background: rgba(11, 15, 20, 0.75); /* Dark mode bg #0B0F14 */
          border-left-color: rgba(55, 65, 81, 0.3);
        }
        .sidebar-glass .sidebar-header {
          background-color: transparent !important;
          border-bottom-color: rgba(234, 220, 205, 0.25);
        }
        .dark .sidebar-glass .sidebar-header {
          border-bottom-color: rgba(55, 65, 81, 0.35);
        }
        .sidebar-glass .category-card,
        .sidebar-glass .subcategory-card,
        .sidebar-glass .sub-subcategory-card {
          background: rgba(254, 254, 254, 0.65) !important; /* var(--bg-light) */
          border-color: rgba(240, 240, 240, 0.5) !important;
        }
        .dark .sidebar-glass .category-card,
        .dark .sidebar-glass .subcategory-card,
        .dark .sidebar-glass .sub-subcategory-card {
          background: rgba(31, 41, 55, 0.65) !important; /* dark var(--bg-light) */
          border-color: rgba(55, 65, 81, 0.5) !important;
        }
    </style>
    {{-- ====== END: NEW STYLES ====== --}}

    <!-- Meta Pixel Code -->
    <script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '1188484969888183');
    fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
    src="https://www.facebook.com/tr?id=1188484969888183&ev=PageView&noscript=1"
    /></noscript>
    <!-- End Meta Pixel Code -->

    <style>
      .page-content-shell {
        position: relative;
        min-height: 40vh;
        isolation: isolate;
      }
      .home-shell-line {
        height: 16px;
        border-radius: 999px;
      }
      .home-shell-line.is-title {
        width: 32%;
        margin-bottom: 1rem;
      }
      .home-shell-categories {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 0.9rem;
      }
      .home-shell-chip {
        aspect-ratio: 1 / 1;
        border-radius: 14px;
      }
      .home-shell-products {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 1rem;
      }
      .home-shell-card {
        border-radius: 14px;
        padding: 10px;
        min-height: 248px;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
      }
      .home-shell-image {
        width: 100%;
        aspect-ratio: 1 / 1;
        border-radius: 12px;
        margin-bottom: 0.7rem;
      }
      .home-shell-card .home-shell-line {
        margin-bottom: 0.55rem;
      }
      .home-shell-card .home-shell-line.is-short {
        width: 56%;
      }
      @media (max-width: 1024px) {
        .home-shell-categories { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .home-shell-products { grid-template-columns: repeat(4, minmax(0, 1fr)); }
      }
      @media (max-width: 768px) {
        .home-shell-line.is-title { width: 52%; }
        .home-shell-categories { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .home-shell-products { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.85rem; }
        .home-shell-card { min-height: 210px; }
      }
    </style>

    @stack("styles")
</head>

<body
  class="relative flex flex-col min-h-screen pb-0 bg-[#f7f7f7] text-gray-900 dark:bg-[#0A0A0A] dark:text-white transition-colors duration-300"
    x-data="{
        wishlistCount: {{ auth()->check() ? auth()->user()->favorites()->count() : 0 }},
        cartCount: {{ count(session('cart', [])) }},
        isWishlistUpdated: false,
        isCartUpdated: false,
        showWelcome: @json(($show_welcome_screen ?? 'off') === 'on') && !sessionStorage.getItem('welcomeScreenShown'),
        isDark: document.documentElement.classList.contains('dark'),
        sidebarOpen: false,
        searchFocused: false,
        mobileSearchOpen: false,

        toggleTheme() {
            this.isDark = !this.isDark;
            document.documentElement.classList.toggle('dark', this.isDark);
            localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
        },

        closeWelcomeModal() {
            this.showWelcome = false;
            sessionStorage.setItem('welcomeScreenShown', 'true');
        }
    }"
    @wishlist-updated.window="wishlistCount = $event.detail.count; isWishlistUpdated = true; setTimeout(() => isWishlistUpdated = false, 500)"
    @cart-updated.window="cartCount = $event.detail.cartCount; isCartUpdated = true; setTimeout(() => isCartUpdated = false, 500)"
    x-init="
        fetch('{{ route('cart.count') }}').then(res => res.json()).then(data => { cartCount = data.count; }).catch(() => {});
        @auth
        fetch('{{ route('wishlist.count') }}').then(res => res.json()).then(data => { wishlistCount = data.count; }).catch(() => {});
        @endauth
    "
>
    @php
        $sessionErrorMessage = session('error') ?? ($errors->has('session') ? $errors->first('session') : null);
    @endphp

    <div class="sticky top-0 z-40">@if($sessionErrorMessage)<div x-data="{ show: true }" x-show="show" x-transition.opacity.duration.300ms class="bg-red-600 text-white"><div class="container mx-auto px-4 py-2 flex items-center justify-between gap-4 text-sm md:text-base"><span class="font-medium">{{ $sessionErrorMessage }}</span><button type="button" class="text-white/80 hover:text-white text-lg" @click="show = false">&times;</button></div></div>@endif 
        @if(isset($show_dashboard_notification) && $show_dashboard_notification == 'on' && !empty($dashboard_notification_content))
            <style>
                @keyframes scrollLeft {
                    0% { transform: translateX(0); }
                    100% { transform: translateX(-100%); }
                }
                @keyframes scrollRight {
                    0% { transform: translateX(0); }
                    100% { transform: translateX(100%); }
                }
                .animate-scroll-left {
                    display: inline-block;
                    white-space: nowrap;
                    padding-left: 100%;
                    animation: scrollLeft 20s linear infinite;
                }
                .animate-scroll-right {
                    display: inline-block;
                    white-space: nowrap;
                    padding-right: 100%;
                    animation: scrollRight 20s linear infinite;
                }
                .notification-container {
                    overflow: hidden;
                    width: 100%;
                    position: relative;
                }
                .notification-container:hover .animate-scroll-left,
                .notification-container:hover .animate-scroll-right {
                    animation-play-state: paused;
                }
            </style>
            <div x-data="{ show: true }" x-show="show" x-transition 
                 class="bg-black text-white p-2 text-sm relative overflow-hidden {{ ($dashboard_notification_animation ?? 'none') === 'none' ? 'text-center' : '' }}">
                <div class="notification-container">
                    <div class="{{ ($dashboard_notification_animation ?? 'none') !== 'none' ? 'animate-' . $dashboard_notification_animation : '' }}">
                        {!! $dashboard_notification_content !!}
                    </div>
                </div>
                <button @click="show = false" class="absolute top-1/2 left-4 -translate-y-1/2 text-xl z-20 hover:opacity-75 transition-opacity">&times;</button>
            </div>
        @endif
        <header id="mobileHeader" class="bg-[#6d0e16] py-3 border-b border-white/20 dark:border-white/15 shadow-md dark:shadow-black/40 relative overflow-visible">
            <div class="container mx-auto hidden lg:flex items-center justify-between px-4 md:px-8 text-white font-semibold" dir="ltr">
                <a href="{{ route('homepage') }}" class="hover:opacity-90 transition block">
                    <img src="{{ asset('sec-logo.png') }}" alt="logo" class="h-12 w-auto object-contain">
                </a>
                <div 
                    x-data="liveSearch('{{ route('products.liveSearch') }}')" 
                    @click.away="showResults = false"
                    class="flex flex-1 mx-6 max-w-2xl relative"
                    x-ref="searchContainerDesktop"
                >
                    <form action="{{ route('products.search') }}" method="GET" class="w-full md:w-[480px] lg:w-[640px]" @submit.prevent="if (highlightedIndex !== -1) selectHighlighted(); else $el.submit()">
                        <div class="flex w-full bg-white rounded-full overflow-hidden dark:bg-gray-800 dark:border dark:border-gray-700">
                            <input type="text" name="query" placeholder="{{ __('header.search_placeholder') }}" class="flex-1 px-4 py-2 text-sm text-gray-700 placeholder-gray-500 focus:outline-none dark:text-gray-100 dark:placeholder-gray-400 dark:bg-transparent"
                                x-model="query" @input.debounce.300ms="search" @keydown.down.prevent="moveHighlight('down')" @keydown.up.prevent="moveHighlight('up')"
                                @keydown.enter.prevent="if (highlightedIndex > -1) { selectHighlighted() } else { $el.closest('form').submit() }" @focus="onFocus(); searchFocused = true" @blur="searchFocused = false" autocomplete="off">
                    <button type="submit" class="px-4 bg-white text-[#6d0e16] hover:text-[#6d0e16] dark:bg-transparent"><i class="bi bi-search text-lg"></i></button>
                        </div>
                    </form>
                    <template x-teleport="body">
                        <div x-show="showResults" x-transition class="search-popup" x-cloak
                            x-init="$watch('showResults', (value) => { if (value) { $nextTick(() => { let rect = $refs.searchContainerDesktop.getBoundingClientRect(); $el.style.top = `${rect.bottom + 4}px`; $el.style.left = `${rect.left}px`; $el.style.width = `${rect.width}px`; }); } })">
                            <div x-show="loading" class="p-4 text-center text-gray-500 dark:text-gray-300">{{ __('layout.searching') }}</div>
                            <template x-if="!loading && results.length === 0 && query.length >= minChars"><div class="p-4 text-center text-gray-500 dark:text-gray-300">{{ __('layout.no_products_matching') }}</div></template>
                            <template x-for="(result, index) in results" :key="index">
                                <div>
                                    <template x-if="result.type === 'header'">
                                        <div class="px-4 py-2 text-xs font-bold uppercase tracking-wider text-gray-500 bg-gray-50/50 dark:bg-gray-900/50 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700">
                                            <span x-text="result.label"></span>
                                        </div>
                                    </template>
                                    <template x-if="result.type !== 'header'">
                                        <a :href="result.url" class="search-result-item" :class="{ 'highlighted': index === highlightedIndex }" @mouseenter="highlightedIndex = index">
                                            <template x-if="result.type === 'product'">
                                                <img :src="result.image" alt="" class="search-result-img">
                                            </template>
                                            <template x-if="result.type === 'brand'">
                                                <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700 mr-2">
                                                    <i class="bi bi-tag text-lg text-[#6d0e16]"></i>
                                                </div>
                                            </template>
                                            <template x-if="result.type === 'category'">
                                                <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700 mr-2">
                                                    <i class="bi bi-grid text-lg text-[#6d0e16]"></i>
                                                </div>
                                            </template>
                                            <div class="flex flex-col">
                                                <span class="text-gray-800 dark:text-gray-200 font-semibold" x-text="result.name"></span>
                                                <template x-if="result.type === 'product'">
                                                    <small class="text-gray-500 dark:text-gray-400" x-text="result.category || ''"></small>
                                                </template>
                                            </div>
                                        </a>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
                <div class="hidden md:flex items-center gap-4" dir="ltr">
                    @if($showDashboardLink)
                        <a href="{{ url('/admin/dashboard') }}" class="inline-flex items-center gap-2 rounded-full px-3 py-2 bg-white/10 hover:bg:white/20 transition" title="{{ __('layout.dashboard') }}">
                            <i class="bi bi-speedometer2 text-xl"></i><span class="hidden lg:inline">{{ __('layout.dashboard') }}</span>
                        </a>
                    @endif
                    {{-- Language Switcher Desktop --}}
                    <div x-data="{ langOpen: false }" class="relative" @click.away="langOpen = false">
                        <button @click="langOpen = !langOpen"
                            class="flex items-center gap-1.5 rounded-full px-3 py-1.5 bg-white/15 hover:bg-white/25 transition text-sm font-semibold"
                            title="{{ __('layout.change_language') }}">
                            @if($locale === 'ar')
                              <img src="{{ request()->root() }}/flags/iq.svg" class="w-5 h-3.5 object-cover rounded-sm shadow-sm" alt="Arabic"><span>ع</span>
                            @elseif($locale === 'en')
                              <img src="{{ request()->root() }}/flags/us.svg" class="w-5 h-3.5 object-cover rounded-sm shadow-sm" alt="English"><span>En</span>
                            @endif
                            <i class="bi bi-chevron-down text-xs" :class="{ 'rotate-180': langOpen }" style="transition: transform 0.2s"></i>
                        </button>
                          <div x-show="langOpen" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                              class="absolute top-full mt-2 {{ $dir === 'rtl' ? 'left-0' : 'right-0' }} w-36 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden z-[99999] text-gray-800 dark:text-gray-100">
                            @php $currentUrl = url()->current(); @endphp
                            <a href="{{ route('language.switch', ['locale' => 'ar', 'from' => $currentUrl]) }}" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-[#6d0e16]/10 transition {{ $locale === 'ar' ? 'font-bold text-[#6d0e16]' : '' }}">
                              <img src="{{ request()->root() }}/flags/iq.svg" class="w-5 h-3.5 object-cover rounded-sm shadow-sm" alt="Arabic"> عربي
                                @if($locale === 'ar') <i class="bi bi-check2 mr-auto text-[#6d0e16]"></i> @endif
                            </a>
                            <a href="{{ route('language.switch', ['locale' => 'en', 'from' => $currentUrl]) }}" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-[#6d0e16]/10 transition {{ $locale === 'en' ? 'font-bold text-[#6d0e16]' : '' }}">
                              <img src="{{ request()->root() }}/flags/us.svg" class="w-5 h-3.5 object-cover rounded-sm shadow-sm" alt="English"> English
                                @if($locale === 'en') <i class="bi bi-check2 mr-auto text-[#6d0e16]"></i> @endif
                            </a>
                          </div>
                    </div>
                    <button @click="toggleTheme" class="rounded-full w-10 h-10 inline-flex items-center justify-center bg-white/10 hover:bg.white/20 transition" title="{{ __('layout.toggle_theme') }}">
                      <i class="bi bi-moon-fill text-xl" :class="isDark ? 'bi-sun-fill' : 'bi-moon-fill'"></i>
                    </button>
                    @auth
                    <a href="{{ route('profile.show') }}" class="hover:opacity-80 transition relative group" title="{{ __('layout.my_account') }}"><i class="bi bi-person text-xl"></i></a>
                    @else
                    <a href="{{ route('login') }}" class="hover:underline transition text-sm">{{ __('layout.login_register') }}</a>
                    @endauth
                    <a href="{{ route('cart.index') }}" class="hover:opacity-80 transition relative" title="{{ __('layout.cart') }}">
                        <i class="bi bi-cart2 text-xl"></i>
                        <span x-show="cartCount > 0" x-text="cartCount" class="badge" :class="{'animate-ping-once': isCartUpdated}" style="display: none;"></span>
                    </a>
                    <a href="{{ route('wishlist') }}" class="hover:opacity-80 transition relative" x-ref="wishlistCounter" title="{{ __('layout.wishlist') }}">
                        <i class="bi bi-heart text-xl"></i>
                        <span x-show="wishlistCount > 0" x-text="wishlistCount" class="badge" :class="{'animate-ping-once': isWishlistUpdated}" style="display: none;"></span>
                    </a>
                    @auth
                    <div x-data="userNotificationsComponent('{{ route('user.notifications.index') }}', '{{ route('user.notifications.markAsRead') }}')" x-init="fetchNotifications(); setInterval(() => fetchNotifications(), 60000)" class="relative" x-ref="notificationContainerDesktop">
                        <button @click="dropdownOpen = !dropdownOpen" class="relative" title="{{ __('layout.notifications') }}">
                            <i class="bi bi-bell text-2xl"></i>
                            <span x-show="unreadCount > 0" x-text="unreadCount" class="badge" style="display: none;"></span>
                        </button>
                        <template x-teleport="body">
                           <div x-show="dropdownOpen" @click.away="dropdownOpen = false" class="text-right notification-popup fixed z-[9999] w-80 bg-white dark:bg-gray-800 shadow-xl rounded-xl border border-gray-100" style="display: none;"
                                x-init="$watch('dropdownOpen', (value) => { if (value) { $nextTick(() => { let rect = $refs.notificationContainerDesktop.getBoundingClientRect(); $el.style.top = `${rect.bottom + 12}px`; let safeLeft = rect.left; if (safeLeft + 320 > window.innerWidth) safeLeft = window.innerWidth - 330; $el.style.left = `${Math.max(10, safeLeft)}px`; }); } })">
                                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 font-bold text-gray-800 dark:text-gray-100">{{ __('layout.notifications') }}</div>
                                <div class="py-1 max-h-[80vh] overflow-y-auto">
                                    <template x-if="notifications.length === 0"><p class="text-center text-gray-500 py-6">{{ __('layout.no_notifications') }}</p></template>
                                    <template x-for="notification in notifications" :key="notification.id">
                                        <a @click.prevent="markAsRead(notification)" :href="notification.data?.url || '#'" class="flex items-start px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition" :class="{ 'bg-blue-50/50 dark:bg-gray-900/60': !notification.read_at }">
                                            <i class="bi ml-3 mt-1" :class="notification.data?.icon || 'bi-info-circle'"></i>
                                            <div><p class="text-sm" x-text="notification.data?.message"></p><small class="text-xs text-gray-500" x-text="timeAgo(notification.created_at)"></small></div>
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                    @endauth
                </div>
            </div>

            {{-- Mobile Header --}}
            <div class="container mx-auto lg:hidden flex flex-col px-4 text-white" dir="ltr">
              <div class="flex w-full items-center justify-between min-h-[42px] relative gap-4">
                
                {{-- Left Side: Logo --}}
                <div class="flex items-center z-10">
                  <a href="{{ route('homepage') }}" class="flex items-center">
                    <img src="{{ asset('sec-logo.png') }}" alt="logo" class="h-9 w-auto object-contain">
                  </a>
                </div>

                {{-- Right Side: Other Icons --}}
                <div class="flex items-center justify-end gap-1 sm:gap-2.5 z-10">
                  @auth
                  <div class="relative" x-data="userNotificationsComponent('{{ route('user.notifications.index') }}', '{{ route('user.notifications.markAsRead') }}')" x-init="fetchNotifications(); setInterval(() => fetchNotifications(), 60000)" x-ref="notificationContainerMobile">
                    <button @click="dropdownOpen = !dropdownOpen" class="relative w-9 h-9 inline-flex items-center justify-center hover:bg-white/10 rounded-full" title="{{ __('layout.notifications') }}">
                      <i class="bi bi-bell text-lg"></i>
                      <span x-show="unreadCount > 0" x-text="unreadCount" class="badge" style="display: none;"></span>
                    </button>
                    <template x-teleport="body">
                        <div x-show="dropdownOpen" @click.away="dropdownOpen = false" class="text-right notification-popup fixed z-[9999] w-[90vw] max-w-sm bg-white dark:bg-gray-800 shadow-2xl rounded-xl border border-gray-100" style="display:none;"
                             x-init="$watch('dropdownOpen', (value) => { if (value) { $nextTick(() => { let rect = $refs.notificationContainerMobile.getBoundingClientRect(); $el.style.top = `${rect.bottom + 12}px`; let safeLeft = Math.max(10, (window.innerWidth - $el.offsetWidth) / 2); $el.style.left = `${safeLeft}px`; }); } })">
                          <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 font-bold text-gray-800 dark:text-gray-100">{{ __('layout.notifications') }}</div>
                          <div class="py-1 max-h-[70vh] overflow-y-auto">
                            <template x-if="notifications.length === 0"><p class="text-center text-gray-500 py-6">{{ __('layout.no_notifications') }}</p></template>
                            <template x-for="notification in notifications" :key="notification.id">
                              <a @click.prevent="markAsRead(notification)" :href="notification.data?.url || '#'" class="flex items-start px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition" :class="{ 'bg-blue-50/50 dark:bg-gray-900/60': !notification.read_at }">
                                <i class="bi ml-3 mt-1" :class="notification.data?.icon || 'bi-info-circle'"></i>
                                <div><p class="text-sm" x-text="notification.data?.message"></p><small class="text-xs text-gray-500" x-text="timeAgo(notification.created_at)"></small></div>
                              </a>
                            </template>
                          </div>
                        </div>
                    </template>
                  </div>
                  @endauth

                  {{-- Search Toggle --}}
                  <button
                    @click="mobileSearchOpen = !mobileSearchOpen; if (mobileSearchOpen) { $nextTick(() => document.getElementById('mobileSearchInput')?.focus()); } else { searchFocused = false; }"
                    class="w-9 h-9 inline-flex items-center justify-center hover:bg-white/10 rounded-full"
                    :aria-expanded="mobileSearchOpen"
                    aria-controls="mobileSearchPanel"
                    title="{{ __('header.search_placeholder') }}"
                  >
                    <i class="bi" :class="mobileSearchOpen ? 'bi-x-lg' : 'bi-search'"></i>
                  </button>

                  {{-- Language Switcher Mobile --}}
                  <div x-data="{ langOpen: false }" class="relative" @click.away="langOpen = false">
                      <button @click="langOpen = !langOpen"
                          class="flex items-center gap-1 p-1 hover:bg-white/10 rounded-full text-xs font-bold"
                          title="{{ __('layout.change_language') }}">
                          @if($locale === 'ar')
                            <img src="{{ request()->root() }}/flags/iq.svg" class="w-5 h-3.5 object-cover rounded-sm shadow-sm" alt="Arabic">
                          @elseif($locale === 'en')
                            <img src="{{ request()->root() }}/flags/us.svg" class="w-5 h-3.5 object-cover rounded-sm shadow-sm" alt="English">
                          @endif
                      </button>
                        <div x-show="langOpen" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            class="absolute top-full mt-2 {{ $dir === 'rtl' ? 'left-0' : 'right-0' }} w-36 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden z-[99999] text-gray-800 dark:text-gray-100">
                          @php $currentUrl = url()->current(); @endphp
                          <a href="{{ route('language.switch', ['locale' => 'ar', 'from' => $currentUrl]) }}" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-[#6d0e16]/10 transition {{ $locale === 'ar' ? 'font-bold text-[#6d0e16]' : '' }}">
                              <img src="{{ request()->root() }}/flags/iq.svg" class="w-5 h-3.5 object-cover rounded-sm shadow-sm" alt="Arabic"> عربي
                              @if($locale === 'ar') <i class="bi bi-check2 mr-auto text-[#6d0e16]"></i> @endif
                          </a>
                          <a href="{{ route('language.switch', ['locale' => 'en', 'from' => $currentUrl]) }}" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-[#6d0e16]/10 transition {{ $locale === 'en' ? 'font-bold text-[#6d0e16]' : '' }}">
                              <img src="{{ request()->root() }}/flags/us.svg" class="w-5 h-3.5 object-cover rounded-sm shadow-sm" alt="English"> English
                              @if($locale === 'en') <i class="bi bi-check2 mr-auto text-[#6d0e16]"></i> @endif
                          </a>
                        </div>
                  </div>

                  {{-- Theme Toggle --}}
                  <button @click="toggleTheme" class="w-9 h-9 inline-flex items-center justify-center hover:bg-white/10 rounded-full" title="{{ __('layout.toggle_theme') }}">
                    <i class="bi bi-moon-fill text-lg" :class="isDark ? 'bi-sun-fill' : 'bi-moon-fill'"></i>
                  </button>

                  <a href="{{ route('wishlist') }}" class="relative w-9 h-9 inline-flex items-center justify-center hover:bg-white/10 rounded-full" title="{{ __('layout.wishlist') }}">
                    <i class="bi bi-heart text-lg"></i>
                    <span x-show="wishlistCount > 0" x-text="wishlistCount" class="badge" style="display: none;"></span>
                  </a>
                </div>
              </div>

                <div
                id="mobileSearchPanel"
                x-cloak
                x-show="mobileSearchOpen"
                x-transition:enter="transform transition ease-out duration-250"
                x-transition:enter-start="-translate-y-4 opacity-0"
                x-transition:enter-end="translate-y-0 opacity-100"
                x-transition:leave="transform transition ease-in duration-220"
                x-transition:leave-start="translate-y-0 opacity-100"
                x-transition:leave-end="-translate-y-4 opacity-0"
                class="absolute left-0 right-0 top-full z-[85] px-4 pt-2"
                >
                <div x-data="liveSearch('{{ route('products.liveSearch') }}')" @click.away="showResults = false; mobileSearchOpen = false; searchFocused = false" class="w-full relative" x-ref="searchContainerMobile">
                  <form action="{{ route('products.search') }}" method="GET" @submit.prevent="if (highlightedIndex !== -1) selectHighlighted(); else $el.submit()">
                  <div class="flex w-full bg-white rounded-2xl overflow-hidden dark:bg-gray-800 dark:border dark:border-gray-700 shadow-lg">
                    <input id="mobileSearchInput" type="text" name="query" placeholder="{{ __('header.search_placeholder') }}" class="flex-1 px-4 py-2.5 text-sm text-gray-700 placeholder-gray-500 focus:outline-none dark:text-gray-100 dark:placeholder-gray-400 dark:bg-transparent"
                      x-model="query" @input.debounce.300ms="search" @keydown.down.prevent="moveHighlight('down')" @keydown.up.prevent="moveHighlight('up')"
                      @keydown.enter.prevent="if (highlightedIndex > -1) { selectHighlighted() } else { $el.closest('form').submit() }"
                      @focus="onFocus(); searchFocused = true" @blur="searchFocused = false" autocomplete="off">
                    <button type="submit" class="px-4 bg-white text-[#6d0e16] hover:text-[#6d0e16] dark:bg-transparent"><i class="bi bi-search"></i></button>
                  </div>
                  </form>
                  <template x-teleport="body">
                    <div x-show="showResults" x-transition class="search-popup" x-cloak
                       x-init="$watch('showResults', (value) => { if (value) { $nextTick(() => { let rect = $refs.searchContainerMobile.getBoundingClientRect(); $el.style.top = `${rect.bottom + 4}px`; $el.style.left = `${rect.left}px`; $el.style.width = `${rect.width}px`; }); } })">
                      <div x-show="loading" class="p-4 text-center text-gray-500 dark:text-gray-300">{{ __('layout.searching') }}</div>
                      <template x-if="!loading && results.length === 0 && query.length >= minChars"><div class="p-4 text-center text-gray-500 dark:text-gray-300">{{ __('layout.no_products_matching') }}</div></template>
                      <template x-for="(result, index) in results" :key="index">
                        <div>
                          <template x-if="result.type === 'header'">
                            <div class="px-4 py-2 text-xs font-bold uppercase tracking-wider text-gray-500 bg-gray-50/50 dark:bg-gray-900/50 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700">
                              <span x-text="result.label"></span>
                            </div>
                          </template>
                          <template x-if="result.type !== 'header'">
                            <a :href="result.url" class="search-result-item" :class="{ 'highlighted': index === highlightedIndex }" @mouseenter="highlightedIndex = index">
                              <template x-if="result.type === 'product'">
                                <img :src="result.image" alt="" class="search-result-img">
                              </template>
                              <template x-if="result.type === 'brand'">
                                <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700 mr-3">
                                  <i class="bi bi-tag text-lg text-[#6d0e16]"></i>
                                </div>
                              </template>
                              <template x-if="result.type === 'category'">
                                <div class="w-10 h-10 flex items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700 mr-3">
                                  <i class="bi bi-grid text-lg text-[#6d0e16]"></i>
                                </div>
                              </template>
                              <div class="flex flex-col">
                                <span class="text-gray-800 dark:text-gray-200 font-semibold" x-text="result.name"></span>
                                <template x-if="result.type === 'product'">
                                  <small class="text-gray-500 dark:text-gray-400" x-text="result.category || ''"></small>
                                </template>
                              </div>
                            </a>
                          </template>
                        </div>
                      </template>
                    </div>
                  </template>
                </div>
                </div>
            </div>
        </header>

        <header id="desktopNav" class="hidden lg:block relative z-30 desktop-liquid-shell shadow-md">
          <div class="container mx-auto px-6 py-3 flex justify-center items-center">
            <nav class="desktop-liquid-nav flex items-center space-x-10 space-x-reverse">
{{-- ==================== الفئات (يمين) ← البراندات (يسار) | تصميم V4 (معكوس) ==================== --}}
@php
    // خريطة: brand => [name, image, categories[]]
    $brandCategoriesMap = [];

    if (isset($categories) && $categories->isNotEmpty()) {
        foreach ($categories as $cat) {
            // IDs للفئة + الأبناء
            $catIds = collect([$cat->id]);
            if ($cat->children->isNotEmpty()) {
                foreach ($cat->children as $child) {
                    $catIds->push($child->id);
                    if ($child->children->isNotEmpty()) {
                        foreach ($child->children as $grand) { $catIds->push($grand->id); }
                    }
                }
            }
            $catIds = $catIds->unique()->values();

            // البراندات التي تملك منتجات داخل هذه الفئة/الأبناء
            $brands = \App\Models\PrimaryCategory::query()
                ->active()->ordered()->select('id','name_ar','name_en','slug','icon','image')
                ->whereHas('products', fn($q)=>$q->where('is_active',true)->whereIn('category_id',$catIds))
                ->get();

            foreach ($brands as $b) {
                $bImg = $b->image ?: $b->icon;
                if ($bImg && !Str::startsWith($bImg, ['http','//'])) {
                    $bImg = asset('storage/'.ltrim($bImg,'/'));
                }

                if (!isset($brandCategoriesMap[$b->slug])) {
                    $brandCategoriesMap[$b->slug] = [
                        'slug' => $b->slug,
                        'name' => app()->getLocale() === 'en' && !empty($b->name_en) ? $b->name_en : $b->name_ar,
                        'image'=> $bImg,
                        'categories' => [],
                    ];
                }

                $cImg = !empty($cat->image)
                    ? (Str::startsWith($cat->image, ['http','//']) ? $cat->image : asset('storage/'.ltrim($cat->image,'/')))
                    : null;

                $brandCategoriesMap[$b->slug]['categories'][$cat->slug] = [
                    'slug'  => $cat->slug,
                    'name'  => app()->getLocale() === 'en' && !empty($cat->name_en) ? $cat->name_en : ($cat->name_ar ?? $cat->name ?? ''),
                    'image' => $cImg,
                ];
            }
        }

        // ترتيب أبجدي
        foreach ($brandCategoriesMap as &$entry) {
            $entry['categories'] = array_values($entry['categories']);
            usort($entry['categories'], fn($a,$b)=> strnatcasecmp($a['name'], $b['name']));
        }
        unset($entry);
        uasort($brandCategoriesMap, fn($a,$b)=> strnatcasecmp($a['name'], $b['name']));
    }
@endphp

<div class="relative" x-data="brandMenuV4()" x-init="init()"
     @mouseenter="open = true" @mouseleave="open = false">

  <style>
    .mmv4-wrap{
      position:fixed; z-index:9990!important; overflow:hidden; border-radius:22px;
      background:linear-gradient(180deg, rgba(255,255,255,.66), rgba(255,255,255,.28));
      border:1px solid rgba(255,255,255,.45);
      -webkit-backdrop-filter:blur(26px); backdrop-filter:blur(26px);
      box-shadow:0 20px 48px rgba(0,0,0,.18);
    }
    html.dark .mmv4-wrap{
      background:linear-gradient(180deg, rgba(15,23,42,.55), rgba(15,23,42,.28));
      border-color:rgba(148,163,184,.28);
      box-shadow:0 24px 54px rgba(0,0,0,.55);
    }

    .mmv4-head{
      padding:16px 18px; display:flex; align-items:center; justify-content:space-between;
      background:linear-gradient(90deg, rgba(205,137,133,.12), transparent);
      border-bottom:1px dashed rgba(205,137,133,.35);
    }
    html.dark .mmv4-head{ border-bottom-color:rgba(240,176,173,.25); }

    /* يسار: البراندات */
    .mmv4-search{
      width:100%; background:rgba(255,255,255,.95);
      border:1px solid rgba(234,219,205,.95); border-radius:16px;
      padding:.7rem .9rem; padding-left:2.4rem; font-weight:700;
    }
    .mmv4-search::placeholder{ color:#9ca3af; font-weight:500; }
    html.dark .mmv4-search{ background:rgba(31,41,55,.75); border-color:#374151; color:#e5e7eb; }
    .mmv4-search-icon{ position:absolute; left:.7rem; top:50%; transform:translateY(-50%); color:#9ca3af; }

    .mmv4-brand{
      display:flex; align-items:center; gap:.9rem; text-align:right;
      padding:.65rem .8rem; border-radius:14px; cursor:pointer;
      border:1px solid rgba(234,219,205,.85);
      background:linear-gradient(180deg, rgba(255,255,255,.90), rgba(255,255,255,.70));
      transition:transform .16s ease, background .16s ease, border-color .16s ease, box-shadow .16s ease;
    }
    .mmv4-brand:hover{ transform:translateY(-2px); background:rgba(190,102,97,.10); }
    .mmv4-brand.active{
      background:linear-gradient(180deg, rgba(255,255,255,.98), rgba(255,255,255,.86));
      border-color:#6d0e16; box-shadow:0 10px 22px rgba(205,137,133,.18);
    }
    html.dark .mmv4-brand{ background:rgba(31,41,55,.62); border-color:#374151; }
    html.dark .mmv4-brand.active{ border-color:#f0b0ad; }

    .mmv4-logo{
      width:44px; height:44px; border-radius:12px; overflow:hidden;
      display:grid; place-items:center; background:#ffffff; border:1px solid #e5e5e5; flex-shrink:0;
    }
    .mmv4-logo img{ width:100%; height:100%; object-fit:cover; }
    html.dark .mmv4-logo{ background:rgba(255,255,255,.08); border-color:#475569; }

    .mmv4-brands-scroll{ max-height:440px; overflow-y:auto; padding-left:.25rem; }
    .mmv4-brands-scroll::-webkit-scrollbar{ width:8px; }
    .mmv4-brands-scroll::-webkit-scrollbar-track{ background:rgba(0,0,0,.05); border-radius:10px; }
    .mmv4-brands-scroll::-webkit-scrollbar-thumb{ background:#6d0e16; border-radius:10px; border:2px solid transparent; background-clip:content-box; }
    .mmv4-brands-scroll::-webkit-scrollbar-thumb:hover{ background:#8d121c; }
    html.dark .mmv4-brands-scroll::-webkit-scrollbar-track{ background:rgba(255,255,255,.07); }

    /* يمين: الفئات */
    .mmv4-cat{
      position:relative; border-radius:16px; overflow:hidden;
      border:1px solid #1a1a1a; background:linear-gradient(180deg, #fff, #fff8f7);
      transition:transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    }
    .mmv4-cat:hover{ transform:translateY(-4px); box-shadow:0 16px 36px rgba(0,0,0,.12); border-color:#e6c2bf; }
    html.dark .mmv4-cat{ border-color:#374151; background:linear-gradient(180deg, rgba(31,41,55,.85), rgba(31,41,55,.70)); }
    html.dark .mmv4-cat:hover{ border-color:#475569; }

    .mmv4-cat-top{ display:flex; align-items:center; gap:12px; padding:12px 12px 8px 12px; }
    .mmv4-thumb{
      width:56px; height:56px; border-radius:12px; overflow:hidden; flex-shrink:0;
      display:grid; place-items:center; background:#0a0a0a; border:1px solid #1a1a1a;
    }
    .mmv4-thumb img{ width:100%; height:100%; object-fit:cover; }
    html.dark .mmv4-thumb{ background:rgba(255,255,255,.08); border-color:#475569; }

    .mmv4-cat-name{ font-weight:800; font-size:1rem; line-height:1.2; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .mmv4-cat-badge{ font-size:.72rem; font-weight:700; padding:.2rem .5rem; border-radius:999px; background:#fff0f0; color:#6d0e16; border:1px solid #f3c6c3; }
    html.dark .mmv4-cat-badge{ background:rgba(240,176,173,.15); color:#f0b0ad; border-color:#6b7280; }

    .mmv4-cat-foot{ display:flex; justify-content:space-between; align-items:center; padding:10px 12px; border-top:1px dashed #1a1a1a; }
    html.dark .mmv4-cat-foot{ border-top-color:#475569; }
    .mmv4-link{ font-size:.85rem; font-weight:800; color:#6d0e16; }
    .mmv4-link:hover{ text-decoration:underline; }
  </style>

@php
    // جلب البراندات مع أبنائها لعرضها بشكل هرمي
  try {
    $brandsTree = Cache::remember('global_brands_tree_for_liquid_menu', now()->addHours(6), function () {
      return PrimaryCategory::whereNull('parent_id')
        ->orderBy('name_ar', 'asc')
        ->withCount('products')
        ->with(['children' => function ($query) {
          $query->orderBy('name_ar', 'asc')->withCount('products');
        }])
        ->get();
    });
  } catch (\Throwable $e) {
    $brandsTree = collect();
  }
@endphp

{{-- ==================================================================== --}}
{{-- ✅ [تصحيح] تعديل لون اللوجو واستبدال كلمة "براند" بـ "فئة" --}}
{{-- ==================================================================== --}}
<div class="relative" 
    x-data="{
        open: false,
        timer: null,
        show() {
            clearTimeout(this.timer);
            this.open = true;
        },
        hide() {
            this.timer = setTimeout(() => {
                this.open = false;
            }, 200);
        }
    }"
    @mouseenter="show()" 
    @mouseleave="hide()">

    <style>
        .liquid-glass-panel {
            position: fixed; 
            z-index: 50;
            width: 400px;
            height: 70vh;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.88);
            -webkit-backdrop-filter: blur(40px);
            backdrop-filter: blur(40px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: 0 20px 50px rgba(0,0,0,0.12);
            overflow: hidden; 
        }
        .liquid-scroll-container {
            position: relative; z-index: 2; width: 100%; height: 100%;
            overflow-y: auto; padding: 0.5rem;
        }
        .liquid-glass-panel::before,
        .liquid-glass-panel::after {
            content: ""; position: absolute; border-radius: 50%;
            filter: blur(80px); pointer-events: none; z-index: 1;
        }
        .liquid-glass-panel::before {
            width: 250px; height: 250px; background: rgba(205, 137, 133, 0.3);
            top: -50px; right: -80px; animation: move-blob-1 15s infinite alternate;
        }
        .liquid-glass-panel::after {
            width: 200px; height: 200px; background: rgba(240, 192, 183, 0.3);
            bottom: -50px; left: -50px; animation: move-blob-2 18s infinite alternate;
        }

        @keyframes move-blob-1 { from { transform: translate(0, 0) rotate(0deg); } to { transform: translate(40px, -30px) rotate(90deg); } }
        @keyframes move-blob-2 { from { transform: translate(0, 0) rotate(0deg); } to { transform: translate(-30px, 30px) rotate(-90deg); } }
        
        .liquid-item {
            display: flex; align-items: center; padding: 0.9rem 1rem;
            text-decoration: none; border-radius: 12px;
            transition: all 0.25s cubic-bezier(.4,0,.2,1);
        }
        .liquid-item:hover { background: rgba(255, 255, 255, 0.15); transform: scale(1.02); }

        /* ✅ [تعديل] تغيير لون خلفية اللوجو */
        .liquid-logo {
            width: 44px; height: 44px; border-radius: 50%; margin-left: 1rem; flex-shrink: 0;
            background-color: transparent;
            border: none;
            padding: 0; display: flex; align-items: center; justify-content: center;
            overflow: hidden;
        }
        .liquid-logo img { width: 100%; height: 100%; object-fit: contain; }
        
        .liquid-name {
            font-weight: 700; color: #1a1a1a;
            transition: color 0.3s ease;
        }

        .liquid-toggle { margin-right: auto; padding: 0.5rem; color: rgba(52, 40, 44, 0.6); }
        
        .liquid-sub-list { padding-right: 2.5rem; padding-bottom: 0.5rem; }
        .liquid-sub-item { padding-top: 0.5rem; padding-bottom: 0.5rem; }
        .liquid-sub-item .liquid-logo { width: 32px; height: 32px; }
        .liquid-sub-item .liquid-name { font-size: 0.9rem; }

        /* Dark Mode */
        html.dark .liquid-glass-panel { background: rgba(15, 23, 42, 0.9); border-color: rgba(255, 255, 255, 0.1); }
        html.dark .liquid-item:hover { background: rgba(255, 255, 255, 0.05); }
        html.dark .liquid-name { color: #f9fafb; text-shadow: none; }
        html.dark .liquid-toggle { color: rgba(240, 242, 245, 0.8); }
        html.dark .liquid-logo { background-color: transparent; border-color: transparent; }
        
        .liquid-scroll-container::-webkit-scrollbar { width: 6px; }
        .liquid-scroll-container::-webkit-scrollbar-track { background: transparent; }
        .liquid-scroll-container::-webkit-scrollbar-thumb { background: rgba(205, 137, 133, 0.5); border-radius: 10px; }
        .liquid-scroll-container::-webkit-scrollbar-thumb:hover { background: rgba(190, 102, 97, 0.8); }
    </style>

    {{-- زر فتح القائمة --}}
    <button type="button"
            x-ref="brandsTrigger"
            class="relative group font-medium hover:text-[#f3e5e3] transition-colors duration-300 py-1 px-4 flex items-center gap-1 {{ request()->routeIs('categories.*') ? 'active-link pointer-events-none' : '' }}"
            aria-haspopup="true" :aria-expanded="open.toString()">
        {{-- ✅ [تعديل] تغيير كلمة البراندات إلى الفئات --}}
        <i class="bi bi-tags-fill"></i> {{ __('layout.categories_nav') }}
    </button>

    {{-- القائمة المنسدلة --}}
    <template x-teleport="body">
        <div x-show="open" x-cloak
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="liquid-glass-panel"
            x-init="$watch('open', (value) => {
                if (value) {
                    $nextTick(() => {
                        let rect = $refs.brandsTrigger.getBoundingClientRect();
                        $el.style.top = `${rect.bottom + 8}px`;
                        $el.style.left = `${rect.left}px`;
                    });
                }
            })"
            @mouseenter="show()" 
            @mouseleave="hide()">
            
            <div class="liquid-scroll-container">
                @forelse ($brandsTree as $brand)
                    <div x-data="{ open: false }">
                        <div class="liquid-item">
                            <a href="{{ route('shop', ['brand' => $brand->slug]) }}" class="flex items-center flex-grow text-decoration-none">
                                <div class="liquid-logo">
                                    @php
                                        $img = $brand->image ?: $brand->icon;
                                        // ✅ [تعديل] الشعار الافتراضي للمتجر الرئيسي
                                        // !!! هام: غيّر هذا الرابط إلى رابط شعار متجرك !!!
                                        $defaultLogo = asset('sec-logo.png'); 
                                    @endphp
                                    @if($img) 
                                        <img src="{{ asset('storage/' . $img) }}" alt="{{ app()->getLocale() === 'en' && !empty($brand->name_en) ? $brand->name_en : $brand->name_ar }}">
                                    @else 
                                        <img src="{{ $defaultLogo }}" alt="Default Logo">
                                    @endif
                                </div>
                                <span class="liquid-name">{{ app()->getLocale() === 'en' && !empty($brand->name_en) ? $brand->name_en : $brand->name_ar }}</span>
                            </a>
                            @if($brand->children->isNotEmpty())
                                <button type="button" @click="open = !open" class="liquid-toggle">
                                    <i class="bi transition-transform duration-300" :class="open ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                                </button>
                            @endif
                        </div>

                        @if($brand->children->isNotEmpty())
                            <div x-show="open" x-collapse.duration.300ms class="liquid-sub-list">
                                @foreach($brand->children as $child)
                                    <a href="{{ route('shop', ['brand' => $child->slug]) }}" class="liquid-item liquid-sub-item">
                                        <div class="liquid-logo">
                                            @php $childImg = $child->image ?: $child->icon; @endphp
                                            @if($childImg) 
                                                <img src="{{ asset('storage/' . $childImg) }}" alt="{{ app()->getLocale() === 'en' && !empty($child->name_en) ? $child->name_en : $child->name_ar }}">
                                            @else 
                                                <img src="{{ $defaultLogo }}" alt="Default Logo">
                                            @endif
                                        </div>
                                        <span class="liquid-name">{{ app()->getLocale() === 'en' && !empty($child->name_en) ? $child->name_en : $child->name_ar }}</span>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center p-8 text-sm text-gray-500">
                        {{-- ✅ [تعديل] تغيير كلمة البراندات إلى الفئات --}}
                        {{ __('layout.no_categories') }}
                    </div>
                @endforelse
            </div>
        </div>
    </template>
</div>

  <script type="application/json" id="__brandCatMapV4">@json(array_values($brandCategoriesMap))</script>
</div>

<script>
function brandMenuV4(){
  return {
    open:false,
    list:[],
    brandQuery:'',
    activeBrand:null,
    activeCategories:[],

    init(){
      const el = document.getElementById('__brandCatMapV4');
      if(el){
        try{ this.list = JSON.parse(el.textContent||'[]'); }catch(e){ this.list = []; }
      }
    },

    filteredBrands(){
      const q = (this.brandQuery||'').trim().toLowerCase();
      if(!q) return this.list;
      return this.list.filter(b => (b.name||'').toLowerCase().includes(q));
    },

    selectBrand(br){
      this.activeBrand = { slug: br.slug, name: br.name, image: br.image };
      this.activeCategories = Array.isArray(br.categories) ? br.categories : [];
    },

    shopUrlForBrand(brandSlug){
      const u = new URL(@json(route('shop')), window.location.origin);
      if(brandSlug) u.searchParams.set('brand', brandSlug);
      return u.toString();
    },

    shopUrlForCatBrand(categorySlug, brandSlug){
      const u = new URL(@json(route('shop')), window.location.origin);
      if(categorySlug) u.searchParams.set('category', categorySlug);
      if(brandSlug)    u.searchParams.set('brand', brandSlug);
      return u.toString();
    }
  }
}
</script>



                    <a href="{{ route('homepage') }}" class="relative group font-medium hover:text-[#f3e5e3] py-1 px-4 {{ request()->routeIs('homepage') ? 'active-link pointer-events-none' : '' }}">{{ __('layout.home') }}<span class="absolute bottom-0 left-0 w-0 h-0.5 bg-white transition-all duration-300 group-hover:w-full"></span></a>
                    <a href="{{ route('shop') }}" class="relative group font-medium hover:text-[#f3e5e3] py-1 px-4 {{ request()->routeIs('shop*') ? 'active-link pointer-events-none' : '' }}">{{ __('layout.shop') }}<span class="absolute bottom-0 left-0 w-0 h-0.5 bg-white transition-all duration-300 group-hover:w-full"></span></a>

                    <a href="{{ route('blog.index') }}" class="relative group font-medium hover:text-[#f3e5e3] py-1 px-4 {{ request()->routeIs('blog*') ? 'active-link pointer-events-none' : '' }}">{{ __('layout.blog') }}<span class="absolute bottom-0 left-0 w-0 h-0.5 bg-white transition-all duration-300 group-hover:w-full"></span></a>
                    <a href="{{ route('about.us') }}" class="relative group font-medium hover:text-[#f3e5e3] py-1 px-4 {{ request()->routeIs('about.us') ? 'active-link pointer-events-none' : '' }}">{{ __('layout.about_us') }}<span class="absolute bottom-0 left-0 w-0 h-0.5 bg-white transition-all duration-300 group-hover:w-full"></span></a>
                    <a href="{{ route('page.contact-us') }}" class="relative group font-medium hover:text-[#f3e5e3] py-1 px-4 {{ request()->routeIs('page.contact-us') ? 'active-link pointer-events-none' : '' }}">
    {{ __('layout.contact_us') }}
    <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-white transition-all duration-300 group-hover:w-full"></span>
</a>
                </nav>
            </div>
        </header>
    </div>

    {{-- Categories Sidebar --}}
    <div x-show="sidebarOpen" style="display: none;" class="fixed inset-0 z-50" aria-labelledby="sidebar-title" role="dialog" aria-modal="true">
      <div x-show="sidebarOpen" x-transition:enter="ease-in-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-black/80 transition-opacity" @click="sidebarOpen = false"></div>
      <div class="fixed inset-y-0 right-0 flex max-w-full">
        <div x-show="sidebarOpen" @click.away="sidebarOpen = false"
             x-transition:enter="transform transition ease-in-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
             x-transition:leave="transform transition ease-in-out duration-300" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
             class="relative w-screen max-w-md">
          <div class="flex h-full flex-col overflow-y-auto shadow-xl sidebar-glass">
            <div class="px-4 sm:px-6 py-4 border-b sidebar-header">
              <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-[#6d0e16] dark:text-[#f0b0ad]" id="sidebar-title"><i class="bi bi-grid-3x3-gap"></i> {{ __('layout.browse_sections') }}</h2>
                <button type="button" class="rounded-md text-gray-400 hover:text-red-500" @click="sidebarOpen = false"><i class="bi bi-x-lg text-xl"></i></button>
              </div>
            </div>
            <div class="relative flex-1 px-4 sm:px-6 py-6 category-tree-container">
              <div class="category-tree">
                @forelse ($categories as $category)
                  <div class="category-node" x-data="{ open: false }">
                    <div class="category-card">
                      <a href="{{ route('shop', ['category' => $category->slug]) }}" class="category-link">
                        <div class="category-icon">
                          @php $img = $category->image ? asset('storage/' . $category->image) : null; @endphp
                          @if($img) <img src="{{ $img }}" alt="{{ app()->getLocale() === 'en' && !empty($category->name_en) ? $category->name_en : $category->name_ar }}" class="icon-image"> @else <div class="icon-placeholder">🧴</div> @endif
                        </div>
                        <div class="category-details">
                          <h3 class="category-name">{{ app()->getLocale() === 'en' && !empty($category->name_en) ? $category->name_en : $category->name_ar }}</h3>
                          <div class="category-meta"><span class="meta-item"><i class="bi bi-diagram-3"></i> {{ __('layout.main_category') }}</span><span class="meta-item"><i class="bi bi-box-seam"></i> {{ $category->total_products_count ?? 0 }} {{ __('layout.product_unit') }}</span></div>
                        </div>
                      </a>
                      @if($category->children->isNotEmpty())<button class="expand-btn" @click="open = !open"><i class="bi bi-chevron-down" :class="{'rotate-180': open}"></i></button>@endif
                    </div>
                    @if($category->children->isNotEmpty())
                      <div class="subcategories" x-show="open" x-transition style="display: none;">
                        <ul class="subcategory-list">
                          @foreach($category->children as $child)
                            <li class="subcategory-node" x-data="{ open: false }">
                              <div class="subcategory-card">
                                <a href="{{ route('shop', ['category' => $child->slug]) }}" class="subcategory-link">
                                  <div class="subcategory-icon">
                                    @php $img2 = $child->image ? asset('storage/' . $child->image) : null; @endphp
                                    @if($img2) <img src="{{ $img2 }}" alt="{{ app()->getLocale() === 'en' && !empty($child->name_en) ? $child->name_en : $child->name_ar }}" class="icon-image"> @else <div class="icon-placeholder">🧴</div> @endif
                                  </div>
                                  <div class="subcategory-details">
                                    <h4 class="subcategory-name">{{ app()->getLocale() === 'en' && !empty($child->name_en) ? $child->name_en : $child->name_ar }}</h4>
                                    <div class="category-meta"><span class="meta-item"><i class="bi bi-box-seam"></i> {{ $child->total_products_count ?? 0 }} {{ __('layout.product_unit') }}</span></div>
                                  </div>
                                </a>
                                @if($child->children->isNotEmpty())<button class="expand-btn" @click="open = !open"><i class="bi bi-chevron-down" :class="{'rotate-180': open}"></i></button>@endif
                              </div>
                              @if($child->children->isNotEmpty())
                                <div class="sub-subcategories" x-show="open" x-transition style="display: none;">
                                  <ul class="sub-subcategory-list">
                                    @foreach($child->children as $grand)
                                      <li class="sub-subcategory-node">
                                        <div class="sub-subcategory-card">
                                          <a href="{{ route('shop', ['category' => $grand->slug]) }}" class="sub-subcategory-link">
                                            <div class="sub-subcategory-icon">
                                              @php $img3 = $grand->image ? asset('storage/' . $grand->image) : null; @endphp
                                              @if($img3) <img src="{{ $img3 }}" alt="{{ app()->getLocale() === 'en' && !empty($grand->name_en) ? $grand->name_en : $grand->name_ar }}" class="icon-image"> @else <div class="icon-placeholder">🧴</div> @endif
                                            </div>
                                            <div class="subcategory-details">
                                              <h5 class="sub-subcategory-name">{{ app()->getLocale() === 'en' && !empty($grand->name_en) ? $grand->name_en : $grand->name_ar }}</h5>
                                              <div class="category-meta"><span class="meta-item"><i class="bi bi-box-seam"></i> {{ $grand->total_products_count ?? 0 }} {{ __('layout.product_unit') }}</span></div>
                                            </div>
                                          </a>
                                        </div>
                                      </li>
                                    @endforeach
                                  </ul>
                                </div>
                              @endif
                            </li>
                          @endforeach
                        </ul>
                      </div>
                    @endif
                  </div>
                @empty
                  <div class="text-center py-6 text-gray-500">{{ __('layout.no_sections') }}</div>
                @endforelse
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <main id="pageContent" class="flex-grow page-content-shell">

        {{-- ===== Blur Backdrop للبحث (موبايل) ===== --}}
        <div
          x-show="mobileSearchOpen"
          x-transition:enter="transition ease-out duration-200"
          x-transition:enter-start="opacity-0"
          x-transition:enter-end="opacity-100"
          x-transition:leave="transition ease-in duration-150"
          x-transition:leave-start="opacity-100"
          x-transition:leave-end="opacity-0"
          @click="mobileSearchOpen = false; searchFocused = false"
          class="lg:hidden fixed inset-0 z-30 search-blur-backdrop"
          style="display:none;"
          aria-hidden="true"
        ></div>


        @yield('content')
    </main>

<footer class="bg-gray-50 text-gray-800 pt-16 pb-8 border-t border-gray-200 dark:bg-gray-950 dark:text-gray-300 dark:border-gray-800 transition-colors duration-300">
  <div class="container mx-auto px-4 md:px-8">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-10 lg:gap-12">
      <!-- Logo and Description -->
      <div class="lg:col-span-4 flex flex-col items-center md:items-start text-center md:text-right">
        <a href="{{ route('homepage') }}" class="inline-block mb-6 hover:opacity-90 transition-opacity">
          <img src="{{ asset('logo-black.png') }}" alt="Tofof Logo" class="w-40 h-auto object-contain dark:hidden">
          <img src="{{ asset('sec-logo.png') }}" alt="Tofof Logo" class="w-40 h-auto object-contain hidden dark:block">
        </a>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6 leading-relaxed max-w-sm">
          أفضل الساعات والاكسسوارات الفاخرة التي تناسب جميع الأذواق والمناسبات. تميز بإطلالتك مع طفوف.
        </p>
        <div class="flex items-center gap-3">
          <a href="https://www.facebook.com/p/%D8%B7%D9%81%D9%88%D9%81-%D9%84%D9%84%D8%B3%D8%A7%D8%B9%D8%A7%D8%AA-100091444293851/" target="_blank" rel="noopener noreferrer" class="w-10 h-10 rounded-full bg-white dark:bg-gray-900 shadow-sm border border-gray-100 dark:border-gray-800 flex items-center justify-center text-[#6d0e16] dark:text-[#f0b0ad] hover:bg-[#6d0e16] hover:text-white dark:hover:bg-[#f0b0ad] dark:hover:text-gray-900 transition-all duration-300 hover:-translate-y-1">
            <i class="bi bi-facebook text-lg"></i>
          </a>
          <a href="https://www.instagram.com/tofof_watches" target="_blank" rel="noopener noreferrer" class="w-10 h-10 rounded-full bg-white dark:bg-gray-900 shadow-sm border border-gray-100 dark:border-gray-800 flex items-center justify-center text-[#6d0e16] dark:text-[#f0b0ad] hover:bg-[#6d0e16] hover:text-white dark:hover:bg-[#f0b0ad] dark:hover:text-gray-900 transition-all duration-300 hover:-translate-y-1">
            <i class="bi bi-instagram text-lg"></i>
          </a>
          <a href="https://wa.me/9647744969024" target="_blank" rel="noopener noreferrer" class="w-10 h-10 rounded-full bg-white dark:bg-gray-900 shadow-sm border border-gray-100 dark:border-gray-800 flex items-center justify-center text-[#6d0e16] dark:text-[#f0b0ad] hover:bg-[#6d0e16] hover:text-white dark:hover:bg-[#f0b0ad] dark:hover:text-gray-900 transition-all duration-300 hover:-translate-y-1">
            <i class="bi bi-whatsapp text-lg"></i>
          </a>
        </div>
      </div>

      <!-- Quick Links 1 -->
      <div class="lg:col-span-2 text-center md:text-right">
        <h3 class="text-lg font-bold text-[#6d0e16] dark:text-[#f0b0ad] mb-6 relative inline-block pb-2">
          {{ __('layout.about_tofof') }}
          <span class="absolute bottom-0 right-0 w-1/2 h-0.5 bg-[#6d0e16] dark:bg-[#f0b0ad] rounded-full"></span>
        </h3>
        <ul class="space-y-3 font-medium text-sm">
          <li><a href="{{ route('about.us') }}" class="inline-flex items-center gap-2 hover:text-[#6d0e16] dark:hover:text-[#f0b0ad] transition-all duration-200 hover:translate-x-[-4px]"><i class="bi bi-chevron-left text-[10px] text-gray-400"></i>{{ __('layout.about_us') }}</a></li>
          <li><a href="{{ route('privacy.policy') }}" class="inline-flex items-center gap-2 hover:text-[#6d0e16] dark:hover:text-[#f0b0ad] transition-all duration-200 hover:translate-x-[-4px]"><i class="bi bi-chevron-left text-[10px] text-gray-400"></i>{{ __('layout.privacy_policy') }}</a></li>
          @if (Route::has('terms'))<li><a href="{{ route('terms') }}" class="inline-flex items-center gap-2 hover:text-[#6d0e16] dark:hover:text-[#f0b0ad] transition-all duration-200 hover:translate-x-[-4px]"><i class="bi bi-chevron-left text-[10px] text-gray-400"></i>{{ __('layout.terms_conditions') }}</a></li>@endif
        </ul>
      </div>

      <!-- Quick Links 2 -->
      <div class="lg:col-span-3 text-center md:text-right">
        <h3 class="text-lg font-bold text-[#6d0e16] dark:text-[#f0b0ad] mb-6 relative inline-block pb-2">
          {{ __('layout.our_services') }}
          <span class="absolute bottom-0 right-0 w-1/2 h-0.5 bg-[#6d0e16] dark:bg-[#f0b0ad] rounded-full"></span>
        </h3>
        <ul class="space-y-3 font-medium text-sm">
          <li><a href="{{ Route::has('payment.delivery') ? route('payment.delivery') : url('/payment-delivery') }}" class="inline-flex items-center gap-2 hover:text-[#6d0e16] dark:hover:text-[#f0b0ad] transition-all duration-200 hover:translate-x-[-4px]"><i class="bi bi-chevron-left text-[10px] text-gray-400"></i>{{ __('layout.payment_delivery') }}</a></li>
          <li><a href="{{ route('faq') }}" class="inline-flex items-center gap-2 hover:text-[#6d0e16] dark:hover:text-[#f0b0ad] transition-all duration-200 hover:translate-x-[-4px]"><i class="bi bi-chevron-left text-[10px] text-gray-400"></i>{{ __('layout.faq') }}</a></li>
          @if (Route::has('contact.us'))<li><a href="{{ route('contact.us') }}" class="inline-flex items-center gap-2 hover:text-[#6d0e16] dark:hover:text-[#f0b0ad] transition-all duration-200 hover:translate-x-[-4px]"><i class="bi bi-chevron-left text-[10px] text-gray-400"></i>{{ __('layout.contact_us') }}</a></li>@endif
          <li><a href="{{ route('blog.index') }}" class="inline-flex items-center gap-2 hover:text-[#6d0e16] dark:hover:text-[#f0b0ad] transition-all duration-200 hover:translate-x-[-4px]"><i class="bi bi-chevron-left text-[10px] text-gray-400"></i>{{ __('layout.blog') }}</a></li>
          <li><a href="{{ route('return.policy') }}" class="inline-flex items-center gap-2 hover:text-[#6d0e16] dark:hover:text-[#f0b0ad] transition-all duration-200 hover:translate-x-[-4px]"><i class="bi bi-chevron-left text-[10px] text-gray-400"></i>{{ __('layout.return_policy') }}</a></li>
        </ul>
      </div>

      <!-- Quick Links 3 -->
      <div class="lg:col-span-3 text-center md:text-right">
        <h3 class="text-lg font-bold text-[#6d0e16] dark:text-[#f0b0ad] mb-6 relative inline-block pb-2">
          {{ __('layout.support') }}
          <span class="absolute bottom-0 right-0 w-1/2 h-0.5 bg-[#6d0e16] dark:bg-[#f0b0ad] rounded-full"></span>
        </h3>
        <ul class="space-y-3 font-medium text-sm">
          <li><a href="{{ route('profile.show') }}" class="inline-flex items-center gap-2 hover:text-[#6d0e16] dark:hover:text-[#f0b0ad] transition-all duration-200 hover:translate-x-[-4px]"><i class="bi bi-chevron-left text-[10px] text-gray-400"></i>{{ __('layout.my_account') }}</a></li>
          @if (Route::has('orders.index'))<li><a href="{{ route('orders.index') }}" class="inline-flex items-center gap-2 hover:text-[#6d0e16] dark:hover:text-[#f0b0ad] transition-all duration-200 hover:translate-x-[-4px]"><i class="bi bi-chevron-left text-[10px] text-gray-400"></i>{{ __('layout.my_orders') }}</a></li>@endif
          <li><a href="{{ route('wishlist') }}" class="inline-flex items-center gap-2 hover:text-[#6d0e16] dark:hover:text-[#f0b0ad] transition-all duration-200 hover:translate-x-[-4px]"><i class="bi bi-chevron-left text-[10px] text-gray-400"></i>{{ __('layout.wishlist') }}</a></li>
          @if (Route::has('track.order'))<li><a href="{{ route('track.order') }}" class="inline-flex items-center gap-2 hover:text-[#6d0e16] dark:hover:text-[#f0b0ad] transition-all duration-200 hover:translate-x-[-4px]"><i class="bi bi-chevron-left text-[10px] text-gray-400"></i>{{ __('layout.track_order') }}</a></li>@endif
          @if (Route::has('page.contact-us'))<li><a href="{{ route('page.contact-us') }}" class="inline-flex items-center gap-2 hover:text-[#6d0e16] dark:hover:text-[#f0b0ad] transition-all duration-200 hover:translate-x-[-4px]"><i class="bi bi-chevron-left text-[10px] text-gray-400"></i>{{ __('layout.contact_us') }}</a></li>@endif
          <li>
              <a href="#" x-data @click.prevent="window.dispatchEvent(new CustomEvent('open-request-modal'))" class="inline-flex items-center gap-2 hover:text-[#6d0e16] dark:hover:text-[#f0b0ad] transition-all duration-200 hover:translate-x-[-4px]"><i class="bi bi-chevron-left text-[10px] text-gray-400"></i>{{ __('layout.request_product') }}</a>
          </li>
        </ul>
      </div>
    </div>

    <!-- Bottom Bar -->
    <div class="mt-12 pt-8 border-t border-gray-200 dark:border-gray-800 flex flex-col xl:flex-row items-center justify-between gap-6">
      
      <!-- Payment Methods -->
      <div class="flex items-center justify-center gap-3">
        <div class="w-12 h-8 bg-white dark:bg-gray-100 rounded flex items-center justify-center p-1.5 shadow-sm">
          <img src="https://upload.wikimedia.org/wikipedia/commons/5/5c/Visa_Inc._logo_%282021%E2%80%93present%29.svg" alt="Visa" class="h-full object-contain">
        </div>
        <div class="w-12 h-8 bg-white dark:bg-gray-100 rounded flex items-center justify-center p-1.5 shadow-sm">
          <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2a/Mastercard-logo.svg/200px-Mastercard-logo.svg.png" alt="Mastercard" class="h-full object-contain">
        </div>
        <div class="w-12 h-8 bg-white dark:bg-gray-100 rounded flex items-center justify-center p-1.5 shadow-sm">
          <img src="https://zaincash.com/static/media/ZainCashLogo.fea8cf3bb90421f45dd384d6afc6fe3b.svg" alt="Zain Cash" class="h-full object-contain">
        </div>
        <div class="w-12 h-8 bg-white dark:bg-gray-100 rounded flex items-center justify-center p-1.5 shadow-sm">
          <img src="https://qi.iq/images/logo.svg?1=1" alt="Qi Card" class="h-full object-contain">
        </div>
      </div>

      <!-- Copyright -->
      <div class="text-sm font-medium text-center flex flex-col sm:flex-row items-center gap-2 text-gray-600 dark:text-gray-400">
        <span>&copy; {{ date('Y') }} {{ __('layout.copyright') }} <a href="{{ route('homepage') }}" class="font-bold text-[#6d0e16] dark:text-[#f0b0ad] hover:underline">Tofof</a></span>
        <span class="hidden sm:inline text-gray-300 dark:text-gray-700">|</span>
        <a href="https://wosooll.com" target="_blank" class="flex items-center gap-1 hover:text-gray-900 dark:hover:text-white transition group">
          <span dir="ltr">Powered By Wosool</span>
          <i class="bi bi-heart-fill text-red-400 group-hover:scale-110 transition-transform"></i>
        </a>
      </div>

      <!-- App Badges -->
      <div class="flex flex-wrap justify-center gap-3">
        <!-- Google Play -->
        <button type="button" class="pwa-install-btn flex items-center gap-2 bg-black hover:bg-gray-900 border border-gray-800 text-white px-3 py-1.5 rounded-lg transition shadow-md" aria-label="تثبيت التطبيق على اندرويد">
          <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" width="20" height="20">
            <polygon points="3,1.5 3,12 12,12" fill="#4CAF50"/>
            <polygon points="3,12 12,12 3,22.5" fill="#1976D2"/>
            <polygon points="3,1.5 12,12 21.5,12" fill="#FFC107"/>
            <polygon points="12,12 21.5,12 3,22.5" fill="#F44336"/>
          </svg>
          <div class="text-right flex flex-col justify-center leading-none">
            <span class="mini text-[10px] text-gray-300">{{ __('layout.available_on') }}</span>
            <span class="label text-sm font-bold">Google Play</span>
          </div>
        </button>

        <!-- App Store -->
        <button type="button" class="pwa-install-btn flex items-center gap-2 bg-black hover:bg-gray-900 border border-gray-800 text-white px-3 py-1.5 rounded-lg transition shadow-md" aria-label="تثبيت التطبيق على ايفون">
          <i class="bi bi-apple text-xl"></i>
          <div class="text-right flex flex-col justify-center leading-none">
            <span class="mini text-[10px] text-gray-300">{{ __('layout.available_on') }}</span>
            <span class="label text-sm font-bold">App Store</span>
          </div>
        </button>

        <!-- Windows -->
        <button type="button" class="pwa-install-btn flex items-center gap-2 bg-black hover:bg-gray-900 border border-gray-800 text-white px-3 py-1.5 rounded-lg transition shadow-md" aria-label="تثبيت التطبيق على ويندوز">
          <i class="bi bi-windows text-[#00a4ef] text-xl"></i>
          <div class="text-right flex flex-col justify-center leading-none">
            <span class="mini text-[10px] text-gray-300">{{ __('layout.available_for') }}</span>
            <span class="label text-sm font-bold">Windows</span>
          </div>
        </button>
      </div>

    </div>
  </div>
</footer>

<!-- عنصر تفريغ المساحة أسفل الصفحة لضمان عدم تغطية الفوتر العائم لأي محتوى (خصوصاً داخل التطبيق/PWA) -->
<div class="h-[120px] w-full lg:hidden bg-transparent pointer-events-none" aria-hidden="true"></div>

@php
  $mobileNavIndex = request()->routeIs('homepage')
      ? 0
      : (request()->routeIs('shop')
          ? 1
          : (request()->routeIs('cart.index')
              ? 2
              : (request()->routeIs('categories.index') ? 3 : 4)));
@endphp
<footer class="fixed bottom-0 left-0 right-0 footer-mobile z-40 lg:hidden" x-show="!searchFocused && !mobileSearchOpen">
  <div class="glass-nav-wrap">
    <nav class="glass-nav" role="navigation" aria-label="التنقل السفلي">
      <div class="glass-items" style="--glass-index: {{ $mobileNavIndex }};">
        <a href="{{ route('homepage') }}" data-fast-nav="true" class="glass-item {{ request()->routeIs('homepage') ? 'active pointer-events-none' : '' }}">
          <i class="bi bi-house-door-fill icon"></i><span class="glass-label mt-0.5">{{ __('layout.home') }}</span>
        </a>
        <a href="{{ route('shop') }}" data-fast-nav="true" class="glass-item {{ request()->routeIs('shop') ? 'active pointer-events-none' : '' }}">
          <i class="bi bi-grid-fill icon"></i><span class="glass-label mt-0.5">{{ __('layout.shop') }}</span>
        </a>

        <a href="{{ route('cart.index') }}" data-fast-nav="true" class="glass-item {{ request()->routeIs('cart.index') ? 'active pointer-events-none' : '' }} relative">
          <i class="bi bi-cart2 icon"></i><span class="glass-label mt-0.5">{{ __('layout.cart') }}</span>
          <span x-show="cartCount > 0" x-text="cartCount" class="badge badge-cart" :class="{'animate-ping-once': isCartUpdated}" style="display: none; top: 2px; right: 15px;"></span>
        </a>

        <a href="{{ route('categories.index') }}" data-fast-nav="true" class="glass-item {{ request()->routeIs('categories.index') ? 'active pointer-events-none' : '' }}">
          <i class="bi bi-grid-3x3-gap-fill icon"></i><span class="glass-label mt-0.5">{{ __('layout.categories') }}</span>
        </a>
        <a href="{{ route('profile.show') }}" data-fast-nav="true" class="glass-item {{ request()->routeIs('profile.*', 'login', 'register') ? 'active pointer-events-none' : '' }}">
          <i class="bi bi-person-fill icon"></i><span class="glass-label mt-0.5">{{ __('layout.my_account') }}</span>
        </a>
        <span class="glass-indicator" aria-hidden="true"></span>
      </div>
    </nav>
  </div>
</footer>

    @auth
    <script>
    function userNotificationsComponent(fetchUrl, markUrl) {
        return {
            notifications: [], unreadCount: 0, dropdownOpen: false,
            fetchNotifications() {
                fetch(fetchUrl).then(response => response.json()).then(data => {
                    this.notifications = data.notifications || []; this.unreadCount = data.unread_count || 0;
                }).catch(err => console.error('Failed to fetch user notifications:', err));
            },
            markAsRead(notification) {
                if (notification?.data?.url) window.location.href = notification.data.url;
                if (!notification.read_at) {
                    fetch(markUrl, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')},
                        body: JSON.stringify({ id: notification.id })
                    }).then(() => {
                        notification.read_at = new Date().toISOString(); this.unreadCount = Math.max(0, this.unreadCount - 1);
                    }).catch(err => console.error('Failed to mark notification as read:', err));
                }
            },
            timeAgo(dateString) {
                const date = new Date(dateString); const seconds = Math.floor((new Date() - date) / 1000);
                const intervals = { 'سنة': 31536000, 'شهر': 2592000, 'يوم': 86400, 'ساعة': 3600, 'دقيقة': 60 };
                for (let unit in intervals) { const interval = Math.floor(seconds / intervals[unit]); if (interval >= 1) return `منذ ${interval} ${unit}`; }
                return 'الآن';
            }
        }
    }
    </script>
    @endauth

    <script>
      (function(){
        const nav = document.getElementById('desktopNav'); if (!nav) return;
        let lastY = window.pageYOffset || 0, ticking = false, threshold = 10;
        function onScroll(){
          const y = window.pageYOffset || 0, delta = y - lastY;
          if (Math.abs(delta) > threshold) {
            nav.classList.toggle('fade-out', delta > 0); lastY = y;
          }
          ticking = false;
        }
        window.addEventListener('scroll', function(){
          if (window.innerWidth < 768) return;
          if (!ticking) { window.requestAnimationFrame(onScroll); ticking = true; }
        }, { passive: true });
        window.addEventListener('load', () => { if ((window.pageYOffset || 0) <= 0) nav.classList.remove('fade-out'); });
      })();
    </script>

{{-- Skeleton Loader (Transitions) --}}
<div id="tofof-skeleton">
  <div class="sk-body">
    <div class="sk-banner"></div>
    <div class="sk-grid">
      <div class="sk-card"></div>
      <div class="sk-card"></div>
      <div class="sk-card"></div>
      <div class="sk-card"></div>
      <div class="sk-card"></div>
      <div class="sk-card"></div>
    </div>
    <div class="sk-list">
      <div class="sk-row"></div>
      <div class="sk-row"></div>
      <div class="sk-row"></div>
    </div>
  </div>
</div>

<style>
  /* ===== Skeleton أمام الهيدر وخلف الفوتر ===== */
  #tofof-skeleton {
    position: fixed;
    inset: 0;
    z-index: 35;
    background: var(--sk-bg, #f3f4f6);
    overflow: hidden;
    display: none; /* مخفي افتراضياً */
  }
  html.app-shell-loading #tofof-skeleton {
    display: block; /* يظهر فقط عند التحميل */
  }
  html.dark #tofof-skeleton { --sk-bg: #111827; --sk-card: #1f2937; --sk-shine: rgba(255,255,255,0.04); }

  .sk-body {
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    height: 100%;
  }

  /* شيمر مشترك */
  .sk-banner, .sk-card, .sk-row {
    background: linear-gradient(
      90deg,
      var(--sk-card, #e5e7eb) 25%,
      var(--sk-shine, rgba(255,255,255,0.7)) 50%,
      var(--sk-card, #e5e7eb) 75%
    );
    background-size: 300% 100%;
    border-radius: 10px;
    animation: skShimmer 1.6s ease-in-out infinite;
  }

  .sk-banner  { height: 160px; border-radius: 14px; }

  .sk-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
  }
  .sk-card    { height: 128px; border-radius: 12px; }

  .sk-list    { display: flex; flex-direction: column; gap: 10px; margin-top: 4px; }
  .sk-row     { height: 48px; border-radius: 10px; }

  @keyframes skShimmer {
    0%   { background-position: 100% 0; }
    100% { background-position: -100% 0; }
  }

  /* تأخير بسيط لكل عنصر */
  .sk-card:nth-child(2)  { animation-delay: 0.10s; }
  .sk-card:nth-child(3)  { animation-delay: 0.20s; }
  .sk-card:nth-child(4)  { animation-delay: 0.05s; }
  .sk-card:nth-child(5)  { animation-delay: 0.15s; }
  .sk-card:nth-child(6)  { animation-delay: 0.25s; }
  .sk-row:nth-child(2)   { animation-delay: 0.12s; }
  .sk-row:nth-child(3)   { animation-delay: 0.24s; }
</style>

<script>
(function () {
  const STORAGE_KEY = 'tofof-page-loading';
  const root = document.documentElement;

  function clearLoadingState() {
    root.classList.remove('app-shell-loading');
    root.classList.add('app-shell-ready');
    try { sessionStorage.removeItem(STORAGE_KEY); } catch (e) {}
    setTimeout(() => root.classList.remove('app-shell-ready'), 320);
  }

  function activateLoadingState() {
    root.classList.add('app-shell-loading');
    try { sessionStorage.setItem(STORAGE_KEY, '1'); } catch (e) {}
  }

  function isInternalNavigableLink(link, event) {
    if (!link) return false;
    if (event.defaultPrevented) return false;
    if (event.button !== 0) return false;
    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return false;
    if (link.hasAttribute('download')) return false;
    if (link.target && link.target.toLowerCase() === '_blank') return false;
    if (link.dataset.noSkeleton === 'true') return false;

    const href = link.getAttribute('href') || '';
    if (!href || href.startsWith('#')) return false;
    if (/^(javascript:|mailto:|tel:)/i.test(href)) return false;

    let url;
    try {
      url = new URL(link.href, window.location.href);
    } catch (e) {
      return false;
    }

    if (url.origin !== window.location.origin) return false;

    // Ignore anchor-only moves in the same document.
    if (
      url.pathname === window.location.pathname &&
      url.search === window.location.search &&
      url.hash &&
      url.hash !== '#'
    ) {
      return false;
    }

    return true;
  }

  window.tofofActivateSkeletonTransition = function (targetUrl, options = {}) {
    activateLoadingState();

    if (!targetUrl) {
      return;
    }

    const delay = Number.isFinite(options.delay) ? options.delay : 90;
    const useReplace = options.replace === true;
    setTimeout(() => {
      if (useReplace) {
        window.location.replace(targetUrl);
      } else {
        window.location.assign(targetUrl);
      }
    }, Math.max(0, delay));
  };

  document.addEventListener('click', function (event) {
    const link = event.target.closest('a[href]');
    if (!isInternalNavigableLink(link, event)) return;
    activateLoadingState();
  }, true);

  window.addEventListener('DOMContentLoaded', clearLoadingState);
  window.addEventListener('pageshow', clearLoadingState);
})();
</script>

<script>
(function () {
  /* =====================================================
     نظام التنقل السريع للفوتر - مع تحميل مسبق
     ===================================================== */

  const NAV_ITEMS = [
    '{{ route("homepage") }}',
    '{{ route("shop") }}',
    '{{ route("cart.index") }}',
    '{{ route("categories.index") }}',
    '{{ route("profile.show") }}'
  ];

  // مؤشر الصفحة الحالية
  const currentPath = window.location.pathname;
  let currentIndex = 0;
  NAV_ITEMS.forEach((url, i) => {
    try {
      const p = new URL(url, window.location.origin).pathname;
      if (currentPath === p || currentPath.startsWith(p + '/')) {
        currentIndex = i;
      }
    } catch(e) {}
  });
  // صفحات Auth → تحديد "حسابي" (index 4)
  const authPaths = ['/login', '/register', '/password', '/verify-otp', '/otp', '/whatsapp/verify'];
  if (authPaths.some(p => currentPath === p || currentPath.startsWith(p + '/'))) {
    currentIndex = 4;
  }

  // ===== التحميل المسبق لكل صفحات الفوتر =====
  function prefetchFooterPages() {
    NAV_ITEMS.forEach(url => {
      try {
        const parsed = new URL(url, window.location.origin);
        if (parsed.pathname === window.location.pathname) return;
        if (document.querySelector(`link[rel="prefetch"][href="${url}"]`)) return;
        const link = document.createElement('link');
        link.rel = 'prefetch';
        link.as = 'document';
        link.href = url;
        document.head.appendChild(link);
      } catch(e) {}
    });
  }

  // ===== إعداد أزرار الفوتر =====
  function setupFooterNav() {
    const container = document.querySelector('.footer-mobile .glass-items');
    if (!container) return;

    const items = Array.from(container.querySelectorAll('.glass-item'));

    // تعيين الـ active الصحيح
    items.forEach((item, idx) => {
      if (idx === currentIndex) {
        item.classList.add('active');
        container.style.setProperty('--glass-index', String(idx));
      } else {
        item.classList.remove('active');
      }
    });

    items.forEach((item, idx) => {
      item.addEventListener('click', (e) => {
        e.preventDefault();
        if (idx === currentIndex) {
          // Do nothing
          return;
        }
        // تحديث الـ UI فوراً
        items.forEach(el => el.classList.toggle('active', el === item));
        container.style.setProperty('--glass-index', String(idx));

        window.location.assign(NAV_ITEMS[idx]);
      });
    });
  }

  // ===== التحميل المسبق للروابط العادية عند التمرير عليها =====
  function setupGeneralPrefetch() {
    document.addEventListener('pointerover', (e) => {
      const link = e.target.closest('a[href]');
      if (!link || link.closest('.footer-mobile')) return;
      const href = link.href;
      if (!href || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) return;
      if (document.querySelector(`link[rel="prefetch"][href="${href}"]`)) return;
      try {
        const parsed = new URL(href);
        if (parsed.origin !== window.location.origin) return;
        if (parsed.pathname === window.location.pathname) return;
        const pf = document.createElement('link');
        pf.rel = 'prefetch'; pf.as = 'document'; pf.href = href;
        document.head.appendChild(pf);
      } catch(e) {}
    }, { passive: true });
  }

  document.addEventListener('DOMContentLoaded', () => {
    prefetchFooterPages();
    setupFooterNav();
    setupGeneralPrefetch();
  });

})();
</script>


<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('liveSearch', (searchUrl) => ({
    query: '', results: [], loading: false, showResults: false, highlightedIndex: -1, minChars: 2,
    onFocus() { if (this.query.length >= this.minChars) this.showResults = true; },
    search() {
      this.showResults = true;
      if (this.query.trim().length < this.minChars) { this.results = []; this.loading = false; return; }
      clearTimeout(this._debounce);
      this._debounce = setTimeout(() => this.fetchResults(searchUrl), 250);
    },
    fetchResults(searchUrl) {
      if (this._ac) { try { this._ac.abort(); } catch (e) {} }
      this._ac = new AbortController();
      this.loading = true; this.showResults = true;
      fetch(`${searchUrl}?query=${encodeURIComponent(this.query.trim())}`, { signal: this._ac.signal })
        .then(r => r.json()).then(data => {
          let flattened = [];
          
          // Brands
          if (data.brands && data.brands.length) {
            flattened.push({ type: 'header', label: 'البراندات' });
            data.brands.forEach(b => {
              flattened.push({
                type: 'brand', id: b.id, name: b.name_ar || b.name_en || '',
                url: `{{ url('/shop') }}?brand=${b.slug || b.id}`
              });
            });
          }

          // Categories
          if (data.categories && data.categories.length) {
            flattened.push({ type: 'header', label: 'الفئات' });
            data.categories.forEach(c => {
              flattened.push({
                type: 'category', id: c.id, name: c.name_ar || c.name_en || '',
                url: `{{ url('/shop') }}?category=${c.slug || c.id}`
              });
            });
          }

          // Products
          if (data.products && data.products.length) {
            flattened.push({ type: 'header', label: 'المنتجات' });
            data.products.forEach(p => {
              flattened.push({
                type: 'product', id: p.id, name: p.name_ar || p.name_en || '',
                category: p.category_name, url: `{{ url('/product') }}/${p.slug || p.id}`,
                image: p.image_url || 'https://via.placeholder.com/150'
              });
            });
          }

          this.results = flattened;
          // Keep highlightedIndex at -1 by default so Enter submits the form unless user moves arrow keys
          this.highlightedIndex = -1;
        })
        .catch(err => { if (err.name !== 'AbortError') { this.results = []; this.showResults = true; }})
        .finally(() => { this.loading = false; });
    },
    moveHighlight(direction) {
      if (!this.showResults || this.loading || !this.results.length) return;
      let index = this.highlightedIndex;
      const total = this.results.length;
      
      for (let i = 0; i < total; i++) {
        index = (direction === 'down') ? (index + 1) % total : (index - 1 + total) % total;
        if (this.results[index].type !== 'header') {
          this.highlightedIndex = index;
          return;
        }
      }
    },
    selectHighlighted() {
      if (this.highlightedIndex > -1 && this.results[this.highlightedIndex]) {
        window.location.href = this.results[this.highlightedIndex].url;
      }
    }
  }));
});
</script>

<script>
(function(){
  const header = document.getElementById('mobileHeader'); if (!header) return;
  const DOWN_THRESHOLD = 100, UP_THRESHOLD = 60, SPEED_TRIGGER = 1.2;
  const isMobile = () => window.innerWidth < 768;
  let isRounded = false, lastY = window.scrollY, lastT = performance.now();
  function snapRemoveRounded() {
    header.classList.add('fast-off'); header.classList.remove('rounded'); isRounded = false;
    requestAnimationFrame(() => header.classList.remove('fast-off'));
  }
  function apply() {
    if (!isMobile()) {
      header.classList.remove('rounded', 'fast-off'); isRounded = false;
      lastY = window.scrollY; lastT = performance.now(); return;
    }
    const y = window.scrollY, t = performance.now(), dy = y - lastY, dt = Math.max(1, t - lastT), vy = dy / dt;
    if (!isRounded && y > DOWN_THRESHOLD) { header.classList.add('rounded'); isRounded = true; }
    if (dy < 0 && (Math.abs(vy) > SPEED_TRIGGER || (isRounded && y < UP_THRESHOLD))) snapRemoveRounded();
    lastY = y; lastT = t;
  }
  apply();
  window.addEventListener('scroll', apply, { passive: true });
  window.addEventListener('resize', apply);
})();
</script>
{{-- ==== Global "Request Unavailable Product" Modal ==== --}}
<template x-teleport="body">
  <div
    x-data="{open:false,loading:false,successMsg:'',errorMsg:''}"
    x-on:open-request-modal.window="
      open = true;
      loading = false;
      successMsg = '';
      errorMsg = '';
    "
    x-show="open"
    style="display:none"
    class="fixed inset-0 z-[200]"
  >
    <div class="absolute inset-0 bg-black/50" @click="open=false"></div>

    <div
      class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-[95vw] max-w-xl bg-white rounded-2xl shadow-2xl border p-5 dark:bg-gray-800 dark:border-gray-700"
    >
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-extrabold text-gray-900 dark:text-gray-100">
          طلب منتج غير متوفر
        </h3>
        <button class="text-2xl leading-none text-gray-500" @click="open=false">&times;</button>
      </div>

      <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
        يرجى تعبئة التفاصيل أدناه لنقوم بتوفير المنتج لك في أقرب وقت ممكن.
      </p>

      <form class="mt-4 grid gap-3" @submit.prevent="
        loading=true; successMsg=''; errorMsg='';
        fetch('{{ route('product-requests.store') }}', {
          method:'POST',
          headers:{
            'X-CSRF-TOKEN':'{{ csrf_token() }}',
            'Accept':'application/json',
            'Content-Type':'application/json'
          },
          body: JSON.stringify({
            product_name: $refs.product_name.value,
            brand: $refs.brand.value,
            link: $refs.link.value,
            notes: $refs.notes.value,
            phone: $refs.phone.value
          })
        }).then(r=>r.json()).then(d=>{
          if(d.success){
            successMsg = 'تم استلام طلبك، سنتواصل معك في أقرب وقت.';
            $refs.product_name.value='';
            $refs.brand.value='';
            $refs.link.value='';
            $refs.notes.value='';
            $refs.phone.value='';
          }else{
            errorMsg = d.message || 'تعذر إرسال الطلب. يرجى المحاولة مرة أخرى.';
          }
        }).catch(()=>{ errorMsg='تعذر إرسال الطلب. يرجى المحاولة مرة أخرى.' })
        .finally(()=>loading=false)
      ">
        <div>
          <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200">
            اسم المنتج <span class="text-red-500">*</span>
          </label>
          <input x-ref="product_name" type="text" required
                 class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[var(--primary-color)] dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
                 placeholder="مثال: ساعة رولكس، إكسسوارات، الخ.">
        </div>

        <div class="grid sm:grid-cols-2 gap-3">
          <div>
            <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200">الماركة</label>
            <input x-ref="brand" type="text"
                   class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
                   placeholder="مثال: رولكس، كاسيو، الخ.">
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200">رابط للمنتج ان وجد (اختياري)</label>
            <input x-ref="link" type="url"
                   class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
                   placeholder="https://example.com/product">
          </div>
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200">ملاحظات إضافية</label>
          <textarea x-ref="notes" rows="3"
                     class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 resize-y dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
                     placeholder="مثال: يرجى توفير اللون الأسود، ويفضل التوصيل في أسرع وقت."></textarea>
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200">
            رقم الهاتف / واتساب <span class="text-red-500">*</span>
          </label>
          <input x-ref="phone" type="tel" required
                 class="mt-1 w-full rounded-xl border border-gray-300 px-3 py-2 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
                 placeholder="+9647xxxxxxxxx">
          <p class="text-xs text-gray-500 mt-1">
            نستخدم هذا الرقم للتواصل معك عبر الواتساب لتوفير المنتج لك في أسرع وقت.
          </p>
        </div>

        <div class="flex items-center gap-3 mt-2">
  <button type="submit" :disabled="loading"
          class="inline-flex items-center gap-2 px-4 py-2 rounded-xl font-bold text-white disabled:opacity-70"
          style="background:#6d0e16;">
    <span x-show="!loading"><i class="bi bi-send"></i> إرسال الطلب</span>
    <span x-show="loading"><i class="bi bi-arrow-repeat animate-spin"></i> جاري الإرسال...</span>
  </button>

  <button type="button"
          class="px-4 py-2 rounded-xl border"
          @click="open=false">
    إلغاء
  </button>
</div>

        <p x-show="successMsg" class="text-sm text-green-600 mt-2" x-text="successMsg"></p>
        <p x-show="errorMsg" class="text-sm text-red-600 mt-2" x-text="errorMsg"></p>
      </form>
    </div>
  </div>
</template>

{{-- 🌟 Premium Welcome Screen Modal --}}
@if(($show_welcome_screen ?? 'off') === 'on')
<div
    x-show="showWelcome"
  x-data="{
    welcomeImageOnly: false,
    closeBtnSize: 36,
    closeBtnGap: 10,
    syncWelcomeCloseSize() {
      if (!this.welcomeImageOnly) {
        this.closeBtnSize = 36;
        return;
      }

      const img = this.$refs.welcomeContent?.querySelector('img');
      if (!img) {
        this.closeBtnSize = 36;
        return;
      }

      const renderedWidth = img.getBoundingClientRect().width || 0;
      const computed = Math.round(renderedWidth * 0.075);
      this.closeBtnSize = Math.max(30, Math.min(44, computed || 36));

      if (!img.complete) {
        img.addEventListener('load', () => {
          this.syncWelcomeCloseSize();
        }, { once: true });
      }
    },
    detectWelcomeMode() {
      const container = this.$refs.welcomeContent;
      if (!container) {
        this.welcomeImageOnly = false;
        return;
      }

      const clone = container.cloneNode(true);
      clone.querySelectorAll('script,style,noscript').forEach((el) => el.remove());

      const hasImage = !!clone.querySelector('img');
      const plainText = (clone.textContent || '').replace(/\u00A0/g, ' ').trim();
      this.welcomeImageOnly = hasImage && plainText.length === 0;

      this.$nextTick(() => this.syncWelcomeCloseSize());
    }
  }"
  x-init="$watch('showWelcome', (value) => { if (value) { $nextTick(() => detectWelcomeMode()); } }); window.addEventListener('resize', () => { if (showWelcome) { syncWelcomeCloseSize(); } }); if (showWelcome) { $nextTick(() => detectWelcomeMode()); }"
    x-transition:enter="transition ease-out duration-500"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[300] flex items-center justify-center p-0"
    x-cloak
>
    {{-- Overlay --}}
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="closeWelcomeModal()"></div>

    {{-- Simplified Modal Card --}}
    <div
      class="welcome-modal-card relative w-full max-w-[480px] bg-white dark:bg-gray-900 shadow-2xl overflow-hidden transition-all mx-4 rounded-3xl flex flex-col items-center justify-center p-8 min-h-[480px]"
      :class="{ 'welcome-image-only': welcomeImageOnly }"
      :style="welcomeImageOnly ? `--welcome-close-space:${closeBtnSize + closeBtnGap + 4}px;` : '--welcome-close-space:0px;'"
        x-show="showWelcome"
        x-transition:enter="transition ease-out duration-500 transform"
        x-transition:enter-start="scale-95 opacity-0"
        x-transition:enter-end="scale-100 opacity-100"
        x-transition:leave="transition ease-in duration-300 transform"
        x-transition:leave-start="scale-100 opacity-100"
        x-transition:leave-end="scale-95 opacity-0"
    >
        <div class="welcome-content-shell relative z-10 flex flex-col items-center text-center w-full" :class="{ 'welcome-content-shell-image': welcomeImageOnly }">
      <button
        type="button"
        @click="closeWelcomeModal()"
        class="welcome-close-btn"
        :class="{ 'welcome-close-btn-image': welcomeImageOnly }"
        :style="`width:${closeBtnSize}px;height:${closeBtnSize}px;`"
        aria-label="إغلاق"
      >
        <i class="bi bi-x-lg"></i>
      </button>

            {{-- Content only --}}
        <div x-ref="welcomeContent" class="w-full welcome-text-container dark:text-gray-100" dir="rtl">
                {!! $welcome_screen_content ?? '' !!}
            </div>
        </div>
    </div>

        {{-- Hidden Close Area (clicking the modal itself won't close it, but children can) --}}
    </div>
</div>

<style>
  .welcome-modal-card {
    max-width: min(92vw, 480px);
  }

  .welcome-close-btn {
    position: absolute;
    top: 0.75rem;
    left: 0.75rem;
    width: clamp(2rem, 6vw, 2.25rem);
    height: clamp(2rem, 6vw, 2.25rem);
    border: 0;
    border-radius: 999px;
    background: rgba(17, 24, 39, 0.7);
    color: #ffffff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: clamp(0.9rem, 2.5vw, 1rem);
    z-index: 20;
    transition: transform 0.2s ease, background-color 0.2s ease;
  }
  .welcome-close-btn:hover {
    transform: scale(1.05);
    background: rgba(17, 24, 39, 0.9);
  }

  .welcome-content-shell {
    width: 100%;
  }

  .welcome-content-shell.welcome-content-shell-image {
    width: auto;
    display: inline-block;
    position: relative;
    padding-top: var(--welcome-close-space, 0px);
  }

  .welcome-close-btn.welcome-close-btn-image {
    position: absolute;
    top: 0;
    left: 50%;
    transform: translateX(-50%);
  }

  .welcome-close-btn.welcome-close-btn-image:hover {
    transform: translateX(-50%) scale(1.05);
  }

  .welcome-modal-card.welcome-image-only {
    background: transparent !important;
    box-shadow: none !important;
    min-height: 0 !important;
    padding: 0 !important;
    width: auto !important;
    max-width: calc(100vw - 1rem) !important;
    border-radius: 0 !important;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .welcome-modal-card.welcome-image-only .welcome-text-container {
    width: auto !important;
  }

    .welcome-text-container img {
        max-width: 100%;
        height: auto;
    display: block;
    margin: 0 auto;
  }

  .welcome-modal-card.welcome-image-only .welcome-text-container img {
    width: auto;
    max-width: calc(100vw - 1rem);
    max-height: 88vh;
    margin: 0;
    border-radius: 18px;
    }

    .welcome-text-container p, .welcome-text-container h1, .welcome-text-container h2 {
        margin: 0;
    }
</style>
@endif

<div id="iosInstallGuideModal" class="fixed inset-0 z-[250] hidden items-center justify-center p-4 ios-install-modal" aria-hidden="true">
  <div class="ios-install-card w-full max-w-md p-5 md:p-6 text-right">
    <div class="flex items-center justify-between gap-3 mb-4">
      <div class="flex items-center gap-3">
        <img src="{{ asset('logo.png') }}" alt="Tofof" class="w-10 h-10 rounded-lg border border-gray-200 dark:border-gray-700 object-contain bg-white">
        <div>
          <p id="installGuideTitle" class="font-extrabold text-[#6d0e16] dark:text-[#f0b0ad] leading-none">تثبيت طفوف</p>
          <p id="installGuideSubTitle" class="text-xs text-gray-500 dark:text-gray-400 mt-1">شرح التثبيت على جهازك</p>
        </div>
      </div>
      <button type="button" id="closeIosInstallGuide" class="text-gray-500 hover:text-red-600 text-2xl leading-none" aria-label="اغلاق">
        <i class="bi bi-x"></i>
      </button>
    </div>

    <div class="space-y-3 text-sm text-gray-700 dark:text-gray-200" id="installGuideSteps">
      <p class="font-bold">الخطوات:</p>
      <p>1. اضغط زر التثبيت المباشر.</p>
      <p>2. إذا لم يظهر خيار التثبيت، استخدم خيار إضافة إلى الشاشة الرئيسية من المتصفح.</p>
    </div>

    <div class="mt-5 flex justify-end">
      <button type="button" id="iosInstallGuideDone" class="px-4 py-2 rounded-xl text-white font-bold" style="background:#6d0e16;">
        فهمت
      </button>
    </div>
  </div>
</div>

<script>
  (function () {
    const pwaBtns = document.querySelectorAll('.pwa-install-btn');
    const iosModal = document.getElementById('iosInstallGuideModal');
    const installGuideTitle = document.getElementById('installGuideTitle');
    const installGuideSubTitle = document.getElementById('installGuideSubTitle');
    const installGuideSteps = document.getElementById('installGuideSteps');
    const closeIosBtn = document.getElementById('closeIosInstallGuide');
    const doneIosBtn = document.getElementById('iosInstallGuideDone');

    const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    const ua = navigator.userAgent || navigator.vendor || window.opera;
    const isIOS = /iPad|iPhone|iPod/.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);

    function openInstallGuide() {
      if (!iosModal) return;
      if (isIOS) {
        installGuideTitle.textContent = 'تثبيت طفوف على iPhone / iPad';
        installGuideSubTitle.textContent = 'في iOS لا يوجد تثبيت مباشر من الزر، اتبع الخطوات التالية';
        installGuideSteps.innerHTML = `
          <p class="font-bold">الخطوات:</p>
          <p>1. افتح الموقع من متصفح Safari.</p>
          <p>2. اضغط زر المشاركة <i class="bi bi-box-arrow-up"></i>.</p>
          <p>3. اختر "Add to Home Screen" أو "إضافة إلى الشاشة الرئيسية".</p>
          <p>4. اضغط "Add" وسيظهر التطبيق على سطح الجهاز.</p>
        `;
      } else {
        installGuideTitle.textContent = 'تثبيت طفوف (للكمبيوتر والأندرويد)';
        installGuideSubTitle.textContent = 'إذا لم يظهر التثبيت المباشر، اتبع هذه الخطوات:';
        installGuideSteps.innerHTML = `
          <p class="font-bold">الخطوات:</p>
          <p>1. من متصفحك الحالي اضغط قائمة الخيارات (⋮ أو ⋯).</p>
          <p>2. اختر "Install app" أو "Add to Home screen" أو "App install".</p>
          <p>3. أكّد التثبيت وسيظهر التطبيق على جهازك.</p>
        `;
      }
      iosModal.classList.remove('hidden');
      iosModal.classList.add('flex');
      iosModal.setAttribute('aria-hidden', 'false');
    }

    function closeInstallGuide() {
      if (!iosModal) return;
      iosModal.classList.add('hidden');
      iosModal.classList.remove('flex');
      iosModal.setAttribute('aria-hidden', 'true');
    }

    if (closeIosBtn) closeIosBtn.addEventListener('click', closeInstallGuide);
    if (doneIosBtn) doneIosBtn.addEventListener('click', closeInstallGuide);
    if (iosModal) {
      iosModal.addEventListener('click', function (e) {
        if (e.target === iosModal) closeInstallGuide();
      });
    }

    function resetButtonsToInstallState() {
      pwaBtns.forEach(pwaBtn => {
        const label = pwaBtn.querySelector('.label');
        const mini = pwaBtn.querySelector('.mini');
        const ariaLabel = pwaBtn.getAttribute('aria-label') || '';
        
        if (label) {
          if (ariaLabel.includes('اندرويد')) label.textContent = 'Google Play';
          else if (ariaLabel.includes('ايفون')) label.textContent = 'App Store';
          else if (ariaLabel.includes('ويندوز')) label.textContent = 'Windows';
          else label.textContent = 'تثبيت التطبيق';
        }
        if (mini) {
          if (ariaLabel.includes('ويندوز')) mini.textContent = 'متوفر لـ';
          else mini.textContent = 'متوفر على';
        }
        
        pwaBtn.classList.remove('opacity-70', 'cursor-not-allowed');
        pwaBtn.dataset.installed = "false";
      });
    }

    function setButtonsToInstalledState() {
      pwaBtns.forEach(pwaBtn => {
        const label = pwaBtn.querySelector('.label');
        const mini = pwaBtn.querySelector('.mini');
        if (label) label.textContent = 'مثبت بالفعل';
        if (mini) mini.textContent = 'التطبيق';
        pwaBtn.classList.add('opacity-70', 'cursor-not-allowed');
        pwaBtn.dataset.installed = "true";
      });
    }

    window.addEventListener('beforeinstallprompt', function (e) {
      e.preventDefault();
      window.__tofofDeferredInstallPrompt = e;
      if (localStorage.getItem('tofof_pwa_installed') === '1') {
        localStorage.removeItem('tofof_pwa_installed');
        if (!isStandalone) {
          resetButtonsToInstallState();
        }
      }
    });

    async function waitForInstallPrompt(timeoutMs) {
      if (window.__tofofDeferredInstallPrompt) return window.__tofofDeferredInstallPrompt;
      return new Promise((resolve) => {
        let done = false;
        const onReady = function (e) {
          if (done) return;
          done = true;
          clearTimeout(timer);
          resolve(window.__tofofDeferredInstallPrompt);
        };
        const timer = setTimeout(() => {
          if (done) return;
          done = true;
          window.removeEventListener('beforeinstallprompt', onReady);
          resolve(window.__tofofDeferredInstallPrompt);
        }, timeoutMs);
        window.addEventListener('beforeinstallprompt', onReady, { once: true });
      });
    }

    window.addEventListener('appinstalled', function() {
      localStorage.setItem('tofof_pwa_installed', '1');
      setButtonsToInstalledState();
    });

    const isInstalled = isStandalone || localStorage.getItem('tofof_pwa_installed') === '1';

    if (pwaBtns.length > 0) {
      if (isInstalled) {
        setButtonsToInstalledState();
      }

      pwaBtns.forEach(pwaBtn => {
        pwaBtn.addEventListener('click', async function () {
          if (pwaBtn.dataset.installed === "true") {
            alert('التطبيق مثبت بالفعل على جهازك.');
            return;
          }

          const deferredPrompt = await waitForInstallPrompt(1500);

          if (deferredPrompt) {
            try {
              deferredPrompt.prompt();
              const choice = await deferredPrompt.userChoice;
              if (choice.outcome === 'accepted') {
                window.__tofofDeferredInstallPrompt = null;
                localStorage.setItem('tofof_pwa_installed', '1');
                setButtonsToInstalledState();
              }
            } catch (err) {
              console.error('Install prompt failed:', err);
              openInstallGuide();
            }
          } else {
            if (isIOS) {
              openInstallGuide();
            } else {
              alert('التطبيق مثبت بالفعل على جهازك.');
            }
          }
        });
      });
    }

    if ('serviceWorker' in navigator) {
      navigator.serviceWorker
        .register('/sw.js', { scope: '/' })
        .catch(function (err) {
          console.error('Service worker registration failed:', err);
        });
    }
  })();
</script>

    @stack("scripts")
</body>
</html>
