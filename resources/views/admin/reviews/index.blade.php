@extends('admin.layout')

@section('title', 'إدارة تعليقات المنتجات')

@push('styles')
<style>
    .reviews-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .reviews-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.2rem 2.6rem; color: #fff; }
    .stats-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .85rem; }
    .stat-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: .85rem; }
    .review-item { border: 1px solid #e2e8f0; border-radius: 12px; background: #fff; padding: .9rem; }
    .review-avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 1px solid #e2e8f0; }
    .flags-wrap { display: flex; gap: .35rem; flex-wrap: wrap; }
    .filter-card { border: 1px solid #e2e8f0; border-radius: 12px; background: #f8fafc; }
    @media (max-width: 992px) {
        .stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .reviews-header { padding: 1.4rem 1rem; }
    }
</style>
@endpush

@section('content')
<div class="reviews-card">
    <div class="reviews-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2 class="mb-2 fw-bold text-white"><i class="bi bi-chat-left-text me-2"></i> إدارة تعليقات المنتجات</h2>
            <p class="mb-0 opacity-75 small">فلترة ومراجعة التعليقات قبل النشر مع إجراءات الموافقة أو الرفض أو الحذف.</p>
        </div>
    </div>

    <div class="p-4 p-lg-5">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="stats-grid mb-4">
            <div class="stat-box">
                <div class="small text-muted">كل التعليقات</div>
                <div class="h5 mb-0 fw-bold">{{ number_format($counts['all'] ?? 0) }}</div>
            </div>
            <div class="stat-box">
                <div class="small text-muted">منشور</div>
                <div class="h5 mb-0 fw-bold text-success">{{ number_format($counts['approved'] ?? 0) }}</div>
            </div>
            <div class="stat-box">
                <div class="small text-muted">معلّق للمراجعة</div>
                <div class="h5 mb-0 fw-bold text-warning">{{ number_format($counts['pending'] ?? 0) }}</div>
            </div>
            <div class="stat-box">
                <div class="small text-muted">مرفوض</div>
                <div class="h5 mb-0 fw-bold text-danger">{{ number_format($counts['rejected'] ?? 0) }}</div>
            </div>
        </div>

        <div class="filter-card p-3 mb-4">
            <form method="GET" action="{{ route('admin.reviews.index') }}" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label mb-1">بحث</label>
                    <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="اسم المستخدم أو نص التعليق أو اسم المنتج">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">الحالة</label>
                    <select name="status" class="form-select">
                        <option value="">الكل</option>
                        <option value="approved" @selected(request('status') === 'approved')>منشور</option>
                        <option value="pending" @selected(request('status') === 'pending')>معلّق</option>
                        <option value="rejected" @selected(request('status') === 'rejected')>مرفوض</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i> فلترة</button>
                    <a href="{{ route('admin.reviews.index') }}" class="btn btn-outline-secondary">إعادة ضبط</a>
                </div>
            </form>
        </div>

        <div class="d-flex flex-column gap-3">
            @forelse($reviews as $review)
                <div class="review-item">
                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                        <div class="d-flex align-items-start gap-2">
                            <img src="{{ $review->user?->avatar_url ?? asset('storage/avatars/default.png') }}"
                                 class="review-avatar"
                                 alt="avatar"
                                 onerror="this.onerror=null;this.src='{{ asset('storage/avatars/default.png') }}';">
                            <div>
                                <div class="fw-bold">{{ $review->user?->name ?? 'مستخدم محذوف' }}</div>
                                <div class="small text-muted">
                                    المنتج: <a href="{{ route('admin.products.show', $review->product_id) }}">{{ $review->product?->name_ar ?: ($review->product?->name_en ?: ('#' . $review->product_id)) }}</a>
                                </div>
                                <div class="small text-muted">{{ optional($review->created_at)->format('Y-m-d H:i') }}</div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-warning text-dark">{{ (int) $review->rating }}/5</span>

                            @if($review->status === 'approved')
                                <span class="badge bg-success">منشور</span>
                            @elseif($review->status === 'pending')
                                <span class="badge bg-secondary">معلّق</span>
                            @else
                                <span class="badge bg-danger">مرفوض</span>
                            @endif
                        </div>
                    </div>

                    <div class="mt-2" style="white-space:pre-line;">{{ $review->comment ?: 'لا يوجد نص تعليق.' }}</div>

                    @if(!empty($review->moderation_flags) && is_array($review->moderation_flags))
                        <div class="flags-wrap mt-2">
                            @foreach($review->moderation_flags as $flag)
                                <span class="badge text-bg-light border">{{ $flag }}</span>
                            @endforeach
                            @if(!is_null($review->moderation_score))
                                <span class="badge text-bg-warning">Score: {{ (int) $review->moderation_score }}</span>
                            @endif
                        </div>
                    @endif

                    @if(!empty($review->admin_reply))
                        <div class="mt-2 p-2 rounded border" style="background:#eef6ff; border-color:#bfdbfe !important;">
                            <div class="small fw-bold text-primary mb-1">رد الأدمن</div>
                            <div style="white-space: pre-line;">{{ $review->admin_reply }}</div>
                        </div>
                    @endif

                    <div class="mt-3 d-flex flex-wrap gap-2">
                        <form method="POST" action="{{ route('admin.reviews.updateStatus', $review->id) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="approved">
                            <button type="submit" class="btn btn-sm btn-success">موافقة ونشر</button>
                        </form>

                        <form method="POST" action="{{ route('admin.reviews.updateStatus', $review->id) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="pending">
                            <button type="submit" class="btn btn-sm btn-outline-secondary">تعليق للمراجعة</button>
                        </form>

                        <form method="POST" action="{{ route('admin.reviews.updateStatus', $review->id) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="rejected">
                            <button type="submit" class="btn btn-sm btn-outline-warning">رفض</button>
                        </form>

                        <form method="POST" action="{{ route('admin.products.reviews.destroy', ['product' => $review->product_id, 'review' => $review->id]) }}" onsubmit="return confirm('هل أنت متأكد من حذف التعليق؟');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">حذف</button>
                        </form>

                        @if(!empty($review->admin_reply))
                            <form method="POST" action="{{ route('admin.products.reviews.reply.destroy', ['product' => $review->product_id, 'review' => $review->id]) }}" onsubmit="return confirm('هل تريد حذف رد الأدمن فقط؟');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">حذف رد الأدمن فقط</button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="alert alert-light border mb-0">لا توجد تعليقات مطابقة للفلاتر الحالية.</div>
            @endforelse
        </div>

        <div class="mt-3">
            {{ $reviews->links() }}
        </div>
    </div>
</div>
@endsection
