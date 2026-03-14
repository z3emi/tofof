@extends('admin.layout')

@section('title', 'تعديل المصروف')

@section('content')
<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">تعديل المصروف</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.expenses.update', $expense->id) }}" method="POST">
            @csrf
            @method('PUT')
            @include('admin.expenses._form', ['expense' => $expense])
        </form>
    </div>
</div>
@endsection