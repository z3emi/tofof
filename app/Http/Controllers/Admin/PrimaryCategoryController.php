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
        [$sortBy, $sortDir] = \App\Support\Sort::resolve($request, $allowedSorts, 'sort_order', 'asc');

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
        if ($data['sort_order'] === 0) {
            $data['sort_order'] = $this->nextSortOrder($data['parent_id'] ?? null);
        }

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
        $oldParentId = $primary_category->parent_id ? (int) $primary_category->parent_id : null;

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
        if ($data['sort_order'] === 0) {
            $data['sort_order'] = $this->nextSortOrder($data['parent_id'] ?? null, $primary_category->id);
        }

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

        $newParentId = isset($data['parent_id']) && $data['parent_id'] !== null ? (int) $data['parent_id'] : null;
        $this->normalizeSortOrders($newParentId);
        if ($newParentId !== $oldParentId) {
            $this->normalizeSortOrders($oldParentId);
        }

        return redirect()->route('admin.primary-categories.index')->with('success', 'تم تحديث الفئة بنجاح.');
    }

    public function move(Request $request, PrimaryCategory $primary_category, string $direction)
    {
        abort_unless(in_array($direction, ['up', 'down'], true), 404);

        $parentId = $primary_category->parent_id ? (int) $primary_category->parent_id : null;

        $siblings = PrimaryCategory::query()
            ->where('parent_id', $parentId)
            ->ordered()
            ->get()
            ->values();

        $currentIndex = $siblings->search(fn (PrimaryCategory $item) => $item->is($primary_category));

        if ($currentIndex === false) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => 'تعذر العثور على العنصر.'], 422);
            }
            return redirect()->route('admin.primary-categories.index');
        }

        $swapIndex = $direction === 'up' ? $currentIndex - 1 : $currentIndex + 1;

        if (! isset($siblings[$swapIndex])) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => 'لا يمكن التحريك بهذا الاتجاه.'], 422);
            }
            return redirect()->route('admin.primary-categories.index');
        }

        $swapItem = $siblings[$swapIndex];
        $currentSortOrder = (int) $primary_category->sort_order;

        $primary_category->updateQuietly(['sort_order' => (int) $swapItem->sort_order]);
        $swapItem->updateQuietly(['sort_order' => $currentSortOrder]);

        $this->normalizeSortOrders($parentId);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'تم تحديث ترتيب الفئة.',
                'moved_id' => $primary_category->id,
                'swapped_id' => $swapItem->id,
                'direction' => $direction,
            ]);
        }

        return redirect()
            ->route('admin.primary-categories.index')
            ->with('success', 'تم تحديث ترتيب الفئة.');
    }

    public function reorder(Request $request)
    {
        if ($request->filled('order_ids')) {
            $data = $request->validate([
                'order_ids' => ['required', 'array', 'min:1'],
                'order_ids.*' => ['integer', 'distinct', 'exists:primary_categories,id'],
            ]);

            $orderedIds = collect($data['order_ids'])->map(fn ($id) => (int) $id)->values()->all();
            $first = PrimaryCategory::findOrFail($orderedIds[0]);
            $parentId = $first->parent_id ? (int) $first->parent_id : null;

            $countInGroup = PrimaryCategory::query()->where('parent_id', $parentId)->count();
            if ($countInGroup !== count($orderedIds)) {
                return response()->json(['ok' => false, 'message' => 'عدد العناصر المرسلة لا يطابق المجموعة.'], 422);
            }

            $items = PrimaryCategory::query()
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

            return response()->json([
                'ok' => true,
                'message' => 'تم تحديث الترتيب بنجاح.',
                'order_ids' => $orderedIds,
            ]);
        }

        $data = $request->validate([
            'moved_id' => ['required', 'integer', 'exists:primary_categories,id'],
            'target_id' => ['required', 'integer', 'exists:primary_categories,id', 'different:moved_id'],
            'position' => ['required', 'in:before,after'],
        ]);

        $moved = PrimaryCategory::findOrFail((int) $data['moved_id']);
        $target = PrimaryCategory::findOrFail((int) $data['target_id']);

        $movedParentId = $moved->parent_id ? (int) $moved->parent_id : null;
        $targetParentId = $target->parent_id ? (int) $target->parent_id : null;

        if ($movedParentId !== $targetParentId) {
            return response()->json(['ok' => false, 'message' => 'السحب مسموح فقط ضمن نفس المستوى.'], 422);
        }

        $siblings = PrimaryCategory::query()
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
            /** @var PrimaryCategory|null $item */
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

        return response()->json([
            'ok' => true,
            'message' => 'تم تحديث الترتيب بنجاح.',
            'order_ids' => $orderedIds,
        ]);
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

    protected function nextSortOrder(?int $parentId, ?int $ignoreId = null): int
    {
        return (int) PrimaryCategory::query()
            ->where('parent_id', $parentId)
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->max('sort_order') + 1;
    }

    protected function normalizeSortOrders(?int $parentId): void
    {
        PrimaryCategory::query()
            ->where('parent_id', $parentId)
            ->ordered()
            ->get()
            ->values()
            ->each(function (PrimaryCategory $item, int $index) {
                $targetOrder = $index + 1;

                if ((int) $item->sort_order !== $targetOrder) {
                    $item->updateQuietly(['sort_order' => $targetOrder]);
                }
            });
    }
}
