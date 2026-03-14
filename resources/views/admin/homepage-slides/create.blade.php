@extends('admin.layout')

@section('title', 'إضافة سلايد جديد')

@section('content')
<div class="mb-4">
    <h1 class="h3 mb-1">إضافة سلايد جديد</h1>
    <p class="text-muted mb-0">أضف سلايد جديد لأي قسم من سلايدرات الصفحة الرئيسية.</p>
</div>

<form action="{{ route('admin.homepage-slides.store') }}" method="POST" enctype="multipart/form-data">
    @include('admin.homepage-slides._form')
</form>
@endsection
