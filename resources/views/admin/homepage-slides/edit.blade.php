@extends('admin.layout')

@section('title', 'تعديل السلايد')

@section('content')
<div class="mb-4">
    <h1 class="h3 mb-1">تعديل السلايد</h1>
    <p class="text-muted mb-0">عدّل النصوص، الخلفية، الرابط، أو الترتيب لهذا السلايد.</p>
</div>

<form action="{{ route('admin.homepage-slides.update', $homepageSlide) }}" method="POST" enctype="multipart/form-data">
    @method('PUT')
    @include('admin.homepage-slides._form')
</form>
@endsection
