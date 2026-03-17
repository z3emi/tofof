@extends('frontend.profile.layout')

@section('title', 'محفظتي')

@push('styles')
<style>
  :root{
    --wallet-brand:#6d0e16;
    --wallet-brand-2:#8f1a23;
    --wallet-surface:#ffffff;
    --wallet-text:#142133;
    --wallet-muted:#667085;
    --wallet-border:#e7ddd2;
    --wallet-soft:#f7f3ee;
    --wallet-good:#15803d;
    --wallet-bad:#b42318;
    --wallet-shadow:0 14px 36px rgba(10, 24, 40, 0.08);
  }

  html.dark{
    --wallet-surface:#0f172a;
    --wallet-text:#e5e7eb;
    --wallet-muted:#9ca3af;
    --wallet-border:#223148;
    --wallet-soft:#111b2d;
    --wallet-shadow:0 18px 44px rgba(0, 0, 0, 0.34);
  }

  .wallet-shell{ display:grid; gap:16px; }
  .wallet-hero{
    position:relative;
    overflow:hidden;
    border-radius:20px;
    border:1px solid color-mix(in srgb, var(--wallet-brand) 22%, var(--wallet-border));
    background:
      radial-gradient(130% 100% at 100% 0%, rgba(255,255,255,.2), transparent 60%),
      linear-gradient(140deg, var(--wallet-brand) 0%, var(--wallet-brand-2) 65%, #4d0a10 100%);
    color:#fff;
    padding:20px;
    box-shadow:var(--wallet-shadow);
  }

  .wallet-hero::after{
    content:"";
    position:absolute;
    inset:auto -42px -52px auto;
    width:170px;
    height:170px;
    border-radius:999px;
    border:1px solid rgba(255,255,255,.24);
    background:rgba(255,255,255,.08);
    transform:rotate(8deg);
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
    background:rgba(255,255,255,.1);
    padding:6px 12px;
    font-weight:800;
    font-size:.82rem;
  }

  .wallet-balance{
    font-size:2rem;
    font-weight:900;
    line-height:1.1;
    letter-spacing:.2px;
    margin-top:10px;
  }

  .wallet-note{ color:rgba(255,255,255,.88); margin-top:8px; font-size:.92rem; }

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
    border-radius:16px;
    padding:16px;
    box-shadow:var(--wallet-shadow);
  }

  .stat-head{
    display:flex;
    align-items:center;
    justify-content:space-between;
    color:var(--wallet-muted);
    font-weight:700;
    font-size:.9rem;
  }

  .stat-head i{ font-size:1.1rem; }
  .stat-value{ font-size:1.65rem; font-weight:900; margin-top:10px; color:var(--wallet-text); }
  .is-credit{ color:var(--wallet-good); }
  .is-debit{ color:var(--wallet-bad); }

  .wallet-panel{
    background:var(--wallet-surface);
    border:1px solid var(--wallet-border);
    border-radius:16px;
    box-shadow:var(--wallet-shadow);
    padding:14px;
  }

  .wallet-panel-head{ display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:10px; }
  .wallet-panel-title{ font-size:1.05rem; font-weight:900; color:var(--wallet-text); }
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
  }

  .wallet-table th{
    text-align:right;
    background:var(--wallet-soft);
    color:var(--wallet-text);
    font-size:.88rem;
    font-weight:900;
    padding:12px 12px;
    border-bottom:1px solid var(--wallet-border);
  }

  .wallet-table td{
    padding:11px 12px;
    border-bottom:1px solid var(--wallet-border);
    color:var(--wallet-text);
    font-size:.93rem;
    vertical-align:middle;
  }

  .wallet-table tbody tr:last-child td{ border-bottom:none; }

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

  .tx-card-list{ display:grid; gap:10px; }

  .tx-card{
    border:1px solid var(--wallet-border);
    background:var(--wallet-surface);
    border-radius:14px;
    box-shadow:var(--wallet-shadow);
    padding:12px;
    display:grid;
    gap:8px;
  }

  .tx-card-top,
  .tx-card-row{ display:flex; align-items:center; justify-content:space-between; gap:10px; }
  .tx-label{ color:var(--wallet-muted); font-size:.82rem; font-weight:700; }
  .tx-value{ color:var(--wallet-text); font-weight:800; font-size:.92rem; }

  .empty-wallet{
    border:1px dashed var(--wallet-border);
    border-radius:14px;
    padding:22px 14px;
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

  .pagination .page-item.active .page-link{
    background:var(--wallet-brand) !important;
    color:#fff !important;
    border-color:var(--wallet-brand) !important;
  }

  @media (max-width: 767.98px){
    .wallet-hero{ padding:16px; border-radius:16px; }
    .wallet-balance{ font-size:1.65rem; }
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
        <span class="wallet-chip"><i class="bi bi-wallet2"></i> محفظتي</span>
        <span class="wallet-chip"><i class="bi bi-shield-check"></i> آمن ومحدّث</span>
      </div>
      <div class="wallet-balance">{{ number_format($balance, 0) }} د.ع</div>
      <div class="wallet-note">يمكن استخدام الرصيد تلقائيًا أثناء إنشاء الطلبات المؤهلة.</div>
    </section>

    <section class="wallet-stats">
      <div class="stat-card">
        <div class="stat-head">
          <span>إجمالي الإضافات</span>
          <i class="bi bi-arrow-down-left"></i>
        </div>
        <div class="stat-value is-credit">{{ number_format($creditsTotal, 0) }} د.ع</div>
      </div>

      <div class="stat-card">
        <div class="stat-head">
          <span>إجمالي الخصومات</span>
          <i class="bi bi-arrow-up-right"></i>
        </div>
        <div class="stat-value is-debit">{{ number_format($debitsTotal, 0) }} د.ع</div>
      </div>
    </section>

    <section class="wallet-panel">
      <div class="wallet-panel-head">
        <div>
          <div class="wallet-panel-title">سجل العمليات</div>
          <div class="wallet-panel-sub">آخر العمليات المسجلة على محفظتك</div>
        </div>
      </div>

      @if($transactions->isEmpty())
        <div class="empty-wallet">لا توجد حركات في المحفظة حتى الآن.</div>
      @else
        <div class="tx-card-list md:hidden">
          @foreach($transactions as $tx)
          @php $isCredit = $tx->type === 'credit'; @endphp
          <div class="tx-card">
            <div class="tx-card-top">
              <span class="tx-type {{ $isCredit ? 'credit' : 'debit' }}">
                <i class="bi {{ $isCredit ? 'bi-arrow-down-left' : 'bi-arrow-up-right' }}"></i>
                {{ $isCredit ? 'إيداع' : 'سحب' }}
              </span>
              <span class="tx-amount {{ $isCredit ? 'credit' : 'debit' }}">
                {{ number_format($tx->amount, 0) }} د.ع
              </span>
            </div>

            <div class="tx-card-row">
              <span class="tx-label">التاريخ</span>
              <span class="tx-value">
                {{ optional($tx->created_at)->timezone(config('app.timezone','Asia/Baghdad'))->format('Y-m-d H:i') }}
              </span>
            </div>

            <div class="tx-card-row">
              <span class="tx-label">الوصف</span>
              <span class="tx-value">{{ $tx->description ?: '—' }}</span>
            </div>

            <div class="tx-card-row">
              <span class="tx-label">رقم الطلب</span>
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
          @endforeach

          <div>
            {{ $transactions->links() }}
          </div>
        </div>

        <div class="table-wrap hidden md:block">
          <table class="wallet-table">
            <thead>
              <tr>
                <th>التاريخ</th>
                <th>النوع</th>
                <th>القيمة</th>
                <th>الوصف</th>
                <th>رقم الطلب</th>
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
                    {{ $isCredit ? 'إيداع' : 'سحب' }}
                  </span>
                </td>
                <td>
                  <span class="tx-amount {{ $isCredit ? 'credit' : 'debit' }}">
                    {{ number_format($tx->amount, 0) }} د.ع
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
