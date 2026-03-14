@extends('admin.layout')
@section('title','تعديل كود باركود')

@push('styles')
<style>
:root{ --brand:#cd8985; }
.card-header{ background:#f9f5f1; border-bottom:2px solid var(--brand); }
.help{color:#6c757d;font-size:.9rem}
</style>
@endpush

@section('content')
<div class="card shadow-sm">
    <div class="card-header"><h4 class="mb-0">تعديل الكود: {{ $barcode->code }}</h4></div>
    <div class="card-body">
        <form action="{{ route('admin.barcodes.update',$barcode) }}" method="POST">
            @csrf @method('PUT')

            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                </div>
            @endif

            @php
                // استخدام روابط مباشرة بدل route(...)
                $publicUrl = url('/b/'.$barcode->code);
                $qrUrl     = url('/b/'.$barcode->code.'.png');
            @endphp

            <div class="row g-3">
                {{-- 1) الكود (ثابت) --}}
                <div class="col-md-4">
                    <label class="form-label">الكود</label>
                    <input type="text" class="form-control" value="{{ $barcode->code }}" disabled>
                    <div class="help mt-1">الكود ثابت ولا يمكن تغييره.</div>
                </div>

                {{-- 2) العنوان --}}
                <div class="col-md-8">
                    <label class="form-label">العنوان (اختياري)</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title',$barcode->title) }}">
                </div>

                {{-- 3) الرابط الهدف --}}
                <div class="col-12">
                    <label class="form-label">الرابط الهدف <span class="text-danger">*</span></label>
                    <input type="url" name="target_url" class="form-control" required value="{{ old('target_url',$barcode->target_url) }}">
                    <div class="help mt-1">تقدر تغيّر هذا الرابط بأي وقت—الكود يبقى نفسه.</div>
                </div>

                {{-- 4) الحالة + 5) المعاينة --}}
                <div class="col-md-3">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                               {{ old('is_active',$barcode->is_active)?'checked':'' }}>
                        <label class="form-check-label" for="is_active">تفعيل</label>
                    </div>
                </div>

                <div class="col-md-9">
                    <div class="alert alert-light border mt-4">
                        <div class="mb-2">
                            <strong>الرابط العام:</strong>
                            <a href="{{ $publicUrl }}" target="_blank">{{ $publicUrl }}</a>
                            <button type="button" class="btn btn-sm btn-outline-secondary ms-2"
                                    onclick="navigator.clipboard.writeText('{{ $publicUrl }}')">نسخ</button>
                        </div>
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <img src="{{ $qrUrl }}?v={{ $barcode->updated_at?->timestamp ?? now()->timestamp }}"
                                 alt="QR" style="width:96px;height:96px">
                            <a href="{{ $qrUrl }}" target="_blank" class="btn btn-sm btn-outline-dark">
                                <i class="bi bi-qr-code"></i> فتح صورة QR
                            </a>
                            <a href="{{ $qrUrl }}" download class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-download"></i> تحميل الصورة
                            </a>
                        </div>
                        <div class="help mt-2">لا يوجد زر إعادة توليد—الصورة تُعرض من المسار العام مباشرة.</div>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary">حفظ التعديلات</button>
                <a href="{{ route('admin.barcodes.index') }}" class="btn btn-secondary">رجوع</a>
            </div>
        </form>
    </div>
</div>
@endsection
