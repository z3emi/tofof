@extends('layouts.app')

@section('title', 'غير مصرح به')

@section('content')
<div class="flex items-center justify-center min-h-[60vh] text-center px-4">
    <div>
        <h1 class="text-8xl md:text-9xl font-bold text-brand-primary opacity-50">401</h1>
        <h2 class="text-2xl md:text-3xl font-bold text-brand-text mt-4">غير مصرح به</h2>
        <p class="text-gray-600 dark:text-gray-400 mt-3 max-w-md mx-auto">
            عذراً، يجب عليك تسجيل الدخول أولاً للوصول إلى هذه الصفحة.
        </p>
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('login') }}" 
               class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3 bg-[#6d0e16] text-white font-bold rounded-full hover:opacity-90 transition shadow-lg">
                <i class="bi bi-box-arrow-in-right ml-2"></i>
                تسجيل الدخول
            </a>
            <a href="{{ route('homepage') }}" 
               class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3 bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 font-bold rounded-full hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                العودة إلى الرئيسية
            </a>
        </div>
    </div>
</div>
@endsection
