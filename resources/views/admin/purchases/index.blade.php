@extends('admin.layout')

@section('title', 'إدارة المشتريات')

@section('content')

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2" style="background-color: #f9f5f1;">
        <h4 class="mb-0" style="color: #cd8985;">جميع فواتير الشراء</h4>
        <a href="{{ route('admin.purchases.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i> إضافة فاتورة شراء
        </a>
    </div>

    <div class="card-body p-4 p-md-5">
        <form method="GET" action="{{ route('admin.purchases.index') }}" class="row g-3 mb-4 p-4 bg-light rounded-4 border align-items-end">
            <div class="col-md-8">
                <label class="small fw-bold text-muted mb-2">بحث ذكي (رقم الفاتورة أو المورد)</label>
                <input type="text" name="search" class="form-control" style="border-radius:12px; height:58px" placeholder="بحث برقم الفاتورة..." value="{{ request('search') }}">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn text-white px-4 py-3 fw-bold flex-grow-1" style="background:var(--primary-dark); border-radius:12px; height:58px">بحث</button>
                <button type="button" class="btn btn-outline-dark d-flex align-items-center justify-content-center" style="width:58px; height:58px; border-radius:12px" data-bs-toggle="modal" data-bs-target="#filtersModal" title="فلاتر إضافية">
                    <i class="bi bi-funnel fs-4"></i>
                </button>
                @if(request()->anyFilled(['search','supplier_id','status','date_from','date_to']))
                    <a href="{{ route('admin.purchases.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" style="width:58px; height:58px; border-radius:12px" title="تصفير">
                        <i class="bi bi-arrow-counterclockwise fs-4"></i>
                    </a>
                @endif
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>رقم الفاتورة</th>
                        <th>المورّد</th>
                        <th>تاريخ الفاتورة</th>
                        <th>المبلغ الإجمالي</th>
                        <th>الحالة</th>
                        <th>العمليات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($purchases as $purchase)
                        <tr>
                            <td>{{ $purchase->id }}</td>
                            <td>{{ $purchase->invoice_number ?? 'N/A' }}</td>
                            <td>{{ $purchase->supplier?->name ?? 'غير محدد' }}</td>
                            <td>{{ $purchase->invoice_date->format('Y-m-d') }}</td>
                            <td>{{ number_format($purchase->total_amount, 0) }} د.ع</td>
                            <td>
                                @if($purchase->status == 'received')
                                    <span class="badge bg-success">تم الاستلام</span>
                                @elseif($purchase->status == 'pending')
                                    <span class="badge bg-warning text-dark">قيد الانتظار</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($purchase->status) }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.purchases.show', $purchase->id) }}" class="btn btn-sm btn-outline-info m-1 px-2" title="عرض التفاصيل">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.purchases.edit', $purchase->id) }}" class="btn btn-sm btn-outline-primary m-1 px-2" title="تعديل">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.purchases.destroy', $purchase->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('هل أنت متأكد من حذف هذه الفاتورة؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger m-1 px-2" title="حذف">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-4">لا توجد فواتير شراء لعرضها.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $purchases->links() }}
        </div>
    </div>
</div>

{{-- Filters Modal --}}
<div class="modal fade" id="filtersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:20px">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold mb-0"><i class="bi bi-funnel me-2"></i>تصفية المشتريات المتقدمة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form method="GET" action="{{ route('admin.purchases.index') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted mb-2">المورد</label>
                            <select name="supplier_id" class="form-select" style="border-radius:12px; height:50px">
                                <option value="">كل الموردين</option>
                                @foreach(\App\Models\Supplier::orderBy('name')->get() as $s)
                                    <option value="{{ $s->id }}" @selected(request('supplier_id')==$s->id)>{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted mb-2">الحالة</label>
                            <select name="status" class="form-select" style="border-radius:12px; height:50px">
                                <option value="">كل الحالات</option>
                                <option value="received" @selected(request('status')=='received')>تم الاستلام</option>
                                <option value="pending" @selected(request('status')=='pending')>قيد الانتظار</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted mb-2">تاريخ الفاتورة (من)</label>
                            <input type="date" name="date_from" class="form-control" style="border-radius:12px; height:50px" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted mb-2">تاريخ الفاتورة (إلى)</label>
                            <input type="date" name="date_to" class="form-control" style="border-radius:12px; height:50px" value="{{ request('date_to') }}">
                        </div>
                    </div>
                    <div class="mt-5 d-flex gap-2">
                        <button type="submit" class="btn text-white w-100 py-3 fw-bold" style="background:var(--primary-dark); border-radius:12px">تطبيق الفلاتر</button>
                        <a href="{{ route('admin.purchases.index') }}" class="btn btn-outline-secondary px-4 py-3" style="border-radius:12px">تصفير الكل</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection