@extends('admin.layout')

@section('title', 'إضافة مخزن')

@section('content')
<div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
    <div>
        <h1 class="h4 mb-1">إضافة مخزن جديد</h1>
        <p class="text-muted mb-0">سجّل مواقع التخزين ليظهر مخزونك مقسمًا حسب المخزن.</p>
    </div>
    <a href="{{ route('admin.warehouses.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-right"></i>
        الرجوع لقائمة المخازن
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.warehouses.store') }}" class="row g-3">
            @csrf

            <div class="col-12 col-lg-6">
                <label for="warehouseName" class="form-label">اسم المخزن <span class="text-danger">*</span></label>
                <input type="text" name="name" id="warehouseName" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-lg-6">
                <label for="warehouseCode" class="form-label">الكود</label>
                <input type="text" name="code" id="warehouseCode" value="{{ old('code') }}" class="form-control @error('code') is-invalid @enderror">
                @error('code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-lg-6">
                <label for="warehouseLocation" class="form-label">الموقع</label>
                <input type="text" name="location" id="warehouseLocation" value="{{ old('location') }}" class="form-control @error('location') is-invalid @enderror">
                @error('location')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <label for="warehouseNotes" class="form-label">ملاحظات</label>
                <textarea name="notes" id="warehouseNotes" rows="3" class="form-control @error('notes') is-invalid @enderror" placeholder="أي تفاصيل إضافية مثل ساعات العمل أو مسؤول المخزن.">{{ old('notes') }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-2"></i>
                    حفظ المخزن
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
