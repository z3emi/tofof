@extends('frontend.profile.layout')

@section('title', __('profile.edit_profile_title'))

@section('profile-content')
@php
    $user = auth()->user();
    $avatar = $user->avatar_url;
    $ordersCount = $user->orders()->count();
    $tier = $user->tier ?? '—';
    $wallet = number_format($user->wallet_balance ?? 0, 0);

    $parts = explode(' ', $user->name, 2);
    $firstName = $parts[0] ?? '';
    $lastName  = $parts[1] ?? '';

    // Official Tofof brand color
    $brand = "#6d0e16"; // Deep Burgundy (Main Color)
    $brandDark = "#500a10";
    $accent = "#6d0e16";
    $textMain = "#1a1a1a";
@endphp

<div class="space-y-8">
    {{-- Alerts --}}
    @if (session('success'))
        <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl animate-in fade-in slide-in-from-top-4 duration-500">
            <i class="bi bi-check-circle-fill text-xl"></i>
            <p class="font-bold">{{ session('success') }}</p>
        </div>
    @endif
    @if ($errors->any())
        <div class="p-4 bg-red-50 border border-red-100 text-red-700 rounded-2xl">
            <div class="flex items-center gap-2 mb-2 font-bold">
                <i class="bi bi-exclamation-triangle-fill"></i>
                {{ __('profile.fix_errors') }}
            </div>
            <ul class="list-disc pr-6 text-sm">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form id="profile-edit-form" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="m-0">
        @csrf
        @method('PATCH')

        <input type="file" id="avatar" name="avatar" class="hidden" accept="image/*" onchange="previewAvatar(event)">

        {{-- Hero Header --}}
        <div class="relative flex flex-col items-center">
            <div class="relative group cursor-pointer" onclick="document.getElementById('avatar').click()">
                <div class="absolute -inset-2 bg-gradient-to-tr from-[{{ $brand }}] via-[{{ $brand }}] to-[{{ $brand }}] rounded-full blur-md opacity-10 group-hover:opacity-25 transition duration-500"></div>
                <div class="relative">
                    <img id="avatarPreview"
                         src="{{ $avatar }}"
                         alt="avatar"
                         class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-2xl transition duration-500 group-hover:scale-[1.02]"
                         onerror="this.onerror=null;this.src='/storage/avatars/default.png';">
                    <div class="absolute -bottom-2 -right-2 w-10 h-10 bg-[{{ $brand }}] text-white rounded-xl shadow-lg flex items-center justify-center border-2 border-white transition-transform duration-300 group-hover:rotate-12">
                        <i class="bi bi-camera-fill text-lg"></i>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 text-center">
                <h1 class="text-3xl font-black text-slate-900 mb-1">{{ $user->name }}</h1>
                <p class="text-slate-400 font-medium ltr">{{ $user->phone_number }}</p>
            </div>

            <div class="flex flex-wrap justify-center gap-3 mt-8 w-full md:w-auto">
                <div class="flex-1 md:flex-none min-w-[100px] bg-slate-50 border border-slate-100 rounded-2xl p-3 text-center">
                    <span class="block text-[10px] uppercase tracking-widest text-slate-400 font-bold mb-1">{{ __('profile.orders_stat') }}</span>
                    <span class="block font-black text-slate-800 text-lg">{{ $ordersCount }}</span>
                </div>
                <div class="flex-1 md:flex-none min-w-[100px] bg-slate-50 border border-slate-100 rounded-2xl p-3 text-center">
                    <span class="block text-[10px] uppercase tracking-widest text-slate-400 font-bold mb-1">{{ __('profile.tier_stat') }}</span>
                    <span class="block font-black text-[{{ $brand }}] text-lg">{{ $tier }}</span>
                </div>
                <div class="flex-1 md:flex-none min-w-[100px] bg-slate-50 border border-slate-100 rounded-2xl p-3 text-center">
                    <span class="block text-[10px] uppercase tracking-widest text-slate-400 font-bold mb-1">{{ __('profile.balance_stat') }}</span>
                    <span class="block font-black text-slate-800 text-lg">{{ $wallet }}</span>
                </div>
            </div>
        </div>

        {{-- Referral Section --}}
        <div class="mt-12 bg-gradient-to-br from-slate-50 to-white border border-slate-100 rounded-3xl p-6 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-[{{ $brand }}]/5 rounded-full -mr-16 -mt-16 transition-transform duration-700 group-hover:scale-150"></div>
            <div class="relative">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="max-w-md">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-8 h-8 rounded-lg bg-[{{ $brand }}]/10 text-[{{ $brand }}] flex items-center justify-center">
                                <i class="bi bi-gift-fill"></i>
                            </div>
                            <h4 class="font-black text-slate-800">{{ __('profile.referral_program') }}</h4>
                        </div>
                        <p class="text-slate-500 text-sm font-medium">{{ __('profile.share_code') }}</p>
                    </div>
                    
                    <div class="flex items-center gap-2 bg-white/80 backdrop-blur-md p-2 pl-4 rounded-2xl border border-slate-100 shadow-sm">
                        <span id="referralCode" class="text-xl font-black text-slate-800 tracking-wider ltr px-4">{{ $user->referral_code }}</span>
                        <button type="button" 
                                onclick="copyReferralCode()" 
                                class="bg-[{{ $accent }}] hover:bg-slate-800 text-white w-10 h-10 rounded-xl transition-all shadow-md active:scale-95 flex items-center justify-center">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                </div>
                <div id="copy-success" class="absolute -bottom-6 left-0 text-emerald-500 text-xs font-bold opacity-0 transition-opacity" style="display:none">
                    <i class="bi bi-check-circle"></i> {{ __('profile.code_copied') }}
                </div>
            </div>
        </div>

        {{-- Form Fields --}}
        <div class="mt-12 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
                <label class="text-sm font-black text-slate-400 uppercase tracking-tight px-1 flex items-center gap-2" for="first_name">
                    {{ __('profile.first_name') }}
                    @error('first_name')<span class="text-red-500 lowercase">*</span>@enderror
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-300 group-focus-within:text-[{{ $brand }}] transition-colors">
                        <i class="bi bi-person"></i>
                    </div>
                    <input class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-3.5 pr-11 pl-4 text-slate-800 font-bold focus:bg-white focus:ring-4 focus:ring-[{{ $brand }}]/5 focus:border-[{{ $brand }}] transition-all outline-none" 
                           type="text" id="first_name" name="first_name" value="{{ old('first_name', $firstName) }}">
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-black text-slate-400 uppercase tracking-tight px-1 flex items-center gap-2" for="last_name">
                    {{ __('profile.last_name') }}
                    @error('last_name')<span class="text-red-500 lowercase">*</span>@enderror
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-300 group-focus-within:text-[{{ $brand }}] transition-colors">
                        <i class="bi bi-person"></i>
                    </div>
                    <input class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-3.5 pr-11 pl-4 text-slate-800 font-bold focus:bg-white focus:ring-4 focus:ring-[{{ $brand }}]/5 focus:border-[{{ $brand }}] transition-all outline-none" 
                           type="text" id="last_name" name="last_name" value="{{ old('last_name', $lastName) }}">
                </div>
            </div>

            <div class="space-y-2 md:col-span-2">
                <label class="text-sm font-black text-slate-400 uppercase tracking-tight px-1 flex items-center gap-2" for="email">
                    {{ __('profile.email') }}
                    @error('email')<span class="text-red-500 lowercase">*</span>@enderror
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-300 group-focus-within:text-[{{ $brand }}] transition-colors">
                        <i class="bi bi-envelope"></i>
                    </div>
                    <input class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-3.5 pr-11 pl-4 text-slate-800 font-bold focus:bg-white focus:ring-4 focus:ring-[{{ $brand }}]/5 focus:border-[{{ $brand }}] transition-all outline-none ltr text-right" 
                           type="email" id="email" name="email" value="{{ old('email', $user->email) }}" placeholder="example@mail.com">
                </div>
            </div>

            <div class="space-y-2 md:col-span-2">
                <label class="text-sm font-black text-slate-400 uppercase tracking-tight px-1 flex items-center gap-2" for="phone_number">
                    {{ __('profile.phone') }}
                </label>
                <div class="relative group opacity-60">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-300">
                        <i class="bi bi-phone"></i>
                    </div>
                    <input class="w-full bg-slate-100/50 border border-slate-200/50 rounded-2xl py-3.5 pr-11 pl-4 text-slate-400 font-bold cursor-not-allowed ltr text-right" 
                           type="text" id="phone_number" value="{{ $user->phone_number }}" readonly>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="mt-12 flex flex-col sm:flex-row items-center gap-4">
            <button type="submit" class="w-full sm:w-auto min-w-[200px] flex items-center justify-center gap-2 bg-[{{ $brand }}] hover:bg-[{{ $brandDark }}] text-white font-black py-4 px-8 rounded-2xl transition-all shadow-xl shadow-[{{ $brand }}]/20 active:scale-95 group">
                <i class="bi bi-check-circle transition-transform group-hover:scale-110"></i>
                {{ __('profile.save_changes') }}
            </button>
            
            @if(Route::has('profile.change-password'))
                <a href="{{ route('profile.change-password') }}" class="w-full sm:w-auto flex items-center justify-center gap-2 bg-white border-2 border-slate-100 text-slate-700 font-bold py-4 px-8 rounded-2xl hover:bg-slate-50 hover:border-slate-200 transition-all active:scale-95">
                    <i class="bi bi-shield-lock"></i>
                    {{ __('profile.change_password') }}
                </a>
            @endif
        </div>
    </form>

</div>

<style>
    /* Dark mode overrides for show page */
    html.dark .bg-emerald-50 { background-color: rgba(16, 185, 129, 0.1) !important; color: #6ee7b7 !important; border-color: rgba(16, 185, 129, 0.2) !important; }
    html.dark .bg-red-50 { background-color: rgba(239, 68, 68, 0.1) !important; color: #f87171 !important; border-color: rgba(239, 68, 68, 0.2) !important; }
    html.dark .bg-slate-100 { background-color: rgba(15, 23, 42, 0.5) !important; }
    html.dark .border-slate-100 { border-color: #1e293b !important; }
    html.dark .text-slate-500 { color: #94a3b8 !important; }
    html.dark .text-slate-400 { color: #64748b !important; }
    html.dark input { background-color: rgba(30, 41, 59, 0.3) !important; color: #f1f5f9 !important; border-color: #1e293b !important; }
    html.dark input:focus { background-color: rgba(30, 41, 59, 0.6) !important; }
    html.dark .shadow-xl { shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5) !important; }
    html.dark .bg-white\/80 { background-color: rgba(15, 23, 42, 0.8) !important; }
</style>
@endsection

@push('scripts')
<script>
    function previewAvatar(e){
        const input = e.target;
        if(input.files && input.files[0]){
            const reader = new FileReader();
            reader.onload = (ev)=> {
                const preview = document.getElementById('avatarPreview');
                preview.src = ev.target.result;
                preview.classList.add('scale-105');
                setTimeout(() => preview.classList.remove('scale-105'), 200);
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    function copyReferralCode(){
        const text = document.getElementById('referralCode').innerText.trim();
        navigator.clipboard.writeText(text).then(()=>{
            const el = document.getElementById('copy-success');
            el.style.display='flex';
            el.style.opacity='1';
            setTimeout(()=> {
                el.style.opacity='0';
                setTimeout(() => el.style.display='none', 300);
            }, 2000);
            
            // Animation for button
            const btn = event.currentTarget;
            const icon = btn.querySelector('i');
            const originalIconClass = 'bi-clipboard';
            const originalBtnClass = 'bg-[{{ $accent }}]';
            
            icon.classList.replace(originalIconClass, 'bi-check-lg');
            btn.classList.add('!bg-emerald-500');
            setTimeout(() => {
                icon.classList.replace('bi-check-lg', originalIconClass);
                btn.classList.remove('!bg-emerald-500');
            }, 2000);
        });
    }
</script>
@endpush
