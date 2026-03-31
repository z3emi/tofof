<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrimaryCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Traits\HandlesImageUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PrimaryCategoriesExport;

class PrimaryCategoryController extends Controller
{
    use HandlesImageUploads;
    public function index(Request $request)
    {
        $search = $request->get('search');
        $allowedSorts = ['id', 'name_ar', 'sort_order', 'created_at'];
        [$sortBy, $sortDir] = \App\Support\Sort::resolve($request, $allowedSorts, 'id');

        $items = PrimaryCategory::query()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name_ar', 'like', "%{$search}%")
                       ->orWhere('name_en', 'like', "%{$search}%")
                       ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->whereNull('parent_id')
            ->withCount('products')
            ->with(['children' => function ($q) {
                $q->withCount('products');
            }])
            ->orderBy($sortBy, $sortDir)
            ->paginate($request->get('per_page', 10))
            ->withQueryString();

        return view('admin.primary_categories.index', compact('items', 'search', 'sortBy', 'sortDir'));
    }

    public function create()
    {
        $item = new PrimaryCategory();
        $parents = PrimaryCategory::ordered()->get(); // خيارات الأب
        return view('admin.primary_categories.create', compact('item','parents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name_ar'    => ['required','string','max:255'],
            'name_en'    => ['nullable','string','max:255'],
            'slug'       => ['nullable','string','max:255','unique:primary_categories,slug'],
            'sort_order' => ['nullable','integer','min:0'],
            'is_active'  => ['sometimes','boolean'],
            'parent_id'  => ['nullable','integer','exists:primary_categories,id'],
            'icon_file'  => ['nullable','mimes:svg,png,jpg,jpeg,gif,webp'],
            'image_file' => ['nullable','image','mimes:png,jpg,jpeg,gif,webp'],
        ]);

        // استلام checkbox كبولياني
        $data['is_active'] = $request->boolean('is_active');

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name_en'] ?: $data['name_ar']);
        }
        $data['sort_order'] = $data['sort_order'] ?? 0;

        if ($request->hasFile('icon_file')) {
            $data['icon'] = $this->uploadAndConvertImage($request->file('icon_file'), 'primary_categories/icons');
        }
        if ($request->hasFile('image_file')) {
            $data['image'] = $this->uploadAndConvertImage($request->file('image_file'), 'primary_categories/images');
        }

        PrimaryCategory::create($data);

