@extends('frontend.profile.layout')
@section('title', __('profile.discount_codes'))

@push('styles')
<style>
  :root {
    --brand: #6d0e16;
    --brand-dark: #500a10;
    --soft: #f3ece5;
    --hair: #f0e8e8;
    --text: #1a1a1a;
    --text-soft: #6b7280;
    --surface: #ffffff;
    --border: #e8dada;
  }

  html.dark {
    --soft: rgba(55, 65, 81, 0.35);
    --hair: #1f2937;
    --text: #e5e7eb;
    --text-soft: #cbd5e1;
    --surface: #0f172a;
    --border: #1f2937;
  }

  .surface {
    background: transparent;
    border-radius: 16px;
  }

  .page-head h2 {
    color: var(--text);
    font-weight: 800;
  }

  .page-head p {
    color: var(--text-soft);
  }

  .discount-grid {
    display: grid;
    gap: 12px;
    grid-template-columns: 1fr;
  }

  @media (min-width: 768px) {
    .discount-grid {
      gap: 16px;
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
  }

  .discount-card {
    position: relative;
    background: var(--surface);
    border-radius: 16px;
    padding: 1rem;
    box-shadow: 0 12px 30px rgba(109, 14, 22, 0.07), 0 6px 14px rgba(0, 0, 0, 0.06), inset 0 0 0 1px var(--border);
    transition: box-shadow 0.18s ease, transform 0.12s ease;
    overflow: hidden;
  }

  .discount-card:hover {
    box-shadow: 0 16px 36px rgba(109, 14, 22, 0.12), 0 8px 18px rgba(0, 0, 0, 0.08), inset 0 0 0 1px var(--border);
    transform: translateY(-1px);
  }

  .discount-card::after {
    content: "";
    position: absolute;
    top: 0;
    bottom: 0;
    right: -6px;
    width: 6px;
    border-radius: 12px 0 0 12px;
    background: linear-gradient(180deg, var(--brand), var(--brand-dark));
    opacity: 0;
    transform: translateX(6px);
    transition: opacity 0.2s ease, transform 0.2s ease;
  }

  .discount-card:hover::after {
    opacity: 1;
    transform: translateX(0);
  }

  .status-pill {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: 0.26rem 0.72rem;
    font-size: 0.75rem;
    font-weight: 800;
    white-space: nowrap;
  }

  .discount-expiry {
    color: var(--text-soft);
    font-size: 0.78rem;
    text-align: end;
  }

  .discount-code {
    font-weight: 900;
    letter-spacing: 0.45px;
    background: #fdf7f7;
    border: 1px dashed var(--brand);
    color: var(--brand);
    border-radius: 12px;
    padding: 0.52rem 0.82rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    min-height: 42px;
  }

  .btn-copy {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    border: 0;
    border-radius: 12px;
    padding: 0.54rem 0.88rem;
    background: var(--brand);
    color: #fff;
    font-weight: 800;
    font-size: 0.85rem;
    transition: 0.18s ease;
  }

  .btn-copy:hover {
    background: var(--brand-dark);
  }

  .discount-value {
    color: var(--text);
    font-size: 0.9rem;
    font-weight: 700;
  }

  .empty-state {
    text-align: center;
    padding: 2rem 1rem;
    background: var(--surface);
    border-radius: 16px;
    box-shadow: 0 12px 30px rgba(109, 14, 22, 0.07), inset 0 0 0 1px var(--border);
  }

  .empty-state .icon {
    font-size: 2.8rem;
    color: #ddcfc2;
  }

  .btn-brand {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    background: var(--brand);
    color: #fff;
    font-weight: 800;
    padding: 0.55rem 1rem;
    border-radius: 12px;
    border: 0;
    transition: 0.18s ease;
    text-decoration: none;
  }

  .btn-brand:hover {
    background: var(--brand-dark);
    color: #fff;
  }

  html.dark .discount-card,
  html.dark .empty-state,
  html.dark .bg-white {
    background: var(--surface) !important;
    border-color: var(--border) !important;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.35), inset 0 0 0 1px var(--border) !important;
  }

  html.dark .discount-code {
    background: #111827;
    color: #fca5a5;
    border-color: rgba(252, 165, 165, 0.5);
  }

  html.dark .empty-state .icon {
    color: #6b7280;
  }

  @media (max-width: 640px) {
    .discount-card {
      padding: 0.9rem;
    }

    .discount-card .code-row {
      flex-direction: column;
      align-items: stretch;
    }

    .btn-copy {
      width: 100%;
    }
  }
</style>
@endpush

@section('profile-content')
<div class="surface">
  <div class="page-head mb-4 md:mb-6">
    <h2 class="text-xl md:text-2xl"><br>{{ __('profile.discounts_heading') }}</h2>
    <p class="text-sm mt-1">{{ __('profile.discounts_subheading') }}</p>
  </div>

  @if($codes->isEmpty())
    <div class="empty-state">
      <i class="bi bi-ticket-perforated icon"></i>
      <p class="mt-3" style="color:var(--text-soft)">{{ __('profile.discounts_empty') }}</p>
      <a href="{{ route('shop') }}" class="btn-brand mt-3">
        <i class="bi bi-bag-plus"></i>
        {{ __('profile.start_shopping') }}
      </a>
    </div>
  @else
    <div class="discount-grid">
      @foreach($codes as $code)
        @php
          $isExpired = $code->expires_at && $code->expires_at->isPast();
          $isInactive = ! $code->is_active;
        @endphp
        <div class="discount-card">
          <div class="flex items-center justify-between gap-2 mb-3 flex-wrap">
            <span class="status-pill {{ $isInactive ? 'bg-gray-100 text-gray-700' : ($isExpired ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700') }}">
              {{ $isInactive ? __('profile.discounts_status_inactive') : ($isExpired ? __('profile.discounts_status_expired') : __('profile.discounts_status_available')) }}
            </span>
            <small class="discount-expiry">
              {{ $code->expires_at ? __('profile.discounts_expires_at') . ' ' . $code->expires_at->format('Y-m-d H:i') : __('profile.discounts_no_expiry') }}
            </small>
          </div>

          <div class="code-row flex items-center justify-between gap-3 mb-3">
            <div class="discount-code" id="discount-code-{{ $code->id }}">{{ $code->code }}</div>
            <button type="button" class="btn-copy" onclick="copyDiscountCode('discount-code-{{ $code->id }}', this)">
              <i class="bi bi-clipboard"></i>
              {{ __('profile.copy') }}
            </button>
          </div>

          <div class="discount-value">
            @if($code->type === 'percentage')
              {{ __('profile.discounts_type_discount') }} {{ rtrim(rtrim(number_format((float) $code->value, 2, '.', ''), '0'), '.') }}%
            @elseif($code->type === 'fixed')
              {{ __('profile.discounts_type_discount') }} {{ number_format((float) $code->value, 0) }} {{ __('profile.currency') }}
            @else
              {{ __('profile.discounts_type_free_shipping') }}
            @endif
          </div>
        </div>
      @endforeach
    </div>
  @endif
</div>

<script>
function copyDiscountCode(elementId, button) {
  const text = document.getElementById(elementId)?.innerText?.trim();
  if (!text) return;

  navigator.clipboard.writeText(text).then(() => {
    const prev = button.innerText;
    button.innerText = '{{ __('profile.code_copied_short') }}';
    setTimeout(() => button.innerText = prev, 1200);
  });
}
</script>
@endsection
