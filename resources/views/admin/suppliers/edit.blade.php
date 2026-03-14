@extends('admin.layout')

@section('title', 'تعديل مورد')

@section('content')
<div class="card shadow-sm">
    <div class="card-header">
        <h4 class="mb-0">تعديل بيانات المورد: {{ $supplier->name }}</h4>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.suppliers.update', $supplier->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">اسم المورد <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $supplier->name) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="phone_number" class="form-label">رقم الهاتف</label>
                    <input type="text" class="form-control" id="phone_number" name="phone_number" value="{{ old('phone_number', $supplier->phone_number) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">البريد الإلكتروني</label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $supplier->email) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="address" class="form-label">العنوان</label>
                    <input type="text" class="form-control" id="address" name="address" value="{{ old('address', $supplier->address) }}">
                </div>
                <div class="col-12 mb-3">
                    <label for="notes" class="form-label">ملاحظات</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes', $supplier->notes) }}</textarea>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">تحديث المورد</button>
        </form>
    </div>
</div>
@endsection
