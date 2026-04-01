@php
$statusLabels = ['pending' => 'قيد الانتظار', 'processing' => 'قيد المعالجة', 'shipped' => 'تم الشحن', 'delivered' => 'تم التوصيل', 'cancelled' => 'ملغي', 'returned' => 'مرتجع'];
$governorates = ['بغداد', 'نينوى', 'البصرة', 'صلاح الدين', 'دهوك', 'أربيل', 'السليمانية', 'ديالى', 'واسط', 'ميسان', 'ذي قار', 'المثنى', 'بابل', 'كربلاء', 'النجف', 'الانبار', 'الديوانية', 'كركوك', 'حلبجة'];
@endphp

@extends('admin.layout')

@section('title', 'إدارة الطلبات')

@push('styles')
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem; color: white; border-radius: 0 !important; }
    .status-badge { border-radius: 8px; padding: 0.4rem 0.8rem; font-weight: 700; font-size: 0.8rem; color: #fff; }
    .bg-pending { background: #ffc107; color: #000; }
    .bg-processing { background: #0dcaf0; color: #000; }
    .bg-shipped { background: #0d6efd; }
    .bg-delivered { background: #198754; }
    .bg-returned { background: #dc3545; }
    .bg-cancelled { background: #6c757d; }
    .table-container { border-radius: 15px; border: 1px solid #f1f5f9; overflow: hidden; background: #fff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    .search-input { border-radius: 12px; border: 1px solid #e2e8f0; padding: 0.8rem 1.2rem; background: #fafbff; }
    .btn-delete-disabled {
        background: #f1f3f5 !important;
        border-color: #e5e7eb !important;
        color: #9ca3af !important;
        cursor: not-allowed;
        pointer-events: none;
        opacity: .75;
    }
</style>
@endpush

@section('content')
<div class="form-card">
    <div class="form-card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2 class="mb-2 fw-bold text-white"><i class="bi bi-cart-check-fill me-2"></i> إدارة طلبات العملاء</h2>
            <p class="mb-0 opacity-75 fs-6 text-white small">تحكم في حالات الطلبات، التوصيل، والتقارير اليومية.</p>
        </div>
        <div class="d-flex gap-2">
            <div class="col-toggle-place"></div>
            @can('view-orders')
                <a href="{{ route('admin.orders.export', request()->all()) }}" class="btn btn-outline-info p-2 d-inline-flex align-items-center justify-content-center" style="width:40px; height:40px; border-radius:10px" title="تصدير إكسل"><i class="bi bi-file-earmark-excel"></i></a>
            @endcan
            <a href="{{ route('admin.orders.trash') }}" class="btn btn-outline-danger p-2 d-inline-flex align-items-center justify-content-center" style="width:40px; height:40px; border-radius:10px" title="المهملات"><i class="bi bi-trash"></i></a>
            @can('create-orders')
                <a href="{{ route('admin.orders.create') }}" class="btn btn-light px-4 fw-bold text-brand d-inline-flex align-items-center"><i class="bi bi-plus-circle me-1"></i> طلب يدوي</a>
            @endcan
        </div>
    </div>
    
    <div class="p-4 p-lg-5">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="row g-3 mb-4 p-4 bg-light rounded-4 border align-items-end">
            <div class="col-md-6">
                <label class="small fw-bold text-muted mb-2">بحث سريع</label>
                <input type="text" name="search" class="form-control search-input" placeholder="اسم، هاتف، رقم طلب..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="small fw-bold text-muted mb-2">الحالة</label>
                <select name="status" class="form-select search-input" onchange="this.form.submit()">
                    <option value="">كل الحالات</option>
                    @foreach($statusLabels as $k => $v) <option value="{{ $k }}" @selected(request('status')==$k)>{{ $v }}</option> @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn text-white px-4 py-3 fw-bold flex-grow-1" style="background:var(--primary-dark); border-radius:12px">بحث</button>
                <button type="button" class="btn btn-outline-dark d-flex align-items-center justify-content-center" style="width:58px; height:58px; border-radius:12px" data-bs-toggle="modal" data-bs-target="#filtersModal" title="فلاتر إضافية">
                    <i class="bi bi-funnel fs-4"></i>
                </button>
                @if(request()->anyFilled(['search','status','date_from','date_to','governorate']))
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" style="width:58px; height:58px; border-radius:12px" title="تصفير">
                        <i class="bi bi-arrow-counterclockwise fs-4"></i>
                    </a>
                @endif
            </div>
        </form>

        <div class="table-container shadow-sm border overflow-hidden">
            <table class="table mb-0 align-middle text-center" id="orders_table">
                <thead class="bg-light border-bottom">
                    <tr class="text-muted small fw-bold">
                        <th class="py-3" width="50" data-column-id="seq">{!! \App\Support\Sort::link('id', '#') !!}</th>
                        <th class="py-3" width="70" data-hide="true" data-column-id="id">ID</th>
                        <th class="py-3 text-start" data-column-id="customer">{!! \App\Support\Sort::link('user_name', 'المستخدم') !!}</th>
                        <th class="py-3" data-column-id="phone">الهاتف</th>
                        <th class="py-3" data-column-id="total">{!! \App\Support\Sort::link('total_amount', 'المبلغ') !!}</th>
                        <th class="py-3" data-column-id="status">{!! \App\Support\Sort::link('status', 'الحالة') !!}</th>
                        <th class="py-3" data-column-id="date">{!! \App\Support\Sort::link('created_at', 'التاريخ') !!}</th>
                        <th class="py-3" data-column-id="location">{!! \App\Support\Sort::link('city', 'الموقع') !!}</th>
                        <th class="py-3" width="170" data-column-id="actions">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td class="small text-muted">{{ $loop->iteration + ($orders->perPage() * ($orders->currentPage() - 1)) }}</td>
                            <td class="fw-bold">#{{ $order->id }}</td>
                            <td class="text-start">
                                <div class="fw-bold text-dark">{{ $order->customer->name ?? 'مستخدم محذوف' }}</div>
                                @if($order->is_gift) <span class="badge bg-danger p-1 small" style="font-size:0.6rem">هدية <i class="bi bi-gift-fill"></i></span> @endif
                            </td>
                            <td><span class="text-muted small">{{ $order->customer->phone_number ?? '-' }}</span></td>
                            <td><div class="fw-bold text-brand">{{ number_format($order->total_amount, 0) }} د.ع</div></td>
                            <td>
                                <span class="status-badge bg-{{ $order->status }} shadow-sm">
                                    {{ $statusLabels[$order->status] ?? $order->status }}
                                </span>
                            </td>
                            <td><div class="text-muted small">{{ $order->created_at->format('Y-m-d') }}</div></td>
                            <td><div class="small fw-medium">{{ $order->city }} / {{ $order->governorate }}</div></td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary rounded-3 px-2 me-1" title="عرض"><i class="bi bi-eye"></i></a>
                                    @can('edit-orders')
                                        <button type="button"
                                                class="btn btn-sm btn-outline-warning rounded-3 px-2 me-1"
                                                data-bs-toggle="modal"
                                                data-bs-target="#statusModal"
                                                data-order-id="{{ $order->id }}"
                                                data-current-status="{{ $order->status }}"
                                                title="تغيير الحالة">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                    @endcan
                                    @can('edit-orders') <a href="{{ route('admin.orders.edit', $order->id) }}" class="btn btn-sm btn-outline-info rounded-3 px-2 me-1 text-dark" title="تعديل"><i class="bi bi-pencil"></i></a> @endcan
                                    @can('delete-orders')
                                        @if(in_array($order->status, ['cancelled', 'returned']))
                                            <form action="{{ route('admin.orders.destroy', $order->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('هل أنت متأكد من نقل الطلب إلى سلة المحذوفات؟');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-3 px-2" title="حذف">
                                                    <i class="bi bi-trash me-1"></i>
                                                    حذف
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" class="btn btn-sm rounded-3 px-2 btn-delete-disabled" title="حذف" disabled aria-disabled="true" tabindex="-1">
                                                <i class="bi bi-trash me-1"></i>
                                                حذف
                                            </button>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="py-5 text-muted">لم يتم العثور على أي طلبات.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <form method="GET" action="{{ route('admin.orders.index') }}" class="d-flex align-items-center bg-light p-2 rounded-3 border">
                @foreach(request()->except(['per_page','page']) as $k=>$v) <input type="hidden" name="{{ $k }}" value="{{ $v }}"> @endforeach
                <span class="small text-muted me-2 ms-2">إظهار:</span>
                <select name="per_page" class="form-select form-select-sm border-0 bg-transparent fw-bold" onchange="this.form.submit()" style="width:70px">
                    @foreach([10,25,50,100] as $s) <option value="{{$s}}" @selected(request('per_page',10)==$s)>{{$s}}</option> @endforeach
                </select>
            </form>
            <div>{{ $orders->withQueryString()->links() }}</div>
        </div>
    </div>
</div>

@can('edit-orders')
<div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 14px;">
            <div class="modal-header">
                <h5 class="modal-title">تغيير حالة الطلب</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="statusForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="statusOrderId">
                    <label for="statusSelect" class="form-label fw-bold">اختر الحالة</label>
                    <select name="status" id="statusSelect" class="form-select" required>
                        @foreach($statusLabels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

@can('edit-orders')
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const statusModal = document.getElementById('statusModal');
        if (!statusModal) return;

        const statusForm = document.getElementById('statusForm');
        const statusSelect = document.getElementById('statusSelect');
        const saveStatusBtn = statusForm.querySelector('button[type="submit"]');
        const bootstrapModal = window.bootstrap ? bootstrap.Modal.getOrCreateInstance(statusModal) : null;

        statusModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const orderId = button.getAttribute('data-order-id');
            const currentStatus = button.getAttribute('data-current-status');

            statusForm.action = "{{ url('admin/orders') }}/" + orderId + "/update-status";
            statusSelect.value = currentStatus;
        });

        statusForm.addEventListener('submit', async function (event) {
            event.preventDefault();

            const originalText = saveStatusBtn.textContent;
            saveStatusBtn.disabled = true;
            saveStatusBtn.textContent = 'جاري الحفظ...';

            try {
                const response = await fetch(statusForm.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: new FormData(statusForm)
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'تعذر تحديث الحالة.');
                }

                if (bootstrapModal) {
                    bootstrapModal.hide();
                }

                window.location.reload();
            } catch (error) {
                alert(error.message || 'حدث خطأ أثناء تحديث الحالة.');
            } finally {
                saveStatusBtn.disabled = false;
                saveStatusBtn.textContent = originalText;
            }
        });
    });
</script>
@endpush
@endcan

{{-- Filters Modal --}}
<div class="modal fade" id="filtersModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius:20px">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold"><i class="bi bi-funnel-fill me-2 text-brand"></i> فلاتر البحث المتقدمة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form method="GET" action="{{ route('admin.orders.index') }}" class="row g-3">
                    @foreach(request()->only(['search','status','per_page']) as $k=>$v) <input type="hidden" name="{{ $k }}" value="{{ $v }}"> @endforeach
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">من تاريخ</label>
                        <input type="date" name="date_from" class="form-control" style="border-radius:10px" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">إلى تاريخ</label>
                        <input type="date" name="date_to" class="form-control" style="border-radius:10px" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">المحافظة</label>
                        <select name="governorate" class="form-select" style="border-radius:10px">
                            <option value="">كل المحافظات</option>
                            @foreach($governorates as $gov) <option value="{{$gov}}" @selected(request('governorate')==$gov)>{{$gov}}</option> @endforeach
                        </select>
                    </div>
                    <div class="col-12 mt-4">
                        <button type="submit" class="btn text-white w-100 py-3 fw-bold" style="background:var(--primary-dark); border-radius:12px">تطبيق الفلتر</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
