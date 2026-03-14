@extends('admin.layout')

@section('title', 'تعديل المنتج')

@section('content')
<div class="card">
    <div class="card-header">
        تعديل المنتج: {{ $product->name_ar }}
    </div>
    <div class="card-body">
        <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            {{-- استدعاء الفورم المشترك مع تمرير بيانات المنتج --}}
            @include('admin.products._form', ['product' => $product])
        </form>
    </div>
</div>
@endsection
