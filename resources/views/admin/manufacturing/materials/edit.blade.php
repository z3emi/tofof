@extends('admin.layout')

@section('title', 'تعديل مادة خام')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">تعديل مادة: {{ $material->name }}</h4>
    <a href="{{ route('admin.manufacturing.materials.index') }}" class="btn btn-outline-secondary">عودة للقائمة</a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card shadow-sm">
    <div class="card-body">
        <form action="{{ route('admin.manufacturing.materials.update', $material) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">اسم المادة <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $material->name) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">الرمز (SKU)</label>
                    <input type="text" name="sku" class="form-control" value="{{ old('sku', $material->sku) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">وحدة القياس</label>
                    <input type="text" name="unit" class="form-control" value="{{ old('unit', $material->unit) }}" placeholder="كغم، لتر ...">
                </div>
                <div class="col-md-4">
                    <label class="form-label">تكلفة الوحدة <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">{{ \App\Support\Currency::symbol() }}</span>
                        <input type="number" step="0.01" min="0" name="cost_per_unit" class="form-control" value="{{ old('cost_per_unit', $material->cost_per_unit) }}" required>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">ملاحظات</label>
                    <textarea name="notes" rows="3" class="form-control" placeholder="أية تفاصيل إضافية عن المادة">{{ old('notes', $material->notes) }}</textarea>
                </div>
            </div>
            <div class="mt-4 d-flex justify-content-end gap-2">
                <a href="{{ route('admin.manufacturing.materials.index') }}" class="btn btn-light">إلغاء</a>
                <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
            </div>
        </form>
    </div>
</div>
@endsection
