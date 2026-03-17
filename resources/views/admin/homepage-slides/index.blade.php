@extends('admin.layout')

@section('title', 'سلايدرات الصفحة الرئيسية')

@php
    $sectionBadgeClasses = [
        \App\Models\HomepageSlide::SECTION_HERO => 'bg-primary-subtle text-primary',
        \App\Models\HomepageSlide::SECTION_PROMO_PRIMARY => 'bg-warning-subtle text-warning-emphasis',
        \App\Models\HomepageSlide::SECTION_PROMO_SECONDARY => 'bg-info-subtle text-info-emphasis',
    ];
@endphp

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">سلايدرات الصفحة الرئيسية</h1>
        <p class="text-muted mb-0">هنا تكدر تتحكم بالنص، صورة الخلفية، الترتيب، والحالة لكل سلايد.</p>
    </div>

    <a href="{{ route('admin.homepage-slides.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>
        إضافة سلايد
    </a>
</div>

@foreach($sections as $sectionKey => $sectionLabel)
    @php
        $sectionSlides = $slides->get($sectionKey, collect());
        $badgeClass = $sectionBadgeClasses[$sectionKey] ?? 'bg-secondary-subtle text-secondary';
    @endphp

    <div class="card">
        <div class="card-header">
            <div class="d-flex align-items-center gap-2">
                <span class="badge {{ $badgeClass }}">{{ $sectionLabel }}</span>
                <span class="text-muted small">{{ $sectionSlides->count() }} سلايد</span>
            </div>

            <a href="{{ route('admin.homepage-slides.create', ['section' => $sectionKey]) }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-plus-lg me-1"></i>
                إضافة لهذا القسم
            </a>
        </div>

        <div class="card-body p-0">
            @if($sectionSlides->isEmpty())
                <div class="p-4 text-center text-muted">
                    لا يوجد سلايدرات مضافة لهذا القسم حالياً.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 70px;">ترتيب</th>
                                <th style="width: 150px;">المعاينة</th>
                                <th>المحتوى</th>
                                <th style="width: 140px;">الحالة</th>
                                <th style="width: 250px;">التحكم</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sectionSlides as $slide)
                                <tr>
                                    <td>
                                        <div class="d-flex flex-column align-items-center gap-1">
                                            <span class="fw-bold">{{ $slide->sort_order }}</span>

                                            <div class="btn-group btn-group-sm">
                                                <form action="{{ route('admin.homepage-slides.move', [$slide, 'up']) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-secondary" title="رفع">
                                                        <i class="bi bi-arrow-up"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.homepage-slides.move', [$slide, 'down']) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-secondary" title="خفض">
                                                        <i class="bi bi-arrow-down"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        @if($slide->background_image_url)
                                                <img src="{{ $slide->background_image_url }}"
                                                    alt="{{ $slide->alt_text ?: ($slide->title ?: 'صورة سلايدر') }}"
                                                 class="rounded border"
                                                 style="width: 130px; height: 72px; object-fit: cover;">
                                        @else
                                            <div class="border rounded d-flex align-items-center justify-content-center text-muted"
                                                 style="width: 130px; height: 72px;">
                                                بدون صورة
                                            </div>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="fw-bold mb-1">{{ $slide->title ?: 'بدون عنوان' }}</div>
                                        @if($slide->subtitle)
                                            <div class="text-muted small mb-2">{{ \Illuminate\Support\Str::limit($slide->subtitle, 120) }}</div>
                                        @endif

                                        <div class="small">
                                            <span class="text-muted">الزر:</span>
                                            {{ $slide->button_text ?: 'بدون زر' }}
                                        </div>

                                        @if($slide->button_url)
                                            <div class="small text-muted">{{ \Illuminate\Support\Str::limit($slide->button_url, 70) }}</div>
                                        @endif
                                    </td>

                                    <td>
                                        @if($slide->is_active)
                                            <span class="badge bg-success">ظاهر</span>
                                        @else
                                            <span class="badge bg-secondary">مخفي</span>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="{{ route('admin.homepage-slides.edit', $slide) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil-square me-1"></i>
                                                تعديل
                                            </a>

                                            <form action="{{ route('admin.homepage-slides.toggle-status', $slide) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-dark">
                                                    <i class="bi {{ $slide->is_active ? 'bi-eye-slash' : 'bi-eye' }} me-1"></i>
                                                    {{ $slide->is_active ? 'إخفاء' : 'إظهار' }}
                                                </button>
                                            </form>

                                            <form action="{{ route('admin.homepage-slides.destroy', $slide) }}" method="POST"
                                                  onsubmit="return confirm('هل أنت متأكد من حذف هذا السلايد؟');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash me-1"></i>
                                                    حذف
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endforeach
@endsection
