@extends('admin.layout')

@section('title', 'سلة المحذوفات - الطلبات')

@push('styles')
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem; color: white; border-radius: 0 !important; }
    .table-container { border-radius: 15px; border: 1px solid #f1f5f9; overflow: hidden; background: #fff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    .search-input { border-radius: 12px; border: 1px solid #e2e8f0; padding: 0.8rem 1.2rem; background: #fafbff; }
    .select-col { width: 44px; }
</style>
@endpush

@section('content')
<div class="form-card">
    <div class="form-card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2 class="mb-2 fw-bold text-white"><i class="bi bi-trash-fill me-2"></i> سلة محذوفات الطلبات</h2>
            <p class="mb-0 opacity-75 fs-6 text-white small">إدارة الطلبات المحذوفة، الاستعادة، والحذف النهائي.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.orders.index') }}" class="btn btn-light px-4 fw-bold text-brand d-inline-flex align-items-center">
                <i class="bi bi-arrow-right-circle me-1"></i> العودة للطلبات
            </a>
        </div>
    </div>

    <div class="p-4 p-lg-5">
        <form method="GET" action="{{ route('admin.orders.trash') }}" class="row g-3 mb-4 p-4 bg-light rounded-4 border align-items-end">
            <div class="col-md-8">
                <label class="small fw-bold text-muted mb-2">بحث في المحذوفات</label>
                <input type="text" name="search" class="form-control search-input" placeholder="اسم، هاتف، رقم طلب..." value="{{ request('search') }}">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn text-white px-4 py-3 fw-bold flex-grow-1" style="background:var(--primary-dark); border-radius:12px">بحث</button>
                @if(request()->filled('search'))
                    <a href="{{ route('admin.orders.trash') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" style="width:58px; height:58px; border-radius:12px" title="تصفير">
                        <i class="bi bi-arrow-counterclockwise fs-4"></i>
                    </a>
                @endif
            </div>
        </form>

        <div class="d-flex flex-wrap gap-2 mb-3">
            <form action="{{ route('admin.orders.forceDeleteAll') }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف جميع الطلبات نهائياً؟ لا يمكن التراجع عن هذا الإجراء.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-trash3-fill me-1"></i> حذف الكل
                </button>
            </form>
            <button type="submit" form="bulkDeleteForm" id="deleteSelectedBtn" class="btn btn-outline-danger" disabled>
                <i class="bi bi-trash me-1"></i> حذف المحدد
            </button>
        </div>

        <form id="bulkDeleteForm" action="{{ route('admin.orders.forceDeleteSelected') }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف الطلبات المحددة نهائياً؟');">
            @csrf
            @method('DELETE')

            <div class="table-container shadow-sm border overflow-hidden">
                <table class="table mb-0 align-middle text-center">
                    <thead class="bg-light border-bottom">
                        <tr class="text-muted small fw-bold">
                            <th class="py-3 select-col">
                                <input type="checkbox" id="selectAllOrders" class="form-check-input">
                            </th>
                            <th class="py-3" width="90">ID</th>
                            <th class="py-3 text-start">العميل</th>
                            <th class="py-3">الهاتف</th>
                            <th class="py-3">المبلغ</th>
                            <th class="py-3">تاريخ الحذف</th>
                            <th class="py-3" width="150">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trashedOrders as $order)
                            <tr>
                                <td>
                                    <input type="checkbox" name="selected_ids[]" value="{{ $order->id }}" class="form-check-input row-checkbox">
                                </td>
                                <td class="fw-bold">#{{ $order->id }}</td>
                                <td class="text-start fw-bold text-dark">{{ $order->customer->name ?? '-' }}</td>
                                <td><span class="text-muted small">{{ $order->customer->phone_number ?? '-' }}</span></td>
                                <td><div class="fw-bold text-brand">{{ number_format($order->total_amount, 0) }} د.ع</div></td>
                                <td><div class="text-muted small">{{ $order->deleted_at->format('Y-m-d H:i') }}</div></td>
                                <td>
                                    <div class="btn-group">
                                        <form action="{{ route('admin.orders.restore', $order->id) }}" method="POST" class="d-inline-block">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success rounded-3 px-2 me-1" title="استعادة">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.orders.forceDelete', $order->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('هل أنت متأكد من الحذف النهائي؟ لا يمكن التراجع عن هذا الإجراء.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-3 px-2" title="حذف نهائي">
                                                <i class="bi bi-trash3-fill"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-5 text-muted">سلة المحذوفات فارغة.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        <div class="mt-4 d-flex justify-content-end">
            {{ $trashedOrders->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAll = document.getElementById('selectAllOrders');
        const rowCheckboxes = Array.from(document.querySelectorAll('.row-checkbox'));
        const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');

        function syncSelectionState() {
            const checkedCount = rowCheckboxes.filter(cb => cb.checked).length;
            const totalCount = rowCheckboxes.length;

            deleteSelectedBtn.disabled = checkedCount === 0;

            if (totalCount === 0) {
                selectAll.checked = false;
                selectAll.indeterminate = false;
                return;
            }

            selectAll.checked = checkedCount === totalCount;
            selectAll.indeterminate = checkedCount > 0 && checkedCount < totalCount;
        }

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                rowCheckboxes.forEach(cb => {
                    cb.checked = selectAll.checked;
                });
                syncSelectionState();
            });
        }

        rowCheckboxes.forEach(cb => {
            cb.addEventListener('change', syncSelectionState);
        });

        syncSelectionState();
    });
</script>
@endpush
