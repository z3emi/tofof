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
    :root {
        --panel-border: #e7edf4;
        --panel-muted: #64748b;
        --panel-title: #334155;
        --accent-soft: #f8eef1;
        --accent-main: var(--primary-medium);
        --accent-main-hover: var(--primary-dark);
        --btn-radius: .7rem;
    }

    .form-card {
        border-radius: 0 !important;
        border: none !important;
        box-shadow: none !important;
        background: #fff;
        width: 100% !important;
        margin: 0 !important;
    }
    .form-card-header {
        background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%);
        padding: 2.5rem 3rem;
        color: #fff;
        border-radius: 0 !important;
    }
    .form-card-header h2 {
        font-size: 1.7rem;
        line-height: 1.25;
    }
    .form-card-header p { font-size: .95rem; }

    .page-wrapper {
        --sticky-top: 0px;
        background: #fff;
        border-radius: 0;
        padding: 0;
    }
    @media (min-width: 1200px) { .sticky-xl { position: sticky; top: var(--sticky-top); } }

    .card {
        border-radius: 15px;
        border: 1px solid var(--panel-border);
        box-shadow: 0 2px 8px rgba(0,0,0,0.04) !important;
        overflow: hidden;
    }
    .card-header {
        background: #fff;
        border-bottom: 1px solid var(--panel-border);
        padding: .9rem 1rem;
    }
    .card-header h4 {
        color: var(--panel-title);
        font-size: 1.08rem;
        font-weight: 700;
    }
    .card-body { padding: 1rem; }
    .map-container {
        height: 250px;
        border-radius: 0.5rem;
        margin-top: 1rem;
        z-index: 1;
        overflow: hidden;
        border: 1px solid #edd6d4;
    }
    .section-gap > * + * { margin-top: 1rem; }
    .card-title { color: var(--panel-title); font-weight: 700; font-size: 1.08rem; }

    .table-container {
        border-radius: 15px;
        border: 1px solid var(--panel-border);
        overflow: hidden;
        background: #fff;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }

    .table {
        --bs-table-striped-bg: #f8fafc;
        --bs-table-hover-bg: #f1f5f9;
        margin-bottom: 0;
        font-size: .92rem;
    }
    .table thead th {
        background-color: #f8fafc !important;
        color: #475569;
        font-weight: 700;
        border-bottom-width: 1px;
        font-size: .85rem;
        white-space: nowrap;
    }
    .table td, .table th {
        vertical-align: middle;
        border-color: var(--panel-border);
    }

    .pagination { justify-content: center !important; gap: 0.4rem; margin-top: 1rem; }
    .pagination .page-item .page-link {
        background-color: #fff !important; color: var(--accent-main) !important; border-color: #d6dbe2 !important;
        font-weight: 600; border-radius: 0.375rem; transition: background-color 0.3s, color 0.3s; box-shadow: none;
    }
    .pagination .page-item .page-link:hover { background-color: var(--accent-soft) !important; color: var(--accent-main) !important; border-color: #cfd6df !important; }
    .pagination .page-item.active .page-link { background-color: var(--accent-main) !important; border-color: var(--accent-main) !important; color: #fff !important; }

    /* تلوين صفوف المحفظة على نفس منطق الطلبات */
    .wallet-row-credit { background-color: #e9f7ef !important; } /* أخضر فاتح */
    .wallet-row-debit  { background-color: #fdecea !important; } /* أحمر فاتح */
    
    .btn {
        border-radius: var(--btn-radius);
        font-weight: 700;
        transition: all .2s ease;
    }
    .btn-sm { font-size: .8rem; padding: .45rem .72rem; min-height: 34px; }

    .btn-ui-primary {
        background: linear-gradient(135deg, var(--accent-main) 0%, var(--accent-main-hover) 100%) !important;
        border-color: var(--accent-main-hover) !important;
        color:#fff !important;
        box-shadow: 0 8px 18px rgba(109, 14, 22, .22);
    }
    .btn-ui-primary:hover {
        filter: brightness(.97);
        transform: translateY(-1px);
        box-shadow: 0 10px 22px rgba(109, 14, 22, .28);
    }
    .btn-ui-primary:active { transform: translateY(0); box-shadow: 0 5px 12px rgba(109, 14, 22, .22); }

    .btn-ui-outline {
        background-color:#fff !important;
        border:1px solid color-mix(in srgb, var(--accent-main) 55%, #fff) !important;
        color:var(--accent-main-hover) !important;
    }
    .btn-ui-outline:hover {
        background: color-mix(in srgb, var(--accent-soft) 72%, #fff) !important;
        border-color: var(--accent-main-hover) !important;
        color: var(--accent-main-hover) !important;
    }

    .btn-ui-muted {
        background: #fff !important;
        border: 1px solid #d8dee7 !important;
        color: #475569 !important;
    }
    .btn-ui-muted:hover {
        background: #f8fafc !important;
        border-color: #d7dee8 !important;
    }

    .btn-ui-header-light {
        background: #fff !important;
        color: var(--primary-dark) !important;
        border: 1px solid rgba(255,255,255,.82) !important;
        min-height: 40px;
        font-size: .86rem;
        padding: .52rem .95rem;
        box-shadow: 0 6px 16px rgba(0, 0, 0, .08);
    }
    .btn-ui-header-light:hover { background: #f8faff !important; transform: translateY(-1px); }
    .btn-ui-header-outline {
        background: transparent !important;
        color: #fff !important;
        border: 1px solid rgba(255,255,255,.75) !important;
        min-height: 40px;
        font-size: .86rem;
        padding: .52rem .95rem;
    }
    .btn-ui-header-outline:hover { background: rgba(255,255,255,.12) !important; }

    .btn-ui-primary:focus-visible,
    .btn-ui-outline:focus-visible,
    .btn-ui-muted:focus-visible,
    .btn-ui-header-light:focus-visible,
    .btn-ui-header-outline:focus-visible {
        outline: 0;
        box-shadow: 0 0 0 .22rem rgba(109, 14, 22, .18);
    }

    /* تمركز المحتوى داخل الأزرار + فجوة صغيرة بين الأيقونة والنص */
    .btn-center {
        display:inline-flex; align-items:center; justify-content:center; gap:.35rem;
    }

    /* لون الأيقونات يعتمد على نوع الزر */
    .btn-ui-primary i,
    .btn-ui-header-outline i { color:#fff !important; }
    .btn-ui-outline i,
    .btn-ui-muted i,
    .btn-ui-header-light i { color: inherit !important; }

    .summary-chip {
        background: #f8fafc;
        color: #475569;
        border: 1px solid var(--panel-border);
        border-radius: 999px;
        padding: .18rem .62rem;
        font-size: .74rem;
        font-weight: 700;
    }

    .stat-soft {
        border-radius: 12px;
        border: 1px solid var(--panel-border);
    }
    .stat-soft-green { background: #f0fdf4; }
    .stat-soft-red { background: #fef2f2; }
    .stat-soft-amber { background: #fffbeb; }
    .stat-soft .text-muted { color: var(--panel-muted) !important; }
    .stat-soft .h5 { font-size: 1.12rem; }

    .badge { font-size: .75rem; padding: .42rem .6rem; }
    .id-badge { font-size: .75rem; color: #475569; background: #f8fafc; border: 1px solid var(--panel-border); }
    .avatar-img { width: 108px; height: 108px; object-fit: cover; }
    .avatar-img { border-radius: 50% !important; }
    .wallet-title { color: #475569 !important; font-size: 1rem; font-weight: 700; }
    .wallet-inline {
        margin-top: .75rem;
        padding: .65rem .85rem;
        border: 1px solid var(--panel-border);
        border-radius: .7rem;
        background: #f8fafc;
        text-align: center;
    }
    .wallet-inline .wallet-title { font-size: .92rem; }
    .wallet-inline .wallet-balance-value { font-size: 1.35rem; }

    .wallet-balance-value {
        font-size: 1.9rem;
        color: #0f172a;
        line-height: 1;
        letter-spacing: -.01em;
    }

    @media (max-width: 767.98px) {
        .form-card-header { padding: 1.35rem 1rem; }
        .form-card-header h2 { font-size: 1.22rem; }
        .form-card-header p { font-size: .82rem; }
        .page-wrapper { padding: 0; border-radius: 0; }
        .wallet-balance-value { font-size: 1.42rem; }
        .card-header h4 { font-size: .96rem; }
        .avatar-img { width: 90px; height: 90px; }
        .btn-sm { min-height: 32px; font-size: .76rem; }
        .btn-ui-header-light,
        .btn-ui-header-outline { min-height: 36px; font-size: .78rem; }
    }
</style>
@endpush

@section('content')
<div class="form-card">
    <div class="form-card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2 class="mb-2 fw-bold text-white">
                <i class="bi bi-person-circle me-2"></i>
                تفاصيل المستخدم: <span class="badge bg-white text-dark px-3 py-2">{{ $user->name }}</span>
            </h2>
            <p class="mb-0 opacity-75 fs-6 text-white small">نظرة كاملة على الملف الشخصي، الطلبات، العناوين، وكشف المحفظة.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.users.index') }}" class="btn btn-ui-header-light btn-center d-inline-flex align-items-center">
                <i class="bi bi-arrow-right me-1"></i> القائمة
            </a>
            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-ui-header-outline btn-center d-inline-flex align-items-center">
                <i class="bi bi-pencil-square me-1"></i> تعديل المستخدم
            </a>
        </div>
    </div>

    <div class="p-4 p-lg-5">
<div class="page-wrapper container-fluid px-0">
    <div class="row g-3 g-xl-4">
        {{-- العمود الأيسر --}}
        <div class="col-12 col-xl-4">
            <div class="sticky-xl section-gap">

                {{-- ملف العميل --}}
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="bi bi-person-circle me-2"></i>ملف المستخدم</h4>
                        <span class="badge id-badge">ID: {{ $user->id }}</span>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-3">
                            @php
                                $src = $user->avatar_url;
                            @endphp
                            <img src="{{ $src }}" alt="{{ $user->name }}" class="img-fluid rounded border avatar-img" onerror="this.onerror=null;this.src='{{ asset('storage/avatars/default.jpg') }}';">
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

                        <div class="wallet-inline">
                            <h6 class="mb-1 d-flex align-items-center justify-content-center wallet-title">
                                <i class="bi bi-wallet2 me-2"></i> رصيد المحفظة
                            </h6>
                            <span class="fw-bold wallet-balance-value">
                                {{ number_format($wallet_balance, 0) }}
                                <small class="fs-6 text-muted">د.ع</small>
                            </span>
                        </div>

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
                        <button id="toggle-addresses-btn" @click="showAddresses = !showAddresses" class="btn btn-sm btn-ui-muted btn-center">
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
                        <ul class="list-unstyled small mb-2 d-grid gap-1">
                            @foreach($orderCounts as $status => $count)
                                <li class="d-flex justify-content-between align-items-center">
                                    <span>{{ $statusLabels[$status] ?? $status }}</span>
                                    <span class="summary-chip">{{ $count }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <p class="mb-0"><strong>مجموع المبالغ للطلبات المُوصلة:</strong> {{ number_format($deliveredAmount, 0) }} د.ع</p>
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
                            <button type="submit" class="btn btn-sm btn-ui-primary btn-center">
                                <i class="bi bi-search"></i>
                                <span>بحث</span>
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-body">
                    <div class="table-container">
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
                                            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-ui-outline btn-center px-2" title="عرض التفاصيل">
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
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                        <form method="GET" action="{{ route('admin.users.show', $user->id) }}" class="d-flex align-items-center">
                            @foreach(request()->except(['per_page', 'page']) as $key => $value)
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endforeach
                            <label for="per_page" class="me-2">عدد الطلبات:</label>
                            <select name="per_page" id="per_page" class="form-select form-select-sm" onchange="this.form.submit()" style="width: 80px;">
                                @foreach([5, 10, 25, 50, 100] as $size)
                                    <option value="{{ $size }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>{{ $size }}</option>
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
                        <a href="{{ route('admin.users.show', array_merge(['user' => $user->id, 'wallet_view' => 'page'], $baseParams)) }}"
                           class="btn btn-sm btn-ui-primary btn-center">
                            <i class="bi bi-list-ul"></i>
                            <span>عرض مفصل</span>
                        </a>
                    </div>
                </div>

                <div class="card-body">
                        {{-- كروت الإجماليات --}}
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <div class="card stat-soft stat-soft-green">
                                    <div class="card-body">
                                        <div class="text-muted small">إجمالي الإيداعات</div>
                                        <div class="h5 m-0 fw-bold">{{ number_format($walletTotals->credits, 2) }} د.ع</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card stat-soft stat-soft-red">
                                    <div class="card-body">
                                        <div class="text-muted small">إجمالي السحوبات</div>
                                        <div class="h5 m-0 fw-bold">{{ number_format($walletTotals->debits, 2) }} د.ع</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card stat-soft stat-soft-amber">
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
                                <div class="col-12 col-md-2">
                                    <button type="submit" class="btn btn-ui-primary btn-sm w-100 btn-center">
                                        <i class="bi bi-funnel"></i>
                                        <span>تصفية</span>
                                    </button>
                                </div>
                            @endif
                        </form>

                        {{-- جدول الحركات --}}
                        <div class="table-container">
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
