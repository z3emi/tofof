@extends('admin.layout')

@section('title', 'تقارير المخزون')

@push('styles')
<style>
  .stat-card{transition:transform .2s,box-shadow .2s;border:0;border-radius:1rem!important;overflow:hidden}
  .stat-card:hover{transform:translateY(-4px);box-shadow:0 10px 26px rgba(0,0,0,.06)!important}
  .bg-warning-custom{background-color:#fff3cd!important}
  .bg-danger-custom{background-color:#fde2e2!important}
  .card-header{background:linear-gradient(135deg,#f8f9fa 0%,#eef1f4 100%);border-bottom:1px solid #e5e7eb}
  .card-body{background:linear-gradient(135deg,#fff 0%,#fdfdfd 100%)}
  .badge{font-weight:700;font-size:.85rem;border-radius:.6rem;padding:.35em .6em}
  .list-group-item{border:none;border-bottom:1px solid rgba(0,0,0,.05);padding:.65rem 1rem}
  .product-row{display:flex;justify-content:space-between;align-items:center;gap:.75rem}
  .product-name{font-weight:700}
  .meta{color:#6b7280;font-size:.85rem}
  .actions .btn{padding:.25rem .55rem}
  .table thead th{white-space:nowrap}
  .filter-wrap{gap:.5rem}
  .filter-wrap .form-select,.filter-wrap .btn{height:36px}
  /* مودال أنيق */
  .modal-header{border-bottom:1px solid #eef2f7}
  .modal-footer{border-top:1px solid #eef2f7}
  .kv{display:grid;grid-template-columns:150px 1fr;gap:.35rem .75rem;font-size:.95rem}
  .kv .k{color:#6b7280}
  .kv .v{font-weight:700}
</style>
@endpush

@section('content')
<div class="container-fluid">

  {{-- فلتر الشهر والسنة --}}
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h4 mb-0 fw-bold">تقارير المخزون</h1>

    <form method="GET" action="{{ route('admin.reports.stock') }}" class="d-flex align-items-center bg-white p-2 rounded-3 shadow-sm filter-wrap">
      <select name="month" class="form-select form-select-sm">
        @for ($m = 1; $m <= 12; $m++)
          <option value="{{ $m }}" {{ ($month ?? now()->month) == $m ? 'selected' : '' }}>
            {{ date('F', mktime(0,0,0,$m,10)) }}
          </option>
        @endfor
      </select>
      <select name="year" class="form-select form-select-sm">
        @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
          <option value="{{ $y }}" {{ ($year ?? now()->year) == $y ? 'selected' : '' }}>
            {{ $y }}
          </option>
        @endfor
      </select>
      <button type="submit" class="btn btn-sm btn-primary" style="background-color:#cd8985;border-color:#cd8985">
        تطبيق
      </button>
    </form>
  </div>

  <div class="row g-4">

    {{-- المنتجات على وشك النفاد --}}
    <div class="col-lg-6">
      <div class="card stat-card bg-warning-custom shadow-sm h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0 text-warning">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>منتجات على وشك النفاد
          </h5>
          <span class="badge bg-warning text-dark">{{ $lowStockProducts->count() }}</span>
        </div>
        <div class="card-body">
          @if($lowStockProducts->isEmpty())
            <p class="text-center text-muted mt-3">لا توجد منتجات على وشك النفاد حاليًا.</p>
          @else
            <ul class="list-group list-group-flush">
              @foreach($lowStockProducts as $product)
                @php
                  $batchesCount = $product->purchaseItems->count();
                  $lastBatch = $product->purchaseItems->sortByDesc('created_at')->first();
                  $lastRestockAt = optional($lastBatch?->created_at)->format('Y-m-d');
                  $sku = $product->sku ?? '—';
                  $barcode = $product->barcode ?? $product->bar_code ?? '—';
                  $price = $product->price ?? $product->sale_price ?? null;
                @endphp
                <li class="list-group-item">
                  <div class="product-row">
                    <div>
                      <div class="product-name">
                        <a href="{{ route('admin.products.edit', $product->id) }}">{{ $product->name_ar }}</a>
                      </div>
                      <div class="meta">
                        {{ $batchesCount }} دفعة • آخر توريد: {{ $lastRestockAt ?? '—' }}
                      </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 actions">
                      <span class="badge bg-warning text-dark">{{ (int) $product->stock_quantity }} قطعة متبقية</span>
                      <button
                        type="button"
                        class="btn btn-outline-dark btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#p-{{ $product->id }}"
                        title="تفاصيل المنتج">
                        <i class="bi bi-info-circle"></i>
                      </button>
                      <a class="btn btn-outline-primary btn-sm"
                         href="{{ route('admin.products.edit', $product->id) }}"
                         title="فتح صفحة المنتج">
                        <i class="bi bi-box-arrow-up-right"></i>
                      </a>
                    </div>
                  </div>
                </li>

                {{-- Modal تفاصيل المنتج --}}
                <div class="modal fade" id="p-{{ $product->id }}" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h6 class="modal-title fw-bold">{{ $product->name_ar }}</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body">
                        <div class="kv">
                          <div class="k">الرمز (SKU)</div><div class="v">{{ $sku }}</div>
                          <div class="k">الباركود</div><div class="v">{{ $barcode }}</div>
                          <div class="k">الرصيد الحالي</div><div class="v">{{ (int) $product->stock_quantity }} قطعة</div>
                          <div class="k">عدد الدُفعات</div><div class="v">{{ $batchesCount }}</div>
                          <div class="k">آخر توريد</div><div class="v">{{ $lastRestockAt ?? '—' }}</div>
                          <div class="k">سعر البيع</div><div class="v">{{ $price ? number_format($price,0) . ' د.ع' : '—' }}</div>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-primary"
                           style="background-color:#cd8985;border-color:#cd8985">
                          تعديل المنتج
                        </a>
                        <button class="btn btn-light" data-bs-dismiss="modal">إغلاق</button>
                      </div>
                    </div>
                  </div>
                </div>
              @endforeach
            </ul>
          @endif
        </div>
      </div>
    </div>

    {{-- المنتجات النافدة --}}
    <div class="col-lg-6">
      <div class="card stat-card bg-danger-custom shadow-sm h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0 text-danger">
            <i class="bi bi-x-octagon-fill me-2"></i>منتجات نفدت من المخزون
          </h5>
          <span class="badge bg-danger">{{ $outOfStockProducts->count() }}</span>
        </div>
        <div class="card-body">
          @if($outOfStockProducts->isEmpty())
            <p class="text-center text-muted mt-3">لا توجد منتجات نافدة حاليًا.</p>
          @else
            <ul class="list-group list-group-flush">
              @foreach($outOfStockProducts as $product)
                @php
                  $batchesCount = $product->purchaseItems->count();
                  $lastBatch = $product->purchaseItems->sortByDesc('created_at')->first();
                  $lastRestockAt = optional($lastBatch?->created_at)->format('Y-m-d');
                  $sku = $product->sku ?? '—';
                  $barcode = $product->barcode ?? $product->bar_code ?? '—';
                  $price = $product->price ?? $product->sale_price ?? null;
                @endphp
                <li class="list-group-item">
                  <div class="product-row">
                    <div>
                      <div class="product-name">
                        <a href="{{ route('admin.products.edit', $product->id) }}">{{ $product->name_ar }}</a>
                      </div>
                      <div class="meta">
                        {{ $batchesCount }} دفعة • آخر توريد: {{ $lastRestockAt ?? '—' }}
                      </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 actions">
                      <span class="badge bg-danger">نفدت الكمية</span>
                      <button
                        type="button"
                        class="btn btn-outline-dark btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#p-{{ $product->id }}"
                        title="تفاصيل المنتج">
                        <i class="bi bi-info-circle"></i>
                      </button>
                      <a class="btn btn-outline-primary btn-sm"
                         href="{{ route('admin.products.edit', $product->id) }}"
                         title="فتح صفحة المنتج">
                        <i class="bi bi-box-arrow-up-right"></i>
                      </a>
                    </div>
                  </div>
                </li>

                {{-- Modal تفاصيل المنتج (نفس المودال أعلاه) --}}
                <div class="modal fade" id="p-{{ $product->id }}" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h6 class="modal-title fw-bold">{{ $product->name_ar }}</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body">
                        <div class="kv">
                          <div class="k">الرمز (SKU)</div><div class="v">{{ $sku }}</div>
                          <div class="k">الباركود</div><div class="v">{{ $barcode }}</div>
                          <div class="k">الرصيد الحالي</div><div class="v">0 قطعة</div>
                          <div class="k">عدد الدُفعات</div><div class="v">{{ $batchesCount }}</div>
                          <div class="k">آخر توريد</div><div class="v">{{ $lastRestockAt ?? '—' }}</div>
                          <div class="k">سعر البيع</div><div class="v">{{ $price ? number_format($price,0) . ' د.ع' : '—' }}</div>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-primary"
                           style="background-color:#cd8985;border-color:#cd8985">
                          تعديل المنتج
                        </a>
                        <button class="btn btn-light" data-bs-dismiss="modal">إغلاق</button>
                      </div>
                    </div>
                  </div>
                </div>
              @endforeach
            </ul>
          @endif
        </div>
      </div>
    </div>

    {{-- الأكثر مبيعًا --}}
    <div class="col-12">
      <div class="card stat-card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">
            <i class="bi bi-trophy-fill me-2"></i>المنتجات الأكثر مبيعًا (آخر 30 يوم)
          </h5>
          <span class="badge bg-primary">{{ $topSellingProducts->count() }}</span>
        </div>
        <div class="card-body">
          @if($topSellingProducts->isEmpty())
            <p class="text-center text-muted mt-3">لا توجد بيانات مبيعات كافية.</p>
          @else
            <div class="table-responsive">
              <table class="table table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th>المنتج</th>
                    <th class="text-center">عدد مرات البيع</th>
                    <th class="text-center">تفاصيل</th>
                    <th class="text-center">فتح</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($topSellingProducts as $p)
                    <tr>
                      <td>{{ $p->name_ar }}</td>
                      <td class="text-center fw-bold">{{ $p->order_items_count }}</td>
                      <td class="text-center">
                        <button
                          type="button"
                          class="btn btn-outline-dark btn-sm"
                          data-bs-toggle="modal"
                          data-bs-target="#p-{{ $p->id }}">
                          <i class="bi bi-info-circle"></i>
                        </button>
                      </td>
                      <td class="text-center">
                        <a class="btn btn-outline-primary btn-sm" href="{{ route('admin.products.edit', $p->id) }}">
                          <i class="bi bi-box-arrow-up-right"></i>
                        </a>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        </div>
      </div>
    </div>

  </div>
</div>
@endsection
