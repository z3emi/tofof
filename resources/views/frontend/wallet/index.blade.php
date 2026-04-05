@extends('frontend.profile.layout')

@section('title', __('wallet.title'))

@push('styles')
<style>
  :root{
    --wallet-brand:#6d0e16;
    --wallet-brand-2:#b62533;
    --wallet-brand-3:#3f0910;
    --wallet-surface:#ffffff;
    --wallet-text:#1f2937;
    --wallet-muted:#64748b;
    --wallet-border:#e6dcd3;
    --wallet-soft:#f9f4ef;
    --wallet-soft-2:#fffaf7;
    --wallet-good:#15803d;
    --wallet-bad:#b91c1c;
    --wallet-shadow:0 16px 44px rgba(14, 18, 28, 0.08);
  }

  html.dark{
    --wallet-surface:#0f172a;
    --wallet-text:#e5e7eb;
    --wallet-muted:#9fb0c9;
    --wallet-border:#233349;
    --wallet-soft:#101a2b;
    --wallet-soft-2:#0d1523;
    --wallet-shadow:0 22px 48px rgba(0, 0, 0, 0.34);
  }

  .wallet-shell{
    position:relative;
    display:grid;
    gap:18px;
    isolation:isolate;
  }

  .wallet-shell::before,
  .wallet-shell::after{
    content:"";
    position:absolute;
    z-index:-1;
    border-radius:999px;
    filter:blur(36px);
    pointer-events:none;
  }

  .wallet-shell::before{
    width:170px;
    height:170px;
    top:-16px;
    inset-inline-start:-20px;
    background:rgba(182, 37, 51, 0.13);
  }

  .wallet-shell::after{
    width:180px;
    height:180px;
    bottom:-20px;
    inset-inline-end:-10px;
    background:rgba(109, 14, 22, 0.11);
  }

  .wallet-hero{
    position:relative;
    overflow:hidden;
    border-radius:24px;
    border:1px solid rgba(255,255,255,.22);
    background:
      radial-gradient(140% 100% at 100% 0%, rgba(255,255,255,.25), transparent 55%),
      radial-gradient(120% 120% at 0% 100%, rgba(255,255,255,.12), transparent 50%),
      linear-gradient(145deg, var(--wallet-brand) 0%, var(--wallet-brand-2) 58%, var(--wallet-brand-3) 100%);
    color:#fff;
    padding:24px;
    box-shadow:var(--wallet-shadow);
  }

  .wallet-hero::after{
    content:"";
    position:absolute;
    inset:auto -46px -64px auto;
    width:184px;
    height:184px;
    border-radius:999px;
    border:1px solid rgba(255,255,255,.24);
    background:rgba(255,255,255,.08);
    transform:rotate(8deg);
  }

  .wallet-hero::before{
    content:"";
    position:absolute;
    inset:-50px auto auto -55px;
    width:200px;
    height:200px;
    border-radius:999px;
    background:rgba(255,255,255,.08);
    border:1px solid rgba(255,255,255,.2);
  }

  .wallet-topline{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:12px;
  }

  .wallet-chip{
    display:inline-flex;
    align-items:center;
    gap:6px;
    border:1px solid rgba(255,255,255,.3);
    border-radius:999px;
    background:rgba(255,255,255,.14);
    padding:6px 13px;
    font-weight:800;
    font-size:.82rem;
    backdrop-filter:blur(4px);
  }

  .wallet-balance{
    font-size:2.2rem;
    font-weight:900;
    line-height:1.1;
    letter-spacing:.2px;
    margin-top:12px;
    text-wrap:balance;
  }

  .wallet-note{
    color:rgba(255,255,255,.9);
    margin-top:10px;
    font-size:.94rem;
    max-width:56ch;
  }

  .wallet-stats{
    display:grid;
    gap:14px;
    grid-template-columns:1fr;
  }

  @media (min-width: 900px){
    .wallet-stats{ grid-template-columns:1fr 1fr; }
  }

  .stat-card{
    background:var(--wallet-surface);
    border:1px solid var(--wallet-border);
    border-radius:18px;
    padding:16px 16px 15px;
    box-shadow:var(--wallet-shadow);
    position:relative;
    overflow:hidden;
  }

  .stat-card::after{
    content:"";
    position:absolute;
    inset:auto 12px 0 12px;
    height:4px;
    border-radius:10px;
    background:linear-gradient(90deg, rgba(109,14,22,.15), rgba(109,14,22,.45));
  }

  .stat-head{
    display:flex;
    align-items:center;
    justify-content:space-between;
    color:var(--wallet-muted);
    font-weight:700;
    font-size:.9rem;
  }

  .stat-head i{ font-size:1.1rem; color:var(--wallet-brand); }
  .stat-value{ font-size:1.65rem; font-weight:900; margin-top:10px; color:var(--wallet-text); }
  .is-credit{ color:var(--wallet-good); }
  .is-debit{ color:var(--wallet-bad); }

  .wallet-panel{
    background:
      linear-gradient(180deg, var(--wallet-soft-2), var(--wallet-soft-2)) padding-box,
      linear-gradient(180deg, rgba(109,14,22,.22), rgba(109,14,22,.02)) border-box;
    border:1px solid var(--wallet-border);
    border-radius:18px;
    box-shadow:var(--wallet-shadow);
    padding:15px;
  }

  .wallet-panel-head{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
    margin-bottom:12px;
  }
  .wallet-panel-title{ font-size:1.05rem; font-weight:900; color:var(--wallet-text); letter-spacing:.2px; }
  .wallet-panel-sub{ color:var(--wallet-muted); font-size:.86rem; }

  .table-wrap{ overflow-x:auto; }
  .wallet-table{
    width:100%;
    min-width:760px;
    border-collapse:separate;
    border-spacing:0;
    overflow:hidden;
    border:1px solid var(--wallet-border);
    border-radius:14px;
    background:var(--wallet-surface);
  }

  .wallet-table th{
    text-align:right;
    background:var(--wallet-soft);
    color:var(--wallet-text);
    font-size:.88rem;
    font-weight:900;
    padding:12px 12px;
    border-bottom:1px solid var(--wallet-border);
    position:sticky;
    top:0;
    z-index:1;
  }

  .wallet-table td{
    padding:11px 12px;
    border-bottom:1px solid var(--wallet-border);
    color:var(--wallet-text);
    font-size:.93rem;
    vertical-align:middle;
  }

  .wallet-table tbody tr:last-child td{ border-bottom:none; }
  .wallet-table tbody tr:hover{ background:color-mix(in srgb, var(--wallet-soft) 58%, transparent); }

  .tx-type,
  .tx-amount{
    display:inline-flex;
    align-items:center;
    gap:6px;
    border-radius:999px;
    padding:5px 10px;
    font-size:.82rem;
    font-weight:900;
  }

  .tx-type.credit,
  .tx-amount.credit{ background:#e8f7ee; color:#14532d; }
  .tx-type.debit,
  .tx-amount.debit{ background:#ffedf0; color:#8a1c1c; }

  html.dark .tx-type.credit,
  html.dark .tx-amount.credit{ background:rgba(34,197,94,.15); color:#86efac; }

  html.dark .tx-type.debit,
  html.dark .tx-amount.debit{ background:rgba(248,113,113,.14); color:#fecaca; }

  .tx-card-list{ display:grid; gap:12px; }

  .tx-card{
    border:1px solid var(--wallet-border);
    background:var(--wallet-surface);
    border-radius:16px;
    box-shadow:var(--wallet-shadow);
    padding:14px;
    display:grid;
    gap:12px;
    position:relative;
    overflow:hidden;
  }

  .tx-card::before{
    content:"";
    position:absolute;
    inset:0 auto 0 0;
    width:4px;
    background:linear-gradient(180deg, rgba(109,14,22,.85), rgba(109,14,22,.35));
  }

  .tx-card-top{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
    flex-wrap:wrap;
  }

  .tx-meta-grid{
    display:grid;
    gap:8px;
    grid-template-columns:1fr;
  }

  .tx-meta-pill{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    border:1px solid var(--wallet-border);
    border-radius:12px;
    background:var(--wallet-soft-2);
    padding:8px 10px;
  }

  .tx-description{
    border:1px dashed var(--wallet-border);
    border-radius:12px;
    background:var(--wallet-soft-2);
    padding:10px;
  }

  .tx-label{ color:var(--wallet-muted); font-size:.8rem; font-weight:700; }
  .tx-value{ color:var(--wallet-text); font-weight:800; font-size:.92rem; text-align:left; }
  .tx-value.is-text{ text-align:right; line-height:1.55; }

  .empty-wallet{
    border:1px dashed var(--wallet-border);
    border-radius:16px;
    padding:24px 14px;
    text-align:center;
    color:var(--wallet-muted);
    background:var(--wallet-soft);
    font-weight:700;
  }

  .pagination{ display:flex; justify-content:center; gap:.35rem; margin-top:1rem; flex-wrap:wrap; }
  .pagination .page-item .page-link{
    background:var(--wallet-soft) !important;
    color:var(--wallet-brand) !important;
    border:1px solid var(--wallet-border) !important;
    border-radius:10px;
    font-weight:800;
    padding:.45rem .8rem;
    min-width:2.35rem;
  }

  .pagination .page-item .page-link:hover{
    background:color-mix(in srgb, var(--wallet-brand) 13%, var(--wallet-soft)) !important;
  }

  .pagination .page-item.active .page-link{
    background:var(--wallet-brand) !important;
    color:#fff !important;
    border-color:var(--wallet-brand) !important;
  }

  @media (max-width: 767.98px){
    .wallet-shell{ gap:14px; }
    .wallet-hero{ padding:16px; border-radius:16px; }
    .wallet-balance{ font-size:1.78rem; }
    .wallet-note{ font-size:.88rem; }
    .wallet-panel{ padding:12px; border-radius:16px; }
    .wallet-panel-title{ font-size:1rem; }
    .tx-card{ padding:12px; border-radius:14px; }
    .tx-card-top{ gap:8px; }
    .tx-type,
    .tx-amount{ font-size:.78rem; padding:5px 9px; }
    .tx-label{ font-size:.76rem; }
    .tx-value{ font-size:.88rem; }
    .tx-meta-pill{ padding:7px 9px; border-radius:11px; }
    .tx-description{ padding:9px; }
  }
</style>
@endpush

@section('profile-content')
@php
  $user = auth()->user();
  $balance = (int) ($user->wallet_balance ?? 0);
  $creditsTotal = isset($credits) ? (int)$credits : (int) ($transactions->where('type','credit')->sum('amount') ?? 0);
  $debitsTotal  = isset($debits)  ? (int)$debits  : (int) ($transactions->where('type','debit')->sum('amount') ?? 0);
@endphp

<div class="surface">
  <div class="wallet-shell">

    <section class="wallet-hero">
      <div class="wallet-topline">
        <span class="wallet-chip"><i class="bi bi-wallet2"></i> {{ __('wallet.badge_wallet') }}</span>
        <span class="wallet-chip"><i class="bi bi-shield-check"></i> {{ __('wallet.badge_secure') }}</span>
      </div>
      <div class="wallet-balance">{{ number_format($balance, 0) }} {{ __('wallet.currency') }}</div>
      <div class="wallet-note">{{ __('wallet.note') }}</div>
    </section>

    <section class="wallet-stats">
      <div class="stat-card">
        <div class="stat-head">
          <span>{{ __('wallet.total_credits') }}</span>
          <i class="bi bi-arrow-down-left"></i>
        </div>
        <div class="stat-value is-credit">{{ number_format($creditsTotal, 0) }} {{ __('wallet.currency') }}</div>
      </div>

      <div class="stat-card">
        <div class="stat-head">
          <span>{{ __('wallet.total_debits') }}</span>
          <i class="bi bi-arrow-up-right"></i>
        </div>
        <div class="stat-value is-debit">{{ number_format($debitsTotal, 0) }} {{ __('wallet.currency') }}</div>
      </div>
    </section>

    <section class="wallet-panel">
      <div class="wallet-panel-head">
        <div>
          <div class="wallet-panel-title">{{ __('wallet.transactions_title') }}</div>
          <div class="wallet-panel-sub">{{ __('wallet.transactions_subtitle') }}</div>
        </div>
      </div>

      @if($transactions->isEmpty())
        <div class="empty-wallet">{{ __('wallet.empty') }}</div>
      @else
        <div class="tx-card-list md:hidden">
          @foreach($transactions as $tx)
          @php $isCredit = $tx->type === 'credit'; @endphp
          <div class="tx-card">
            <div class="tx-card-top">
              <span class="tx-type {{ $isCredit ? 'credit' : 'debit' }}">
                <i class="bi {{ $isCredit ? 'bi-arrow-down-left' : 'bi-arrow-up-right' }}"></i>
                {{ $isCredit ? __('wallet.credit') : __('wallet.debit') }}
              </span>
              <span class="tx-amount {{ $isCredit ? 'credit' : 'debit' }}">
                {{ number_format($tx->amount, 0) }} {{ __('wallet.currency') }}
              </span>
            </div>

            <div class="tx-meta-grid">
              <div class="tx-meta-pill">
                <span class="tx-label">{{ __('wallet.date') }}</span>
                <span class="tx-value">
                  {{ optional($tx->created_at)->timezone(config('app.timezone','Asia/Baghdad'))->format('Y-m-d H:i') }}
                </span>
              </div>

              <div class="tx-meta-pill">
                <span class="tx-label">{{ __('wallet.order_number') }}</span>
                <span class="tx-value">
                  @php $orderRef = $tx->order_id ?? $tx->related_order_id; @endphp
                  @if(!empty($orderRef))
                    #{{ $orderRef }}
                  @else
                    —
                  @endif
                </span>
              </div>
            </div>

            <div class="tx-description">
              <span class="tx-label">{{ __('wallet.description') }}</span>
              <div class="tx-value is-text mt-1">{{ $tx->description ?: '—' }}</div>
            </div>
          </div>
          @endforeach

          <div>
            {{ $transactions->links() }}
          </div>
        </div>

        <div class="table-wrap hidden md:block">
          <table class="wallet-table">
            <thead>
              <tr>
                <th>{{ __('wallet.date') }}</th>
                <th>{{ __('wallet.type') }}</th>
                <th>{{ __('wallet.amount') }}</th>
                <th>{{ __('wallet.description') }}</th>
                <th>{{ __('wallet.order_number') }}</th>
              </tr>
            </thead>
            <tbody>
              @foreach($transactions as $tx)
              @php $isCredit = $tx->type === 'credit'; @endphp
              <tr>
                <td>
                  {{ optional($tx->created_at)->timezone(config('app.timezone','Asia/Baghdad'))->format('Y-m-d H:i') }}
                </td>
                <td>
                  <span class="tx-type {{ $isCredit ? 'credit' : 'debit' }}">
                    <i class="bi {{ $isCredit ? 'bi-arrow-down-left' : 'bi-arrow-up-right' }}"></i>
                    {{ $isCredit ? __('wallet.credit') : __('wallet.debit') }}
                  </span>
                </td>
                <td>
                  <span class="tx-amount {{ $isCredit ? 'credit' : 'debit' }}">
                    {{ number_format($tx->amount, 0) }} {{ __('wallet.currency') }}
                  </span>
                </td>
                <td>{{ $tx->description ?: '—' }}</td>
                <td>
                  @php $orderRef = $tx->order_id ?? $tx->related_order_id; @endphp
                  @if(!empty($orderRef))
                    #{{ $orderRef }}
                  @else
                    —
                  @endif
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>

          <div class="mt-3">
            {{ $transactions->links() }}
          </div>
        </div>
      @endif
    </section>
  </div>
</div>
@endsection
