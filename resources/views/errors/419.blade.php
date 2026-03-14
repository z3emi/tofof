@extends('layouts.app')

@section('title', 'انتهت صلاحية الصفحة')

@section('content')
<div class="flex items-center justify-center min-h-[60vh] text-center px-4">
    <div>
        <h1 class="text-8xl md:text-9xl font-bold text-brand-primary opacity-50">419</h1>
        <h2 class="text-2xl md:text-3xl font-bold text-brand-text mt-4">عذراً، انتهت صلاحية الجلسة</h2>
        <p class="text-gray-600 dark:text-gray-400 mt-3 max-w-md mx-auto">
            يبدو أنك تركت الصفحة لفترة طويلة دون تفاعل. يرجى تحديث الصفحة والمحاولة مرة أخرى.
        </p>
        <div class="mt-8">
            <a href="{{ url()->current() }}" 
               class="inline-flex items-center justify-center px-8 py-3 bg-[#6d0e16] text-white font-bold rounded-full hover:opacity-90 transition-all shadow-lg">
                <i class="bi bi-arrow-clockwise ml-2"></i>
                تحديث الصفحة
            </a>
        </div>
    </div>
</div>
@endsection
