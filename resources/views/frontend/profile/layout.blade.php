@extends('layouts.app')
@section('title', $title ?? __('profile.my_account'))

@section('content')
@php
    // ألوان الهوية
    $brand      = '#0F2A44';
    $brandDark  = '#0A1D2F';
    $brandBg    = '#E6E6E6';

    // هل نحن في صفحة الملف الرئيسية؟
    $isProfileHome = request()->routeIs('profile.show');

    // وضع التعديل على الموبايل (يفتح المحتوى بدلاً من قائمة الأزرار)
    $forceEdit = request()->boolean('edit') || request('mode') === 'edit';

    // إن كنا في صفحة الملف الرئيسية ولم نطلب التعديل على الموبايل -> نُظهر القائمة فقط
    $showListOnMobile = $isProfileHome && !$forceEdit;

    // بيانات المستخدم المختصرة للبطاقة
    $u = auth()->user();
    $ordersCount = $u?->orders()->count() ?? 0;
    $tierLabel   = $u?->tier ?? '—';
    $wallet      = number_format($u?->wallet_balance ?? 0, 0);

    // ===== مسار الصورة: استخدم الـ accessor avatar_url مع fallback تلقائي =====
    $avatarSrc = $u?->avatar_url ?? asset('storage/avatars/default.jpg');
@endphp

