@extends('layouts.app') {{-- Or your main frontend layout --}}
@section('title', 'الموقع تحت الصيانة')

@section('content')
<div class="container mx-auto text-center py-20">
    <i class="bi bi-gear-wide-connected text-8xl text-brand-primary"></i>
    <h1 class="text-4xl font-bold text-brand-dark mt-4">نعود قريباً!</h1>
    <p class="text-lg text-gray-600 mt-2">
        نحن نقوم حالياً ببعض أعمال الصيانة والتحديثات على الموقع.
        <br>
        شكراً لتفهمكم، سنعود للعمل في أقرب وقت ممكن.
    </p>
</div>
@endsection
