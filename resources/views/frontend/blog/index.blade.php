@extends('layouts.app')

@section('title', __('blog.title'))

@push('styles')
<style>
    .post-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease, border-color .3s ease, background-color .3s ease;
        border: 1px solid #ebe8e6;
    }
    .post-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 22px rgba(0,0,0,0.10);
    }
    .post-image {
        aspect-ratio: 16 / 9;
    }

    /* ===== Dark mode tweaks (مخصوص لهاي الصفحة) ===== */
    html.dark .blog-wrap { background-color: #0b0f14; }
    html.dark .blog-wrap .text-brand-dark { color: #e5e7eb; }
    html.dark .blog-wrap .text-brand-text { color: #e5e7eb; }

    html.dark .blog-wrap .post-card{
        background-color: #0f172a !important;
        border-color: #1f2937 !important;
        box-shadow: 0 10px 26px rgba(0,0,0,.25);
    }
    html.dark .blog-wrap .post-card:hover{
        box-shadow: 0 16px 34px rgba(0,0,0,.35);
    }
    html.dark .blog-wrap .post-card .text-gray-500,
    html.dark .blog-wrap .post-card .text-gray-600,
    html.dark .blog-wrap .post-card .text-gray-400{
        color: #9ca3af !important;
    }

    html.dark .blog-wrap .card {
        background-color: #0f172a !important;
        border: 1px solid #1f2937 !important;
        box-shadow: 0 8px 22px rgba(0,0,0,.22);
    }
    html.dark .blog-wrap .card h3 { color: #e5e7eb !important; }
    html.dark .blog-wrap .card .text-gray-600 { color: #d1d5db !important; }
    html.dark .blog-wrap .chip { background: #111827; color: #e5e7eb; border-color: #374151; }

    /* روابط وقيم براند */
    .link-brand { transition: color .2s ease; }
    .link-brand:hover { color: #BE6661; }
    html.dark .blog-wrap .link-brand:hover { color: #f0b0ad; }

    /* خلفية الهيدر الرقيقة */
    .hero-soft { background: #f6f2ef; border-bottom: 1px solid #eadbcd; }
    html.dark .blog-wrap .hero-soft { background: #0f172a; border-bottom-color: #1f2937; }

    /* باجينيشن (لارافيل) */
    .blog-wrap nav[role="navigation"] .hidden { display: none; }
    .blog-wrap nav[role="navigation"] .flex a,
    .blog-wrap nav[role="navigation"] .flex span{
        border-radius: .5rem !important;
    }
    html.dark .blog-wrap nav[role="navigation"] .flex a{
        background: #0f172a; border-color: #1f2937; color:#e5e7eb;
    }
    html.dark .blog-wrap nav[role="navigation"] .flex span[aria-current="page"]{
        background: #be6661; border-color: transparent; color: #fff;
    }
</style>
@endpush

@section('content')
<div class="blog-wrap bg-gray-50/50 dark:bg-gray-900">
    <div class="hero-soft dark:bg-[#0f172a]">
        <div class="container mx-auto px-4 py-10 text-center">
            <h1 class="text-4xl font-extrabold text-brand-dark tracking-tight">{{ __('blog.hero_title') }}</h1>
            <p class="text-gray-600 mt-2 dark:text-gray-300">{{ __('blog.hero_subtitle') }}</p>
        </div>
    </div>

    <div class="container mx-auto px-4 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Blog Posts -->
            <div class="lg:col-span-3">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @forelse ($posts as $post)
                        <div class="post-card bg-white rounded-lg overflow-hidden">
                            <a href="{{ route('blog.show', $post->slug) }}">
                                <img
                                    src="{{ $post->image ? asset('storage/' . $post->image) : 'https://placehold.co/600x400/F3E5E3/BE6661?text=Tofof' }}"
                                    alt="{{ $post->title }}"
                                    class="w-full h-48 object-cover post-image">
                            </a>
                            <div class="p-6">
                                <p class="text-sm text-gray-500 mb-2 dark:text-gray-400">{{ $post->category->name }}</p>
                                <h2 class="text-xl font-bold text-brand-text mb-3">
                                    <a href="{{ route('blog.show', $post->slug) }}" class="link-brand">{{ $post->title }}</a>
                                </h2>
                                <p class="text-gray-600 text-sm leading-relaxed mb-4 dark:text-gray-300">
                                    {{ $post->excerpt }}
                                </p>
                                <div class="text-xs text-gray-400 dark:text-gray-400">
                                    <span>{{ __('blog.by') }} {{ $post->author->name }}</span> &bull;
                                    <span>{{ $post->published_at->format('d M, Y') }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="col-span-full text-center text-gray-500 dark:text-gray-400">{{ __('blog.no_posts') }}</p>
                    @endforelse
                </div>

                <!-- Pagination -->
                <div class="mt-12">
                    {{ $posts->links() }}
                </div>
            </div>

            <!-- Sidebar -->
            <aside class="lg:col-span-1 space-y-8">
                <div class="card bg-white p-6 rounded-lg shadow-md border border-[#ebe8e6]">
                    <h3 class="text-lg font-bold text-brand-dark border-b pb-2 mb-4 dark:border-gray-700">{{ __('blog.categories') }}</h3>
                    <ul class="space-y-2">
                        @foreach($categories as $category)
                        <li>
                            <a href="#" class="flex justify-between items-center text-gray-600 hover:text-brand-primary link-brand dark:text-gray-300">
                                <span>{{ $category->name }}</span>
                                <span class="chip text-xs bg-gray-200 text-gray-700 rounded-full px-2 py-1 dark:bg-gray-800 dark:text-gray-200 dark:border dark:border-gray-700">
                                    {{ $category->posts_count }}
                                </span>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>

                <div class="card bg-white p-6 rounded-lg shadow-md border border-[#ebe8e6]">
                    <h3 class="text-lg font-bold text-brand-dark border-b pb-2 mb-4 dark:border-gray-700">{{ __('blog.latest_posts') }}</h3>
                    <ul class="space-y-4">
                        @foreach($recentPosts as $recentPost)
                        <li>
                            <a href="{{ route('blog.show', $recentPost->slug) }}" class="flex items-center gap-4 group">
                                <img
                                    src="{{ $recentPost->image ? asset('storage/' . $recentPost->image) : 'https://placehold.co/100x100/F3E5E3/BE6661?text=...' }}"
                                    alt="{{ $recentPost->title }}"
                                    class="w-16 h-16 object-cover rounded-md flex-shrink-0">
                                <div>
                                    <p class="font-semibold text-sm text-brand-text group-hover:text-brand-primary dark:text-gray-100">{{ $recentPost->title }}</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-400">{{ $recentPost->published_at->format('d M, Y') }}</p>
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