<div class="container mx-auto px-4 pt-2 pb-4 md:pt-4">
    <div class="flex flex-col gap-6">
        <div class="flex flex-col lg:flex-row gap-6">

            {{-- الشريط الجانبي (ديسكتوب) --}}
            <aside class="hidden lg:block w-full lg:w-1/4">
                <nav
                    class="bg-white rounded-lg shadow-sm border sticky top-4"
                    style="border-color:#eadbcd; max-height: calc(100vh - 2rem); overflow:auto;"
                >
                    <ul class="py-3">
                        <li>
                            <a href="{{ route('profile.show') }}"
                               class="menu-item {{ request()->routeIs('profile.show') ? 'is-active' : '' }}">
                                <i class="bi bi-person-fill"></i>
                                <span>{{ __('profile.my_profile') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('profile.orders') }}"
                               class="menu-item {{ request()->routeIs('profile.orders*') ? 'is-active' : '' }}">
                                <i class="bi bi-box-seam"></i>
                                <span>{{ __('profile.my_orders') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('profile.addresses.index') }}"
                               class="menu-item {{ request()->routeIs('profile.addresses*') ? 'is-active' : '' }}">
                                <i class="bi bi-geo-alt-fill"></i>
                                <span>{{ __('profile.shipping_addresses') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('wallet.index') }}"
                               class="menu-item {{ request()->routeIs('wallet.*') ? 'is-active' : '' }}">
                                <i class="bi bi-wallet2"></i>
                                <span>{{ __('profile.wallet') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('wishlist') }}"
                               class="menu-item {{ request()->routeIs('wishlist') ? 'is-active' : '' }}">
                                <i class="bi bi-heart-fill"></i>
                                <span>{{ __('profile.wishlist') }}</span>
                            </a>
                        </li>
                    </ul>

                    <div class="mt-2 p-3 border-t" style="border-color:#eadbcd">
                        <a href="{{ route('logout') }}"
                           class="menu-item danger"
                           onclick="event.preventDefault(); document.getElementById('logout-form-desktop').submit();">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>{{ __('profile.logout') }}</span>
                        </a>
                        <form id="logout-form-desktop" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
                    </div>
                </nav>
            </aside>

            {{-- المحتوى الرئيسي: يظهر دائمًا على الديسكتوب، وعلى الموبايل يظهر فقط إن كان edit=1 --}}
            <main class="w-full lg:w-3/4 bg-white rounded-lg shadow-sm border p-4 md:p-6 {{ $showListOnMobile ? 'profile-main--mobile-hidden' : '' }}">
                @yield('profile-content')
            </main>
        </div>

        {{-- قائمة الجوال (تظهر فقط في صفحة الملف الرئيسية عندما لا يكون edit=1) --}}
        @if($showListOnMobile)
        <section class="lg:hidden space-y-4">

            {{-- ===== بطــاقة المعلومات المختصرة (الصورة + الاسم + الرقم + الإحصائيات) ===== --}}
            <div class="info-card">
                <div class="flex items-center gap-3">
                    <img src="{{ $avatarSrc }}" alt="avatar" class="w-14 h-14 rounded-2xl object-cover border"
                         style="border-color:{{$brand}}">
                    <div class="min-w-0">
                        <div class="font-extrabold text-[#4a3f3f] truncate">{{ $u?->name }}</div>
                        <a href="{{ route('profile.show', ['edit' => 1]) }}"
                           class="text-sm text-[#6b7280] ltr hover:text-[{{$brandDark}}] transition"
                           title="{{ __('profile.edit_profile_btn') }}">
                           {{ $u?->phone_number }}
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2 mt-3">
                    <div class="stat">
                        <b>{{ number_format($ordersCount) }}</b>
                        <small>{{ __('profile.orders_stat') }}</small>
                    </div>
                    <div class="stat">
                        <b>{{ $tierLabel }}</b>
                        <small>{{ __('profile.tier_stat') }}</small>
                    </div>
                    <div class="stat">
                        <b>{{ $wallet }} {{ __('profile.currency') }}</b>
                        <small>{{ __('profile.balance_stat') }}</small>
                    </div>
                </div>

                <a href="{{ route('profile.show', ['edit' => 1]) }}" class="btn-brand block text-center mt-3">
                    {{ __('profile.edit_profile_btn') }}
                </a>
            </div>
            {{-- ===== /بطاقة المعلومات المختصرة ===== --}}

            {{-- قائمة عناصر مثل واجهة التطبيق --}}
            <div class="bg-white border rounded-2xl overflow-hidden shadow-sm" style="border-color:#eadbcd">
                <a href="{{ route('profile.addresses.index') }}" class="app-item border-b">
                    <span class="app-icon"><i class="bi bi-geo-alt"></i></span>
                    <div class="flex-1"><div class="app-title">{{ __('profile.my_addresses') }}</div></div>
                    <i class="bi bi-chevron-left text-gray-400"></i>
                </a>

                <a href="{{ route('profile.orders') }}" class="app-item border-b">
                    <span class="app-icon"><i class="bi bi-box-seam"></i></span>
                    <div class="flex-1"><div class="app-title">{{ __('profile.my_orders') }}</div></div>
                    <i class="bi bi-chevron-left text-gray-400"></i>
                </a>

                <a href="{{ route('wallet.index') }}" class="app-item border-b">
                    <span class="app-icon"><i class="bi bi-wallet2"></i></span>
                    <div class="flex-1"><div class="app-title">{{ __('profile.wallet') }}</div></div>
                    <i class="bi bi-chevron-left text-gray-400"></i>
                </a>

                <a href="{{ route('wishlist') }}" class="app-item border-b">
                    <span class="app-icon" style="color:{{$brandDark}}"><i class="bi bi-heart-fill"></i></span>
                    <div class="flex-1"><div class="app-title">{{ __('profile.wishlist') }}</div></div>
                    <i class="bi bi-chevron-left text-gray-400"></i>
                </a>

                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="app-item !text-red-600 hover:!bg-red-50">
                        <span class="app-icon !bg-red-100 !text-red-600">
                            <i class="bi bi-box-arrow-right"></i>
                        </span>
                        <span class="flex-1 app-title">{{ __('profile.logout') }}</span>
                        <i class="bi bi-chevron-left text-gray-400"></i>
                    </button>
                </form>
            </div>

            {{-- ===== روابط معلومات طفوف (موبايل فقط) ===== --}}
            <div class="info-links-card">
                <div class="info-links-heading">
                    <i class="bi bi-info-circle-fill"></i>
                    <span>حول طفوف</span>
                </div>
                <div class="bg-white border rounded-2xl overflow-hidden shadow-sm" style="border-color:#eadbcd">
                    <a href="{{ route('about.us') }}" class="app-item border-b" data-fast-nav="true">
                        <span class="app-icon" style="background:#f0f4ff"><i class="bi bi-people-fill" style="color:#3b5bdb"></i></span>
                        <div class="flex-1"><div class="app-title">من نحن</div></div>
                        <i class="bi bi-chevron-left text-gray-400"></i>
                    </a>

                    <a href="{{ route('privacy.policy') }}" class="app-item border-b" data-fast-nav="true">
                        <span class="app-icon" style="background:#f0fdf4"><i class="bi bi-shield-lock-fill" style="color:#16a34a"></i></span>
                        <div class="flex-1"><div class="app-title">سياسة الخصوصية</div></div>
                        <i class="bi bi-chevron-left text-gray-400"></i>
                    </a>

                    <a href="{{ Route::has('payment.delivery') ? route('payment.delivery') : url('/payment-delivery') }}" class="app-item border-b" data-fast-nav="true">
                        <span class="app-icon" style="background:#fff7ed"><i class="bi bi-credit-card-2-front-fill" style="color:#ea580c"></i></span>
                        <div class="flex-1"><div class="app-title">طرق الدفع والتوصيل</div></div>
                        <i class="bi bi-chevron-left text-gray-400"></i>
                    </a>

                    <a href="{{ route('faq') }}" class="app-item border-b" data-fast-nav="true">
                        <span class="app-icon" style="background:#fdf4ff"><i class="bi bi-patch-question-fill" style="color:#9333ea"></i></span>
                        <div class="flex-1"><div class="app-title">الأسئلة الشائعة</div></div>
                        <i class="bi bi-chevron-left text-gray-400"></i>
                    </a>

                    <a href="{{ route('blog.index') }}" class="app-item border-b" data-fast-nav="true">
                        <span class="app-icon" style="background:#fef9c3"><i class="bi bi-journal-richtext" style="color:#ca8a04"></i></span>
                        <div class="flex-1"><div class="app-title">المدونة</div></div>
                        <i class="bi bi-chevron-left text-gray-400"></i>
                    </a>

                    <a href="{{ route('return.policy') }}" class="app-item" data-fast-nav="true">
                        <span class="app-icon" style="background:#fff1f2"><i class="bi bi-arrow-repeat" style="color:#e11d48"></i></span>
                        <div class="flex-1"><div class="app-title">سياسة الاستبدال / الارجاع</div></div>
                        <i class="bi bi-chevron-left text-gray-400"></i>
                    </a>
                </div>
            </div>
            {{-- ===== /روابط معلومات طفوف ===== --}}

        </section>
        @endif

    </div>
