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
        $perPage = (int) $request->get('per_page', 10);

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

        $allowedSorts = ['id', 'name_ar', 'total_products_count', 'created_at', 'sort_order'];
        [$sortBy, $sortDir] = \App\Support\Sort::resolve($request, $allowedSorts, 'sort_order', 'asc');

        $categories = $query
            ->orderBy($sortBy, $sortDir)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.categories.index', compact('categories', 'sortBy', 'sortDir'));
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
            'sort_order' => 'nullable|integer|min:1',
            'is_active' => 'sometimes|boolean',
        ]);

        $slug = $this->generateUniqueSlug($request->name_en ?: $request->name_ar);

        $path = $request->file('image')->store('categories', 'public');

        $parentId = $request->filled('parent_id') ? (int) $request->parent_id : null;
        $sortOrder = $request->filled('sort_order')
            ? (int) $request->sort_order
            : $this->nextSortOrder($parentId);

        Category::create([
            'name_ar'   => $request->name_ar,
            'name_en'   => $request->name_en,
            'slug'      => $slug,
            'image'     => $path,
            'parent_id' => $parentId,
            'sort_order' => $sortOrder,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->normalizeSortOrders($parentId);

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
            'sort_order' => 'nullable|integer|min:1',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($request->filled('parent_id') && (int)$request->parent_id === (int)$category->id) {
            return back()->withErrors(['parent_id' => 'لا يمكن جعل القسم أبًا لنفسه.'])->withInput();
        }

        if ($request->filled('parent_id') && $this->isDescendantOf((int)$request->parent_id, $category->id)) {
            return back()->withErrors(['parent_id' => 'لا يمكن نقل القسم تحت أحد أبنائه.'])->withInput();
        }

        $newParentId = $request->filled('parent_id') ? (int) $request->parent_id : null;
        $oldParentId = $category->parent_id ? (int) $category->parent_id : null;

        $data = [
            'name_ar'   => $request->name_ar,
            'name_en'   => $request->name_en,
            'parent_id' => $newParentId,
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->filled('sort_order')) {
            $data['sort_order'] = (int) $request->sort_order;
        }

        if ($newParentId !== $oldParentId && ! $request->filled('sort_order')) {
            $data['sort_order'] = $this->nextSortOrder($newParentId, $category->id);
        }

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

        $this->normalizeSortOrders($newParentId);
        if ($newParentId !== $oldParentId) {
            $this->normalizeSortOrders($oldParentId);
        }

        $this->flushCategoryCaches();

        return redirect()->route('admin.categories.index')->with('success', 'تم تحديث القسم بنجاح.');
    }

    public function move(Request $request, Category $category, string $direction)
    {
        abort_unless(in_array($direction, ['up', 'down'], true), 404);

        $parentId = $category->parent_id ? (int) $category->parent_id : null;

        $siblings = Category::query()
            ->where('parent_id', $parentId)
            ->ordered()
            ->get()
            ->values();

        $currentIndex = $siblings->search(fn (Category $item) => $item->is($category));

        if ($currentIndex === false) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => 'تعذر العثور على العنصر.'], 422);
            }
            return redirect()->route('admin.categories.index');
        }

        $swapIndex = $direction === 'up' ? $currentIndex - 1 : $currentIndex + 1;

        if (! isset($siblings[$swapIndex])) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => 'لا يمكن التحريك بهذا الاتجاه.'], 422);
            }
            return redirect()->route('admin.categories.index');
        }

        $swapCategory = $siblings[$swapIndex];
        $currentSortOrder = (int) $category->sort_order;

        $category->updateQuietly(['sort_order' => (int) $swapCategory->sort_order]);
        $swapCategory->updateQuietly(['sort_order' => $currentSortOrder]);

        $this->normalizeSortOrders($parentId);
        $this->flushCategoryCaches();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'تم تحديث ترتيب التصنيف.',
                'moved_id' => $category->id,
                'swapped_id' => $swapCategory->id,
                'direction' => $direction,
            ]);
        }

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'تم تحديث ترتيب التصنيف.');
    }

    public function reorder(Request $request)
    {
        if ($request->filled('order_ids')) {
            $data = $request->validate([
                'order_ids' => ['required', 'array', 'min:1'],
                'order_ids.*' => ['integer', 'distinct', 'exists:categories,id'],
            ]);

            $orderedIds = collect($data['order_ids'])->map(fn ($id) => (int) $id)->values()->all();
            $first = Category::findOrFail($orderedIds[0]);
            $parentId = $first->parent_id ? (int) $first->parent_id : null;

            $countInGroup = Category::query()->where('parent_id', $parentId)->count();
            if ($countInGroup !== count($orderedIds)) {
                return response()->json(['ok' => false, 'message' => 'عدد العناصر المرسلة لا يطابق المجموعة.'], 422);
            }

            $items = Category::query()
                ->where('parent_id', $parentId)
                ->whereIn('id', $orderedIds)
                ->get()
                ->keyBy('id');

            if ($items->count() !== count($orderedIds)) {
                return response()->json(['ok' => false, 'message' => 'بعض العناصر خارج نفس المستوى.'], 422);
            }

            foreach ($orderedIds as $index => $id) {
                $item = $items->get($id);
                if (! $item) {
                    continue;
                }

                $targetOrder = $index + 1;
                if ((int) $item->sort_order !== $targetOrder) {
                    $item->updateQuietly(['sort_order' => $targetOrder]);
                }
            }

            $this->normalizeSortOrders($parentId);
            $this->flushCategoryCaches();

            return response()->json([
                'ok' => true,
                'message' => 'تم تحديث الترتيب بنجاح.',
                'order_ids' => $orderedIds,
            ]);
        }

        $data = $request->validate([
            'moved_id' => ['required', 'integer', 'exists:categories,id'],
            'target_id' => ['required', 'integer', 'exists:categories,id', 'different:moved_id'],
            'position' => ['required', 'in:before,after'],
        ]);

        $moved = Category::findOrFail((int) $data['moved_id']);
        $target = Category::findOrFail((int) $data['target_id']);

        $movedParentId = $moved->parent_id ? (int) $moved->parent_id : null;
        $targetParentId = $target->parent_id ? (int) $target->parent_id : null;

        if ($movedParentId !== $targetParentId) {
            return response()->json(['ok' => false, 'message' => 'السحب مسموح فقط ضمن نفس المستوى.'], 422);
        }

        $siblings = Category::query()
            ->where('parent_id', $movedParentId)
            ->ordered()
            ->get()
            ->values();

        $orderedIds = $siblings->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        $orderedIds = array_values(array_filter($orderedIds, fn ($id) => (int) $id !== (int) $moved->id));

        $targetIndex = array_search((int) $target->id, $orderedIds, false);
        if ($targetIndex === false) {
            return response()->json(['ok' => false, 'message' => 'تعذر تحديد موضع الهدف.'], 422);
        }

        $insertIndex = $data['position'] === 'after' ? $targetIndex + 1 : $targetIndex;
        array_splice($orderedIds, $insertIndex, 0, [(int) $moved->id]);

        $itemsById = $siblings->keyBy('id');
        foreach ($orderedIds as $index => $id) {
            /** @var Category|null $item */
            $item = $itemsById->get($id);
            if (! $item) {
                continue;
            }

            $targetOrder = $index + 1;
            if ((int) $item->sort_order !== $targetOrder) {
                $item->updateQuietly(['sort_order' => $targetOrder]);
            }
        }

        $this->normalizeSortOrders($movedParentId);
        $this->flushCategoryCaches();

        return response()->json([
            'ok' => true,
            'message' => 'تم تحديث الترتيب بنجاح.',
            'order_ids' => $orderedIds,
        ]);
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

    protected function nextSortOrder(?int $parentId, ?int $ignoreId = null): int
    {
        return (int) Category::query()
            ->where('parent_id', $parentId)
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->max('sort_order') + 1;
    }

    protected function normalizeSortOrders(?int $parentId): void
    {
        Category::query()
            ->where('parent_id', $parentId)
            ->ordered()
            ->get()
            ->values()
            ->each(function (Category $item, int $index) {
                $targetOrder = $index + 1;

                if ((int) $item->sort_order !== $targetOrder) {
                    $item->updateQuietly(['sort_order' => $targetOrder]);
                }
            });
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
    public function toggleStatus(Category $category, Request $request)
    {
        if ($request->has('status')) {
            $category->update(['is_active' => $request->status == '1']);
        } else {
            $category->update(['is_active' => !$category->is_active]);
        }
        
        $message = $category->is_active ? 'تم تفعيل البراند بنجاح.' : 'تم إيقاف البراند بنجاح.';
        return redirect()->back()->with('success', $message);
    }
}
