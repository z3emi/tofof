@extends('layouts.app')

@section('title', 'الموقع تحت الصيانة')

@section('content')
<div class="flex items-center justify-center min-h-[60vh] text-center px-4">
    <div>
        <i class="bi bi-clock-history text-8xl text-[#6d0e16] opacity-50 mb-4 inline-block"></i>
        <h1 class="text-4xl md:text-5xl font-bold text-gray-800 dark:text-gray-100 mt-4">نعود قريباً!</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-4 max-w-lg mx-auto text-lg leading-relaxed">
            نحن نقوم حالياً ببعض أعمال الصيانة والتحديثات لتقديم تجربة أفضل لكم.
            <br>
            شكراً لتفهمكم، سنكون متاحين مرة أخرى في أقرب وقت ممكن.
        </p>
        <div class="mt-8">
            <div class="flex items-center justify-center gap-4 text-[#6d0e16] text-2xl">
                <a href="https://www.instagram.com/tofof_watches" class="hover:opacity-80 transition"><i class="bi bi-instagram"></i></a>
                <a href="https://wa.me/9647757778099" class="hover:opacity-80 transition"><i class="bi bi-whatsapp"></i></a>
            </div>
        </div>
    </div>
</div>
@endsection