</div>

<style>
    /* متغيرات عامة + دارك */
    :root{
        --brand: {{ $brand }};
        --brand-dark: {{ $brandDark }};
        --brand-bg: {{ $brandBg }};

        /* أساس الثيم */
        --surface:#ffffff;
        --text:#4a3f3f;
        --text-soft:#6b7280;
        --border:#eadbcd;
        --bg-soft:#f9f5f1;
        --soft:#f3ece5;
    }
    html.dark{
        --surface:#0f172a;
        --text:#e5e7eb;
        --text-soft:#9ca3af;
        --border:#1f2937;
        --bg-soft:rgba(55,65,81,.35);
        --soft:rgba(55,65,81,.35);
    }

    .ltr { direction: ltr; }

    /* توحيد شكل بحث الهيدر مثل الرئيسية (دون تغيير HTML) */
    header .container.mx-auto.hidden.md\:flex form .flex.w-full,
    header .container.mx-auto.md\:hidden form .flex.w-full{
      background: var(--surface) !important;
      border: 1px solid var(--border) !important;
      border-radius: 999px !important;
      overflow: hidden !important;
      transition: border-color .2s ease, box-shadow .2s ease;
    }
    header form input[name="query"]{
      color: var(--text) !important; background: transparent !important;
    }
    header form input[name="query"]::placeholder{ color: var(--text-soft) !important; }
    header form button{ background: transparent !important; color: var(--brand) !important; }
    header form button:hover{ color: var(--brand-dark) !important; }
    header form .flex.w-full:focus-within{
      box-shadow: 0 0 0 3px color-mix(in srgb, var(--brand) 25%, transparent) !important;
      border-color: var(--brand-dark) !important;
    }

    /* أزرار الهوية */
    .btn-brand{
        background: var(--brand);
        color:#fff;
        font-weight:700;
        padding:.625rem 1rem;
        border-radius:.75rem;
        text-align:center;
        transition: .2s ease;
        display:inline-block;
    }
    .btn-brand:hover{ background: var(--brand-dark); color:#fff; }

    /* عناصر القائمة الجانبية (ديسكتوب) */
    .menu-item{
        display:flex; align-items:center; gap:.6rem;
        padding:.9rem 1rem;
        margin:.35rem .75rem;
        color: var(--text);
        background: var(--surface);
        border:1px solid var(--border);
        border-radius:.6rem;
        transition: background .2s, color .2s, border-color .2s;
        height: 52px;
    }
    .menu-item i{ font-size:1.05rem; }
    .menu-item:hover{
        background: var(--bg-soft);
        color: var(--brand-dark);
        border-color: var(--brand);
    }
    .menu-item.is-active{
        background: var(--brand);
        color:#fff;
        border-color: var(--brand);
    }
    .menu-item.is-active i{ color:#fff; }
    .menu-item.danger{
        border-color:#fecaca; color:#e11d48; background: var(--surface);
    }
    .menu-item.danger:hover{ background:#fff1f2; border-color:#fca5a5; }

    /* بطاقة المعلومات (موبايل) */
    .info-card{
        background: var(--surface);
        border:1px solid var(--border);
        border-radius:16px;
        padding:1rem;
        box-shadow:0 8px 22px rgba(0,0,0,.08);
        color: var(--text);
    }

    /* عناصر قائمة التطبيق (موبايل) */
    .app-item{
        display:flex; align-items:center; gap:.75rem;
        padding: 1rem;
        color: var(--text);
        transition: background .18s ease;
        border-color: var(--border) !important;
        background: var(--surface);
    }
    .app-item:hover{ background: var(--bg-soft); }
    .app-icon{
        width: 44px; height:44px;
        display:grid; place-items:center;
        background: #f7e3e1;
        color: var(--text);
        border-radius: 12px;
        flex: 0 0 44px;
    }
    .app-title{ font-weight:600; color: var(--text); }

    /* أخفي المحتوى فقط على الشاشات الصغيرة عندما يجب إظهار القائمة */
    @media (max-width: 1023px){
        .profile-main--mobile-hidden{ display: none !important; }
    }

    /* ===== روابط معلومات طفوف (موبايل) ===== */
    .info-links-card{
        margin-top: .25rem;
    }
    .info-links-heading{
        display: flex;
        align-items: center;
        gap: .45rem;
        font-weight: 700;
        font-size: .88rem;
        color: var(--text-soft);
        padding: .25rem .25rem .6rem;
        letter-spacing: .02em;
        text-transform: uppercase;
    }
    .info-links-heading i{ font-size: .95rem; color: var(--brand); }

    /* شارات الإحصائيات داخل info-card */
    .stat{
        text-align:center; background:#fafafa; border:1px solid #f1e6da;
        border-radius:12px; padding:.45rem .35rem;
    }
    .stat b{ display:block; font-size:.95rem; color:var(--text); font-weight:800; line-height:1.1; }
    .stat small{ display:block; margin-top:2px; font-weight:700; font-size:.78rem; color:var(--text-soft); }

    /* ======= Dark overrides عامة لتغطية الخلفيات البيضاء والحدود الثابتة ======= */
    html.dark .bg-white{ background-color: var(--surface) !important; }
    html.dark [style*="border-color:#eadbcd"]{ border-color: var(--border) !important; }
    html.dark .border{ border-color: var(--border) !important; }
    html.dark .text-[#4a3f3f]{ color: var(--text) !important; }
    html.dark .text-[#6b7280]{ color: var(--text-soft) !important; }

    html.dark .info-card,
    html.dark .app-item,
    html.dark .menu-item{
        background: var(--surface) !important;
        border-color: var(--border) !important;
        color: var(--text) !important;
        box-shadow: 0 8px 22px rgba(0,0,0,.28) !important;
    }
    html.dark .app-item:hover{ background: rgba(55,65,81,.35) !important; }
    html.dark .app-icon{
        background: rgba(59, 130, 246, 0.15) !important;
        color: var(--text) !important;
    }

    html.dark .stat{
        background: rgba(55,65,81,.35) !important;
        border-color: var(--border) !important;
    }

    html.dark .shadow-sm{ box-shadow: 0 6px 16px rgba(0,0,0,.35) !important; }
    /* توحيد قياس زر تسجيل الخروج (ديسكتوب داخل الـborder-t) */
nav .mt-2 .menu-item{
  margin: .35rem .75rem !important;
  height: 52px !important;
  padding: .9rem 1rem !important;
  display: flex; align-items: center; gap: .6rem;
}

/* توحيد قياس عناصر الموبايل (الأزرار والروابط) */
.app-item{
  min-height: 52px;              /* نفس ارتفاع باقي العناصر */
  line-height: 1.25;              /* لتفادي تمدد النص */
}

/* خصوصًا button داخل الفورم بالموبايل */
button.app-item{
  width: 100%;
  border: 0;
  background: var(--surface);
  -webkit-appearance: none;
  appearance: none;
}

/* أيقونات العناصر تبقى بقياس ثابت */
.app-item .bi{ font-size: 1.05rem; }
/* ====== 1) زر تسجيل الخروج: إلغاء التوسيط وتوحيد القياس ====== */
nav .mt-2 .menu-item.danger{
  justify-content: flex-start !important;  /* مو بالنص */
  text-align: right !important;
  height: 52px !important;
  padding: .9rem 1rem !important;
}

/* موبايل: الزر داخل الفورم يتصرف مثل بقية العناصر */
button.app-item{
  justify-content: flex-start !important; /* مو بالنص */
  text-align: right !important;
  width: 100%;
  border: 0;
  background: var(--surface);
  appearance: none;
}

/* ====== 2) Hover / Hold / Active للقائمة الجانبية (ديسكتوب) ====== */
.menu-item:hover{
  background: var(--bg-soft);
  border-color: var(--brand);
  color: var(--brand-dark);
}
.menu-item:active{
  background: color-mix(in srgb, var(--brand) 12%, var(--surface));
}

/* حالة الصفحة الحالية */
.menu-item.is-active{
  background: var(--brand);
  color: #fff;
  border-color: var(--brand);
}
.menu-item.is-active i{ color:#fff; }

/* دارك: تفاعلات أوضح */
html.dark .menu-item:hover{
  background: color-mix(in srgb, var(--brand) 10%, var(--surface));
  border-color: var(--brand);
  color: var(--text);
}
html.dark .menu-item:active{
  background: color-mix(in srgb, var(--brand) 18%, var(--surface));
}
html.dark .menu-item.is-active{
  background: linear-gradient(180deg, var(--brand) 0%, var(--brand-dark) 100%);
  border-color: color-mix(in srgb, var(--brand) 60%, var(--brand-dark) 40%);
  color: #fff;
  box-shadow: 0 8px 24px rgba(0,0,0,.35);
}

/* ====== 3) Hover / Hold / Active لقائمة التطبيق (موبايل) ====== */
.app-item:hover{ background: var(--bg-soft); }
.app-item:active{ background: color-mix(in srgb, var(--brand-bg) 65%, var(--surface)); }

html.dark .app-item:hover{ background: color-mix(in srgb, var(--brand) 8%, var(--surface)); }
html.dark .app-item:active{ background: color-mix(in srgb, var(--brand) 16%, var(--surface)); }

/* حالة الصفحة الحالية للموبايل */
.app-item.is-active{
  background: var(--brand);
  color: #fff;
}
.app-item.is-active .app-icon{ background: rgba(255,255,255,.15); color:#fff; }
.app-item.is-active i.bi-chevron-left{ color:#fff; opacity:.85; }

</style>
@endsection
