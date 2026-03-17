@php
// Helper function to create sortable links
function sortable_link($column, $title, $currentSortBy, $currentSortDir) {
    $sortBy = request('sort_by', $currentSortBy);
    $sortDir = request('sort_dir', $currentSortDir);
    $newSortDir = ($sortBy == $column && $sortDir == 'asc') ? 'desc' : 'asc';
    $icon = '';
    if ($sortBy == $column) {
        $icon = $sortDir == 'asc' ? '<i class="bi bi-sort-up ms-1"></i>' : '<i class="bi bi-sort-down ms-1"></i>';
    }
    $queryParams = request()->except(['sort_by', 'sort_dir']);
    $queryParams['sort_by'] = $column;
    $queryParams['sort_dir'] = $newSortDir;
    return '<a href="' . route('admin.orders.index', $queryParams) . '" class="text-decoration-none text-dark">' . $title . $icon . '</a>';
}

// Status labels in Arabic
$statusLabels = [
    'pending' => 'قيد الانتظار',
    'processing' => 'قيد المعالجة',
    'shipped' => 'تم الشحن',
    'delivered' => 'تم التوصيل',
    'cancelled' => 'ملغي',
    'returned' => 'مرتجع',
];

// Array of Iraqi governorates
$governorates = [
    'بغداد', 'نينوى', 'البصرة', 'صلاح الدين', 'دهوك', 'أربيل', 'السليمانية', 'ديالى', 'واسط', 'ميسان', 'ذي قار', 'المثنى', 'بابل', 'كربلاء', 'النجف', 'الانبار', 'الديوانية', 'كركوك', 'حلبجة'
];
@endphp

@extends('admin.layout')

@section('title', 'إدارة الطلبات')

@push('styles')
<style>
.pagination {
  justify-content: center !important;
  gap: 0.4rem;
  margin-top: 1rem;
}
.pagination .page-item .page-link {
  background-color: #f9f5f1 !important;
  color: #be6661 !important;
  border-color: #be6661 !important;
  font-weight: 600;
  border-radius: 0.375rem;
  transition: background-color 0.3s, color 0.3s;
  box-shadow: none;
}
.pagination .page-item .page-link:hover {
  background-color: #dcaca9 !important;
  color: #fff !important;
  border-color: #dcaca9 !important;
}
.pagination .page-item.active .page-link {
  background-color: #be6661 !important;
  border-color: #be6661 !important;
  color: #fff !important;
  font-weight: 700;
  pointer-events: none;
}
</style>
@endpush

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h4 class="mb-0">جميع الطلبات</h4>
        <div>
            @can('view-orders')
            <a href="{{ route('admin.orders.export') }}" class="btn btn-sm btn-success" title="تصدير Excel" aria-label="تصدير Excel">
                <i class="bi bi-file-earmark-excel"></i>
            </a>
            @endcan
            <a href="{{ route('admin.orders.trash') }}" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i> سلة المحذوفات</a>
            @can('create-orders')
                <a href="{{ route('admin.orders.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle me-1"></i> إضافة طلب يدوي</a>
            @endcan
        </div>
    </div>

    <div class="card-body">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="row g-2 mb-4" id="filterForm">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="ابحث بالاسم أو الهاتف أو رقم الطلب..." value="{{ request('search') }}">
            </div>
            <div class="col-md-1">
 <button type="button" class="btn btn-outline-primary w-100 d-flex justify-content-between align-items-center" data-bs-toggle="modal" data-bs-target="#filtersModal" style="background-color: #cd8985; color: #fff; border-color: #cd8985;">
    <span>فلاتر</span>
    <i class="bi bi-funnel"></i>
