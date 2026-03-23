@extends('frontend.profile.layout')

@section('title', __('profile.edit_profile_title'))

@push('styles')
<style>
  /* ========= Theme Variables ========= */
  :root{
    --brand:#cd8985;
    --brand-dark:#be6661;

    --surface:#ffffff;
    --text:#4a3f3f;
    --text-soft:#6b7280;
    --border:#eadbcd;
    --bg:#f9f5f1;
    --bg-soft:#f9f5f1;
    --soft:#f3ece5; /* tint خفيف للهوفر والظل الداخلي */
  }
  html.dark{
    --surface:#0f172a;
    --text:#e5e7eb;
    --text-soft:#9ca3af;
    --border:#1f2937;
    --bg:#0b0f14;
    --bg-soft:rgba(55,65,81,.35);
    --soft:rgba(55,65,81,.35);
  }

  /* ========= Layout ========= */
  .sheet{
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:16px;
    box-shadow:0 10px 28px rgba(0,0,0,.06);
    padding:1rem;
    overflow:hidden;
  }
  @media (min-width:768px){ .sheet{ padding:1.25rem 1.5rem; } }

  /* ========= Hero ========= */
  .hero{
    border-radius:0;
    background:linear-gradient(180deg, rgba(205,137,133,.18), var(--surface) 65%);
    padding:1.1rem .9rem 1rem;
    text-align:center;
    margin-inline:-1rem;
    padding-inline:1rem;
  }
  html.dark .hero{
    background:linear-gradient(180deg, rgba(205,137,133,.12), var(--surface) 65%);
  }
  @media (min-width:768px){
    .hero{ margin-inline:-1.5rem; padding-inline:1.5rem; }
  }

  .avatar-wrap{ position:relative; width:92px; height:92px; margin-inline:auto; }
  .avatar{
    width:100%; height:100%; border-radius:22px; object-fit:cover;
    border:3px solid var(--surface);
    box-shadow:0 10px 24px rgba(0,0,0,.09);
  }
  .avatar-edit{
    position:absolute; left:50%; transform:translateX(-50%);
    bottom:-8px; width:36px; height:36px; display:grid; place-items:center;
    border:none; border-radius:12px; background:var(--brand); color:#fff;
    box-shadow:0 8px 18px rgba(205,137,133,.4); cursor:pointer;
  }
  .avatar-edit:hover{ background:var(--brand-dark); }

  .title{ color:var(--text); font-weight:800; font-size:1.15rem; margin-top:.8rem; }
  .sub{ color:var(--text-soft); font-size:.9rem; }

  .stats{ margin-top:1rem; display:grid; grid-template-columns:repeat(3,1fr); gap:.5rem; }
  .chip{
    background:var(--surface); border-radius:14px; padding:.55rem .6rem;
    box-shadow:0 6px 18px rgba(0,0,0,.06);
  }
  .chip b{ display:block; color:var(--text); font-weight:800; font-size:.98rem; }
  .chip small{ color:var(--text-soft); font-weight:700; font-size:.78rem; }

  .ref-card{
    background:var(--surface);
    border:1px dashed var(--brand);
    border-radius:14px; padding:1rem;
  }
  .ref-row{ display:flex; align-items:center; justify-content:space-between; gap:.75rem; flex-wrap:wrap; }
  .ref-code{
    font-weight:900; letter-spacing:.12em; color:var(--brand-dark);
    background:var(--surface); padding:.55rem .8rem; border-radius:12px;
    box-shadow:0 6px 18px rgba(0,0,0,.05);
  }
  .ref-row small{ color:var(--text-soft) !important; }

  .field{ margin-top:.9rem; }
  .label{ display:block; margin-bottom:.35rem; color:var(--text); font-weight:700; }
  .control{
    width:100%; background:var(--surface); border:1px solid var(--border); color:var(--text);
    border-radius:12px; padding:.65rem .85rem; outline:none; transition:.18s ease; font-size:.95rem;
  }
  .control::placeholder{ color:var(--text-soft); }
  .control:focus{ border-color:var(--brand); box-shadow:0 0 0 3px rgba(205,137,133,.18); }
  .control[readonly]{ background:var(--bg-soft); color:var(--text-soft); }

  .btn-brand{
    background:var(--brand); color:#fff; font-weight:800; border:none;
    padding:.6rem 1rem; border-radius:.75rem; display:inline-flex; align-items:center; gap:.4rem;
    transition:.18s ease;
  }
  .btn-brand:hover{ background:var(--brand-dark); }
  .btn-outline{
    background:var(--surface); color:var(--brand); border:1px solid var(--brand);
    padding:.5rem .9rem; border-radius:.6rem; font-weight:800; transition:.18s ease;
  }
  .btn-outline:hover{ background:var(--brand); color:#fff; }

  .alert{ border-radius:12px; padding:.8rem 1rem; font-weight:600; }
  .alert-success{ background:#EAFBF0; color:#166534; border:1px solid #CFF6DD; }
  .alert-danger{ background:#FEE2E2; color:#991B1B; border:1px solid #FECACA; }
  html.dark .alert-success{ background: rgba(22,163,74,.12); color:#86efac; border-color: rgba(22,163,74,.32); }
  html.dark .alert-danger{ background: rgba(220,38,38,.12); color:#fecaca; border-color: rgba(220,38,38,.32); }

  .ltr{ direction:ltr; }

  @media (max-width:640px){
    .avatar-wrap{ width:84px; height:84px; }
    .avatar-edit{ width:34px; height:34px; bottom:-6px; border-radius:10px; }
    .stats{ gap:.45rem; }
  }

  html.dark .bg-white{ background-color: var(--surface) !important; }
  html.dark [style*="#eadbcd"], html.dark .border{ border-color: var(--border) !important; }
</style>
@endpush

@section('profile-content')
@php
  $user   = auth()->user();
  $avatar = $user->avatar_url;

  $ordersCount = $user->orders()->count();
  $tier        = $user->tier ?? '—';
  $wallet      = number_format($user->wallet_balance ?? 0, 0);

  $parts = explode(' ', $user->name, 2);
  $firstName = $parts[0] ?? '';
  $lastName  = $parts[1] ?? '';
@endphp

<div class="sheet">

  @if (session('success'))
    <div class="alert alert-success mb-3">{{ session('success') }}</div>
  @endif
  @if ($errors->any())
    <div class="alert alert-danger mb-3">
      <div>{{ __('profile.fix_errors') }}</div>
      <ul class="mt-1 list-disc pr-5">
        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
      </ul>
    </div>
  @endif

  <form id="profile-edit-form" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="mt-0">
    @csrf
    @method('PATCH')

    <input type="file" id="avatar" name="avatar" class="hidden" accept="image/*" onchange="previewAvatar(event)">

  <div class="hero">
    <div class="avatar-wrap">
      <img id="avatarPreview"
           src="{{ $avatar }}"
           alt="avatar"
           class="avatar"
         onerror="this.onerror=null;this.src='{{ asset('storage/avatars/default.png') }}';">
      <label for="avatar" class="avatar-edit" title="{{ __('profile.edit_profile_btn') }}">
        <i class="bi bi-camera-fill"></i>
      </label>
    </div>

    <h3 class="title">{{ $user->name }}</h3>
    <a href="tel:{{ $user->phone_number }}" class="sub ltr hover:text-[var(--brand-dark)]">{{ $user->phone_number }}</a>

    <div class="stats">
      <div class="chip"><b>{{ $ordersCount }}</b><small>{{ __('profile.orders_stat') }}</small></div>
      <div class="chip"><b>{{ $tier }}</b><small>{{ __('profile.tier_stat') }}</small></div>
      <div class="chip"><b>{{ $wallet }} {{ __('profile.currency') }}</b><small>{{ __('profile.balance_stat') }}</small></div>
    </div>
  </div>

  <div class="ref-card mt-4">
    <div class="ref-row mb-2">
      <h4 class="m-0 font-extrabold" style="color:var(--text)">{{ __('profile.referral_program') }}</h4>
      <small>{{ __('profile.share_code') }}</small>
    </div>
    <div class="ref-row">
      <span id="referralCode" class="ref-code">{{ $user->referral_code }}</span>
      <button type="button" class="btn-outline" onclick="copyReferralCode()"><i class="bi bi-clipboard-check"></i> {{ __('profile.copy') }}</button>
    </div>
    <div id="copy-success" class="text-green-600 text-sm mt-2" style="display:none">{{ __('profile.code_copied') }}</div>
  </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4">
      <div class="field">
        <label class="label" for="first_name">{{ __('profile.first_name') }}</label>
        <input class="control" type="text" id="first_name" name="first_name" value="{{ old('first_name', $firstName) }}">
      </div>

      <div class="field">
        <label class="label" for="last_name">{{ __('profile.last_name') }}</label>
        <input class="control" type="text" id="last_name" name="last_name" value="{{ old('last_name', $lastName) }}">
      </div>

      <div class="field md:col-span-2">
        <label class="label" for="email">{{ __('profile.email') }}</label>
        <input class="control" type="email" id="email" name="email" value="{{ old('email', $user->email) }}" placeholder="example@mail.com">
      </div>

      <div class="field md:col-span-2">
        <label class="label" for="phone_number">{{ __('profile.phone') }}</label>
        <input class="control ltr" type="text" id="phone_number" name="phone_number" value="{{ $user->phone_number }}" readonly>
      </div>
    </div>

    <div class="flex flex-col sm:flex-row items-center gap-3 mt-5">
      <button type="submit" class="btn-brand"><i class="bi bi-check2-circle"></i> {{ __('profile.save_changes') }}</button>
      @if(Route::has('profile.change-password'))
        <a href="{{ route('profile.change-password') }}" class="btn-outline"><i class="bi bi-shield-lock"></i> {{ __('profile.change_password') }}</a>
      @endif
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script>
  function previewAvatar(e){
    const input = e.target;
    if(input.files && input.files[0]){
      const reader = new FileReader();
      reader.onload = (ev)=> document.getElementById('avatarPreview').src = ev.target.result;
      reader.readAsDataURL(input.files[0]);
    }
  }
  function copyReferralCode(){
    const text = document.getElementById('referralCode').innerText.trim();
    navigator.clipboard.writeText(text).then(()=>{
      const el = document.getElementById('copy-success');
      el.style.display='block'; setTimeout(()=> el.style.display='none', 1600);
    });
  }
</script>
@endpush
