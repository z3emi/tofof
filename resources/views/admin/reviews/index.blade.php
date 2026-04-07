@extends('admin.layout')

@section('title', 'إدارة تعليقات المنتجات')

@push('styles')
<style>
    .reviews-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .reviews-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.2rem 2.6rem; color: #fff; }
    .stats-grid { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: .85rem; }
    .stat-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: .85rem; }
    .table-container { border-radius: 15px; border: 1px solid #f1f5f9; overflow: hidden; background: #fff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    .search-input { border-radius: 12px; border: 1px solid #e2e8f0; padding: 0.8rem 1.2rem; background: #fafbff; }
    .review-item {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        padding: 1rem;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
    }
    .review-item.is-featured {
        background: #eff6ff;
        border-color: #bfdbfe;
    }
    .review-avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 1px solid #e2e8f0; }
    .review-meta-line { display: flex; align-items: center; flex-wrap: wrap; gap: .5rem; }
    .review-comment-box {
        margin-top: .55rem;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #ffffff;
        padding: .5rem .65rem;
        line-height: 1.6;
        font-size: .9rem;
        color: #0f172a;
        display: block !important;
        height: auto !important;
        min-height: 0 !important;
        max-height: none !important;
    }
    .review-comment-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: .5rem;
    }
    .review-comment-text {
        flex: 1 1 auto;
        min-width: 0;
    }
    .review-comment-box > .review-comment-row > .review-comment-text {
        margin: 0 !important;
        padding: 0 !important;
        min-height: 0 !important;
        line-height: 1.55;
        white-space: normal;
        word-break: break-word;
    }
    .review-comment-actions {
        flex: 0 0 auto;
        margin: 0;
        text-align: left;
        min-width: 58px;
    }
    .reply-toggle-btn {
        padding: .2rem .55rem;
        font-size: .78rem;
        border-radius: 8px;
        line-height: 1.25;
    }
    .review-reply-box {
        margin-top: .75rem;
        border-radius: 12px;
        border: 1px solid #bfdbfe;
        background: #eff6ff;
        padding: .75rem .85rem;
    }
    .admin-reply-form {
        margin-top: .55rem;
        border: 1px solid #dbeafe;
        border-radius: 10px;
        background: #f8fbff;
        padding: .55rem;
    }
    .admin-reply-form .form-control {
        border-radius: 8px;
        min-height: 68px;
        font-size: .9rem;
        resize: vertical;
    }
    .admin-reply-form .btn {
        padding: .25rem .65rem;
        font-size: .8rem;
        border-radius: 8px;
    }
    .review-actions { border-top: 1px dashed #e2e8f0; padding-top: .75rem; margin-top: .8rem; }
    .flags-wrap { display: flex; gap: .35rem; flex-wrap: wrap; }

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
        <div class="d-flex gap-2">
            <div class="col-toggle-place"></div>
            <button type="button" class="btn btn-light px-4 fw-bold text-brand d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#fakeReviewModal">
                <i class="bi bi-plus-circle me-1"></i> إضافة تعليق وهمي
            </button>
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
            <div class="stat-box">
                <div class="small text-muted">منشور بالواجهة</div>
                <div class="h5 mb-0 fw-bold text-primary">{{ number_format($counts['homepage_shown'] ?? 0) }}</div>
            </div>
        </div>

        @php
            $hasActiveFilters = request()->filled('q') || request()->filled('status') || request()->filled('homepage');
        @endphp
        <form method="GET" action="{{ route('admin.reviews.index') }}" class="row g-3 mb-4 p-4 bg-light rounded-4 border align-items-end">
            <input type="hidden" name="homepage" value="{{ request('homepage') }}">
            <div class="col-md-6">
                <label class="small fw-bold text-muted mb-2">بحث سريع</label>
                <input type="text" name="q" class="form-control search-input" placeholder="اسم المستخدم، نص التعليق، أو اسم المنتج..." value="{{ request('q') }}">
            </div>
            <div class="col-md-2">
                <label class="small fw-bold text-muted mb-2">الحالة</label>
                <select name="status" class="form-select search-input" onchange="this.form.submit()">
                    <option value="">كل الحالات</option>
                    <option value="approved" @selected(request('status') === 'approved')>منشور</option>
                    <option value="pending" @selected(request('status') === 'pending')>معلّق</option>
                    <option value="rejected" @selected(request('status') === 'rejected')>مرفوض</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn text-white px-4 py-3 fw-bold flex-grow-1" style="background:var(--primary-dark); border-radius:12px">بحث</button>
                <button type="button" class="btn btn-outline-dark d-flex align-items-center justify-content-center" style="width:58px; height:58px; border-radius:12px" data-bs-toggle="modal" data-bs-target="#reviewFilterModal" title="فلاتر إضافية">
                    <i class="bi bi-funnel fs-4"></i>
                </button>
                @if($hasActiveFilters)
                    <a href="{{ route('admin.reviews.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" style="width:58px; height:58px; border-radius:12px" title="تصفير">
                        <i class="bi bi-arrow-counterclockwise fs-4"></i>
                    </a>
                @endif
            </div>
        </form>

        <div class="modal fade" id="fakeReviewModal" tabindex="-1" aria-labelledby="fakeReviewModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.reviews.fake.store') }}" class="row g-0">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="fakeReviewModalLabel"><i class="bi bi-magic me-1"></i> إنشاء تعليق وهمي</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label mb-1">الاسم الوهمي</label>
                                    <input type="text" name="fake_name" class="form-control" value="{{ old('fake_name') }}" placeholder="مثال: عميل سعيد" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label mb-1">المنتج</label>
                                    <select name="product_id" class="form-select" required>
                                        <option value="">اختر المنتج</option>
                                        @foreach(($products ?? collect()) as $p)
                                            <option value="{{ $p->id }}" @selected((int) old('product_id') === (int) $p->id)>
                                                #{{ $p->id }} - {{ $p->name_ar ?: ($p->name_en ?: 'منتج') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label mb-1">التقييم</label>
                                    <select name="rating" class="form-select" required>
                                        @for($i=5; $i>=1; $i--)
                                            <option value="{{ $i }}" @selected((int) old('rating', 5) === $i)>{{ $i }}/5</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label mb-1">الحالة</label>
                                    <select name="status" class="form-select">
                                        <option value="approved" @selected(old('status', 'approved') === 'approved')>منشور</option>
                                        <option value="pending" @selected(old('status') === 'pending')>معلّق</option>
                                        <option value="rejected" @selected(old('status') === 'rejected')>مرفوض</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label mb-1">نص التعليق</label>
                                    <textarea name="comment" rows="3" class="form-control" placeholder="اكتب التعليق الوهمي هنا..." required>{{ old('comment') }}</textarea>
                                </div>
                                <div class="col-12">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" name="show_on_homepage" id="show_on_homepage_modal" value="1" @checked(old('show_on_homepage'))>
                                        <label class="form-check-label" for="show_on_homepage_modal">عرض بالواجهة</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i> حفظ التعليق الوهمي</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="reviewFilterModal" tabindex="-1" aria-labelledby="reviewFilterModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form method="GET" action="{{ route('admin.reviews.index') }}" class="row g-0">
                        <div class="modal-header">
                            <h5 class="modal-title" id="reviewFilterModalLabel"><i class="bi bi-funnel me-1"></i> فلترة التعليقات</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <div class="row g-3 align-items-end">
                                <input type="hidden" name="q" value="{{ request('q') }}">
                                <div class="col-md-6">
                                    <label class="form-label mb-1">الحالة</label>
                                    <select name="status" class="form-select">
                                        <option value="">الكل</option>
                                        <option value="approved" @selected(request('status') === 'approved')>منشور</option>
                                        <option value="pending" @selected(request('status') === 'pending')>معلّق</option>
                                        <option value="rejected" @selected(request('status') === 'rejected')>مرفوض</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label mb-1">الواجهة</label>
                                    <select name="homepage" class="form-select">
                                        <option value="">الكل</option>
                                        <option value="shown" @selected(request('homepage') === 'shown')>منشور بالواجهة</option>
                                        <option value="hidden" @selected(request('homepage') === 'hidden')>غير منشور بالواجهة</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            @if($hasActiveFilters)
                                <a href="{{ route('admin.reviews.index') }}" class="btn btn-outline-secondary">إعادة ضبط</a>
                            @endif
                            <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i> تطبيق الفلتر</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="d-flex flex-column gap-3">
            @forelse($reviews as $review)
                <div class="review-item {{ !empty($review->show_on_homepage) ? 'is-featured' : '' }}">
                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                        <div class="d-flex align-items-start gap-2">
                            <img src="{{ $review->user?->avatar_url ?? asset('storage/avatars/default.png') }}"
                                 class="review-avatar"
                                 alt="avatar"
                                 onerror="this.onerror=null;this.src='{{ asset('storage/avatars/default.png') }}';">
                            <div>
                                <div class="fw-bold">{{ $review->user?->name ?? 'مستخدم محذوف' }}</div>
                                <div class="small text-muted review-meta-line">
                                    المنتج: <a href="{{ route('admin.products.show', $review->product_id) }}">{{ $review->product?->name_ar ?: ($review->product?->name_en ?: ('#' . $review->product_id)) }}</a>
                                    <span>•</span>
                                    <span>{{ optional($review->created_at)->format('Y-m-d H:i') }}</span>
                                </div>
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

                            @if(!empty($review->show_on_homepage))
                                <span class="badge bg-primary">معروض بالواجهة</span>
                            @endif
                        </div>
                    </div>

                    @php
                        $commentText = trim((string) ($review->comment ?: 'لا يوجد نص تعليق.'));
                        $commentText = preg_replace('/(\r\n|\r|\n){2,}/', "\n", $commentText);
                    @endphp
                    <div class="review-comment-box">
                        <div class="review-comment-row">
                            <div class="review-comment-text">{!! nl2br(e($commentText)) !!}</div>
                            <div class="review-comment-actions">
                                <button type="button"
                                        class="btn btn-sm btn-outline-primary reply-toggle-btn"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#reply-form-{{ $review->id }}"
                                        aria-expanded="false"
                                        aria-controls="reply-form-{{ $review->id }}">
                                    <i class="bi bi-reply-fill me-1"></i> رد
                                </button>
                            </div>
                        </div>
                    </div>

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
                        <div class="review-reply-box">
                            <div class="small fw-bold text-primary mb-1"><i class="bi bi-reply-fill me-1"></i> رد الأدمن</div>
                            <div style="white-space: pre-line;">{{ $review->admin_reply }}</div>
                        </div>
                    @endif

                    <div class="collapse" id="reply-form-{{ $review->id }}">
                        <form method="POST" action="{{ route('admin.products.reviews.reply', ['product' => $review->product_id, 'review' => $review->id]) }}" class="admin-reply-form">
                            @csrf
                            <label class="form-label fw-bold small mb-2">رد الأدمن</label>
                            <textarea name="admin_reply" class="form-control" placeholder="اكتب رد الأدمن هنا...">{{ old('admin_reply', $review->admin_reply) }}</textarea>
                            <div class="d-flex justify-content-end mt-2">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="bi bi-send me-1"></i>
                                    {{ !empty($review->admin_reply) ? 'تحديث رد الأدمن' : 'حفظ رد الأدمن' }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="review-actions d-flex flex-wrap gap-2">
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

                        @if($review->status === 'approved')
                            <form method="POST" action="{{ route('admin.reviews.toggleFeatured', $review->id) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="show_on_homepage" value="{{ !empty($review->show_on_homepage) ? 0 : 1 }}">
                                @if(!empty($review->show_on_homepage))
                                    <button type="submit" class="btn btn-sm btn-outline-primary">إزالة من الواجهة</button>
                                @else
                                    <button type="submit" class="btn btn-sm btn-primary">إضافة للواجهة</button>
                                @endif
                            </form>
                        @endif

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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const shouldOpenFakeModal = {{ old('fake_name') || old('product_id') || old('comment') ? 'true' : 'false' }};
        if (!shouldOpenFakeModal) {
            return;
        }

        const modalElement = document.getElementById('fakeReviewModal');
        if (!modalElement || typeof bootstrap === 'undefined' || !bootstrap.Modal) {
            return;
        }

        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalElement);
        modalInstance.show();
    });
</script>
@endpush

@endsection
