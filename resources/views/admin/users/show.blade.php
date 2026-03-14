@php
// مصفوفة الحالات بالعربية لتوحيد التصميم
$statusLabels = [
    'pending' => 'قيد الانتظار',
    'processing' => 'قيد المعالجة',
    'shipped' => 'تم الشحن',
    'delivered' => 'تم التوصيل',
    'cancelled' => 'ملغي',
    'returned' => 'مرتجع',
];
@endphp

@extends('admin.layout')
@section('title', 'تفاصيل المستخدم: ' . $user->name)

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .page-wrapper { --sticky-top: 0px; }
    @media (min-width: 1200px) { .sticky-xl { position: sticky; top: var(--sticky-top); } }

    .card { border-radius: .75rem; }
    .map-container { height: 250px; border-radius: 0.375rem; margin-top: 1rem; z-index: 1; overflow: hidden; }
    .section-gap > * + * { margin-top: 1rem; }

    .pagination { justify-content: center !important; gap: 0.4rem; margin-top: 1rem; }
    .pagination .page-item .page-link {
        background-color: #f9f5f1 !important; color: #cd8985 !important; border-color: #cd8985 !important;
        font-weight: 600; border-radius: 0.375rem; transition: background-color 0.3s, color 0.3s; box-shadow: none;
    }
    .pagination .page-item .page-link:hover { background-color: #dcaca9 !important; color: #fff !important; border-color: #dcaca9 !important; }
    .pagination .page-item.active .page-link { background-color: #cd8985 !important; border-color: #cd8985 !important; color: #fff !important; }

    /* تلوين صفوف المحفظة على نفس منطق الطلبات */
    .wallet-row-credit { background-color: #e9f7ef !important; } /* أخضر فاتح */
    .wallet-row-debit  { background-color: #fdecea !important; } /* أحمر فاتح */
    
    /* أزرار بالهوية البصرية */
    .btn-brand {
        background-color:#cd8985 !important;
        border-color:#cd8985 !important;
        color:#fff !important;
    }
    .btn-brand-outline {
        background-color:#fff !important;
        border:1px solid #cd8985 !important;
        color:#cd8985 !important;
    }

    /* تمركز المحتوى داخل الأزرار + فجوة صغيرة بين الأيقونة والنص */
    .btn-center {
        display:inline-flex; align-items:center; justify-content:center; gap:.35rem;
    }

    /* لون الأيقونات يعتمد على نوع الزر */
    .btn-brand i { color:#fff !important; }
    .btn-brand-outline i { color:#cd8985 !important; }
</style>
@endpush

@section('content')
<div class="page-wrapper container-fluid px-0">
    <div class="row g-3 g-xl-4">
        {{-- العمود الأيسر --}}
        <div class="col-12 col-xl-4">
            <div class="sticky-xl section-gap">

                {{-- ملف العميل --}}
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="bi bi-person-circle me-2"></i>ملف المستخدم</h4>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-brand btn-sm">
                                <i class="bi bi-pencil-square"></i> تعديل
                            </a>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-right"></i> القائمة
                            </a>
                        </div>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-3">
                            @php
                                $avatar = $user->avatar;
                                $src = $avatar ? asset('storage/'.$avatar) : asset('storage/avatars/default.jpg');
                            @endphp
                            <img src="{{ $src }}" alt="{{ $user->name }}" class="rounded-circle mx-auto" width="100" height="100" style="object-fit: cover;">
                        </div>
                        <h5 class="card-title mb-2">{{ $user->name }}</h5>

                        {{-- Badge الفئة --}}
                        @if($user->tier)
                            <div class="mb-2">
                                @if($user->tier === 'Gold')
                                    <span class="badge bg-warning text-dark fs-6">🥇 الفئة الذهبية</span>
                                @elseif($user->tier === 'Silver')
                                    <span class="badge bg-secondary fs-6">🥈 الفئة الفضية</span>
                                @elseif($user->tier === 'Bronze')
                                    <span class="badge fs-6" style="background-color:#cd7f32;color:#fff;">🥉 الفئة البرونزية</span>
                                @endif
                            </div>
                        @endif

                        <p class="card-text mb-1"><strong>رقم الهاتف:</strong> {{ $user->phone_number }}</p>
                        <p class="card-text mb-2"><strong>البريد الإلكتروني:</strong> {{ $user->email ?? 'لا يوجد' }}</p>

                        @if($user->governorate || $user->city || $user->address)
                            <div class="mt-3 pt-3 border-top text-start">
                                <h6 class="fw-bold text-primary small mb-2"><i class="bi bi-house-door me-1"></i>العنوان الأساسي:</h6>
                                <p class="small mb-1">
                                    <strong>{{ $user->governorate }}@if($user->city), {{ $user->city }}@endif</strong>
                                </p>
                                @if($user->address)
                                    <p class="small text-muted mb-2">{{ $user->address }}</p>
                                @endif
                                
                                @if($user->latitude && $user->longitude)
                                    <div id="map-primary" class="map-container" style="height: 150px;"></div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                {{-- العناوين --}}
                <div class="card shadow-sm" x-data="{ showAddresses: false }">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="bi bi-geo-alt-fill me-2"></i>العناوين المحفوظة</h4>
                        <button id="toggle-addresses-btn" @click="showAddresses = !showAddresses" class="btn btn-sm btn-outline-secondary">
                            <span x-show="!showAddresses">عرض <i class="bi bi-chevron-down"></i></span>
                            <span x-show="showAddresses" style="display:none;">إخفاء <i class="bi bi-chevron-up"></i></span>
                        </button>
                    </div>
                    <div class="card-body" x-show="showAddresses" x-collapse style="display:none;">
                        @if($user->addresses->isNotEmpty())
                            @foreach($user->addresses as $address)
                                <div class="mb-3 @if(!$loop->last) border-bottom pb-3 @endif">
                                    <p class="mb-1"><strong>{{ $address->governorate }}, {{ $address->city }}</strong></p>
                                    <p class="text-muted mb-1">{{ $address->address_details }}</p>
                                    @if($address->nearest_landmark)
                                        <p class="text-muted small mb-0">نقطة دالة: {{ $address->nearest_landmark }}</p>
                                    @endif

                                    @if($address->latitude && $address->longitude)
                                        <div id="map-{{ $address->id }}" class="map-container"></div>
                                    @endif
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted text-center mb-0">لا توجد عناوين محفوظة لهذا المستخدم.</p>
                        @endif
                    </div>
                </div>

                {{-- إحصائيات الطلبات (ملخص مرتب) --}}
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="bi bi-bar-chart-line me-2"></i>إحصائيات الطلبات</h4>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong>إجمالي الطلبات:</strong> {{ $totalOrders }}</p>
                        <ul class="list-unstyled small mb-2">
                            @foreach($orderCounts as $status => $count)
                                <li>{{ $statusLabels[$status] ?? $status }}: {{ $count }}</li>
                            @endforeach
                        </ul>
                        <p class="mb-0"><strong>مجموع المبالغ للطلبات المُوصلة:</strong> {{ number_format($deliveredAmount, 0) }} د.ع</p>
                    </div>
                </div>

                {{-- رصيد المحفظة الحالي --}}
                <div class="card shadow-sm border-0">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 d-flex align-items-center" style="color:#be6661;">
                            <i class="bi bi-wallet2 me-2"></i> رصيد المحفظة
                        </h5>
                        <span class="fw-bold" 
                              style="font-size:2rem; color:#3a3a3a;">
                            {{ number_format($wallet_balance, 0) }}
                            <small class="fs-6" style="color:#555;">د.ع</small>
                        </span>
                    </div>
                </div>

            </div>
        </div>

        {{-- العمود الأيمن --}}
        <div class="col-12 col-xl-8">
            {{-- سجل الطلبات --}}
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h4 class="mb-0"><i class="bi bi-receipt me-2"></i>سجل طلبات المستخدم</h4>
                    <form method="GET" action="{{ route('admin.users.show', $user->id) }}" class="ms-auto">
                        <div class="input-group input-group-sm" style="min-width: 280px;">
                            <input type="text" name="search" class="form-control" placeholder="ابحث برقم الطلب أو حالته..." value="{{ request('search') }}">
                            <button type="submit" class="btn btn-primary d-flex align-items-center justify-content-center gap-1"
                                    style="background-color:#cd8985; border-color:#cd8985; color:#fff;">
                                <i class="bi bi-search" style="color:#fff;"></i> 
                                <span>بحث</span>
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover text-center align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>المبلغ الإجمالي</th>
                                    <th>الخصم</th>
                                    <th>الحالة</th>
                                    <th>تاريخ الطلب</th>
                                    <th>العمليات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($orders as $order)
                                    <tr @class([
                                        'table-warning'  => $order->status == 'pending',
                                        'table-info'     => $order->status == 'processing',
                                        'table-primary'  => $order->status == 'shipped',
                                        'table-success'  => $order->status == 'delivered',
                                        'table-secondary'=> $order->status == 'cancelled',
                                        'table-danger'   => $order->status == 'returned',
                                    ])>
                                        <td>#{{ $order->id }}</td>
                                        <td>{{ number_format($order->total_amount, 0) }} د.ع</td>
                                        <td>{{ number_format($order->discount_amount ?? 0, 0) }} د.ع</td>
                                        <td>
                                            <span class="badge @if($order->status == 'pending') bg-warning text-dark @elseif($order->status == 'processing') bg-info text-dark @elseif($order->status == 'shipped') bg-primary @elseif($order->status == 'delivered') bg-success @elseif($order->status == 'cancelled') bg-secondary @elseif($order->status == 'returned') bg-danger @endif">
                                                {{ $statusLabels[$order->status] ?? $order->status }}
                                            </span>
                                        </td>
                                        <td>{{ $order->created_at->format('Y-m-d') }}</td>
                                        <td>
                                            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary px-2" title="عرض التفاصيل">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="p-4">لا توجد طلبات لهذا المستخدم.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                        <form method="GET" action="{{ route('admin.users.show', $user->id) }}" class="d-flex align-items-center">
                            @foreach(request()->except(['per_page', 'page']) as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach
                            <label for="per_page" class="me-2">عدد الطلبات:</label>
                            <select name="per_page" id="per_page" class="form-select form-select-sm" onchange="this.form.submit()" style="width: 80px;">
                                @foreach([5, 15, 25, 50] as $size)
                                    <option value="{{ $size }}" {{ request('per_page', 5) == $size ? 'selected' : '' }}>{{ $size }}</option>
                                @endforeach
                            </select>
                        </form>
                        <div>{{ $orders->withQueryString()->links() }}</div>
                    </div>
                </div>
            </div>

            {{-- كشف حساب المحفظة --}}
            <div class="card shadow-sm mt-3">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h4 class="mb-0"><i class="bi bi-journal-text me-2"></i>كشف حساب المحفظة</h4>

@php
    // نحافظ على باراميترات الفلاتر الحالية بدون تغيير
    $baseParams = request()->except(['wallet_view','wallet_page']);
@endphp

<div class="d-flex gap-2">
    {{-- عرض مفصّل --}}
    <a href="{{ route('admin.users.show', array_merge(['user' => $user->id, 'wallet_view' => 'page'], $baseParams)) }}"
       class="btn btn-sm btn-brand">
       <i class="bi bi-list-ul"></i>
       <span>عرض مفصل</span>
    </a>
</div>


                <div class="card-body">
                        {{-- كروت الإجماليات --}}
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <div class="card" style="background:#e9f7ef;">
                                    <div class="card-body">
                                        <div class="text-muted small">إجمالي الإيداعات</div>
                                        <div class="h5 m-0 fw-bold">{{ number_format($walletTotals->credits, 2) }} د.ع</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card" style="background:#fdecea;">
                                    <div class="card-body">
                                        <div class="text-muted small">إجمالي السحوبات</div>
                                        <div class="h5 m-0 fw-bold">{{ number_format($walletTotals->debits, 2) }} د.ع</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card" style="background:#fff3cd;">
                                    <div class="card-body">
                                        <div class="text-muted small">الصافي ضمن الفترة</div>
                                        <div class="h5 m-0 fw-bold">{{ number_format($walletTotals->net, 2) }} د.ع</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- فلاتر (تظهر دومًا، لكن المفصّل يعطيك تحكم كامل) --}}
                        <form method="GET" action="{{ route('admin.users.show', $user->id) }}" class="row g-2 align-items-end mb-3">
                            {{-- حافظ على جميع باراميترات الصفحة --}}
                            @foreach(request()->except(['wallet_from','wallet_to','wallet_type','wallet_q','wallet_per_page','wallet_page']) as $key => $val)
                                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                            @endforeach

                            @if($walletView === 'detailed')
                                <div class="col-12 col-md-3">
                                    <label class="form-label">من تاريخ</label>
                                    <input type="date" name="wallet_from" value="{{ $walletFrom }}" class="form-control form-control-sm">
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label">إلى تاريخ</label>
                                    <input type="date" name="wallet_to" value="{{ $walletTo }}" class="form-control form-control-sm">
                                </div>
                                <div class="col-12 col-md-2">
                                    <label class="form-label">النوع</label>
                                    <select name="wallet_type" class="form-select form-select-sm">
                                        <option value="">الكل</option>
                                        <option value="credit" {{ $walletType === 'credit' ? 'selected' : '' }}>إيداع</option>
                                        <option value="debit"  {{ $walletType === 'debit'  ? 'selected' : '' }}>سحب</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-2">
                                    <label class="form-label">بحث</label>
                                    <input type="text" name="wallet_q" value="{{ $walletSearch }}" class="form-control form-control-sm" placeholder="الوصف...">
                                </div>
                            @endif

                        {{-- جدول الحركات --}}
                        <div class="table-responsive">
                            @php
                                // === إضافة بسيطة: تراكمي لمعالجة الرصيد بعد العملية عند غيابه ===
                                $runningAfter = $wallet_balance ?? (float)($user->wallet_balance ?? 0);
                            @endphp
                            <table class="table table-bordered table-hover text-center align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>التاريخ</th>
                                        <th>النوع</th>
                                        <th>المبلغ</th>
                                        <th>الوصف</th>
                                        <th>الرصيد بعد العملية</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($walletTransactions as $tx)
                                        @php
                                            // لو balance_after غير محفوظة، نستخدم التراكمي الحالي
                                            $after = is_null($tx->balance_after) ? $runningAfter : (float)$tx->balance_after;

                                            // نُحدّث التراكمي للحركة الأقدم التالية
                                            if ($tx->type === 'credit') {
                                                $runningAfter = $after - (float)$tx->amount;
                                            } else {
                                                $runningAfter = $after + (float)$tx->amount;
                                            }
                                        @endphp
                                        <tr class="{{ $tx->type === 'credit' ? 'wallet-row-credit' : 'wallet-row-debit' }}">
                                            <td>{{ $tx->id }}</td>
                                            <td>{{ \Carbon\Carbon::parse($tx->created_at)->format('Y-m-d H:i') }}</td>
                                            <td>
                                                @if($tx->type === 'credit')
                                                    <span class="badge bg-success">إيداع</span>
                                                @else
                                                    <span class="badge bg-danger">سحب</span>
                                                @endif
                                            </td>
                                            <td>{{ number_format($tx->amount, 2) }} د.ع</td>
                                            <td class="text-start">{{ $tx->description ?? '-' }}</td>
                                            <td>{{ number_format($after, 2) }} د.ع</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="p-4">لا توجد حركات مطابقة.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                            <form method="GET" action="{{ route('admin.users.show', $user->id) }}" class="d-flex align-items-center">
                                {{-- نحافظ على كل باراميترات الصفحة ونستبدل فقط wallet_per_page --}}
                                @foreach(request()->except(['wallet_per_page', 'wallet_page']) as $key => $val)
                                    <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                                @endforeach

                                <label for="wallet_per_page" class="me-2">عدد السجلات:</label>
                                <select name="wallet_per_page" id="wallet_per_page" class="form-select form-select-sm" onchange="this.form.submit()" style="width: 80px;">
                                    @foreach([5, 10, 15, 25, 50] as $pp)
                                        <option value="{{ $pp }}" {{ (int)($walletPer ?? 5) === $pp ? 'selected' : '' }}>{{ $pp }}</option>
                                    @endforeach
                                </select>
                                {{-- نثبت الـ view الحالي حتى ما يتغيّر --}}
                                <input type="hidden" name="wallet_view" value="{{ $walletView }}">
                            </form>
                            {{-- باجينيشن كشف المحفظة --}}
                            @if(method_exists($walletTransactions, 'links'))
                                <div class="d-flex justify-content-end">
                                    {{ $walletTransactions->appends([
                                        'wallet_view' => $walletView,
                                        'wallet_from' => $walletFrom,
                                        'wallet_to'   => $walletTo,
                                        'wallet_type' => $walletType,
                                        'wallet_q'    => $walletSearch,
                                        'wallet_per_page' => $walletPer,
                                    ])->links() }}
                                </div>
                            @endif
                        </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    window.__customerMaps = [];

    @if($user->latitude && $user->longitude)
        (function() {
            const el = document.getElementById('map-primary');
            if (!el) return;
            const map = L.map(el).setView([{{ $user->latitude }}, {{ $user->longitude }}], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);
            L.marker([{{ $user->latitude }}, {{ $user->longitude }}]).addTo(map);
            window.__customerMaps.push(map);
        })();
    @endif

    @if($user->addresses->isNotEmpty())
        @foreach($user->addresses as $address)
            @if($address->latitude && $address->longitude)
                (function() {
                    const el = document.getElementById('map-{{ $address->id }}');
                    if (!el) return;
                    const map = L.map(el).setView([{{ $address->latitude }}, {{ $address->longitude }}], 15);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);
                    L.marker([{{ $address->latitude }}, {{ $address->longitude }}]).addTo(map);
                    window.__customerMaps.push(map);
                })();
            @endif
        @endforeach
    @endif

    const toggleBtn = document.getElementById('toggle-addresses-btn');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            setTimeout(function() {
                (window.__customerMaps || []).forEach(function(m) { try { m.invalidateSize(); } catch (e) {} });
            }, 450);
        });
    }

    window.addEventListener('resize', function() {
        clearTimeout(window.__mapResizeTimer);
        window.__mapResizeTimer = setTimeout(function() {
            (window.__customerMaps || []).forEach(function(m) { try { m.invalidateSize(); } catch (e) {} });
        }, 150);
    });
});
</script>
@endpush
