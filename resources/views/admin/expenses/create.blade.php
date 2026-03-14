@extends('admin.layout')

@section('title', 'إضافة مصروف جديد')

@section('content')
<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">تسجيل مصروف جديد</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.expenses.store') }}" method="POST">
            @csrf
            @include('admin.expenses._form')
        </form>
    </div>
</div>
@endsection