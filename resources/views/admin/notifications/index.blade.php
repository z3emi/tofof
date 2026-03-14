@extends('admin.layout')

@section('title', 'إشعاراتي')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="mb-1">الإشعارات المرسلة إلي</h4>
        <p class="text-muted mb-0">اطلع على آخر التنبيهات المرتبطة بعملك وعلّم ما تم الاطلاع عليه بدون حذف.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-primary-subtle text-primary fw-semibold px-3 py-2">
            غير مقروء: {{ $unreadCount }}
        </span>
        <form method="POST" action="{{ route('admin.notifications.markAllRead') }}">
            @csrf
            <button type="submit" class="btn btn-outline-primary btn-sm" {{ $unreadCount === 0 ? 'disabled' : '' }}>
                <i class="bi bi-check2-all me-1"></i>
                تعليم الكل كمقروء
            </button>
        </form>
    </div>
</div>

@if (session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

@if ($errors->has('notification'))
    <div class="alert alert-danger">{{ $errors->first('notification') }}</div>
@endif

<div class="card shadow-sm">
    <div class="card-header border-0 bg-white">
        <ul class="nav nav-pills gap-2" role="tablist">
            @php
                $statusOptions = [
                    'all' => 'جميع الإشعارات',
                    'unread' => 'غير مقروءة',
                    'read' => 'مقروءة',
                ];
            @endphp
            @foreach($statusOptions as $option => $label)
                <li class="nav-item" role="presentation">
                    <a
                        class="nav-link {{ $status === $option ? 'active' : '' }}"
                        href="{{ route('admin.notifications.index', array_merge(request()->except(['page', 'status']), ['status' => $option])) }}"
                    >
                        {{ $label }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="card-body p-0">
        @forelse($notifications as $notification)
            @php
                $data = $notification->data ?? [];
                $icon = $data['icon'] ?? 'bi-bell';
                $message = $data['message'] ?? 'إشعار جديد';
                $orderId = $data['order_id'] ?? null;
                $targetUrl = $orderId ? route('admin.orders.show', $orderId) : ($data['url'] ?? '#');
                $isUnread = is_null($notification->read_at);
            @endphp
            <div class="border-bottom p-4 d-flex flex-column flex-md-row gap-3 align-items-md-center">
                <div class="d-flex align-items-center gap-3 flex-grow-1">
                    <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi {{ $icon }}"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <strong>{{ $message }}</strong>
                            @if($isUnread)
                                <span class="badge bg-warning text-dark">غير مقروء</span>
                            @endif
                        </div>
                        <div class="text-muted small">
                            وصل قبل {{ $notification->created_at->diffForHumans(null, true, true, 2) }}
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 ms-md-auto">
                    @if($targetUrl && $targetUrl !== '#')
                        <a href="{{ $targetUrl }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-box-arrow-up-right me-1"></i>
                            عرض التفاصيل
                        </a>
                    @endif
                    @if($isUnread)
                        <form method="POST" action="{{ route('admin.notifications.markAsRead') }}">
                            @csrf
                            <input type="hidden" name="id" value="{{ $notification->id }}">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-check-lg me-1"></i>
                                تعليم كمقروء
                            </button>
                        </form>
                    @else
                        <span class="text-muted small"><i class="bi bi-check2-circle me-1"></i>تمت قراءته</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-5 text-center text-muted">
                <i class="bi bi-bell-slash display-5 d-block mb-3"></i>
                لا توجد إشعارات مطابقة.
            </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
    <div class="card-footer bg-white">
        {{ $notifications->links() }}
    </div>
    @endif
</div>
@endsection
