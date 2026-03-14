<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogCategoryController extends Controller
{
    public function index()
    {
        $categories = BlogCategory::withCount('posts')->latest()->paginate(15);
        return view('admin.blog.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.blog.categories.create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255|unique:blog_categories,name']);
        
        BlogCategory::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return redirect()->route('admin.blog.categories.index')->with('success', 'تم إنشاء القسم بنجاح.');
    }

    public function edit(BlogCategory $category)
    {
        return view('admin.blog.categories.edit', compact('category'));
    }

    public function update(Request $request, BlogCategory $category)
    {
        $request->validate(['name' => 'required|string|max:255|unique:blog_categories,name,' . $category->id]);

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return redirect()->route('admin.blog.categories.index')->with('success', 'تم تحديث القسم بنجاح.');
    }

    public function destroy(BlogCategory $category)
    {
        if ($category->posts()->count() > 0) {
            return redirect()->back()->with('error', 'لا يمكن حذف هذا القسم لأنه يحتوي على مقالات.');
        }
        $category->delete();
        return redirect()->route('admin.blog.categories.index')->with('success', 'تم نقل القسم إلى سلة المحذوفات.');
    }

    public function trash()
    {
        $categories = BlogCategory::onlyTrashed()->withCount('posts')->latest('deleted_at')->paginate(15);
        return view('admin.blog.categories.trash', compact('categories'));
    }

    public function restore($id)
    {
        $category = BlogCategory::onlyTrashed()->findOrFail($id);
        $category->restore();
        return redirect()->back()->with('success', 'تم استرجاع القسم بنجاح.');
    }

    public function forceDelete($id)
    {
        $category = BlogCategory::onlyTrashed()->findOrFail($id);
        if ($category->posts()->count() > 0) {
            return redirect()->back()->with('error', 'لا يمكن حذف هذا القسم نهائياً لأنه يحتوي على مقالات.');
        }
        $category->forceDelete();
        return redirect()->back()->with('success', 'تم حذف القسم نهائيًا.');
    }
}
