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
                                    <a href="{{ route('profile.discounts') }}"
                                       class="menu-item {{ request()->routeIs('profile.discounts') ? 'is-active' : '' }}">
                                        <div class="icon-box"><i class="bi bi-ticket-perforated"></i></div>
                                        <span>{{ __('profile.discount_codes') }}</span>
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
                                data-copy-text="{{ __('profile.copy') }}"
                                data-copied-text="{{ __('profile.code_copied_short') }}"
                                class="shrink-0 inline-flex items-center gap-1.5 bg-[{{ $brand }}] hover:bg-[{{ $brandDark }}] text-white font-bold text-xs px-3 py-2 rounded-xl transition-all active:scale-95">
                            <i class="bi bi-clipboard"></i>
                            <span class="copy-label">{{ __('profile.copy') }}</span>
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

                <a href="{{ route('profile.discounts') }}" class="mobile-nav-item border-b border-slate-50">
                    <div class="icon bg-slate-50 text-[{{ $brand }}]"><i class="bi bi-ticket-perforated"></i></div>
                    <div class="flex-1 font-bold text-slate-700">{{ __('profile.discount_codes') }}</div>
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

            <div class="grid grid-cols-1 gap-6">
                <div class="bg-gradient-to-br from-slate-50 to-white border border-slate-100 rounded-3xl p-6 shadow-sm">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-11 h-11 rounded-2xl bg-[{{ $brand }}]/10 text-[{{ $brand }}] flex items-center justify-center text-xl">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-slate-800 text-lg">{{ __('profile.rights_policies_title') }}</h3>
                            <p class="text-sm text-slate-500">{{ __('profile.rights_policies_subtitle') }}</p>
                        </div>
                    </div>

                    <div class="space-y-3 text-sm font-bold">
                        <a href="{{ route('about.us') }}" class="flex items-center justify-between gap-3 rounded-2xl bg-white border border-slate-100 px-4 py-3 text-slate-700 hover:text-[{{ $brand }}] hover:border-[{{ $brand }}]/20 transition-all" data-fast-nav="true">
                            <span class="flex items-center gap-3"><i class="bi bi-people text-[{{ $brand }}]"></i> {{ __('profile.about_us') }}</span>
                            <i class="bi bi-chevron-left text-slate-300"></i>
                        </a>
                        <a href="{{ route('faq') }}" class="flex items-center justify-between gap-3 rounded-2xl bg-white border border-slate-100 px-4 py-3 text-slate-700 hover:text-[{{ $brand }}] hover:border-[{{ $brand }}]/20 transition-all" data-fast-nav="true">
                            <span class="flex items-center gap-3"><i class="bi bi-question-circle text-[{{ $brand }}]"></i> {{ __('profile.faq') }}</span>
                            <i class="bi bi-chevron-left text-slate-300"></i>
                        </a>
                        <a href="{{ route('privacy.policy') }}" class="flex items-center justify-between gap-3 rounded-2xl bg-white border border-slate-100 px-4 py-3 text-slate-700 hover:text-[{{ $brand }}] hover:border-[{{ $brand }}]/20 transition-all">
                            <span class="flex items-center gap-3"><i class="bi bi-lock text-[{{ $brand }}]"></i> {{ __('profile.privacy_policy') }}</span>
                            <i class="bi bi-chevron-left text-slate-300"></i>
                        </a>
                        <a href="{{ route('return.policy') }}" class="flex items-center justify-between gap-3 rounded-2xl bg-white border border-slate-100 px-4 py-3 text-slate-700 hover:text-[{{ $brand }}] hover:border-[{{ $brand }}]/20 transition-all">
                            <span class="flex items-center gap-3"><i class="bi bi-arrow-left-right text-[{{ $brand }}]"></i> {{ __('profile.return_policy') }}</span>
                            <i class="bi bi-chevron-left text-slate-300"></i>
                        </a>
                        <a href="{{ Route::has('payment.delivery') ? route('payment.delivery') : url('/payment-delivery') }}" class="flex items-center justify-between gap-3 rounded-2xl bg-white border border-slate-100 px-4 py-3 text-slate-700 hover:text-[{{ $brand }}] hover:border-[{{ $brand }}]/20 transition-all">
                            <span class="flex items-center gap-3"><i class="bi bi-truck text-[{{ $brand }}]"></i> {{ __('profile.payment_delivery') }}</span>
                            <i class="bi bi-chevron-left text-slate-300"></i>
                        </a>
                        <a href="{{ route('page.contact-us') }}" class="flex items-center justify-between gap-3 rounded-2xl bg-white border border-slate-100 px-4 py-3 text-slate-700 hover:text-[{{ $brand }}] hover:border-[{{ $brand }}]/20 transition-all">
                            <span class="flex items-center gap-3"><i class="bi bi-headset text-[{{ $brand }}]"></i> {{ __('profile.contact_support') }}</span>
                            <i class="bi bi-chevron-left text-slate-300"></i>
                        </a>
                        @if (Route::has('terms'))
                            <a href="{{ route('terms') }}" class="flex items-center justify-between gap-3 rounded-2xl bg-white border border-slate-100 px-4 py-3 text-slate-700 hover:text-[{{ $brand }}] hover:border-[{{ $brand }}]/20 transition-all">
                                <span class="flex items-center gap-3"><i class="bi bi-journal-text text-[{{ $brand }}]"></i> {{ __('profile.terms_conditions') }}</span>
                                <i class="bi bi-chevron-left text-slate-300"></i>
                            </a>
                        @endif
                    </div>
                </div>

                <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-11 h-11 rounded-2xl bg-[{{ $brand }}]/10 text-[{{ $brand }}] flex items-center justify-center text-xl">
                            <i class="bi bi-credit-card-2-front"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-slate-800 text-lg">{{ __('profile.payment_methods_title') }}</h3>
                            <p class="text-sm text-slate-500">{{ __('profile.payment_methods_subtitle') }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="h-12 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center p-2">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/5/5c/Visa_Inc._logo_%282021%E2%80%93present%29.svg" alt="Visa" class="h-full object-contain">
                        </div>
                        <div class="h-12 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center p-2">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2a/Mastercard-logo.svg/200px-Mastercard-logo.svg.png" alt="Mastercard" class="h-full object-contain">
                        </div>
                        <div class="h-12 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center p-2">
                            <img src="https://zaincash.com/static/media/ZainCashLogo.fea8cf3bb90421f45dd384d6afc6fe3b.svg" alt="Zain Cash" class="h-full object-contain">
                        </div>
                        <div class="h-12 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center p-2">
                            <img src="https://qi.iq/images/logo.svg?1=1" alt="Qi Card" class="h-full object-contain">
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-slate-100 rounded-3xl p-6 shadow-sm">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-11 h-11 rounded-2xl bg-[{{ $brand }}]/10 text-[{{ $brand }}] flex items-center justify-center text-xl">
                            <i class="bi bi-share"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-slate-800 text-lg">{{ __('profile.social_title') }}</h3>
                            <p class="text-sm text-slate-500">{{ __('profile.social_subtitle') }}</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a href="https://www.facebook.com/p/%D8%B7%D9%81%D9%88%D9%81-%D9%84%D9%84%D8%B3%D8%A7%D8%B9%D8%A7%D8%AA-100091444293851/" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-2xl bg-slate-50 border border-slate-100 px-4 py-3 text-slate-700 font-bold hover:bg-[#6d0e16] hover:text-white hover:border-[#6d0e16] transition-all">
                            <i class="bi bi-facebook"></i>
                            {{ __('profile.facebook') }}
                        </a>
                        <a href="https://www.instagram.com/tofof_watches" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-2xl bg-slate-50 border border-slate-100 px-4 py-3 text-slate-700 font-bold hover:bg-[#6d0e16] hover:text-white hover:border-[#6d0e16] transition-all">
                            <i class="bi bi-instagram"></i>
                            {{ __('profile.instagram') }}
                        </a>
                        <a href="https://wa.me/9647744969024" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-2xl bg-slate-50 border border-slate-100 px-4 py-3 text-slate-700 font-bold hover:bg-[#6d0e16] hover:text-white hover:border-[#6d0e16] transition-all">
                            <i class="bi bi-whatsapp"></i>
                            {{ __('profile.whatsapp') }}
                        </a>
                    </div>
                </div>
            </div>

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
            const label = btn.querySelector('.copy-label');
            const copyText = btn.dataset.copyText || 'Copy';
            const copiedText = btn.dataset.copiedText || 'Copied';

            if (icon) {
                icon.classList.remove('bi-clipboard');
                icon.classList.add('bi-check-lg');
            }
            if (label) {
                label.textContent = copiedText;
            }

            setTimeout(() => {
                if (icon) {
                    icon.classList.remove('bi-check-lg');
                    icon.classList.add('bi-clipboard');
                }
                if (label) {
                    label.textContent = copyText;
                }
            }, 1400);
        });
    }
</script>
@endpush
@endsection