        return redirect()->route('admin.primary-categories.index')->with('success', 'تم إنشاء الفئة بنجاح.');
    }

    public function edit(PrimaryCategory $primary_category)
    {
        $item = $primary_category;
        $parents = PrimaryCategory::where('id','!=',$item->id)->ordered()->get();
        return view('admin.primary_categories.edit', compact('item','parents'));
    }

    public function update(Request $request, PrimaryCategory $primary_category)
    {
        $data = $request->validate([
            'name_ar'      => ['required','string','max:255'],
            'name_en'      => ['nullable','string','max:255'],
            'slug'         => ['nullable','string','max:255','unique:primary_categories,slug,'.$primary_category->id],
            'sort_order'   => ['nullable','integer','min:0'],
            'is_active'    => ['sometimes','boolean'],
            'parent_id'    => ['nullable','integer','exists:primary_categories,id','not_in:'.$primary_category->id],
            'icon_file'    => ['nullable','mimes:svg,png,jpg,jpeg,gif,webp'],
            'image_file'   => ['nullable','image','mimes:png,jpg,jpeg,gif,webp'],
            'remove_icon'  => ['sometimes','boolean'],
            'remove_image' => ['sometimes','boolean'],
        ]);

        // منع جعل الأب أحد الأحفاد (منع الحلقة)
        if ($request->filled('parent_id')) {
            $newParent = PrimaryCategory::find($request->parent_id);
            $cursor = $newParent;
            while ($cursor) {
                if ($cursor->id === $primary_category->id) {
                    return back()->withErrors(['parent_id' => 'لا يمكن جعل الفئة تابعة لأحد أبنائها/أحفادها.'])->withInput();
                }
                $cursor = $cursor->parent;
            }
        }

        $data['is_active'] = $request->boolean('is_active');

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name_en'] ?: $data['name_ar']);
        }
        $data['sort_order'] = $data['sort_order'] ?? 0;

        // حذف اختياري
        if ($request->boolean('remove_icon') && $primary_category->icon) {
            Storage::disk('public')->delete($primary_category->icon);
            $data['icon'] = null;
        }
        if ($request->boolean('remove_image') && $primary_category->image) {
            Storage::disk('public')->delete($primary_category->image);
            $data['image'] = null;
        }

        // استبدال ملفات
        if ($request->hasFile('icon_file')) {
            if ($primary_category->icon) Storage::disk('public')->delete($primary_category->icon);
            $data['icon'] = $this->uploadAndConvertImage($request->file('icon_file'), 'primary_categories/icons');
        }
        if ($request->hasFile('image_file')) {
            if ($primary_category->image) Storage::disk('public')->delete($primary_category->image);
            $data['image'] = $this->uploadAndConvertImage($request->file('image_file'), 'primary_categories/images');
        }

        $primary_category->update($data);

        return redirect()->route('admin.primary-categories.index')->with('success', 'تم تحديث الفئة بنجاح.');
    }

    public function destroy(PrimaryCategory $primary_category)
    {
        // تم النقل للحذف النهائي (forceDelete) للحفاظ على الصورة في سلة المحذوفات
        $primary_category->delete();

        return redirect()->route('admin.primary-categories.index')->with('success', 'تم نقل الفئة إلى سلة المحذوفات.');
    }

    public function trash(Request $request)
    {
        $search = $request->get('search');

        $items = PrimaryCategory::onlyTrashed()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name_ar', 'like', "%{$search}%")
                       ->orWhere('name_en', 'like', "%{$search}%")
                       ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->paginate($request->get('per_page', 10))
            ->withQueryString();

        return view('admin.primary_categories.trash', compact('items', 'search'));
    }

    public function restore($id)
    {
        $item = PrimaryCategory::onlyTrashed()->findOrFail($id);
        $item->restore();
        return redirect()->back()->with('success', 'تم استرجاع الفئة بنجاح.');
    }

    public function forceDelete($id)
    {
        $item = PrimaryCategory::onlyTrashed()->findOrFail($id);
        
        if ($item->icon)  Storage::disk('public')->delete($item->icon);
        if ($item->image) Storage::disk('public')->delete($item->image);

        $item->forceDelete();

        return redirect()->back()->with('success', 'تم حذف الفئة نهائيًا.');
    }

    public function toggleStatus(PrimaryCategory $primary_category, Request $request)
    {
        if ($request->has('status')) {
            $primary_category->update(['is_active' => $request->status == '1']);
        } else {
            $primary_category->update(['is_active' => !$primary_category->is_active]);
        }
        return back()->with('success', $primary_category->is_active ? 'تم التفعيل بنجاح' : 'تم الإيقاف بنجاح');
    }
    // app/Http/Controllers/Admin/PrimaryCategoryController.php
public function children(\App\Models\PrimaryCategory $primary_category)
{
    $children = $primary_category->children()
        ->active()
        ->ordered()
        ->get(['id', 'name_ar', 'image']);

    $children->each(function ($item) {
        $item->image_url = $item->image 
            ? asset('storage/' . $item->image) 
            : 'https://placehold.co/100x100?text=' . urlencode($item->name_ar);
    });

    return response()->json($children);
}

    public function exportExcel()
    {
        $items = PrimaryCategory::withCount('products')->with('parent')->get();
        $data = $items->map(function ($item) {
            return [
                $item->name_ar,
                $item->name_en ?? '-',
                $item->parent?->name_ar ?? '-',
                $item->products_count,
            ];
        })->toArray();

        return Excel::download(new PrimaryCategoriesExport($data), 'primary-categories.xlsx');
    }
}
