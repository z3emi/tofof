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

    <div class="card-body">
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

@endsection