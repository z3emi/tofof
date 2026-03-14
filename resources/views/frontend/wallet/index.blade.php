@extends('frontend.profile.layout')

@section('title', 'محفظتي')

@push('styles')
<style>
  /* ===== Theme tokens ===== */
  :root{
    --brand:#0F2A44; --brand-dark:#0A1D2F;
    --surface:#ffffff; --text:#111827; --text-soft:#6b7280; --border:#eadbcd;
    --bg:#FFFFFF; --bg-soft:#E6E6E6;
    --tile-shadow: 0 12px 30px rgba(0,0,0,.06);
    --tile-shadow-hover: 0 18px 38px rgba(0,0,0,.10);
    --good:#16a34a; --bad:#b91c1c;
  }
  html.dark{
    --surface:#0f172a; --text:#e5e7eb; --text-soft:#9ca3af; --border:#1f2937;
    --bg:#0b0f14; --bg-soft:#111827;
    --tile-shadow: 0 10px 26px rgba(0,0,0,.28);
    --tile-shadow-hover: 0 18px 40px rgba(0,0,0,.36);
  }

  .surface{ background:transparent; border:none; box-shadow:none; padding:0; }

  /* ===== Grid ===== */
  .wallet-grid{ display:grid; gap:14px; grid-template-columns: 1fr; }
  @media(min-width:768px){ .wallet-grid{ grid-template-columns: repeat(12,minmax(0,1fr)); gap:16px; } }

  /* ===== Tile ===== */
  .tile{
    background:var(--surface); border:1px solid var(--border); border-radius:16px;
    box-shadow: var(--tile-shadow); padding:14px; transition:.2s ease;
  }
  .tile:hover{ transform: translateY(-2px); box-shadow: var(--tile-shadow-hover); border-color: color-mix(in srgb, var(--brand) 30%, var(--border)); }
  .tile h3{ margin:0; color:var(--text); font-weight:800; font-size:1rem; }
  .sub{ color:var(--text-soft); font-size:.86rem; }

  /* ===== Hero (رصيدك الآن) ===== */
  .tile-accent{
    background:
      radial-gradient(120% 80% at 50% -20%, color-mix(in srgb, var(--brand) 10%, transparent) 0%, transparent 60%),
      linear-gradient(180deg, color-mix(in srgb, var(--brand) 10%, var(--surface)) 0%, var(--surface) 70%);
    overflow:hidden; padding:16px 18px;
  }
  .hero-row{ display:flex; align-items:center; justify-content:space-between; }
  [dir="rtl"] .hero-row{ flex-direction: row-reverse; }
  .pill{
    display:inline-flex; align-items:center; gap:.45rem;
    background: rgba(255,255,255,.7); color: var(--brand-dark);
    border:1px solid #D4AF37;
    padding:.35rem .6rem; border-radius:999px; font-weight:800; font-size:.82rem; backdrop-filter: blur(4px);
  }
  html.dark .pill{ background: rgba(255,255,255,.08); color:#fff; border-color: rgba(255,255,255,.18); }
  .kpi-balance{ font-size:1.7rem; font-weight:900; color:#10b981; letter-spacing:.2px; }
  @media(min-width:768px){ .kpi-balance{ font-size:2rem; } }

  /* ===== KPI card ===== */
  .kpi-grid{
    display:grid; grid-template-columns: 1fr auto;
    grid-template-areas: "title value" "delta value";
    align-items:center; min-height:110px;
  }
  .kpi-title{ grid-area:title; text-align:right; }
  .kpi-value{
    grid-area:value; margin-inline-start:auto;
    font-size:1.35rem; font-weight:900; color:var(--text);
  }
  .kpi-delta{ grid-area:delta; text-align:right; font-weight:700; font-size:.85rem; }
  .up{ color:var(--good); } .down{ color:var(--bad); }

  /* ===== Table ===== */
  .table-wrap{ overflow-x:auto; }
  table.wallet-table{
    min-width:760px; width:100%; border-collapse:separate; border-spacing:0;
    background:var(--surface); border:1px solid var(--border); border-radius:14px; overflow:hidden;
    box-shadow: var(--tile-shadow); font-size:.95rem;
  }
  .wallet-table thead th{
    text-align:right; background:var(--bg-soft); color:var(--text); font-weight:800;
    padding:.7rem .8rem; border-bottom:1px solid var(--border);
  }
  .wallet-table tbody td{ padding:.7rem .8rem; color:var(--text); border-bottom:1px solid var(--border); }
  .wallet-table tbody tr:last-child td{ border-bottom:none; }

  /* badges + amount with arrow */
  .badge{
    display:inline-flex; align-items:center; gap:.35rem; font-weight:800;
    padding:.3rem .55rem; border-radius:10px; border:1px solid transparent; font-size:.82rem;
  }
  .badge-credit{ background:#e8f7ee; color:#166534; border-color:#cdeed9; }
  .badge-debit { background:#ffecef; color:#8a1e1e; border-color:#ffd6db; }
  html.dark .badge-credit{ background: rgba(22,163,74,.15); color:#86efac; border-color: rgba(22,163,74,.35); }
  html.dark .badge-debit { background: rgba(220,38,38,.15); color:#fecaca; border-color: rgba(220,38,38,.35); }

  .amount-badge{
    display:inline-flex; align-items:center; gap:.45rem; font-weight:900;
    padding:.25rem .55rem; border-radius:10px;
  }
  .amount-badge.up{ background:#e8f7ee; color:#166534; }
  .amount-badge.down{ background:#ffecef; color:#8a1e1e; }
  html.dark .amount-badge.up{ background: rgba(22,163,74,.15); color:#86efac; }
  html.dark .amount-badge.down{ background: rgba(220,38,38,.15); color:#fecaca; }

  /* ===== Mobile transaction cards ===== */
  .tx-card{
    display:grid; grid-template-columns: 1fr auto; align-items:center; gap:.5rem;
    background:var(--surface); border:1px solid var(--border); border-radius:12px; padding:.9rem .95rem; box-shadow: var(--tile-shadow);
  }
  .tx-row{ display:flex; gap:.45rem; align-items:center; }
  .tx-label{ color:var(--text-soft); font-size:.8rem; min-width:72px; }
  .tx-value{ color:var(--text); font-weight:700; font-size:.95rem; }
  .tx-order{ color:var(--text-soft); font-weight:700; font-size:.85rem; }

  .pagination{ display:flex; justify-content:center; gap:.35rem; margin-top:1rem; flex-wrap:wrap; }
  .pagination .page-item .page-link{
    background:var(--bg-soft)!important; color:var(--brand-dark)!important;
    border:1px solid var(--border)!important; font-weight:800; border-radius:10px;
    padding:.45rem .8rem; font-size:.875rem; min-width:2.4rem; transition:.18s ease;
  }
  .pagination .page-item .page-link:hover{ background: color-mix(in srgb, var(--brand) 28%, var(--bg-soft))!important; color:#fff!important; border-color:var(--brand)!important; }
  .pagination .page-item.active .page-link{ background:var(--brand-dark)!important; color:#fff!important; border-color:var(--brand-dark)!important; }
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
  <div class="wallet-grid">

    {{-- 1) Hero balance --}}
    <section class="tile tile-accent" style="grid-column: span 12;">
      <div class="hero-row">
        <span class="pill"><i class="bi bi-wallet2"></i> رصيدك الآن</span>
        <div class="kpi-balance">{{ number_format($balance, 0) }} د.ع</div>
      </div>
      <div class="sub mt-2">يُطبّق الرصيد تلقائيًا على الطلبات المؤهلة</div>
    </section>

    {{-- 2) إجمالي الإضافات (مربع مستقل) --}}
    <section class="tile md:col-span-6" style="grid-column: span 12;">
      <div class="kpi-grid">
        <div class="kpi-title"><h3>إجمالي الإضافات</h3></div>
        <div class="kpi-value">{{ number_format($creditsTotal, 0) }} د.ع</div>
        <div class="kpi-delta up"><i class="bi bi-arrow-down-left"></i> إيداعات</div>
      </div>
    </section>

    {{-- 3) إجمالي الخصومات (مربع مستقل) --}}
    <section class="tile md:col-span-6" style="grid-column: span 12;">
      <div class="kpi-grid">
        <div class="kpi-title"><h3>إجمالي الخصومات</h3></div>
        <div class="kpi-value">{{ number_format($debitsTotal, 0) }} د.ع</div>
        <div class="kpi-delta down"><i class="bi bi-arrow-up-right"></i> سحوبات</div>
      </div>
    </section>

    {{-- 4) آخر الحركات --}}
    <section class="tile" style="grid-column: span 12;">
      <h3>آخر الحركات</h3>

      @if($transactions->isEmpty())
        <div class="sub py-3">لا توجد حركات في المحفظة حتى الآن.</div>
      @else
        {{-- موبايل --}}
        <div class="mt-3 space-y-2 md:hidden">
          @foreach($transactions as $tx)
          @php $isCredit = $tx->type === 'credit'; @endphp
          <div class="tx-card">
            <div style="grid-column:1 / -1; display:flex; justify-content:space-between; align-items:center; gap:.5rem;">
              <div class="tx-row">
                <span class="tx-label">التاريخ</span>
                <span class="tx-value">
                  {{ optional($tx->created_at)->timezone(config('app.timezone','Asia/Baghdad'))->format('Y-m-d H:i') }}
                </span>
              </div>
              <div>
                @if($isCredit)
                  <span class="badge badge-credit"><i class="bi bi-arrow-down-left"></i> إيداع</span>
                @else
                  <span class="badge badge-debit"><i class="bi bi-arrow-up-right"></i> سحب</span>
                @endif
              </div>
            </div>

            <div class="tx-row">
              <span class="tx-label">القيمة</span>
              <span class="amount-badge {{ $isCredit ? 'up' : 'down' }}">
                <i class="bi {{ $isCredit ? 'bi-arrow-down-left' : 'bi-arrow-up-right' }}"></i>
                {{ number_format($tx->amount, 0) }} د.ع
              </span>
            </div>

            <div class="tx-row" style="grid-column:1 / -1;">
              <span class="tx-label">الوصف</span>
              <span class="tx-value">{{ $tx->description ?: '—' }}</span>
            </div>

            <div class="tx-row" style="grid-column:1 / -1;">
              <span class="tx-label">#الطلب</span>
              <span class="tx-value">
                @if(!empty($tx->related_order_id)) #{{ $tx->related_order_id }} @else — @endif
              </span>
            </div>
          </div>
          @endforeach

          <div class="mt-2">
            {{ $transactions->links() }}
          </div>
        </div>

        {{-- دِسكتوب --}}
        <div class="table-wrap hidden md:block mt-3">
          <table class="wallet-table">
            <thead>
              <tr>
                <th>التاريخ</th>
                <th>النوع</th>
                <th>القيمة</th>
                <th>الوصف</th>
                <th>#الطلب</th>
              </tr>
            </thead>
            <tbody>
              @foreach($transactions as $tx)
              @php $isCredit = $tx->type === 'credit'; @endphp
              <tr>
                <td class="nowrap">
                  {{ optional($tx->created_at)->timezone(config('app.timezone','Asia/Baghdad'))->format('Y-m-d H:i') }}
                </td>
                <td>
                  @if($isCredit)
                    <span class="badge badge-credit"><i class="bi bi-arrow-down-left"></i> إيداع</span>
                  @else
                    <span class="badge badge-debit"><i class="bi bi-arrow-up-right"></i> سحب</span>
                  @endif
                </td>
                <td>
                  <span class="amount-badge {{ $isCredit ? 'up' : 'down' }}">
                    <i class="bi {{ $isCredit ? 'bi-arrow-down-left' : 'bi-arrow-up-right' }}"></i>
                    {{ number_format($tx->amount, 0) }} د.ع
                  </span>
                </td>
                <td>{{ $tx->description ?: '—' }}</td>
                <td>
                  @if(!empty($tx->related_order_id))
                    #{{ $tx->related_order_id }}
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
