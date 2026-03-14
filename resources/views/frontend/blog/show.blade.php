@extends('layouts.app')

@section('title', $post->title)

@push('styles')
<style>
    /* ===== تنسيقات موجودة مسبقاً ===== */
    .prose h2 { font-size: 1.5em; margin-top: 2em; margin-bottom: 1em; }
    .prose p { line-height: 1.7; }
    .prose img { border-radius: 0.5rem; }

    /* ===== Dark Mode (مقيّد بهذه الصفحة فقط عبر .blog-post-wrap) ===== */
    html.dark .blog-post-wrap { background-color: #0b0f14; }

    /* الخلفية العامة الهادئة */
    .blog-post-hero { background: #f6f2ef; border-bottom: 1px solid #eadbcd; }
    html.dark .blog-post-wrap .blog-post-hero { background: #0f172a; border-bottom-color: #1f2937; }

    /* الكارد الأساسية الخاصة بالمحتوى */
    html.dark .blog-post-wrap main.bg-white {
        background-color: #0f172a !important;
        border-color: #1f2937 !important;
        box-shadow: 0 10px 26px rgba(0,0,0,.25);
    }

    /* كروت السايدبار */
    html.dark .blog-post-wrap .card {
        background-color: #0f172a !important;
        border: 1px solid #1f2937 !important;
        box-shadow: 0 8px 22px rgba(0,0,0,.22);
    }
    html.dark .blog-post-wrap .card h3 { color: #e5e7eb !important; }

    /* ألوان النصوص الثانوية داخل الصفحة فقط */
    html.dark .blog-post-wrap .text-brand-dark,
    html.dark .blog-post-wrap .text-brand-text { color: #e5e7eb !important; }
    html.dark .blog-post-wrap .text-gray-600,
    html.dark .blog-post-wrap .text-gray-500,
    html.dark .blog-post-wrap .text-gray-400 { color: #9ca3af !important; }

    /* الشيب/البادج الصغيرة */
    html.dark .blog-post-wrap .chip {
        background: #111827;
        color: #e5e7eb;
        border: 1px solid #374151;
    }

    /* الروابط داخل الصفحة */
    .blog-link { transition: color .2s ease; }
    .blog-link:hover { color: #BE6661; }
    html.dark .blog-post-wrap .blog-link:hover { color: #f0b0ad; }

    /* تنسيق النصوص الغنية (prose) في الوضع الليلي */
    html.dark .blog-post-wrap .prose h1,
    html.dark .blog-post-wrap .prose h2,
    html.dark .blog-post-wrap .prose h3,
    html.dark .blog-post-wrap .prose h4,
    html.dark .blog-post-wrap .prose h5,
    html.dark .blog-post-wrap .prose h6 { color: #e5e7eb; }

    html.dark .blog-post-wrap .prose p,
    html.dark .blog-post-wrap .prose li { color: #d1d5db; }

    html.dark .blog-post-wrap .prose a { color: #f0b0ad; text-decoration: underline; }
    html.dark .blog-post-wrap .prose code {
        background: #0b1220; color: #f3f4f6; padding: .15rem .4rem; border-radius: .3rem;
        border: 1px solid #1f2937;
    }
    html.dark .blog-post-wrap .prose pre {
        background: #0b1220; color: #e5e7eb; border: 1px solid #1f2937;
        padding: 1rem; border-radius: .6rem; overflow: auto;
    }
    html.dark .blog-post-wrap .prose blockquote {
        color: #e5e7eb; border-right: 4px solid #be6661; background: #0f172a; padding: .75rem 1rem; border-radius: .5rem;
    }

    /* صور المقال تُحافظ على نفس الشكل */
    /* لا تغيير مطلوب هنا غير أنّ الخلفية حولها داكنة */
</style>
@endpush

@section('content')
<div class="blog-post-wrap bg-gray-50/50 dark:bg-gray-900">
    <div class="container mx-auto px-4 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Post Content -->
            <main class="lg:col-span-3 bg-white p-8 rounded-lg shadow-md border border-[#ebe8e6]">
                <div class="mb-8">
                    <p class="text-brand-primary font-semibold">{{ $post->category->name }}</p>
                    <h1 class="text-3xl md:text-4xl font-bold text-brand-dark mt-2">{{ $post->title }}</h1>
                    <div class="text-sm text-gray-500 mt-4">
                        <span>بواسطة {{ $post->author->name }}</span> &bull;
                        <span>{{ $post->published_at->format('d M, Y') }}</span>
                    </div>
                </div>

                @if($post->image)
                <div class="mb-8">
                    <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}" class="w-full h-auto max-h-96 object-cover rounded-lg">
                </div>
                @endif

                <div class="prose max-w-none">
                    {!! $post->body !!}
                </div>
            </main>

            <!-- Sidebar -->
            <aside class="lg:col-span-1 space-y-8">
                <div class="card bg-white p-6 rounded-lg shadow-md border border-[#ebe8e6]">
                    <h3 class="text-lg font-bold text-brand-dark border-b pb-2 mb-4 dark:border-gray-700">الأقسام</h3>
                    <ul class="space-y-2">
                        @foreach($categories as $category)
                        <li>
                            <a href="#" class="flex justify-between items-center text-gray-600 hover:text-brand-primary blog-link dark:text-gray-300">
                                <span>{{ $category->name }}</span>
                                <span class="chip text-xs bg-gray-200 text-gray-700 rounded-full px-2 py-1">
                                    {{ $category->posts_count }}
                                </span>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
                <div class="card bg-white p-6 rounded-lg shadow-md border border-[#ebe8e6]">
                    <h3 class="text-lg font-bold text-brand-dark border-b pb-2 mb-4 dark:border-gray-700">أحدث المقالات</h3>
                    <ul class="space-y-4">
                        @foreach($recentPosts as $recentPost)
                        <li>
                            <a href="{{ route('blog.show', $recentPost->slug) }}" class="flex items-center gap-4 group">
                                <img src="{{ $recentPost->image ? asset('storage/' . $recentPost->image) : 'https://placehold.co/100x100/F3E5E3/BE6661?text=...' }}" alt="{{ $recentPost->title }}" class="w-16 h-16 object-cover rounded-md flex-shrink-0">
                                <div>
                                    <p class="font-semibold text-sm text-brand-text group-hover:text-brand-primary dark:text-gray-100">{{ $recentPost->title }}</p>
                                    <p class="text-xs text-gray-400">{{ $recentPost->published_at->format('d M, Y') }}</p>
                                </div>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection
