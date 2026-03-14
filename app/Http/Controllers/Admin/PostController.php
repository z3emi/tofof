<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('author', 'category')->latest()->paginate(15);
        return view('admin.blog.posts.index', compact('posts'));
    }

    public function create()
    {
        $categories = BlogCategory::all();
        return view('admin.blog.posts.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'blog_category_id' => 'required|exists:blog_categories,id',
            'body' => 'required|string',
            'excerpt' => 'nullable|string|max:300',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_published' => 'nullable|boolean',
        ]);

        $data = $request->only(['title', 'blog_category_id', 'body', 'excerpt']);
        $data['slug'] = Str::slug($request->title);
        $data['user_id'] = Auth::id();
        $data['is_published'] = $request->has('is_published');
        $data['published_at'] = $data['is_published'] ? now() : null;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('posts', 'public');
        }

        Post::create($data);

        return redirect()->route('admin.blog.posts.index')->with('success', 'تم إنشاء المقال بنجاح.');
    }
    
    public function show(Post $post)
    {
        return view('admin.blog.posts.show', compact('post'));
    }

    public function edit(Post $post)
    {
        $categories = BlogCategory::all();
        return view('admin.blog.posts.edit', compact('post', 'categories'));
    }

    public function update(Request $request, Post $post)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'blog_category_id' => 'required|exists:blog_categories,id',
            'body' => 'required|string',
            'excerpt' => 'nullable|string|max:300',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_published' => 'nullable|boolean',
        ]);

        $data = $request->only(['title', 'blog_category_id', 'body', 'excerpt']);
        $data['slug'] = Str::slug($request->title);
        $data['is_published'] = $request->has('is_published');

        if ($data['is_published'] && !$post->published_at) {
            $data['published_at'] = now();
        }

        if ($request->hasFile('image')) {
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }
            $data['image'] = $request->file('image')->store('posts', 'public');
        }

        $post->update($data);

        return redirect()->route('admin.blog.posts.index')->with('success', 'تم تحديث المقال بنجاح.');
    }

    public function destroy(Post $post)
    {
        $post->delete();
        return redirect()->route('admin.blog.posts.index')->with('success', 'تم نقل المقال إلى سلة المحذوفات.');
    }

    public function trash(Request $request)
    {
        $posts = Post::onlyTrashed()->with('author', 'category')->latest('deleted_at')->paginate(15);
        return view('admin.blog.posts.trash', compact('posts'));
    }

    public function restore($id)
    {
        $post = Post::onlyTrashed()->findOrFail($id);
        $post->restore();
        return redirect()->back()->with('success', 'تم استرجاع المقال بنجاح.');
    }

    public function forceDelete($id)
    {
        $post = Post::onlyTrashed()->findOrFail($id);
        
        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }

        $post->forceDelete();
        return redirect()->back()->with('success', 'تم حذف المقال نهائيًا.');
    }
}
