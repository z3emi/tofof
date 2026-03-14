@extends('admin.layout')

@section('title', 'طلبات المدير: ' . $manager->name)

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">جميع طلبات المدير: {{ $manager->name }}</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.managers.edit', $manager->id) }}" class="btn btn-sm btn-outline-secondary">العودة لملف المدير</a>
            <a href="{{ route('admin.managers.index') }}" class="btn btn-sm btn-secondary">قائمة المدراء</a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle">
                <thead class="table-light">
                    <tr>
                        <th>رقم الطلب</th>
                        <th>الحالة</th>
                        <th>المبلغ</th>
                        <th>تاريخ الطلب</th>
                        <th class="text-nowrap">التفاصيل</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr data-dblclick-url="{{ route('admin.orders.show', $order->id) }}"
                            data-dblclick-label="عرض تفاصيل الطلب رقم #{{ $order->id }}">
                            <td>#{{ $order->id }}</td>
                            <td>
                                <span class="badge 
                                    @if($order->status == 'pending') bg-warning text-dark 
                                    @elseif($order->status == 'processing') bg-info text-dark
                                    @elseif($order->status == 'shipped') bg-primary
                                    @elseif($order->status == 'delivered') bg-success
                                    @elseif($order->status == 'cancelled') bg-danger
                                    @elseif($order->status == 'returned') bg-dark
                                    @endif">
                                    {{ __('status.' . $order->status) }}
                                </span>
                            </td>
                            <td>{{ number_format($order->total_amount, 0) }} د.ع</td>
                            <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center p-4">لا توجد طلبات سابقة لهذا المدير.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- عرض روابط تقسيم الصفحات --}}
        <div class="mt-3 d-flex justify-content-center">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection
