@extends('frontend.profile.layout')
@section('title', __('profile.my_addresses'))

@push('styles')
<style>:root {
        /* Brand palette */
        --primary-color: #6d0e16;
        --primary-hover: #500a10;
        --secondary-color: #f0e8e8;
        --accent-color: #a04050;

        /* Base (Light) */
        --bg: #fcfcfc;
        --bg-soft: #fdfaf9;
        --surface: #ffffff;
        --card-bg: #fdfaf9;
        --text: #1a1a1a;
        --text-soft: #444;
        --muted: #888;
        --border: #e8dada;

        /* Badges */
        --new-badge-color: #4CAF50;
        --bestseller-badge-color: #FF9800;
        --sale-badge-color: #E53935;

        /* Hero gradient (light) */
        --hero-start: rgba(253, 250, 249, 0.6);
        --hero-end: rgba(240, 232, 232, 0.6);

        /* Section gradient (light) */
        --cat-grad-from: #fcfcfc;
        --cat-grad-to: #fdfaf9;

        /* Slider overlay */
        --slider-overlay-from: rgba(80, 10, 16, 0.8);
        --slider-overlay-to: rgba(109, 14, 22, 0.8);
    }

html.dark {
        --bg: #0b0f14;
        --bg-soft: #0f172a;
        --surface: #0f172a;
        --card-bg: #111827;
        --text: #e5e7eb;
        --text-soft: #cbd5e1;
        --muted: #94a3b8;
        --border: #1f2937;

        /* Hero gradient (dark) */
        --hero-start: rgba(15, 23, 42, 0.6);
        --hero-end: rgba(17, 24, 39, 0.6);

        /* Section gradient (dark) */
        --cat-grad-from: #0b0f14;
        --cat-grad-to: #0f172a;

        /* Slider overlay */
        --slider-overlay-from: rgba(80, 10, 16, 0.75);
        --slider-overlay-to: rgba(109, 14, 22, 0.75);
    }


  /* سطح موحد خفيف بدون صناديق كثيرة */
  .surface{
    background:transparent;
    border-radius:16px;
  }

  .page-head h2{ color:var(--text); font-weight:800; }
  .page-head p{ color:var(--text-soft); }

  /* زر رئيسي */
  .btn-brand{
    display:inline-flex; align-items:center; gap:.45rem;
    background:var(--primary-color); color:var(--surface); font-weight:800;
    padding:.55rem 1rem; border-radius:12px; border:0;
    transition:.18s ease; text-decoration:none;
  }
  .btn-brand:hover{ background:var(--primary-hover); color:var(--surface); }

  /* بطاقة العنوان – بارزة بظل ناعم وحدّ شعري */
  .address-card{
    position:relative;
    background:var(--surface); border-radius:16px; padding:1rem;
    box-shadow: 0 12px 30px rgba(109,14,22,.07), 0 6px 14px rgba(0,0,0,.06), inset 0 0 0 1px var(--border);
    transition: box-shadow .18s ease, transform .12s ease;
  }
  .address-card:hover{
    box-shadow: 0 16px 36px rgba(109,14,22,.12), 0 8px 18px rgba(0,0,0,.08), inset 0 0 0 1px var(--border);
    transform: translateY(-1px);
  }

  .address-title{ color:var(--text); font-weight:800; }
  .address-text{ color:var(--text-soft); }
  .address-hint{ color:var(--text-soft); font-size:.8rem; }

  /* زر حذف – أوتلاين براند/أحمر أنيق */
  .btn-delete{
    display:inline-flex; align-items:center; gap:.35rem;
    font-weight:800; font-size:.85rem;
    color:#b91c1c; background:var(--surface);
    border-radius:10px; padding:.45rem .75rem;
    box-shadow: inset 0 0 0 1.5px #fecaca;
    transition:.18s ease; border:0;
  }
  .btn-delete:hover{ background:#fee2e2; color:#7f1d1d; box-shadow:none; }

  /* حالة عدم وجود عناوين */
  .empty-state{
    text-align:center; padding:2rem 1rem; background:var(--surface); border-radius:16px;
    box-shadow: 0 12px 30px rgba(109,14,22,.07), inset 0 0 0 1px var(--border);
  }
  .empty-state .icon{ font-size:3rem; color:#ddcfc2; }

  /* شبكة بطاقات */
  .address-grid{
    display:grid; gap:12px;
    grid-template-columns: 1fr;
  }
  @media (min-width:768px){
    .address-grid{ gap:16px; grid-template-columns: repeat(2,minmax(0,1fr)); }
  }

  /* تقليل الحواف على الجوال */
  @media (max-width:640px){
    .address-card{ padding:.9rem; }
  }
  /* مكمّل متغيّر ناقص نحتاجه بالهوفر */
:root { --soft: #f3ece5; }
html.dark { --soft: rgba(55,65,81,.35); }

/* إصلاح: أي خلفية بيضاء تبقى عنيدة بالدارك داخل هذه الصفحة تتحوّل لسطح الدارك */
html.dark .surface .bg-white,
html.dark .address-card,
html.dark .empty-state,
html.dark .card,
html.dark .shadow-card {
  background: var(--surface) !important;
  border-color: var(--border) !important;
  box-shadow: 0 12px 30px rgba(0,0,0,.35), inset 0 0 0 1px var(--border) !important;
}

/* تغطية حالات bg-white مباشرة (لو جاي من الlayout أو كلاسات جاهزة) */
html.dark .bg-white { background-color: var(--surface) !important; }
html.dark .border-white { border-color: var(--border) !important; }

/* بعض الكلاسات المربوطة بألوان محددة تُستبدل في الدارك */
html.dark [class*="border-[#eadbcd]"] { border-color: var(--border) !important; }
html.dark [class*="text-[#7a6e6e]"] { color: var(--text-soft) !important; }

/* زر الحذف على الدارك */
html.dark .btn-delete{
  background: #111827 !important;
  color: #e5e7eb !important;
  box-shadow: inset 0 0 0 1.5px rgba(220,38,38,.25) !important;
}
html.dark .btn-delete:hover{
  background: #1f2937 !important;
  color: #fecaca !important;
  box-shadow: none !important;
}

/* أيقونة الحالة الفارغة */
html.dark .empty-state .icon { color: #6b7280 !important; }

</style>
@endpush

@section('profile-content')
<div class="surface">
  <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-3 mb-4 md:mb-6 page-head">
      <div>
        <h2 class="text-xl md:text-2xl"><br>{{ __('profile.addresses_title') }}</h2>
        <p class="text-sm mt-1">{{ __('profile.addresses_subheading') }}</p>
      </div>

      @if($addresses->count() < 5)
        <a href="{{ route('profile.addresses.create') }}" class="btn-brand w-full md:w-auto justify-center">
          <i class="bi bi-plus-circle"></i>
          {{ __('profile.add_new_address') }}
        </a>
      @endif
  </div>

  @if ($addresses->isEmpty())
    <div class="empty-state">
      <i class="bi bi-geo-alt icon"></i>
      <p class="mt-3 text-[#7a6e6e]">{{ __('profile.no_addresses_short') }}</p>
      <a href="{{ route('profile.addresses.create') }}" class="btn-brand mt-3">
        <i class="bi bi-plus-circle"></i> {{ __('profile.add_new_address') }}
      </a>
    </div>
  @else
    <div class="address-grid">
      @foreach ($addresses as $address)
        <div class="address-card">
          <div class="flex items-start justify-between gap-3">
            <div class="flex-1">
              <p class="address-title text-sm md:text-base">
                <i class="bi bi-geo-alt-fill text-[var(--brand)] ms-1"></i>
                {{ $address->governorate }}، {{ $address->city }}
              </p>

              <p class="address-text text-xs md:text-sm mt-1">
                {{ $address->address_details }}
              </p>

              @if($address->nearest_landmark)
                <p class="address-hint mt-1">
                  {{ __('profile.nearest_landmark') }}: {{ $address->nearest_landmark }}
                </p>
              @endif
            </div>

            <form action="{{ route('profile.addresses.destroy', $address->id) }}" method="POST"
                  onsubmit="return confirm('{{ __('profile.confirm_delete_address') }}')">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn-delete" title="{{ __('profile.delete_address') }}">
                <i class="bi bi-trash3"></i> {{ __('profile.delete_address') }}
              </button>
            </form>
          </div>
        </div>
      @endforeach
    </div>
  @endif
</div>
@endsection
