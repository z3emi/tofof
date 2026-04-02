@extends('admin.layout')

@section('title', 'تفاصيل كود الخصم: ' . $discount_code->code)

@push('styles')
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem; color: white; border-radius: 0 !important; }
    .table-container { border-radius: 15px; border: 1px solid #f1f5f9; overflow: hidden; background: #fff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    .stat-card { border-radius: 15px; border: 1px solid #f1f5f9; padding: 1.5rem; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
    .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; }
    .bg-pending   { background: #ffc107; color: #000; }
    .bg-processing { background: #0dcaf0; color: #000; }
    .bg-shipped   { background: #0d6efd; color: #fff; }
    .bg-delivered { background: #198754; color: #fff; }
    .bg-returned  { background: #fd7e14; color: #fff; }
    .bg-cancelled { background: #6c757d; color: #fff; }
    code { background: rgba(109, 14, 22, 0.05); color: var(--primary-dark); padding: 0.3rem 0.6rem; border-radius: 8px; font-weight: 700; border: 1px dashed rgba(109, 14, 22, 0.2); }
</style>
@endpush

@section('content')
@php
$statusLabels = [
    'pending'    => ['text' => 'قيد الانتظار', 'class' => 'bg-pending'],
    'processing' => ['text' => 'قيد المعالجة', 'class' => 'bg-processing'],
    'shipped'    => ['text' => 'تم الشحن',      'class' => 'bg-shipped'],
    'delivered'  => ['text' => 'تم التوصيل',    'class' => 'bg-delivered'],
    'returned'   => ['text' => 'مرتجع',          'class' => 'bg-returned'],
    'cancelled'  => ['text' => 'ملغي',           'class' => 'bg-cancelled'],
];
@endphp

<div class="form-card">
    {{-- Header --}}
    <div class="form-card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2 class="mb-2 fw-bold text-white">
                <i class="bi bi-percent me-2"></i>
                تفاصيل كود الخصم: <code class="bg-white bg-opacity-25 border-0 text-white px-3">{{ $discount_code->code }}</code>
            </h2>
            <p class="mb-0 opacity-75 fs-6 text-white small">المستخدمون والطلبات التي استفادت من هذا الكوبون.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.discount-codes.index') }}" class="btn btn-light px-4 fw-bold text-brand d-inline-flex align-items-center">
                <i class="bi bi-arrow-right me-1"></i> العودة للأكواد
            </a>
            @can('edit-discount-codes')
                <a href="{{ route('admin.discount-codes.edit', $discount_code->id) }}" class="btn btn-outline-light px-4 fw-bold d-inline-flex align-items-center">
                    <i class="bi bi-pencil me-1"></i> تعديل الكود
                </a>
            @endcan
        </div>
    </div>

    <div class="p-4 p-lg-5">

        {{-- ====== معلومات الكود ====== --}}
        <div class="row g-3 mb-5">
            <div class="col-12">
                <h6 class="fw-bold text-muted small text-uppercase mb-3">معلومات الكود</h6>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card text-center">
                    <div class="stat-icon mx-auto mb-2" style="background:rgba(109,14,22,0.08)"><i class="bi bi-tag-fill text-brand"></i></div>
                    <div class="small text-muted mb-1">نوع الخصم</div>
                    <div class="fw-bold">
                        @if($discount_code->type === 'fixed') مبلغ ثابت
                        @elseif($discount_code->type === 'percentage') نسبة مئوية
                        @else شحن مجاني @endif
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card text-center">
                    <div class="stat-icon mx-auto mb-2" style="background:rgba(40,167,69,0.1)"><i class="bi bi-cash-stack text-success"></i></div>
                    <div class="small text-muted mb-1">قيمة الخصم</div>
                    <div class="fw-bold">
                        @if($discount_code->type === 'fixed') {{ number_format($discount_code->value, 0) }} د.ع
                        @elseif($discount_code->type === 'percentage') {{ $discount_code->value }}%
                        @else — @endif
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card text-center">
                    <div class="stat-icon mx-auto mb-2" style="background:rgba(13,202,240,0.1)"><i class="bi bi-calendar-event text-info"></i></div>
                    <div class="small text-muted mb-1">تاريخ الانتهاء</div>
                    <div class="fw-bold">
                        @if($discount_code->expires_at)
                            {{ $discount_code->expires_at->format('Y-m-d') }}
                            @if($discount_code->isExpired()) <span class="badge bg-danger ms-1 small">منتهي</span> @endif
                        @else
                            <span class="text-muted">بدون انتهاء</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card text-center">
                    <div class="stat-icon mx-auto mb-2" style="background:rgba(255,193,7,0.1)"><i class="bi bi-toggle-on text-warning"></i></div>
                    <div class="small text-muted mb-1">الحالة</div>
                    <div class="fw-bold">
                        @if(!$discount_code->is_active)
                            <span class="badge bg-secondary px-3 py-2 rounded-pill">موقف</span>
                        @elseif($discount_code->isExpired())
                            <span class="badge bg-danger px-3 py-2 rounded-pill">منتهي</span>
                        @else
                            <span class="badge bg-success px-3 py-2 rounded-pill">نشط</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ====== إحصائيات الاستخدام ====== --}}
        <div class="row g-3 mb-5">
            <div class="col-12">
                <h6 class="fw-bold text-muted small text-uppercase mb-3">إحصائيات الاستخدام</h6>
            </div>
            <div class="col-md-4">
                <div class="stat-card text-center">
                    <div class="stat-icon mx-auto mb-2" style="background:rgba(109,14,22,0.08)"><i class="bi bi-receipt text-brand"></i></div>
                    <div class="small text-muted mb-1">إجمالي الطلبات</div>
                    <div class="fw-bold fs-4 text-brand">{{ $orders->total() }}</div>
                    @if($discount_code->max_uses)
                        <div class="small text-muted">من أصل {{ $discount_code->max_uses }} مسموح</div>
                    @endif
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card text-center">
                    <div class="stat-icon mx-auto mb-2" style="background:rgba(13,110,253,0.1)"><i class="bi bi-people-fill text-primary"></i></div>
                    <div class="small text-muted mb-1">مستخدمون فريدون</div>
                    <div class="fw-bold fs-4 text-primary">{{ $uniqueUsersCount }}</div>
                    @if($discount_code->max_uses_per_user)
                        <div class="small text-muted">حد {{ $discount_code->max_uses_per_user }} لكل مستخدم</div>
                    @endif
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card text-center">
                    <div class="stat-icon mx-auto mb-2" style="background:rgba(40,167,69,0.1)"><i class="bi bi-cash text-success"></i></div>
                    <div class="small text-muted mb-1">إجمالي الخصم الممنوح</div>
                    <div class="fw-bold fs-4 text-success">{{ number_format($totalDiscount, 0) }} <span class="fs-6">د.ع</span></div>
                </div>
            </div>
        </div>

        {{-- ====== جدول الطلبات ====== --}}
        <h6 class="fw-bold text-muted small text-uppercase mb-3">الطلبات التي استخدمت هذا الكود</h6>

        @if($orders->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-3 opacity-50"></i>
                لم يستخدم أحد هذا الكوبون بعد.
            </div>
        @else
        <div class="table-container shadow-sm border overflow-hidden mb-4">
            <table class="table mb-0 align-middle text-center">
                <thead class="bg-light border-bottom">
                    <tr class="text-muted small fw-bold">
                        <th class="py-3">#</th>
                        <th class="py-3">رقم الطلب</th>
                        <th class="py-3 text-start">العميل</th>
                        <th class="py-3">إجمالي الطلب</th>
                        <th class="py-3">مبلغ الخصم</th>
                        <th class="py-3">بعد الخصم</th>
                        <th class="py-3">حالة الطلب</th>
                        <th class="py-3">التاريخ</th>
                        <th class="py-3">الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        @php
                            $customer = $order->customer;
                            $user     = $order->user;
                            $name     = $customer?->name ?? $user?->name ?? '—';
                            $phone    = $customer?->phone_number ?? '—';
                            $stLabel  = $statusLabels[$order->status] ?? ['text' => $order->status, 'class' => 'bg-secondary'];
                            $afterDiscount = max(0, $order->total_amount - $order->discount_amount);
                        @endphp
                        <tr>
                            <td class="small text-muted">{{ $loop->iteration + ($orders->perPage() * ($orders->currentPage() - 1)) }}</td>
                            <td class="fw-bold"><span class="badge bg-light text-dark border px-2">#{{ $order->id }}</span></td>
                            <td class="text-start">
                                <div class="fw-bold">{{ $name }}</div>
                                @if($phone !== '—')
                                    <div class="small text-muted">{{ $phone }}</div>
                                @endif

                            </td>
                            <td class="fw-bold">{{ number_format($order->total_amount, 0) }} <small class="text-muted fw-normal">د.ع</small></td>
                            <td>
                                <span class="fw-bold text-danger">- {{ number_format($order->discount_amount, 0) }} <small class="text-muted fw-normal">د.ع</small></span>
                            </td>
                            <td class="fw-bold text-success">{{ number_format($afterDiscount, 0) }} <small class="text-muted fw-normal">د.ع</small></td>
                            <td>
                                <span class="badge {{ $stLabel['class'] }} px-3 py-2 rounded-pill small">{{ $stLabel['text'] }}</span>
                            </td>
                            <td class="small text-muted">{{ $order->created_at->format('Y-m-d') }}</td>
                            <td>
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-secondary rounded-3 px-2" title="عرض الطلب">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-light border-top">
                    <tr class="fw-bold small">
                        <td colspan="3" class="py-3 text-end text-muted">المجاميع:</td>
                        <td class="py-3">{{ number_format($orders->sum('total_amount'), 0) }} <small class="text-muted fw-normal">د.ع</small></td>
                        <td class="py-3 text-danger">- {{ number_format($orders->sum('discount_amount'), 0) }} <small class="text-muted fw-normal">د.ع</small></td>
                        <td class="py-3 text-success">{{ number_format($orders->sum(fn($o) => max(0, $o->total_amount - $o->discount_amount)), 0) }} <small class="text-muted fw-normal">د.ع</small></td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <form method="GET" action="{{ route('admin.discount-codes.show', $discount_code->id) }}" class="d-flex align-items-center bg-light p-2 rounded-3 border">
                @foreach(request()->except(['per_page','page']) as $k => $v)
                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                @endforeach
                <span class="small text-muted me-2 ms-2">إظهار:</span>
                <select name="per_page" class="form-select form-select-sm border-0 bg-transparent fw-bold" onchange="this.form.submit()" style="width:70px">
                    @foreach([10,25,50,100] as $s)
                        <option value="{{ $s }}" @selected(request('per_page', 15) == $s)>{{ $s }}</option>
                    @endforeach
                </select>
            </form>
            <div>{{ $orders->withQueryString()->links() }}</div>
        </div>
        @endif

    </div>
</div>
@endsection
