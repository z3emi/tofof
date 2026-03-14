@extends('admin.layout')

@section('title', 'سلة المحذوفات - الطلبات')

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">سلة المحذوفات للطلبات</h4>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-right"></i> العودة للطلبات
        </a>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.orders.trash') }}" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="ابحث في سلة المحذوفات..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary" style="background-color: #cd8985; border-color: #cd8985;">
                    <i class="bi bi-search"></i> بحث
                </button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>العميل</th>
                        <th>المبلغ</th>
                        <th>تاريخ الحذف</th>
                        <th>العمليات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($trashedOrders as $order)
                        <tr>
                            <td>#{{ $order->id }}</td>
                            <td>{{ $order->customer->name ?? '-' }}</td>
                            <td>{{ number_format($order->total_amount, 0) }} د.ع</td>
                            <td>{{ $order->deleted_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <form action="{{ route('admin.orders.restore', $order->id) }}" method="POST" class="d-inline-block">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success m-1 px-2" title="استعادة">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.orders.forceDelete', $order->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('هل أنت متأكد من الحذف النهائي؟ لا يمكن التراجع عن هذا الإجراء.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger m-1 px-2" title="حذف نهائي">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-4">سلة المحذوفات فارغة.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $trashedOrders->links() }}
        </div>
    </div>
</div>
@endsection
