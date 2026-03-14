@extends('layouts.app')

@section('title', 'طلبات كثيرة جداً')

@section('content')
<div class="flex items-center justify-center min-h-[60vh] text-center px-4">
    <div>
        <h1 class="text-8xl md:text-9xl font-bold text-yellow-600 opacity-50">429</h1>
        <h2 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-gray-100 mt-4">طلبات كثيرة جداً</h2>
        <p class="text-gray-600 dark:text-gray-400 mt-3 max-w-md mx-auto">
            لقد قمت بإرسال الكثير من الطلبات في وقت قصير جداً. يرجى الانتظار قليلاً ثم المحاولة مرة أخرى.
        </p>
        <div class="mt-8">
            <a href="{{ route('homepage') }}" 
               class="inline-flex items-center justify-center px-8 py-3 bg-[#6d0e16] text-white font-bold rounded-full hover:opacity-90 transition shadow-lg">
                <i class="bi bi-house-door-fill ml-2"></i>
                العودة إلى الرئيسية
            </a>
        </div>
    </div>
</div>
@endsection