</button>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">كل الحالات</option>
                    @foreach($statusLabels as $key => $label)
                        <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">بحث</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle" style="min-width: 900px;">
                <thead class="table-light">
                    <tr>
                        <th>{!! sortable_link('id', '#', $sortBy ?? 'id', $sortDir ?? 'desc') !!}</th>
                        <th>{!! sortable_link('user_name', 'اسم العميل', $sortBy ?? 'id', $sortDir ?? 'desc') !!}</th>
                        <th>رقم الهاتف</th>
                        <th>{!! sortable_link('total_amount', 'المبلغ', $sortBy ?? 'id', $sortDir ?? 'desc') !!}</th>
                        <th>{!! sortable_link('status', 'الحالة', $sortBy ?? 'id', $sortDir ?? 'desc') !!}</th>
                        <th>{!! sortable_link('created_at', 'التاريخ', $sortBy ?? 'id', $sortDir ?? 'desc') !!}</th>
                        <th>المدينة</th>
                        <th>المحافظة</th>
                        <th>الملاحظات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        @php
                            // إجمالي المنتجات من العناصر (إذا كانت محمّلة)
                            $itemsSubtotal = 0.0;
                            if ($order->relationLoaded('items') ? $order->items->count() : ($order->items()->count() > 0)) {
                                $items = $order->relationLoaded('items') ? $order->items : $order->items()->get();
                                $itemsSubtotal = $items->sum(function($it){
                                    $p = (float)($it->price ?? 0);
                                    $q = (int)($it->quantity ?? 0);
                                    return $p * $q;
                                });
                            }

                            $discount   = (float)($order->discount_amount ?? 0);
                            $shipping   = (float)($order->shipping_cost ?? 0);

                            // اسماء محتملة لقيمة المبلغ المستخدم من المحفظة (لو موجودة بالطلب)
                            $walletUsed =
                                (float)($order->wallet_used_amount
                                    ?? $order->wallet_amount_used
                                    ?? $order->wallet_applied
                                    ?? 0);

                            // إذا عندي items أحسب الإجمالي الحقيقي قبل المحفظة من البنود مباشرة
                            if ($itemsSubtotal > 0) {
                                $grossTotal = max(0, ($itemsSubtotal - $discount) + $shipping);
                            } else {
                                // فallback: لو total_amount عندك "بعد" المحفظة، نرجّعه للإجمالي الحقيقي بإضافة مبلغ المحفظة
                                $grossTotal = (float)($order->total_amount ?? 0) + $walletUsed;
                                // ضمان عدم السلبية
                                if ($grossTotal < 0) $grossTotal = 0;
                            }
                        @endphp
                        <tr @class([
                            'table-warning'  => $order->status == 'pending',
                            'table-info'     => $order->status == 'processing',
                            'table-primary'  => $order->status == 'shipped',
                            'table-success'  => $order->status == 'delivered',
                            'table-secondary'=> $order->status == 'cancelled',
                            'table-danger'   => $order->status == 'returned',
                        ])>
                            <td>
                                #{{ $order->id }}
                                @if($order->is_gift)
                                    <span class="badge bg-danger ms-1">هدية</span>
                                @endif
                            </td>
                            <td>{{ $order->customer->name ?? '-' }}</td>
                            <td>{{ $order->customer->phone_number ?? '-' }}</td>
                            <td><div class="fw-bold">{{ number_format($grossTotal, 0) }} د.ع</div></td>
                            <td>
                                <span class="badge @if($order->status == 'pending') bg-warning text-dark @elseif($order->status == 'processing') bg-info text-dark @elseif($order->status == 'shipped') bg-primary @elseif($order->status == 'delivered') bg-success @elseif($order->status == 'cancelled') bg-secondary @elseif($order->status == 'returned') bg-danger @endif">
                                    {{ $statusLabels[$order->status] ?? $order->status }}
                                </span>
                            </td>
                            <td>{{ $order->created_at->format('Y-m-d') }}</td>
                            <td>{{ $order->city }}</td>
                            <td>{{ $order->governorate }}</td>
                            <td>
                                {{ $order->notes ?? '-' }}
                                @if($order->is_gift && $order->gift_recipient_name)
                                    <div class="small text-muted mt-1">المستلم: {{ $order->gift_recipient_name }}</div>
                                @endif
                                {{-- HIDDEN ACTIONS FOR CONTEXT MENU --}}
                                <div class="d-none">
                                    @can('view-orders')
                                        <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary" title="عرض الطلب"><i class="bi bi-eye"></i></a>
                                    @endcan
                                    @can('edit-orders')
                                        <a href="{{ route('admin.orders.edit', $order->id) }}" class="btn btn-sm btn-outline-info" title="تعديل الطلب"><i class="bi bi-pencil"></i></a>
                                    @endcan
                                    @can('delete-orders')
                                        <form action="{{ route('admin.orders.destroy', $order->id) }}" method="POST" class="d-inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف الطلب" onclick="return confirm('هل أنت متأكد؟')"><i class="bi bi-trash"></i></button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="p-4">لا توجد طلبات لعرضها.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
            <form method="GET" action="{{ route('admin.orders.index') }}" class="d-flex align-items-center">
                @foreach(request()->except(['per_page', 'page']) as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <label for="per_page" class="me-2">عدد الطلبات:</label>
                <select name="per_page" id="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach([5, 10, 25, 50, 100] as $size)
                        <option value="{{ $size }}" {{ request('per_page', 5) == $size ? 'selected' : '' }}>
                            {{ $size }}
                        </option>
                    @endforeach
                </select>
            </form>
            <div>{{ $orders->links() }}</div>
        </div>
    </div>
</div>

<!-- Modal for Filters -->
<div class="modal fade" id="filtersModal" tabindex="-1" aria-labelledby="filtersModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background-color: #cd8985; color: white;">
        <h5 class="modal-title" id="filtersModalLabel">تصفية الطلبات</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="row g-3" id="modalFiltersForm">
            <div class="col-md-6">
                <label for="date_from" class="form-label">من تاريخ</label>
                <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-6">
                <label for="date_to" class="form-label">إلى تاريخ</label>
                <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-12">
                <label for="min_price" class="form-label">السعر الأدنى</label>
                <input type="number" step="any" name="min_price" id="min_price" class="form-control" placeholder="مثال: 1000" value="{{ request('min_price') }}">
            </div>
            <div class="col-12">
                <label for="max_price" class="form-label">السعر الأعلى</label>
                <input type="number" step="any" name="max_price" id="max_price" class="form-control" placeholder="مثال: 50000" value="{{ request('max_price') }}">
            </div>
            <div class="col-md-6">
                <label for="governorate" class="form-label">المحافظة</label>
                <select name="governorate" id="governorate" class="form-select">
                    <option value="">كل المحافظات</option>
                    @foreach($governorates as $governorate)
                        <option value="{{ $governorate }}" {{ request('governorate') == $governorate ? 'selected' : '' }}>
                            {{ $governorate }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label for="city" class="form-label">المدينة</label>
                <input type="text" name="city" id="city" class="form-control" placeholder="مثال: الكرادة" value="{{ request('city') }}">
            </div>
            <div class="col-12 mt-4">
                <button type="submit" class="btn w-100" style="background-color: #cd8985; color: white; border-color: #cd8985;">تصفية</button>
            </div>
            <div class="col-12 mt-2">
                <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary w-100">إعادة تعيين الفلاتر</a>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
