@extends('admin.layout')

@section('title', 'جميع طلبات المستخدم: ' . $user->name)

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">جميع طلبات: {{ $user->name }}</h4>
        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-secondary">العودة لملف المستخدم</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle">
                <thead class="table-light">
                    <tr>
                        <th>رقم الطلب</th>
                        <th>الحالة</th>
                        <th>المبلغ</th>
                        <th>مصدر الطلب</th>
                        <th>تاريخ الطلب</th>
                        <th>عرض التفاصيل</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
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
                            <td>
                                @if($order->source == 'website')
                                    <i class="bi bi-globe" title="الموقع الإلكتروني"></i>
                                @elseif($order->source == 'whatsapp')
                                    <i class="bi bi-whatsapp" title="واتساب"></i>
                                @elseif($order->source == 'instagram')
                                    <i class="bi bi-instagram" title="انستغرام"></i>
                                @else
                                    {{ $order->source }}
                                @endif
                            </td>
                            <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary">عرض التفاصيل</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center p-4">لا توجد طلبات سابقة لهذا المستخدم.</td>
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
