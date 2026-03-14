@extends('admin.layout')

@section('title', 'تعديل المستخدم: ' . $user->name)

@section('content')
@can('edit-users')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">تعديل بيانات المستخدم</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')
            @include('admin.users.partials.user-form', ['governorates' => $governorates, 'user' => $user])
        </form>
    </div>
</div>
@else
<div class="alert alert-danger">ليس لديك صلاحية لتعديل المستخدمين.</div>
@endcan
@endsection
