@extends('layouts.app')

@section('title', 'وصول غير مصرح به')

@section('content')
<div class="flex items-center justify-center min-h-[60vh] text-center px-4">
    <div>
        <h1 class="text-8xl md:text-9xl font-bold text-brand-primary opacity-50">403</h1>
        <h2 class="text-2xl md:text-3xl font-bold text-brand-text mt-4">وصول غير مصرح به</h2>
        <p class="text-gray-600 mt-3 max-w-md mx-auto">
            عذراً، ليس لديك الصلاحية اللازمة للوصول إلى هذه الصفحة.
        </p>
        <div class="mt-8">
            <a href="{{ route('homepage') }}" 
               class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 bg-brand-accent text-white font-bold rounded-full hover:bg-brand-dark transition-colors shadow-lg">
                <i class="bi bi-house-door-fill ml-2"></i>
                العودة إلى الصفحة الرئيسية
            </a>
        </div>
    </div>
</div>
@endsection
