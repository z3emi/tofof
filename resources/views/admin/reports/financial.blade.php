@extends('admin.layout')

@section('title', 'لوحة التقارير المالية')

@push('styles')
<style>
    .stat-card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        border: 0;
        border-radius: .75rem !important;
        position: relative;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
    }
    .text-purple { color: #6f42c1 !important; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 fw-bold">التقارير المالية</h1>
        <form method="GET" action="{{ route('admin.reports.financial') }}" class="d-flex gap-2 align-items-center bg-white p-2 rounded-3 shadow-sm">
            <select name="month" class="form-select form-select-sm">
                @for ($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                        {{ date('F', mktime(0, 0, 0, $m, 10)) }}
                    </option>
                @endfor
            </select>
            <select name="year" class="form-select form-select-sm">
                @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                @endfor
            </select>
            <button type="submit" class="btn btn-sm btn-primary">تطبيق</button>
            <a href="{{ route('admin.reports.financial.export', ['month' => $month, 'year' => $year]) }}" class="btn btn-sm btn-success" title="تصدير Excel" aria-label="تصدير Excel">
                <i class="bi bi-file-earmark-excel"></i>
            </a>
        </form>
    </div>

    <div class="row g-4 mb-4">
    <div class="row g-4 mb-4">
        @php
            $stats = [
                ['label' => 'إجمالي المبيعات', 'value' => number_format($totalSalesNet, 0) . ' د.ع', 'icon' => 'cash-stack', 'bg' => '#eadbcd', 'color' => 'text-dark', 'route' => route('admin.orders.index')],
                ['label' => 'عدد الطلبات (الفواتير)', 'value' => $totalOrders, 'icon' => 'receipt', 'bg' => '#cfe2ff', 'color' => 'text-primary', 'route' => route('admin.orders.index')],
            ];
        @endphp
        @foreach ($stats as $stat)
            <div class="col-xl-6 col-md-6">
                <div class="card stat-card shadow-sm h-100" style="background-color: {{ $stat['bg'] }};">
                    <div class="card-body d-flex align-items-center">
                        <div class="me-3 fs-2 {{ $stat['color'] }}">
                            <i class="bi bi-{{ $stat['icon'] }}"></i>
                        </div>
                        <div>
                            <div class="fw-semibold fs-6">{{ $stat['label'] }}</div>
                            <div class="fs-4 fw-bold">{!! $stat['value'] !!}</div>
                        </div>
                    </div>
                    @if($stat['route'])
                        <a href="{{ $stat['route'] }}" class="stretched-link"></a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body">
            <h6 class="mb-3">الأداء المالي خلال الفترة المحددة</h6>
            <canvas id="financialChart" height="100"></canvas>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-12">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="mb-3">المنتجات الأكثر مبيعاً</h6>
                    <ul class="list-group list-group-flush">
                        @forelse ($topSellingProducts as $item)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                {{ $item->product->name_ar ?? 'منتج محذوف' }}
                                <span class="badge bg-primary rounded-pill fs-6">{{ $item->total_quantity_sold }} قطعة</span>
                            </li>
                        @empty
                            <li class="list-group-item text-center">لا توجد بيانات كافية.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- جدول الفواتير (الطلبات) -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0 fw-bold" style="color:var(--brand-dark);">تفاصيل مبيعات الفواتير</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle text-center mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>{!! \App\Support\Sort::link('id', 'رقم الفاتورة (الطلب)') !!}</th>
                            <th>{!! \App\Support\Sort::link('created_at', 'تاريخ الفاتورة') !!}</th>
                            <th>العميل</th>
                            <th>{!! \App\Support\Sort::link('total_amount', 'المبلغ الإجمالي (بعد الخصم وبدون الشحن)') !!}</th>
                            <th>عرض الطلب</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ordersList as $order)
                            @php
                                $itemsTotal = $order->items->sum(fn($i) => (float)$i->price * (int)$i->quantity);
                                $discount   = (float)($order->discount_amount ?? 0);
                                $netAmount  = max(0, $itemsTotal - $discount);
                            @endphp
                            <tr>
                                <td class="fw-bold">#{{ $order->id }}</td>
                                <td dir="ltr">{{ $order->created_at->format('Y-m-d H:i') }}</td>
                                <td>{{ $order->customer->name ?? 'غير معروف' }}</td>
                                <td class="fw-bold text-success">{{ number_format($netAmount, 0) }} د.ع</td>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-eye"></i> التفاصيل
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 text-center text-muted">لا توجد مبيعات في هذه الفترة.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($ordersList->hasPages())
            <div class="card-footer bg-white d-flex justify-content-center pt-3 pb-1 border-top-0">
                {{ $ordersList->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('financialChart').getContext('2d');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [
                {
                    label: 'إجمالي المبيعات (صافي بعد الخصم، بدون شحن)',
                    data: @json($salesData),
                    borderColor: '#be6661',
                    backgroundColor: 'rgba(205, 137, 133, 0.2)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('ar-IQ').format(value);
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) label += ': ';
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('ar-IQ').format(context.parsed.y) + ' د.ع';
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush
