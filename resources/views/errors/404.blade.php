@extends('layouts.app')

@section('title', 'الصفحة غير موجودة')

@section('content')
<div class="flex items-center justify-center min-h-[60vh] text-center px-4">
    <div>
        <h1 class="text-8xl md:text-9xl font-bold text-brand-primary opacity-50">404</h1>
        <h2 class="text-2xl md:text-3xl font-bold text-brand-text mt-4">عذراً، الصفحة غير موجودة</h2>
        <p class="text-gray-600 mt-3 max-w-md mx-auto">
            الصفحة التي تبحثين عنها قد تكون حُذفت، تم تغيير رابطها، أو أنها غير متاحة مؤقتاً.
        </p>
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('homepage') }}" 
               class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 bg-brand-accent text-white font-bold rounded-full hover:bg-brand-dark transition-colors shadow-lg">
                <i class="bi bi-house-door-fill ml-2"></i>
                العودة إلى الرئيسية
            </a>
            <a href="{{ route('shop') }}" 
               class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 bg-transparent border-2 border-brand-primary text-brand-primary font-bold rounded-full hover:bg-brand-primary hover:text-white transition-colors">
                <i class="bi bi-shop ml-2"></i>
                الذهاب إلى المتجر
            </a>
        </div>
    </div>
</div>
@endsection
