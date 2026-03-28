<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CategoriesExport;

class CategoryController extends Controller
{
    /**
     * مفاتيح الكاش التي يُستفاد منها بالواجهة (Navbar/Home/Filters ...)
     * عدّلها لو عندك مفاتيح أخرى تعتمدها الواجهة.
     */
    protected array $cacheKeys = [
        'global_categories',
        'navbar_categories',
        'home_categories',
        'filters_categories',
    ];

    public function __construct()
    {
        $permissionMiddleware = \Spatie\Permission\Middleware\PermissionMiddleware::class;
        $this->middleware($permissionMiddleware . ':view-categories', ['only' => ['index']]);
        $this->middleware($permissionMiddleware . ':create-categories', ['only' => ['create', 'store']]);
        $this->middleware($permissionMiddleware . ':edit-categories', ['only' => ['edit', 'update']]);
        $this->middleware($permissionMiddleware . ':delete-categories', ['only' => ['destroy']]);
    }

    /**
     * عرض الأقسام الجذرية مع الأبناء وعدّاد المنتجات + البحث + التصفح.
     */
    public function index(Request $request)
    {
        $q = trim((string)$request->get('q', ''));
        $allowedSizes = [5, 10, 25, 50, 100];
        $perPage = (int) $request->get('per_page', 5);
        if (!in_array($perPage, $allowedSizes, true)) {
            $perPage = 5;
        }

        $query = Category::query()
            ->whereNull('parent_id')
            ->with([
                'children' => function ($q2) {
                    $q2->withCount('products');
                },
            ])
            ->withCount(['products as total_products_count']);

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('name_ar', 'like', "%{$q}%")
                    ->orWhere('name_en', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%")
                    ->orWhere('id', $q)
                    ->orWhereHas('children', function ($c) use ($q) {
                        $c->where('name_ar', 'like', "%{$q}%")
                          ->orWhere('name_en', 'like', "%{$q}%")
                          ->orWhere('slug', 'like', "%{$q}%")
                          ->orWhere('id', $q);
                    });
            });
        }

        $categories = $query->latest()->paginate($perPage)->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $parentCategories = Category::all();
        return view('admin.categories.create', compact('parentCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_ar'   => 'required|string|max:255|unique:categories,name_ar',
            'name_en'   => 'nullable|string|max:255',
            'image'     => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $slug = $this->generateUniqueSlug($request->name_en ?: $request->name_ar);

        $path = $request->file('image')->store('categories', 'public');

        Category::create([
            'name_ar'   => $request->name_ar,
            'name_en'   => $request->name_en,
            'slug'      => $slug,
            'image'     => $path,
            'parent_id' => $request->parent_id,
        ]);

        $this->flushCategoryCaches();

        return redirect()->route('admin.categories.index')->with('success', 'تم إنشاء القسم بنجاح.');
    }

    public function edit(Category $category)
    {
        $parentCategories = Category::where('id', '!=', $category->id)->get();
        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name_ar'   => 'required|string|max:255|unique:categories,name_ar,' . $category->id,
            'name_en'   => 'nullable|string|max:255',
            'image'     => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        if ($request->filled('parent_id') && (int)$request->parent_id === (int)$category->id) {
            return back()->withErrors(['parent_id' => 'لا يمكن جعل القسم أبًا لنفسه.'])->withInput();
        }

        if ($request->filled('parent_id') && $this->isDescendantOf((int)$request->parent_id, $category->id)) {
            return back()->withErrors(['parent_id' => 'لا يمكن نقل القسم تحت أحد أبنائه.'])->withInput();
        }

        $data = [
            'name_ar'   => $request->name_ar,
            'name_en'   => $request->name_en,
            'parent_id' => $request->parent_id,
        ];

        if ($category->name_ar !== $request->name_ar) {
            $data['slug'] = $this->generateUniqueSlug($request->name_ar, $category->id);
        }

        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);

        $this->flushCategoryCaches();

        return redirect()->route('admin.categories.index')->with('success', 'تم تحديث القسم بنجاح.');
    }

    public function destroy(Category $category)
    {
        // تم النقل للحذف النهائي (forceDelete) للحفاظ على الصورة في سلة المحذوفات
        $category->delete();

        $this->flushCategoryCaches();

        return redirect()->route('admin.categories.index')->with('success', 'تم نقل القسم إلى سلة المحذوفات.');
    }

    public function trash(Request $request)
    {
        $q = trim((string)$request->get('q', ''));
        $allowedSizes = [5, 10, 25, 50, 100];
        $perPage = (int) $request->get('per_page', 5);
        if (!in_array($perPage, $allowedSizes, true)) {
            $perPage = 5;
        }

        $query = Category::onlyTrashed();

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('name_ar', 'like', "%{$q}%")
                    ->orWhere('name_en', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%")
                    ->orWhere('id', $q);
            });
        }

        $categories = $query->latest('deleted_at')->paginate($perPage)->withQueryString();

        return view('admin.categories.trash', compact('categories'));
    }

    public function restore($id)
    {
        $category = Category::onlyTrashed()->findOrFail($id);
        $category->restore();
        
        $this->flushCategoryCaches();
        
        return redirect()->back()->with('success', 'تم استرجاع القسم بنجاح.');
    }

    public function forceDelete($id)
    {
        $category = Category::onlyTrashed()->findOrFail($id);
        
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->forceDelete();
        
        $this->flushCategoryCaches();

        return redirect()->back()->with('success', 'تم حذف القسم نهائيًا.');
    }

    protected function flushCategoryCaches(): void
    {
        foreach ($this->cacheKeys as $key) {
            Cache::forget($key);
        }
    }

    protected function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name, '-', 'ar');
        if ($base === '') {
            $base = Str::slug($name) ?: 'category';
        }

        $slug = $base;
        $i = 2;

        while (
            Category::where('slug', $slug)
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    protected function isDescendantOf(int $candidateParentId, int $categoryId): bool
    {
        $current = Category::find($candidateParentId);
        while ($current && $current->parent_id) {
            if ((int)$current->parent_id === $categoryId) {
                return true;
            }
            $current = Category::find($current->parent_id);
        }
        return false;
    }

    public function exportExcel()
    {
        $categories = Category::withCount('products')->with('parent')->get();
        $data = $categories->map(function ($c) {
            return [
                $c->name_ar,
                $c->name_en ?? '-',
                $c->slug ?? '-',
                $c->parent?->name_ar ?? '-',
                $c->products_count,
            ];
        })->toArray();

        return Excel::download(new CategoriesExport($data), 'categories.xlsx');
    }
}
