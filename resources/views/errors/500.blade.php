@extends('layouts.app')

@section('title', 'خطأ في الخادم')

@section('content')
<div class="flex items-center justify-center min-h-[60vh] text-center px-4">
    <div>
        <h1 class="text-8xl md:text-9xl font-bold text-red-600 opacity-50">500</h1>
        <h2 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-gray-100 mt-4">عذراً، حدث خطأ داخلي</h2>
        <p class="text-gray-600 dark:text-gray-400 mt-3 max-w-md mx-auto">
            لقد واجهنا مشكلة غير متوقعة في الخادم. فريقنا يعمل حالياً على حل هذه المشكلة. يرجى المحاولة مرة أخرى لاحقاً.
        </p>
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('homepage') }}" 
               class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 bg-[#6d0e16] text-white font-bold rounded-full hover:opacity-90 transition shadow-lg">
                <i class="bi bi-house-door-fill ml-2"></i>
                العودة إلى الرئيسية
            </a>
            <button onclick="window.location.reload()" 
               class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 font-bold rounded-full hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <i class="bi bi-arrow-clockwise ml-2"></i>
                تحديث الصفحة
            </button>
        </div>
    </div>
</div>
@endsection
