<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\BlogCategory;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    /**
     * Display a listing of the blog posts.
     */
    public function index()
    {
        $posts = Post::where('is_published', true)
                     ->with('author', 'category')
                     ->latest('published_at')
                     ->paginate(9);

        $categories = BlogCategory::withCount('posts')->get();
        $recentPosts = Post::where('is_published', true)->latest('published_at')->take(5)->get();

        return view('frontend.blog.index', compact('posts', 'categories', 'recentPosts'));
    }

    /**
     * Display the specified blog post.
     */
    public function show(Post $post)
    {
        // Ensure only published posts are visible to guests
        if (!$post->is_published) {
            abort(404);
        }

        $post->load('author', 'category');
        $categories = BlogCategory::withCount('posts')->get();
        $recentPosts = Post::where('is_published', true)->where('id', '!=', $post->id)->latest('published_at')->take(5)->get();

        return view('frontend.blog.show', compact('post', 'categories', 'recentPosts'));
    }
}
