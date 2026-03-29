@extends('admin.layout')

@section('title', 'لوحة التحكم الرئيسية')

@push('styles')
<style>
  :root{
    --brand-dark:#be6661;
    --brand:#cd8985;
    --brand-light:#eadbcd;
    --muted:#f9f5f1;

    --surface:#ffffff;
    --ink:#3a3a3a;
    --border:#eee;
  }

  /* Base cards */
  .card{border:0; border-radius:14px; box-shadow:0 1px 8px rgba(0,0,0,.05); background:var(--surface)}
  .card-header{background:transparent; border-bottom:1px solid var(--border)}

  /* Mini KPI cards (glass/soft) */
  .mini{
    position:relative; overflow:hidden;
    border-radius:16px;
    background: linear-gradient(180deg, rgba(255,255,255,.75), rgba(255,255,255,.6));
    backdrop-filter: blur(6px);
    border:1px solid rgba(234,219,205,.7);
    box-shadow:0 6px 18px rgba(205,137,133,.08);
    transition:transform .2s ease, box-shadow .2s ease;
  }
  .mini:hover{ transform:translateY(-2px); box-shadow:0 10px 24px rgba(205,137,133,.16); }
  .mini .title{font-size:.9rem; color:#666; font-weight:600}
  .mini .value{font-size:1.7rem; font-weight:800; letter-spacing:.2px; color:#222}
  .mini .sub{font-size:.8rem; color:#888}
  .spark{ height:42px !important; }

  /* Soft bg tokens (brand-alike) */
  .bg-soft-brand { background:#eadbcd !important; }
  .bg-soft-warning{ background:#fff3cd !important; }
  .bg-soft-success{ background:#d1e7dd !important; }
  .bg-soft-danger { background:#f8d7da !important; }
  .bg-soft-info  { background:#cff4fc !important; }

  /* Filters bar */
  .filters-bar{
    background:#fff; border:1px solid var(--brand-light);
    border-radius:12px; padding:.85rem;
    box-shadow:0 .25rem .5rem rgba(0,0,0,.03);
  }

  /* Stabilize layout when modals/offcanvas open */
  @supports (scrollbar-gutter: stable) { html { scrollbar-gutter: stable both-edges; } }
  body.offcanvas-open, body.modal-open { padding-right:0 !important; }

  .text-brand{color:var(--brand)}
  .btn-brand{background:var(--brand); color:#fff; border:0;}
  .btn-brand:hover{background:var(--brand-dark); color:#fff;}
  /* ===== Status Badges (Tofof Identity) ===== */
.badge-status{
  border-radius: .65rem;
  padding: .4rem .75rem;
  font-weight:600;
  font-size: .85rem;
  border: none;
  display: inline-block;
  color:#fff;
}

/* ====== Status Badges (System colors + shipped) ====== */
.badge-status {
  border-radius: .65rem;
  padding: .35rem .75rem;
  font-weight: 600;
  font-size: .85rem;
  display: inline-block;
  letter-spacing: .2px;
  color: #fff;
}

/* قيد الانتظار */
.badge-status--pending {
  background: #ffc107;  /* أصفر */
  color: #222;
}

/* مكتمل */
.badge-status--delivered {
  background: #28a745;  /* أخضر */
}

/* راجعة */
.badge-status--returned {
  background: #dc3545;  /* أحمر */
}

/* ملغي */
.badge-status--cancelled {
  background: #6c757d;  /* رمادي */
}

/* قيد التنفيذ */
.badge-status--processing {
  background: #0d6efd;  /* أزرق */
}

/* تم الشحن */
.badge-status--shipped {
  background: #3B82F6;  /* أزرق حسب طلبك */
}


</style>
@endpush

@section('content')
<div class="p-4 p-lg-5">
    <h1 class="h4 fw-bold mb-4" style="color: var(--primary-dark);"><i class="bi bi-speedometer2 me-2"></i> لوحة التحكم الرئيسية</h1>

@can('view-orders')
@php
    $stats = [
        ['label' => 'إجمالي الطلبات',     'value' => $totalOrders,     'icon' => 'receipt',          'bg' => 'bg-white',    'color' => 'text-primary', 'route' => route('admin.orders.index')],
        ['label' => 'طلبات قيد الانتظار', 'value' => $pendingOrders,   'icon' => 'clock-history',    'bg' => 'bg-white','color' => 'text-warning','route' => route('admin.orders.index',['status'=>'pending'])],
        ['label' => 'طلبات مكتملة',       'value' => $completedOrders,'icon' => 'check-circle',     'bg' => 'bg-white','color' => 'text-success','route' => route('admin.orders.index',['status'=>'delivered'])],
        ['label' => 'طلبات راجعة',         'value' => $returnedOrders, 'icon' => 'arrow-return-left','bg'=> 'bg-white', 'color' => 'text-danger', 'route' => route('admin.orders.index',['status'=>'returned'])],
    ];
@endphp
<div class="row g-3 mb-4">
  @foreach($stats as $s)
  <div class="col-12 col-sm-6 col-xl-3">
      <div class="card h-100 border-0 shadow-sm {{ $s['bg'] }}">
          <div class="card-body d-flex align-items-center justify-content-between">
              <div>
                  <div class="text-muted fw-bold small mb-1">{{ $s['label'] }}</div>
                  <div class="h3 mb-0 fw-bold">{{ number_format($s['value']) }}</div>
              </div>
              <div class="ms-3 fs-2 {{ $s['color'] }}"><i class="bi bi-{{ $s['icon'] }}"></i></div>
              <a class="stretched-link" href="{{ $s['route'] }}"></a>
          </div>
      </div>
  </div>
  @endforeach
</div>
@endcan

@can('view-reviews')
<div class="row g-3 mb-4">
  <div class="col-12 col-md-6 col-xl-3">
      <div class="card h-100 border-0 shadow-sm" style="background-color: var(--primary-dark); color: white;">
          <div class="card-body">
              <div class="small fw-bold mb-1 opacity-75">متوسط التقييم</div>
              <div class="d-flex align-items-center mt-2">
                  <div class="h3 mb-0 fw-bold">{{ $averageRating }}</div>
                  <div class="ms-2" style="color:#ffc107">
                      @for($i=1;$i<=5;$i++)
                          @if($i <= floor($averageRating)) <i class="bi bi-star-fill"></i>
                          @elseif($i == ceil($averageRating) && $averageRating != floor($averageRating)) <i class="bi bi-star-half"></i>
                          @else <i class="bi bi-star"></i> @endif
                      @endfor
                  </div>
              </div>
              <div class="small opacity-75 mt-2">من {{ number_format($totalReviews) }} مراجعة</div>
          </div>
      </div>
  </div>
{{-- ===== بديل كروت المراجعات: شيء مختلف ===== --}}
<div class="col-12 col-md-6 col-xl-3">
  <div class="card h-100 border-0 shadow-sm" style="background:#fff">
    <div class="card-body d-flex align-items-center justify-content-between">
      <div>
        <div class="text-muted fw-bold small mb-1">العملاء النشطين</div>
        <div class="h3 mb-0 fw-bold">{{ number_format($activeCustomers) }}</div>
        <div class="small text-muted">لديهم طلب واحد على الأقل</div>
      </div>
      <div class="fs-2" style="color: var(--primary-dark);"><i class="bi bi-people"></i></div>
    </div>
  </div>
</div>

<div class="col-12 col-md-6 col-xl-3">
  <div class="card h-100 border-0 shadow-sm" style="background:#fff">
    <div class="card-body d-flex align-items-center justify-content-between">
      <div>
        <div class="text-muted fw-bold small mb-1">الطلبات اليوم</div>
        <div class="h3 mb-0 fw-bold">{{ number_format($todayOrders) }}</div>
        <div class="small text-muted">{{ \Illuminate\Support\Carbon::today()->format('Y-m-d') }}</div>
      </div>
      <div class="fs-2" style="color: var(--primary-dark);"><i class="bi bi-calendar-day"></i></div>
    </div>
  </div>
</div>

@can('view-reports-financial')
<div class="col-12 col-md-6 col-xl-3">
  <div class="card h-100 border-0 shadow-sm text-white" style="background-color: var(--primary-medium);">
    <div class="card-body d-flex align-items-center justify-content-between">
      <div>
        <div class="small fw-bold mb-1 opacity-75">إجمالي المبيعات (صافي)</div>
        <div class="h3 mb-0 fw-bold">
          {{ number_format($totalSales, 0) }} <span class="fs-6">د.ع</span>
        </div>
        <div class="small opacity-75">الطلبات المكتملة فقط</div>
      </div>
      <div class="fs-2"><i class="bi bi-currency-dollar"></i></div>
    </div>
  </div>
</div>
@endcan

{{-- ===== شريط فلاتر الفترة الزمنية ===== --}}
<div class="filters-bar mb-3 border-0 shadow-sm">
  <form method="GET" class="row g-2 align-items-end">
    <div class="col-12 col-md-3">
      <label class="form-label fw-bold small">من تاريخ</label>
      <input type="date" name="date_start" class="form-control" value="{{ request('date_start') }}">
    </div>
    <div class="col-12 col-md-3">
      <label class="form-label fw-bold small">إلى تاريخ</label>
      <input type="date" name="date_end" class="form-control" value="{{ request('date_end') }}">
    </div>
    <div class="col-12 col-md-6 d-flex gap-2">
      <button class="btn text-white px-4 fw-bold" style="background-color: var(--primary-dark);"><i class="bi bi-funnel me-1"></i> تطبيق</button>
      <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary px-3 fw-bold">إعادة الضبط</a>
      <div class="ms-auto d-none d-md-flex align-items-center text-muted small">
        <i class="bi bi-info-circle me-1"></i>
        البيانات محسوبة للمدة المختارة
      </div>
    </div>
  </form>
</div>

{{-- ===== KPIs مع Sparklines ===== --}}
<div class="row g-3 mb-4">
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="mini p-3 h-100">
      <div class="d-flex justify-content-between">
        <div>
          <div class="title">عدد الطلبات</div>
          <div class="value">{{ number_format($kpi['orders_total']) }}</div>
          <div class="sub">
            اليوم: <strong>{{ number_format($todayOrders) }}</strong>
          </div>
        </div>
        <div class="text-brand fs-2"><i class="bi bi-receipt"></i></div>
      </div>
      <canvas id="sparkOrders" class="mt-2 spark"></canvas>
    </div>
  </div>

  @can('view-reports-financial')
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="mini p-3 h-100">
      <div class="d-flex justify-content-between">
        <div>
          <div class="title">الإيراد الصافي</div>
          <div class="value">{{ number_format($kpi['revenue_net'], 0) }} <span class="fs-6">د.ع</span></div>
          <div class="sub">ضمن الفترة المحددة</div>
        </div>
        <div class="text-brand fs-2"><i class="bi bi-currency-dollar"></i></div>
      </div>
      <canvas id="sparkRevenue" class="mt-2 spark"></canvas>
    </div>
  </div>

  <div class="col-12 col-sm-6 col-xl-3">
    <div class="mini p-3 h-100">
      <div class="d-flex justify-content-between">
        <div>
          <div class="title">متوسط قيمة الطلب</div>
          <div class="value">{{ number_format($kpi['aov'], 0) }} <span class="fs-6">د.ع</span></div>
          <div class="sub">Revenue / Orders</div>
        </div>
        <div class="text-brand fs-2"><i class="bi bi-graph-up"></i></div>
      </div>
      <canvas id="sparkAov" class="mt-2 spark"></canvas>
    </div>
  </div>
  @endcan

  <div class="col-12 col-sm-6 col-xl-3">
    <div class="mini p-3 h-100">
      <div class="d-flex justify-content-between">
        <div>
          <div class="title">نسبة الإكمال</div>
          <div class="value">{{ $kpi['completion_rate'] }}%</div>
          <div class="sub">مكتمل / إجمالي</div>
        </div>
        <div class="text-brand fs-2"><i class="bi bi-check-circle"></i></div>
      </div>
      <canvas id="sparkCompletion" class="mt-2 spark"></canvas>
    </div>
  </div>
</div>

{{-- ===== توزيع حالات الطلبات (Doughnut) + الخط الزمني (Line) ===== --}}
<div class="row g-3 mb-4">
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-body">
        <h6 class="mb-3">توزيع حالات الطلبات (ضمن الفترة)</h6>
        <canvas id="statusDoughnut" height="220"></canvas>
        <div class="small text-muted mt-2">
          إجمالي: {{ number_format(array_sum($statusDistribution)) }} طلب
        </div>
      </div>
    </div>
  </div>
  @can('view-reports-financial')
  <div class="col-lg-8">
    <div class="card h-100">
      <div class="card-body">
        <h6 class="mb-3">الطلبات والإيراد اليومي</h6>
        <canvas id="ordersRevenueLine" height="120"></canvas>
      </div>
    </div>
  </div>
  @endcan
</div>

{{-- ===== أقسامك القديمة (تبقى كما هي) ===== --}}

<div class="row g-3 mb-4">
  <div class="col-lg-6">
      <div class="card h-100">
          <div class="card-body">
              <h6 class="mb-3">توزيع التقييمات</h6>
              <canvas id="ratingsBreakdownChart" height="120"></canvas>
          </div>
      </div>
  </div>
  <div class="col-lg-6">
      <div class="card h-100">
          <div class="card-body">
              <h6 class="mb-3">متوسط التقييم عبر الزمن</h6>
              <canvas id="ratingTrendChart" height="120"></canvas>
          </div>
      </div>
  </div>
</div>
@endcan

@can('view-reports')
<div class="card mb-4">
  <div class="card-body">
      <h6 class="mb-3">عدد الطلبات خلال آخر 30 يوم حسب الحالة</h6>
      <canvas id="ordersChart" height="110"></canvas>
  </div>
</div>
@endcan

@can('view-orders')
@php $statusLabels = ['pending'=>'قيد الانتظار','delivered'=>'مكتمل','returned'=>'راجعة']; @endphp
<div class="card mb-4">
  <div class="card-body">
      <h6 class="mb-3">آخر الطلبات</h6>
      <div class="table-responsive">
          <table class="table table-striped table-bordered align-middle">
              <thead class="table-light">
              <tr>
                  <th>الرقم</th>
                  <th>العميل</th>
                  <th>المبلغ</th>
                  <th>الحالة</th>
                  <th>التاريخ</th>
              </tr>
              </thead>
              <tbody>
              @forelse($latestOrders as $order)
                  <tr>
                      <td>#{{ $order->id }}</td>
                      <td>{{ $order->customer->name ?? '—' }}</td>
                      <td>{{ number_format($order->total_amount, 0) }} د.ع</td>
<td>
  @php
    $statusClassMap = [
      'pending'    => 'badge-status badge-status--pending',
      'delivered'  => 'badge-status badge-status--delivered',
      'returned'   => 'badge-status badge-status--returned',
      'cancelled'  => 'badge-status badge-status--cancelled',
      'processing' => 'badge-status badge-status--processing',
      'shipped'    => 'badge-status badge-status--shipped',
    ];
    $status = $order->status;
    $label  = $statusLabels[$status] ?? $status;
    $class  = $statusClassMap[$status] ?? 'badge-status';
  @endphp

  <span class="{{ $class }}">{{ $label }}</span>
</td>
                      <td>{{ $order->created_at?->format('Y-m-d') }}</td>
                  </tr>
              @empty
                  <tr><td colspan="5" class="text-center">لا توجد طلبات</td></tr>
              @endforelse
              </tbody>
          </table>
      </div>
  </div>
</div>
@endcan

<div class="row g-3">
  @can('view-products')
  <div class="col-lg-6">
      <div class="card h-100">
          <div class="card-body">
              <h6 class="mb-3">المنتجات الأكثر مبيعاً</h6>
              <ul class="list-group">
                  @forelse($topProducts as $product)
                      <li class="list-group-item d-flex justify-content-between align-items-center">
                          {{ $product->name_ar ?? $product->name }}
                          <span class="badge bg-primary rounded-pill">{{ $product->orders_count }}</span>
                      </li>
                  @empty
                      <li class="list-group-item text-center">لا توجد بيانات</li>
                  @endforelse
              </ul>
          </div>
      </div>
  </div>
  @endcan

  @can('view-reports-financial')
  <div class="col-lg-6">
      <div class="card h-100">
          <div class="card-body">
              <h6 class="mb-3">الأكثر ربحية (ضمن الفترة)</h6>
              <ul class="list-group">
                  @forelse($topProfitableProductsRange as $item)
                      <li class="list-group-item d-flex justify-content-between align-items-center">
                          {{ $item->product->name_ar ?? 'منتج محذوف' }}
                          <span class="badge bg-success rounded-pill fs-6">{{ number_format($item->profit, 0) }} د.ع</span>
                      </li>
                  @empty
                      <li class="list-group-item text-center">لا توجد بيانات كافية.</li>
                  @endforelse
              </ul>
          </div>
      </div>
  </div>
  @endcan
</div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
/* Helpers */
const brand   = getComputedStyle(document.documentElement).getPropertyValue('--brand').trim() || '#cd8985';
const brandD  = getComputedStyle(document.documentElement).getPropertyValue('--brand-dark').trim() || '#d1e7dd';

/* ===== Sparklines ===== */
(function(){
  const orders = document.getElementById('sparkOrders');
  const revenue= document.getElementById('sparkRevenue');
  const aov    = document.getElementById('sparkAov');
  const comp   = document.getElementById('sparkCompletion');

  const sparkCfg = (data,color)=>({
    type:'line',
    data:{ labels: data.map((_,i)=>i+1), datasets:[{ data, borderColor:color, backgroundColor:'rgba(0,0,0,0)', borderWidth:2, tension:.35, pointRadius:0 }]},
    options:{ responsive:true, plugins:{legend:{display:false}, tooltip:{enabled:false}}, scales:{x:{display:false}, y:{display:false}} }
  });

  if(orders)  new Chart(orders,  sparkCfg({!! json_encode($spark['orders']) !!}, brandD));
  if(revenue) new Chart(revenue, sparkCfg({!! json_encode($spark['revenue']) !!}, brand));
  if(aov)     new Chart(aov,     sparkCfg({!! json_encode($spark['aov']) !!}, '#6f42c1'));
  if(comp)    new Chart(comp,    sparkCfg({!! json_encode($spark['completion']) !!}, '#28a745'));
})();

/* ===== Doughnut: status distribution (ألوان الحالة) ===== */
(function(){
  const el = document.getElementById('statusDoughnut');
  if(!el) return;

  // ألوان الحالة: مكتمل = أخضر، انتظار = أصفر، راجعة = أحمر
  const COLORS = {
    delivered: '#28a745', // أخضر
    pending:   '#ffc107', // أصفر
    returned:  '#dc3545'  // أحمر
  };

  new Chart(el.getContext('2d'),{
    type:'doughnut',
    data:{
      labels: ['مكتمل','قيد الانتظار','راجعة'],
      datasets:[{
        data: [
          {{ $statusDistribution['delivered'] ?? 0 }},
          {{ $statusDistribution['pending'] ?? 0 }},
          {{ $statusDistribution['returned'] ?? 0 }}
        ],
        backgroundColor: [
          COLORS.delivered,
          COLORS.pending,
          COLORS.returned
        ],
        hoverBackgroundColor: [
          COLORS.delivered,
          COLORS.pending,
          COLORS.returned
        ],
        borderWidth: 0
      }]
    },
    options:{
      responsive:true,
      plugins:{ legend:{ position:'bottom' } }
    }
  });
})();

/* ===== Line: orders + revenue per day ===== */
(function(){
  const el = document.getElementById('ordersRevenueLine');
  if(!el) return;
  new Chart(el.getContext('2d'),{
    type:'line',
    data:{
      labels: {!! json_encode($rangeLabels) !!},
      datasets:[
        {label:'الطلبات', data:{!! json_encode($rangeOrders) !!}, borderColor:'#6f42c1', backgroundColor:'rgba(111,66,193,.15)', borderWidth:2, tension:.35, yAxisID:'y'},
        {label:'الإيراد (د.ع)', data:{!! json_encode($rangeRevenue) !!}, borderColor:brand, backgroundColor:'rgba(205,137,133,.15)', borderWidth:2, tension:.35, yAxisID:'y1'}
      ]
    },
    options:{
      responsive:true,
      interaction:{ mode:'index', intersect:false },
      scales:{
        y:  { beginAtZero:true, title:{display:true, text:'عدد الطلبات'} },
        y1: { beginAtZero:true, position:'right', grid:{ drawOnChartArea:false }, title:{display:true, text:'الإيراد'} }
      }
    }
  });
})();

/* ===== رسوماتك القديمة تبقى كما هي ===== */
(function(){
  const el = document.getElementById('ordersChart');
  if(!el) return;
  new Chart(el.getContext('2d'),{
    type:'line',
    data:{
      labels: {!! json_encode($chartLabels) !!},
      datasets:[
        {label:'إجمالي الطلبات', data:{!! json_encode($chartTotalOrdersData) !!}, borderColor:'#6f42c1', backgroundColor:'rgba(111,66,193,.15)', borderWidth:2, tension:.3, fill:false},
        {label:'طلبات مكتملة',  data:{!! json_encode($chartCompletedOrdersData) !!}, borderColor:'#28a745', backgroundColor:'rgba(40,167,69,.15)',   borderWidth:2, tension:.3, fill:false},
        {label:'قيد الانتظار',  data:{!! json_encode($chartPendingOrdersData) !!},    borderColor:'#ffc107', backgroundColor:'rgba(255,193,7,.15)',  borderWidth:2, tension:.3, fill:false},
        {label:'راجعة',         data:{!! json_encode($chartReturnedOrdersData) !!},  borderColor:'#dc3545', backgroundColor:'rgba(220,53,69,.15)',  borderWidth:2, tension:.3, fill:false},
      ]
    },
    options:{responsive:true, scales:{y:{beginAtZero:true}}}
  });
})();

(function(){
  const el = document.getElementById('ratingsBreakdownChart');
  if(!el) return;
  new Chart(el.getContext('2d'),{
    type:'bar',
    data:{
      labels:['5 نجوم','4 نجوم','3 نجوم','2 نجوم','1 نجمة'],
      datasets:[{
        label:'عدد التقييمات',
        data:[
          {{ $ratingsBreakdown[5] ?? 0 }},
          {{ $ratingsBreakdown[4] ?? 0 }},
          {{ $ratingsBreakdown[3] ?? 0 }},
          {{ $ratingsBreakdown[2] ?? 0 }},
          {{ $ratingsBreakdown[1] ?? 0 }},
        ],
        backgroundColor:[
          'rgba(40,167,69,.8)','rgba(40,167,69,.6)',
          'rgba(255,193,7,.8)',
          'rgba(220,53,69,.6)','rgba(220,53,69,.8)'
        ],
        borderWidth:0
      }]
    },
    options:{responsive:true, scales:{y:{beginAtZero:true}}}
  });
})();

(function(){
  const el = document.getElementById('ratingTrendChart');
  if(!el) return;
  new Chart(el.getContext('2d'),{
    type:'line',
    data:{
      labels: {!! json_encode($ratingTrendLabels) !!},
      datasets:[{
        label:'متوسط التقييم',
        data: {!! json_encode($ratingTrendData) !!},
        borderColor:'#6f42c1',
        backgroundColor:'rgba(111,66,193,.15)',
        borderWidth:2,
        tension:.35,
        spanGaps:true,
        fill:false
      }]
    },
    options:{responsive:true, scales:{y:{min:3, max:5}}}
  });
})();
</script>
@endpush
