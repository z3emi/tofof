@extends('admin.layout')

@section('title', 'إنشاء كود QR جديد')

@push('styles')
<style>
    :root{ --brand:#cd8985; --soft:#f9f5f1; }
    .card-header{ background:var(--soft); border-bottom:2px solid var(--brand); }
    .hint{ color:#6c757d; font-size:.9rem; }
</style>
@endpush

@section('content')
<div class="card shadow-sm">
    <div class="card-header"><h4 class="mb-0">إنشاء كود جديد</h4></div>

    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $e) <div>{{ $e }}</div> @endforeach
            </div>
        @endif

        <form action="{{ route('admin.barcodes.store') }}" method="POST">
            @csrf

            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">الوجهة (Target URL) <span class="text-danger">*</span></label>
                    <input type="url" name="target_url" class="form-control" placeholder="https://example.com/landing"
                           value="{{ old('target_url') }}" required>
                    <div class="hint mt-1">الرابط الذي سيتم تحويل الزائر إليه عند فتح QR.</div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">الكود (اختياري)</label>
                    <input type="text" name="code" class="form-control" placeholder="مثال: COSM01"
                           value="{{ old('code') }}">
                    <div class="hint mt-1">اتركه فارغًا لتوليد كود عشوائي (طول 6). أحرف/أرقام فقط.</div>
                </div>

                <div class="col-md-12">
                    <label class="form-label">ملاحظات (اختياري)</label>
                    <textarea name="notes" rows="3" class="form-control">{{ old('notes') }}</textarea>
                </div>

                <div class="col-md-4">
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                        <label class="form-check-label" for="is_active">تفعيل الكود</label>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-check2 me-1"></i> حفظ</button>
                <a href="{{ route('admin.barcodes.index') }}" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
