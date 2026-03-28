@extends('admin.layout')
@section('title', 'إضافة قسم جديد')
@section('content')
<div class="card shadow-sm">
    <div class="card-header">
        <h4 class="mb-0">إضافة قسم جديد</h4>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name_ar" class="form-label">اسم القسم (عربي) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name_ar" name="name_ar" value="{{ old('name_ar') }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="name_en" class="form-label">اسم القسم (إنكليزي)</label>
                    <input type="text" class="form-control" id="name_en" name="name_en" value="{{ old('name_en') }}">
                </div>
            </div>

            {{-- **حقل جديد لاختيار القسم الأب** --}}
            <div class="mb-3">
                <label for="parent_id" class="form-label">القسم الأب (اختياري)</label>
                <select class="form-select" id="parent_id" name="parent_id">
                    <option value="">-- قسم رئيسي --</option>
                    @foreach($parentCategories as $parent)
                        <option value="{{ $parent->id }}">{{ $parent->name_ar }} @if($parent->name_en) ({{ $parent->name_en }}) @endif</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="image" class="form-label">صورة القسم <span class="text-danger">*</span></label>
                <input type="file" class="form-control" id="image" name="image" required>
            </div>
            <button type="submit" class="btn btn-primary">حفظ القسم</button>
        </form>
    </div>
</div>
@endsection
