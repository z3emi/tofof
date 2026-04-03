@extends('layouts.app')
@section('title', $title ?? __('profile.my_account'))

@section('content')
@php
    // Official Tofof brand color
    $brand = "#6d0e16"; // Deep Burgundy (Main Color)
    $brandDark = "#500a10";
    $accent = "#6d0e16"; // Secondary accent matches main
    $brandBg = "#fdfaf9";
    $textMain = "#1a1a1a";
    
    // هل نحن في صفحة الملف الرئيسية؟
    $isProfileHome = request()->routeIs('profile.show');

    // وضع التعديل على الموبايل
    $forceEdit = request()->boolean('edit') || request('mode') === 'edit';

    // إن كنا في صفحة الملف الرئيسية ولم نطلب التعديل على الموبايل -> نُظهر القائمة فقط
    $showListOnMobile = $isProfileHome && !$forceEdit;

    // بيانات المستخدم
    $u = auth()->user();
    $ordersCount = $u?->orders()->count() ?? 0;
    $tierLabel   = $u?->tier ?? '—';
    $wallet      = number_format($u?->wallet_balance ?? 0, 0);

    // ===== مسار الصورة =====
    $avatarSrc = $u?->avatar_url ?? asset('storage/avatars/default.jpg');
@endphp

<div class="container mx-auto px-4 pt-2 pb-4 md:pt-4">
    <div class="flex flex-col gap-6">
        <div class="flex flex-col lg:flex-row gap-6">

            {{-- الشريط الجانبي (ديسكتوب) --}}
            <aside class="hidden lg:block w-full lg:w-1/4">
                <div class="sticky top-6 flex flex-col gap-6">
                    {{-- بطاقة مختصرة فخمة بالجانب --}}
                    <div class="hidden bg-white rounded-3xl p-6 shadow-xl shadow-slate-200/50 border border-slate-100 text-center">
                        <div class="relative mb-4 group">
                            <div class="absolute -inset-1 bg-gradient-to-tr from-[{{ $brand }}] to-[{{ $accent }}] rounded-full blur opacity-25 group-hover:opacity-40 transition duration-500"></div>
                            <img src="{{ $avatarSrc }}" alt="avatar" class="relative w-24 h-24 rounded-full object-cover border-4 border-white shadow-lg">
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-1">{{ $u?->name }}</h3>
                        <p class="text-slate-500 text-sm mb-4 ltr">{{ $u?->phone_number }}</p>
                        
                        <div class="grid grid-cols-2 gap-3 w-full">
                            <div class="bg-slate-50 rounded-2xl p-3 border border-slate-100">
                                <span class="block text-xs text-slate-400 font-medium mb-1">{{ __('profile.wallet') }}</span>
                                <span class="block font-bold text-slate-800 text-sm">{{ $wallet }} <small class="font-normal">{{ __('profile.currency') }}</small></span>
                            </div>
                            <div class="bg-slate-50 rounded-2xl p-3 border border-slate-100">
                                <span class="block text-xs text-slate-400 font-medium mb-1">{{ __('profile.orders_stat') }}</span>
                                <span class="block font-bold text-slate-800 text-sm">{{ $ordersCount }}</span>
                            </div>
                        </div>
                    </div>

                    <nav class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
                        <div class="p-3">
                            <ul class="space-y-1">
                                <li>
                                    <a href="{{ route('profile.show') }}"
                                       class="menu-item {{ request()->routeIs('profile.show') ? 'is-active' : '' }}">
                                        <div class="icon-box"><i class="bi bi-person"></i></div>
                                        <span>{{ __('profile.my_profile') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('profile.orders') }}"
                                       class="menu-item {{ request()->routeIs('profile.orders*') ? 'is-active' : '' }}">
                                        <div class="icon-box"><i class="bi bi-bag"></i></div>
                                        <span>{{ __('profile.my_orders') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('profile.addresses.index') }}"
                                       class="menu-item {{ request()->routeIs('profile.addresses*') ? 'is-active' : '' }}">
                                        <div class="icon-box"><i class="bi bi-geo-alt"></i></div>
                                        <span>{{ __('profile.shipping_addresses') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('wallet.index') }}"
                                       class="menu-item {{ request()->routeIs('wallet.*') ? 'is-active' : '' }}">
                                        <div class="icon-box"><i class="bi bi-wallet2"></i></div>
                                        <span>{{ __('profile.wallet') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('wishlist') }}"
                                       class="menu-item {{ request()->routeIs('wishlist') ? 'is-active' : '' }}">
                                        <div class="icon-box"><i class="bi bi-heart"></i></div>
                                        <span>{{ __('profile.wishlist') }}</span>
                                    </a>
                                </li>
                            </ul>

                            <div class="mt-4 pt-3 border-t border-slate-100 px-2">
                                <a href="{{ route('logout') }}"
                                   class="menu-item logout-btn"
                                   onclick="event.preventDefault(); document.getElementById('logout-form-desktop').submit();">
                                    <div class="icon-box"><i class="bi bi-box-arrow-right"></i></div>
                                    <span>{{ __('profile.logout') }}</span>
                                </a>
                                <form id="logout-form-desktop" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
                            </div>
                        </div>
                    </nav>
                </div>
            </aside>

            {{-- المحتوى الرئيسي --}}
            <main class="w-full lg:w-3/4 bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 p-6 md:p-8 {{ $showListOnMobile ? 'profile-main--mobile-hidden' : '' }}">
                {{-- زر العودة (موبايل فقط) --}}
                @if(!$isProfileHome)
                <div class="lg:hidden mb-5">
                    <a href="{{ route('profile.show') }}"
                       class="inline-flex items-center gap-2 text-[{{ $brand }}] font-bold text-sm bg-slate-50 hover:bg-slate-100 border border-slate-100 px-4 py-2.5 rounded-2xl transition-all active:scale-95">
                        <i class="bi bi-arrow-right text-base"></i>
                        العودة للحساب
                    </a>
                </div>
                @endif
                @yield('profile-content')
            </main>
        </div>

        {{-- قائمة الجوال --}}
        @if($showListOnMobile)
        <section class="lg:hidden space-y-6">

            {{-- ===== بطــاقة المعلومات المختصرة ===== --}}
            <div class="relative overflow-hidden bg-white rounded-3xl p-6 shadow-xl shadow-slate-200/50 border border-slate-50">
                <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-slate-50 to-white rounded-full -mr-16 -mt-16 -z-10"></div>
                
                <div class="flex flex-col items-center text-center mb-6">
                    <div class="relative">
                        <div class="absolute inset-0 bg-[{{ $brand }}]/10 rounded-full blur-sm transform scale-110"></div>
                        <img src="{{ $avatarSrc }}" alt="avatar" class="relative w-20 h-20 rounded-full object-cover border-4 border-white shadow-md">
                    </div>
                    <div class="min-w-0 mt-3">
                        <div class="font-black text-[{{ $brand }}] text-lg truncate">{{ $u?->name }}</div>
                        <span class="text-sm text-slate-400 font-medium ltr">{{ $u?->phone_number }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3 mb-6 font-tajawal">
                    <div class="bg-slate-50/80 backdrop-blur-sm rounded-2xl p-3 text-center border border-slate-100">
                        <span class="block text-[10px] uppercase tracking-wider text-slate-400 font-bold mb-1">{{ __('profile.orders_stat') }}</span>
                        <span class="block font-black text-slate-800">{{ $ordersCount }}</span>
                    </div>
                    <div class="bg-slate-50/80 backdrop-blur-sm rounded-2xl p-3 text-center border border-slate-100">
                        <span class="block text-[10px] uppercase tracking-wider text-slate-400 font-bold mb-1">{{ __('profile.tier_stat') }}</span>
                        <span class="block font-black text-[{{ $brand }}]">{{ $tierLabel }}</span>
                    </div>
                    <div class="bg-slate-50/80 backdrop-blur-sm rounded-2xl p-3 text-center border border-slate-100">
                        <span class="block text-[10px] uppercase tracking-wider text-slate-400 font-bold mb-1">{{ __('profile.balance_stat') }}</span>
                        <span class="block font-black text-slate-800 text-xs">{{ $wallet }}</span>
                    </div>
                </div>

                @if(!empty($u?->referral_code))
                <div class="mb-6 rounded-2xl border border-slate-100 bg-slate-50/80 p-3">
                    <div class="flex items-center justify-between gap-2">
                        <div class="min-w-0">
                            <div class="text-[11px] text-slate-400 font-bold mb-1">{{ __('profile.referral_program') }}</div>
                            <div id="mobileReferralCode" class="font-black text-[{{ $brand }}] tracking-wide ltr truncate">{{ $u->referral_code }}</div>
                        </div>
                        <button type="button"
                                onclick="copyMobileReferralCode(event)"
                                class="shrink-0 inline-flex items-center gap-1.5 bg-[{{ $brand }}] hover:bg-[{{ $brandDark }}] text-white font-bold text-xs px-3 py-2 rounded-xl transition-all active:scale-95">
                            <i class="bi bi-clipboard"></i>
                            نسخ
                        </button>
                    </div>
                </div>
                @endif

                <a href="{{ route('profile.show', ['edit' => 1]) }}" class="flex items-center justify-center gap-2 w-full bg-[{{ $brand }}] hover:bg-[{{ $brandDark }}] text-white font-bold py-3.5 px-6 rounded-2xl transition-all shadow-lg shadow-[{{ $brand }}]/20 active:scale-[0.98]">
                    <i class="bi bi-pencil-square"></i>
                    {{ __('profile.edit_profile_btn') }}
                </a>
            </div>
            {{-- ===== /بطاقة المعلومات المختصرة ===== --}}

            {{-- قائمة عناصر مثل واجهة التطبيق --}}
            <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-50 overflow-hidden">
                <a href="{{ route('profile.addresses.index') }}" class="mobile-nav-item border-b border-slate-50">
                    <div class="icon bg-slate-50 text-[{{ $brand }}]"><i class="bi bi-geo-alt"></i></div>
                    <div class="flex-1 font-bold text-slate-700">{{ __('profile.my_addresses') }}</div>
                    <i class="bi bi-chevron-left text-slate-200"></i>
                </a>

                <a href="{{ route('profile.orders') }}" class="mobile-nav-item border-b border-slate-50">
                    <div class="icon bg-slate-50 text-[{{ $brand }}]"><i class="bi bi-bag"></i></div>
                    <div class="flex-1 font-bold text-slate-700">{{ __('profile.my_orders') }}</div>
                    <i class="bi bi-chevron-left text-slate-200"></i>
                </a>

                <a href="{{ route('wallet.index') }}" class="mobile-nav-item border-b border-slate-50">
                    <div class="icon bg-slate-50 text-[{{ $brand }}]"><i class="bi bi-wallet2"></i></div>
                    <div class="flex-1 font-bold text-slate-700">{{ __('profile.wallet') }}</div>
                    <i class="bi bi-chevron-left text-slate-200"></i>
                </a>

                <a href="{{ route('wishlist') }}" class="mobile-nav-item border-b border-slate-50">
                    <div class="icon bg-slate-50 text-[{{ $brand }}]"><i class="bi bi-heart"></i></div>
                    <div class="flex-1 font-bold text-slate-700">{{ __('profile.wishlist') }}</div>
                    <i class="bi bi-chevron-left text-slate-200"></i>
                </a>

                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="mobile-nav-item w-full text-red-600 hover:bg-red-50/30">
                        <div class="icon bg-red-50 text-red-600"><i class="bi bi-box-arrow-right"></i></div>
                        <span class="flex-1 font-bold text-right">{{ __('profile.logout') }}</span>
                        <i class="bi bi-chevron-left text-red-200"></i>
                    </button>
                </form>
            </div>

            {{-- ===== روابط معلومات طفوف مودرن ===== --}}
            <div class="space-y-3">
                <div class="flex items-center gap-2 px-2">
                    <div class="w-1 h-4 bg-[{{ $brand }}] rounded-full"></div>
                    <h4 class="font-black text-slate-400 text-sm tracking-tight uppercase">حول طفوف</h4>
                </div>
                
                <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-50 overflow-hidden">
                    <a href="{{ route('about.us') }}" class="mobile-nav-item border-b border-slate-50" data-fast-nav="true">
                        <div class="icon bg-slate-50 text-[{{ $brand }}]"><i class="bi bi-people"></i></div>
                        <div class="flex-1 font-bold text-slate-700 px-1">من نحن</div>
                        <i class="bi bi-chevron-left text-slate-200"></i>
                    </a>

                    <a href="{{ route('privacy.policy') }}" class="mobile-nav-item border-b border-slate-50" data-fast-nav="true">
                        <div class="icon bg-slate-50 text-[{{ $brand }}]"><i class="bi bi-shield-check"></i></div>
                        <div class="flex-1 font-bold text-slate-700 px-1">سياسة الخصوصية</div>
                        <i class="bi bi-chevron-left text-slate-200"></i>
                    </a>

                    <a href="{{ Route::has('payment.delivery') ? route('payment.delivery') : url('/payment-delivery') }}" class="mobile-nav-item border-b border-slate-50" data-fast-nav="true">
                        <div class="icon bg-slate-50 text-[{{ $brand }}]"><i class="bi bi-truck"></i></div>
                        <div class="flex-1 font-bold text-slate-700 px-1">طرق الدفع والتوصيل</div>
                        <i class="bi bi-chevron-left text-slate-200"></i>
                    </a>

                    <a href="{{ route('faq') }}" class="mobile-nav-item border-b border-slate-50" data-fast-nav="true">
                        <div class="icon bg-slate-50 text-[{{ $brand }}]"><i class="bi bi-question-circle"></i></div>
                        <div class="flex-1 font-bold text-slate-700 px-1">الأسئلة الشائعة</div>
                        <i class="bi bi-chevron-left text-slate-200"></i>
                    </a>

                    <a href="{{ route('return.policy') }}" class="mobile-nav-item" data-fast-nav="true">
                        <div class="icon bg-slate-50 text-[{{ $brand }}]"><i class="bi bi-arrow-left-right"></i></div>
                        <div class="flex-1 font-bold text-slate-700 px-1">سياسة الاستبدال / الارجاع</div>
                        <i class="bi bi-chevron-left text-slate-200"></i>
                    </a>
                </div>
            </div>
            {{-- ===== /روابط معلومات طفوف ===== --}}

        </section>
        @endif

    </div>
</div>

<style>
    :root {
        --brand: {{ $brand }};
        --accent: {{ $brand }};
        --brand-bg: #fdfaf9;
        --text: {{ $textMain }};
        --surface: #ffffff;
        --bg-main: #fcfcfc;
        --border-soft: #f1f5f9;
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    html.dark {
        --surface: #0f172a;
        --bg-main: #0b1120;
        --border-soft: #1e293b;
    }

    body {
        background-color: var(--bg-main);
    }

    .ltr { direction: ltr; }

    /* Desktop Sidebar Menu Items */
    .menu-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 14px;
        border-radius: 18px;
        color: #64748b;
        font-weight: 700;
        transition: var(--transition);
        margin-bottom: 4px;
    }

    .icon-box {
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f1f5f9;
        border-radius: 12px;
        font-size: 1.15rem;
        transition: var(--transition);
        border: 1px solid #e2e8f0;
    }

    .menu-item:not(.is-active):hover {
        background: #f1f5f9;
        color: var(--brand);
    }

    .menu-item:not(.is-active):hover .icon-box {
        background: white;
        border-color: var(--brand);
        color: var(--brand);
    }

    .menu-item.is-active {
        background: var(--brand);
        color: white;
        box-shadow: 0 10px 20px -5px rgba(109, 14, 22, 0.3);
    }

    .menu-item.is-active .icon-box {
        background: rgba(255, 255, 255, 0.15);
        border-color: rgba(255, 255, 255, 0.2);
        color: white;
    }

    .logout-btn {
        color: #ef4444;
    }
    .logout-btn .icon-box {
        color: #ef4444;
        background: #fef2f2;
        border-color: #fee2e2;
    }
    .logout-btn:hover {
        background: #fef2f2 !important;
        color: #dc2626 !important;
    }

    /* Mobile Nav Items */
    .mobile-nav-item {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px;
        transition: var(--transition);
        background: white;
    }

    .mobile-nav-item .icon {
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 16px;
        font-size: 1.25rem;
    }

    .mobile-nav-item:active {
        background: #f1f5f9;
        transform: scale(0.98);
    }

    /* Dark Mode Overrides */
    html.dark .bg-white { background-color: var(--surface) !important; }
    html.dark .bg-slate-50 { background-color: rgba(241, 245, 249, 0.05) !important; }
    html.dark .border-slate-50 { border-color: var(--border-soft) !important; }
    html.dark .text-slate-800 { color: #f1f5f9 !important; }
    html.dark .text-slate-700 { color: #e2e8f0 !important; }
    html.dark .text-slate-600 { color: #cbd5e1 !important; }
    html.dark .text-slate-500 { color: #94a3b8 !important; }
    html.dark .shadow-xl { shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5) !important; }

    @media (max-width: 1023px) {
        .profile-main--mobile-hidden { display: none !important; }
    }
</style>

@push('scripts')
<script>
    function copyMobileReferralCode(event) {
        const text = document.getElementById('mobileReferralCode')?.innerText?.trim();
        if (!text) return;

        navigator.clipboard.writeText(text).then(() => {
            const btn = event.currentTarget;
            const icon = btn.querySelector('i');
            const label = btn.lastChild;

            if (icon) {
                icon.classList.remove('bi-clipboard');
                icon.classList.add('bi-check-lg');
            }
            if (label && label.nodeType === Node.TEXT_NODE) {
                label.textContent = ' تم';
            }

            setTimeout(() => {
                if (icon) {
                    icon.classList.remove('bi-check-lg');
                    icon.classList.add('bi-clipboard');
                }
                if (label && label.nodeType === Node.TEXT_NODE) {
                    label.textContent = ' نسخ';
                }
            }, 1400);
        });
    }
</script>
@endpush
@endsection
