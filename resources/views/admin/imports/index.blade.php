@extends('admin.layout')
@section('title', 'استيراد البيانات')

@section('content')
<div class="card shadow-sm">
    <div class="card-header">
        <h4 class="mb-0">استيراد البيانات من Excel</h4>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <form action="{{ route('admin.imports.preview') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="form-label">اختر القسم</label>
                <select name="section" class="form-select" required>
                    <option value="products">منتجات</option>
                    <option value="categories">البراندات</option>
                    <option value="brands">الفئات</option>
                    <option value="users">مستخدمين</option>
                    <option value="clients">عملاء</option>
                    <option value="discounts">أكواد خصم</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">ملف Excel</label>
                <input type="file" name="file" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">رفع الملف</button>
        </form>
    </div>
</div>
@endsection