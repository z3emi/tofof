@extends('admin.layout')
@section('title','إدارة الباركود')

@push('styles')
<style>
:root{ --brand:#cd8985; --line:#eadbcd; }
.card-header{ background:#f9f5f1; border-bottom:2px solid var(--brand); }
.badge-pill{border-radius:999px;padding:.4rem .6rem;font-weight:700}
</style>
@endpush

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h4 class="mb-0">أكواد الباركود</h4>
        <a href="{{ route('admin.barcodes.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i> إنشاء كود جديد
        </a>
    </div>

    <div class="card-body">
        <form method="GET" action="{{ route('admin.barcodes.index') }}" class="row g-2 mb-3">
            <div class="col">
                <input type="text" name="search" class="form-control" placeholder="بحث عن الكود/العنوان/الرابط"
                       value="{{ request('search') }}">
            </div>
            <div class="col-auto">
                <select name="status" class="form-select">
                    <option value="">كل الحالات</option>
                    <option value="active"  @selected(request('status')==='active')>فعال</option>
                    <option value="inactive"@selected(request('status')==='inactive')>غير فعال</option>
                </select>
            </div>
            <div class="col-auto">
                <select name="per_page" class="form-select" onchange="this.form.submit()">
                    @foreach([10,15,25,50,100] as $n)
                        <option value="{{ $n }}" @selected(request('per_page',15)===$n)>{{ $n }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-outline-secondary"><i class="bi bi-search me-1"></i> بحث</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>الكود</th>
                        <th>العنوان</th>
                        <th>الرابط</th>
                        <th>الحالة</th>
                        <th>زيارات</th>
                        <th>QR</th>
                        <th style="width:190px;">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($barcodes as $b)
                        @php
                            // مسارات محدثة (بدون تغيير بالشكل)
                            $publicUrl = route('barcodes.go', $b->code);
                            $qrUrl     = route('barcodes.qr.png', $b->code);
                        @endphp
                        <tr>
                            <td>{{ $b->id }}</td>
                            <td>
                                <code class="user-select-all">{{ $b->code }}</code>
                                <div>
                                    <small class="text-muted">{{ $b->created_at->format('Y-m-d') }}</small>
                                </div>
                            </td>
                            <td class="text-start">
                                <div class="fw-semibold">{{ $b->title ?? '—' }}</div>
                                <small class="text-muted">آخر زيارة: {{ $b->last_hit_at?->diffForHumans() ?? '—' }}</small>
                            </td>
                            <td class="text-start" style="max-width:320px;">
                                <div class="small text-truncate" title="{{ $b->target_url }}">{{ $b->target_url }}</div>
                                <div class="mt-1">
                                    <a href="{{ $publicUrl }}" target="_blank" class="small">فتح /b/{{ $b->code }}</a>
                                </div>
                            </td>
                            <td>
                                @if($b->is_active)
                                    <span class="badge badge-pill bg-success">فعال</span>
                                @else
                                    <span class="badge badge-pill bg-secondary">غير فعال</span>
                                @endif
                            </td>
                            <td><span class="badge bg-light text-dark">{{ number_format($b->hits) }}</span></td>
                            <td>
                                <a href="{{ $qrUrl }}" target="_blank" class="btn btn-sm btn-outline-dark">
                                    <i class="bi bi-qr-code"></i> QR
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('admin.barcodes.edit',$b) }}" class="btn btn-sm btn-outline-primary m-1 px-2">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.barcodes.toggle',$b) }}" method="POST" class="d-inline-block">
                                    @csrf
                                    <button class="btn btn-sm {{ $b->is_active? 'btn-outline-warning':'btn-outline-success' }} m-1 px-2"
                                            title="{{ $b->is_active? 'إيقاف':'تفعيل' }}">
                                        <i class="bi {{ $b->is_active? 'bi-pause-circle':'bi-play-circle' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.barcodes.destroy',$b) }}" method="POST" class="d-inline-block"
                                      onsubmit="return confirm('حذف الكود؟')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger m-1 px-2"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="p-4">لا توجد أكواد بعد.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $barcodes->withQueryString()->links() }}</div>
    </div>
</div>
@endsection
