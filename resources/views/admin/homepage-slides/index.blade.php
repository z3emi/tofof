@extends('admin.layout')

@section('title', 'سلايدات الصفحة الرئيسية')

@push('styles')
<style>
    .form-card { border-radius: 0 !important; border: none !important; box-shadow: none !important; background: #fff; width: 100% !important; margin: 0 !important; }
    .form-card-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-medium) 100%); padding: 2.5rem 3rem; color: white; border-radius: 0 !important; }
    .table-container { border-radius: 15px; border: 1px solid #f1f5f9; overflow: hidden; background: #fff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 2rem; }
    .slide-preview { width:120px; height:60px; border-radius:10px; object-fit:cover; border:1px solid #eee; }
    .section-title { font-size: 1.1rem; font-weight: 700; color: var(--primary-dark); margin-bottom: 1rem; display: flex; align-items: center; gap: 10px; }
    .section-title::after { content: ''; flex-grow: 1; height: 2px; background: #f1f5f9; }
</style>
@endpush

@section('content')
<div class="form-card">
    <div class="form-card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2 class="mb-2 fw-bold text-white"><i class="bi bi-images me-2"></i> واجهة المتجر الرئيسية</h2>
            <p class="mb-0 opacity-75 fs-6 text-white small">إدارة السلايدات، البانرات الترويجية، والعروض المرئية في الصفحة الأولى.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.homepage-slides.create') }}" class="btn btn-light px-4 fw-bold text-brand"><i class="bi bi-plus-circle me-1"></i> إضافة سلايد جديد</a>
        </div>
    </div>
    
    <div class="p-4 p-lg-5">
        @foreach(\App\Models\HomepageSlide::sections() as $sectionKey => $sectionLabel)
            @php $sectionSlides = $slides->get($sectionKey, collect()); @endphp
            <div class="section-title text-muted mb-4">
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2">{{ $sectionLabel }}</span>
                <span class="small fw-bold">{{ $sectionSlides->count() }} سلايد</span>
            </div>

            <div class="table-container shadow-sm border overflow-hidden">
                <table class="table mb-0 align-middle text-center">
                    <thead class="bg-light border-bottom">
                        <tr class="text-muted small fw-bold">
                            <th class="py-3" width="80">الترتيب</th>
                            <th class="py-3">المعاينة</th>
                            <th class="py-3 text-start">المحتوى والنصوص</th>
                            <th class="py-3">الحالة</th>
                            <th class="py-3" width="280">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sectionSlides as $slide)
                            <tr>
                                <td>
                                    <div class="d-flex flex-column align-items-center gap-1">
                                        <div class="fw-bold fs-5">{{ $slide->sort_order }}</div>
                                        <div class="btn-group btn-group-sm">
                                            <form action="{{ route('admin.homepage-slides.move', [$slide, 'up']) }}" method="POST">@csrf <button class="btn btn-outline-secondary px-1 py-0"><i class="bi bi-chevron-up"></i></button></form>
                                            <form action="{{ route('admin.homepage-slides.move', [$slide, 'down']) }}" method="POST">@csrf <button class="btn btn-outline-secondary px-1 py-0"><i class="bi bi-chevron-down"></i></button></form>
                                        </div>
                                    </div>
                                </td>
                                <td><img src="{{ $slide->background_image_url }}" class="slide-preview shadow-sm" onerror="this.src='https://placehold.co/120x60?text=No+Image'"></td>
                                <td class="text-start">
                                    <div class="fw-bold text-dark">{{ $slide->title ?: 'بدون عنوان' }}</div>
                                    @if($slide->subtitle) <div class="small text-muted">{{ Str::limit($slide->subtitle, 80) }}</div> @endif
                                    @if($slide->button_text) <div class="mt-1 small"><span class="badge bg-light text-dark border"><i class="bi bi-link-45deg me-1"></i>{{ $slide->button_text }}</span></div> @endif
                                </td>
                                <td>
                                    @if($slide->is_active) <span class="badge bg-success rounded-pill px-3 py-2">نشط</span>
                                    @else <span class="badge bg-secondary rounded-pill px-3 py-2">مخفي</span> @endif
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('admin.homepage-slides.edit', $slide->id) }}" class="btn btn-sm btn-outline-primary rounded-3 px-3 py-2 fw-bold"><i class="bi bi-pencil me-1"></i> تعديل</a>
                                        <form action="{{ route('admin.homepage-slides.toggle-status', $slide->id) }}" method="POST">@csrf<button class="btn btn-sm btn-outline-dark rounded-3 px-3 py-2 fw-bold"><i class="bi {{ $slide->is_active ? 'bi-eye-slash' : 'bi-eye' }} me-1"></i> {{ $slide->is_active ? 'إخفاء' : 'إظهار' }}</button></form>
                                        <form action="{{ route('admin.homepage-slides.destroy', $slide->id) }}" method="POST" onsubmit="return confirm('حذف؟')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger rounded-3 px-2 py-2"><i class="bi bi-trash"></i></button></form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-5 text-muted">لا توجد سلايدات في هذا القسم.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>
</div>
@endsection
