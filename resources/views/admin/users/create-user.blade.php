@extends('admin.layout')

@section('title', 'إضافة مستخدم جديد')

@section('content')
@can('create-users')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">إنشاء مستخدم جديد</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            @include('admin.users.partials.user-form', ['governorates' => $governorates, 'user' => null])
        </form>
    </div>
</div>
@else
<div class="alert alert-danger">ليس لديك صلاحية لإضافة مستخدمين.</div>
@endcan
@endsection
