@extends('admin.layout')

@section('title', 'مركز الإشعارات')

@push('styles')
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem; color: white; border-radius: 0 !important; }
    .notif-item { transition: all 0.2s; border-right: 4px solid transparent; }
    .notif-unread { border-right-color: var(--accent-gold); background: #fffdf8; }
    .notif-read { border-right-color: #f1f5f9; background: #fff; opacity: 0.8; }
    .notif-icon { width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; }
</style>
@endpush

@section('content')
<div class="form-card">
    <div class="form-card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2 class="mb-2 fw-bold text-white"><i class="bi bi-bell-fill me-2"></i> مركز التنبيهات والإشعارات</h2>
            <p class="mb-0 opacity-75 fs-6 text-white small">اطلع على آخر تحديثات النظام، الطلبات الجديدة، وتنبيهات الأمان.</p>
        </div>
        <div class="d-flex gap-2">
            @if($unreadCount > 0)
                <form action="{{ route('admin.notifications.markAllRead') }}" method="POST">
                    @csrf <button type="submit" class="btn btn-light px-4 fw-bold text-brand"><i class="bi bi-check2-all me-1"></i> تعليم الكل كمقروء</button>
                </form>
            @endif
            <span class="badge bg-white text-dark border px-3 py-2 fw-bold">غير مقروء: {{ $unreadCount }}</span>
        </div>
    </div>
    
    <div class="p-4 p-lg-5">
        <div class="mb-4 d-flex gap-2">
            @foreach(['all' => 'الكل', 'unread' => 'غير مقروءة', 'read' => 'مقروءة'] as $opt => $lbl)
                <a href="{{ route('admin.notifications.index', ['status' => $opt]) }}" 
                   class="btn @if($status == $opt) btn-dark @else btn-outline-secondary @endif px-4 rounded-pill fw-bold">
                    {{ $lbl }}
                </a>
            @endforeach
        </div>

        <div class="border rounded-4 shadow-sm overflow-hidden border-bottom-0">
            @forelse($notifications as $notification)
                @php 
                    $data = $notification->data ?? [];
                    $isUnread = is_null($notification->read_at);
                @endphp
                <div class="notif-item @if($isUnread) notif-unread @else notif-read @endif p-4 border-bottom d-flex align-items-center gap-3">
                    <div class="notif-icon @if($isUnread) bg-warning bg-opacity-10 text-warning @else bg-light text-muted @endif">
                        <i class="bi {{ $data['icon'] ?? 'bi-bell' }} fs-5"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold @if($isUnread) text-dark @else text-muted @endif mb-1">{{ $data['message'] ?? 'إشعار جديد' }}</div>
                        <div class="small text-muted"><i class="bi bi-clock me-1"></i> {{ $notification->created_at->diffForHumans() }}</div>
                    </div>
                    <div class="d-flex gap-2">
                        @if($data['order_id'] ?? null)
                            <a href="{{ route('admin.orders.show', $data['order_id']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">عرض الطلب</a>
                        @endif
                        @if($isUnread)
                            <form action="{{ route('admin.notifications.markAsRead') }}" method="POST">
                                @csrf <input type="hidden" name="id" value="{{ $notification->id }}">
                                <button type="submit" class="btn btn-sm btn-dark rounded-pill px-3">مقروء</button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-5 text-center text-muted border-bottom">
                    <i class="bi bi-bell-slash display-5 d-block mb-3"></i>
                    لا توجد أي إشعارات جديدة حالياً.
                </div>
            @endforelse
        </div>

        @if($notifications->hasPages())
            <div class="mt-4 d-flex justify-content-center">{{ $notifications->links() }}</div>
        @endif
    </div>
</div>
@endsection
