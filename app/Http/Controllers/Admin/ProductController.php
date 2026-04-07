<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category; // الأقسام القديمة
use App\Models\ProductImage;
use App\Models\PrimaryCategory; // 👈 الفئة الجديدة (النسخة الثانية)
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use App\Traits\HandlesImageUploads;
use App\Support\RepairsPrimaryKeyAutoIncrement;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductsExport;
use App\Models\ProductReview;
use Throwable;

class ProductController extends Controller
{
    use HandlesImageUploads;
    /**
     * Apply permission middleware to protect controller actions.
     */
    public function __construct()
    {
        $permissionMiddleware = \Spatie\Permission\Middleware\PermissionMiddleware::class;

        $this->middleware($permissionMiddleware . ':view-products', ['only' => ['index', 'show']]);
        $this->middleware($permissionMiddleware . ':create-products', ['only' => ['create', 'store']]);
        $this->middleware($permissionMiddleware . ':edit-products', ['only' => ['edit', 'update', 'toggleStatus', 'updateStock']]);
        $this->middleware($permissionMiddleware . ':delete-products', ['only' => ['destroy', 'destroyImage']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        // تحميل الصور + القسم + الفئة الثانية (مع الأب) — إضافات بدون حذف
        $query = Product::with('firstImage')
            ->with([
                'category',
                'primaryCategories.parent',
            ]);

        // بحث بالاسم/السكيو
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // فلترة حسب القسم القديم (one-to-many)
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // فلترة حسب الفئة الجديدة (many-to-many)
        if ($request->filled('pc')) { // pc = primary_category_id
            $pcId = (int) $request->pc;
            $query->whereHas('primaryCategories', function ($q) use ($pcId) {
                $q->where('primary_category_id', $pcId);
            });
        }

        // حالة التفعيل
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $allowedSorts = ['id', 'name_ar', 'price', 'sale_price', 'available_quantity', 'is_active', 'created_at'];
        [$sortBy, $sortDir] = \App\Support\Sort::resolve($request, $allowedSorts, 'id');

        $products = $query->orderBy($sortBy, $sortDir)->paginate($perPage)->withQueryString();

        // للفلترة في الواجهة (اختياري)
        $categories = Category::all();
        $primaryCategories = PrimaryCategory::active()->ordered()->get();

        return view('admin.products.index', compact('products', 'categories', 'primaryCategories', 'sortBy', 'sortDir', 'allowedSorts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all(); // القديمة
        $primaryCategories = PrimaryCategory::active()->ordered()->get(); // الجديدة
        return view('admin.products.create', compact('categories', 'primaryCategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_id'     => 'required|integer|exists:categories,id',
            'name_ar'        => 'required|string|max:255',
            'name_en'        => 'nullable|string|max:255',
            'description_ar' => 'required|string',
            'description_en' => 'nullable|string',
            'sku'            => 'required|string|max:255|unique:products,sku',
            'price'          => 'required|numeric|min:0',
            'sale_price'     => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'sale_starts_at' => 'nullable|date',
            'sale_ends_at'   => 'nullable|date|after_or_equal:sale_starts_at',
            'images'         => 'required|array',
            'images.*'       => 'image|mimes:jpeg,png,jpg,gif,svg,webp',
            'new_images_position' => 'nullable|in:first,middle,last',

            // 👇 تدعم الطريقتين: متعدد أو واحدة
            'primary_categories'    => 'nullable|array',
            'primary_categories.*'  => 'integer|exists:primary_categories,id',
            'primary_category_id'   => 'nullable|integer|exists:primary_categories,id',
            'primary_category_id_fallback' => 'nullable|integer|exists:primary_categories,id',

            'product_options' => 'nullable|array',
            'product_options.*.name_ar' => 'nullable|string|max:255',
            'product_options.*.name_en' => 'nullable|string|max:255',
            'product_options.*.is_required' => 'nullable|boolean',
            'product_options.*.values' => 'nullable|array',
            'product_options.*.values.*.value_ar' => 'nullable|string|max:255',
            'product_options.*.values.*.value_en' => 'nullable|string|max:255',
            'product_options.*.values.*.client_key' => 'nullable|string|max:100',

            'combination_definitions' => 'nullable|array',
            'combination_definitions.*' => 'nullable|string',
            'combination_existing_image_ids' => 'nullable|array',
            'combination_existing_image_ids.*' => 'nullable|integer|exists:product_images,id',
        ]);

        DB::beginTransaction();
        try {
            // احفظ فقط أعمدة جدول products لتجنب إدخال حقول النموذج المساعدة مثل _token و product_options.
            $data = $request->only([
                'category_id',
                'name_ar',
                'name_en',
                'description_ar',
                'description_en',
                'sku',
                'price',
                'sale_price',
                'sale_starts_at',
                'sale_ends_at',
                'stock_quantity',
            ]);
            $data['is_active'] = $request->boolean('is_active');
            $product = $this->createProductWithRepair($data);

            // ربط الفئات الجديدة (يدعم primary_categories[] أو primary_category_id الواحدة)
            $pcIds = $request->input('primary_categories', []);
            $singlePrimaryId = $this->resolveSinglePrimaryCategoryId($request);
            if (empty($pcIds) && !empty($singlePrimaryId)) {
                $pcIds = [$singlePrimaryId];
            }
            $pcIds = array_values(array_unique(array_filter(array_map('intval', (array) $pcIds))));
            if (!empty($pcIds)) {
                $product->primaryCategories()->sync($pcIds);
            }

            // صور
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $imageFile) {
                    $path = $this->uploadAndConvertImage($imageFile, 'products');
                    $this->createProductImageWithRepair($product, $path);
                }
            }

            $this->reorderProductImages($product);

            $this->syncProductOptionsAndCombinations($product, $request);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'An error occurred: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('admin.products.index')->with('success', 'تم إضافة المنتج وصوره بنجاح.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $product->load([
            'images',
            'options.values',
            'optionCombinations.images',
        ]);

        $categories = Category::query()->ordered()->get(); // القديمة
        if ($product->category_id && !$categories->contains('id', $product->category_id)) {
            $selectedCategory = Category::withTrashed()->find($product->category_id);
            if ($selectedCategory) {
                $categories->prepend($selectedCategory);
            }
        }
        $primaryCategories = PrimaryCategory::active()->ordered()->get(); // الجديدة
        // selectedPrimary ينقره بالـ Blade عبر $product->primaryCategories()->pluck('id')
        return view('admin.products.edit', compact('product', 'categories', 'primaryCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'category_id'     => 'required|integer|exists:categories,id',
            'name_ar'        => 'required|string|max:255',
            'name_en'        => 'nullable|string|max:255',
            'description_ar' => 'required|string',
            'description_en' => 'nullable|string',
            'sku'            => 'required|string|max:255|unique:products,sku,' . $product->id,
            'price'          => 'required|numeric|min:0',
            'sale_price'     => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'sale_starts_at' => 'nullable|date',
            'sale_ends_at'   => 'nullable|date|after_or_equal:sale_starts_at',
            'images'         => 'nullable|array',
            'images.*'       => 'image|mimes:jpeg,png,jpg,gif,svg,webp',
            'image_order'    => 'nullable|array',
            'image_order.*'  => 'nullable|integer',
            'new_images_position' => 'nullable|in:first,middle,last',

            // 👇 تدعم الطريقتين: متعدد أو واحدة
            'primary_categories'    => 'nullable|array',
            'primary_categories.*'  => 'integer|exists:primary_categories,id',
            'primary_category_id'   => 'nullable|integer|exists:primary_categories,id',
            'primary_category_id_fallback' => 'nullable|integer|exists:primary_categories,id',

            'product_options' => 'nullable|array',
            'product_options.*.name_ar' => 'nullable|string|max:255',
            'product_options.*.name_en' => 'nullable|string|max:255',
            'product_options.*.is_required' => 'nullable|boolean',
            'product_options.*.values' => 'nullable|array',
            'product_options.*.values.*.value_ar' => 'nullable|string|max:255',
            'product_options.*.values.*.value_en' => 'nullable|string|max:255',
            'product_options.*.values.*.client_key' => 'nullable|string|max:100',

            'combination_definitions' => 'nullable|array',
            'combination_definitions.*' => 'nullable|string',
            'combination_existing_image_ids' => 'nullable|array',
            'combination_existing_image_ids.*' => 'nullable|integer|exists:product_images,id',
        ]);

        DB::beginTransaction();
        try {
            $data = $request->only([
                'category_id',
                'name_ar',
                'name_en',
                'description_ar',
                'description_en',
                'sku',
                'price',
                'sale_price',
                'sale_starts_at',
                'sale_ends_at',
                'stock_quantity',
            ]);
            $data['is_active'] = $request->boolean('is_active');
            $product->update($data);

            // تحديث ربط الفئات الجديدة (يدعم الطريقتين)
            $pcIds = $request->input('primary_categories', []);
            $singlePrimaryId = $this->resolveSinglePrimaryCategoryId($request);
            if (empty($pcIds) && !empty($singlePrimaryId)) {
                $pcIds = [$singlePrimaryId];
            }
            $pcIds = array_values(array_unique(array_filter(array_map('intval', (array) $pcIds))));
            $product->primaryCategories()->sync($pcIds);

            $this->applyRequestedImageOrder($product, (array) $request->input('image_order', []));

            // صور جديدة (إن وُجدت)
            $newlyAddedImageIds = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $imageFile) {
                    $path = $this->uploadAndConvertImage($imageFile, 'products');
                    $image = $this->createProductImageWithRepair($product, $path);
                    if ($image) {
                        $newlyAddedImageIds[] = (int) $image->id;
                    }
                }
            }

            if (!empty($newlyAddedImageIds)) {
                $this->placeNewImages($product, $newlyAddedImageIds, (string) $request->input('new_images_position', 'last'));
            }

            $this->reorderProductImages($product);

            $this->syncProductOptionsAndCombinations($product, $request);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'An error occurred: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('admin.products.index')->with('success', 'تم تحديث المنتج بنجاح.');
    }

    /**
     * Toggle the active status of the specified resource.
     */
    public function toggleStatus(Product $product, Request $request)
    {
        if ($request->has('status')) {
            $product->update(['is_active' => $request->status == '1']);
        } else {
            $product->update(['is_active' => !$product->is_active]);
        }
        
        $message = $product->is_active ? 'تم تفعيل المنتج بنجاح.' : 'تم إيقاف المنتج بنجاح.';
        return redirect()->back()->with('success', $message);
    }

    public function destroy(Product $product)
    {
        // حذف ناعم
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'تم نقل المنتج إلى سلة المحذوفات.');
    }

    public function trash(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $products = Product::onlyTrashed()
            ->with(['category', 'primaryCategories.parent'])
            ->latest('deleted_at')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.products.trash', compact('products'));
    }

    public function restore($id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        $product->restore();
        
        return redirect()->back()->with('success', 'تم استرجاع المنتج بنجاح.');
    }

    public function forceDelete($id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);

        $product->loadMissing('optionCombinations.images');
        
        // حذف الصور من التخزين
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->image_path);
        }

        foreach ($product->optionCombinations as $combination) {
            foreach ($combination->images as $comboImage) {
                if (!empty($comboImage->image_path)) {
                    Storage::disk('public')->delete($comboImage->image_path);
                }
            }
        }

        // فك الارتباط من الفئات الجديدة
        $product->primaryCategories()->detach();

        $product->forceDelete();

        return redirect()->back()->with('success', 'تم حذف المنتج وصوره نهائيًا.');
    }

    public function destroyImage(ProductImage $image)
    {
        $this->authorize('edit-products');

        if (Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }

        $image->delete();

        return response()->json(['success' => true, 'message' => 'تم حذف الصورة بنجاح.']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->loadMissing([
            'images',
            'category',
            'primaryCategories.parent',
            'options.values',
            'optionCombinations.images',
        ]);

        $reviews = ProductReview::query()
            ->with('user')
            ->where('product_id', $product->id)
            ->latest()
            ->get();

        return view('admin.products.show', compact('product', 'reviews'));
    }

    /**
     * Update product stock quantity directly.
     */
    public function updateStock(Request $request, Product $product)
    {
        $this->authorize('edit-products');

        $request->validate([
            'stock_quantity' => 'required|integer|min:0'
        ]);

        $product->update([
            'stock_quantity' => $request->stock_quantity
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث الكمية بنجاح.',
                'stock_quantity' => $product->stock_quantity
            ]);
        }

        return redirect()->back()->with('success', 'تم تحديث الكمية بنجاح.');
    }

    public function exportExcel()
    {
        $products = Product::with('category')->get();
        $data = $products->map(function ($p) {
            return [
                $p->name_ar,
                $p->sku ?? '-',
                (int) $p->stock_quantity,
                $p->price,
                $p->category?->name_ar ?? '-',
                $p->is_active ? 'مفعّل' : 'معطّل',
            ];
        })->toArray();

        return Excel::download(new ProductsExport($data), 'products.xlsx');
    }

    private function createProductWithRepair(array $attributes): Product
    {
        try {
            return Product::create($attributes);
        } catch (QueryException $exception) {
            if (! RepairsPrimaryKeyAutoIncrement::isMissingAutoIncrementError($exception, 'products')) {
                throw $exception;
            }

            if (DB::transactionLevel() > 0) {
                return $this->createProductWithExplicitId($attributes);
            }

            RepairsPrimaryKeyAutoIncrement::ensure('products');

            try {
                return Product::create($attributes);
            } catch (QueryException $retryException) {
                if (! RepairsPrimaryKeyAutoIncrement::isMissingAutoIncrementError($retryException, 'products')) {
                    throw $retryException;
                }

                // Final safety net for imported schemas that still miss AUTO_INCREMENT.
                $nextId = ((int) DB::table('products')->max('id')) + 1;

                return $this->createModelWithExplicitId(Product::class, array_merge($attributes, ['id' => $nextId]));
            }
        }
    }

    private function createProductWithExplicitId(array $attributes): Product
    {
        $nextId = ((int) DB::table('products')->max('id')) + 1;

        return $this->createModelWithExplicitId(Product::class, array_merge($attributes, ['id' => $nextId]));
    }

    /**
     * Resolve single primary category from the two form inputs.
     *
     * - `primary_category_id` comes from JS (child or parent)
     * - `primary_category_id_fallback` comes directly from parent select
     *
     * If both exist and differ, keep child only when it actually belongs to selected parent;
     * otherwise trust fallback to avoid stale hidden input issues.
     */
    private function resolveSinglePrimaryCategoryId(Request $request): ?int
    {
        $primaryId = $request->input('primary_category_id');
        $fallbackId = $request->input('primary_category_id_fallback');

        $primaryId = filled($primaryId) ? (int) $primaryId : null;
        $fallbackId = filled($fallbackId) ? (int) $fallbackId : null;

        if ($primaryId === null && $fallbackId === null) {
            return null;
        }

        if ($primaryId === null) {
            return $fallbackId;
        }

        if ($fallbackId === null || $primaryId === $fallbackId) {
            return $primaryId;
        }

        $primary = PrimaryCategory::find($primaryId);
        if ($primary && (int) ($primary->parent_id ?? 0) === $fallbackId) {
            return $primaryId;
        }

        return $fallbackId;
    }

    private function syncProductOptionsAndCombinations(Product $product, Request $request): void
    {
        $this->deleteExistingCombinationUploads($product);
        $product->optionCombinations()->delete();
        $product->options()->delete();

        $normalizedOptions = $this->normalizeProductOptions($request->input('product_options', []));
        if (empty($normalizedOptions)) {
            return;
        }

        $valueIdByClientKey = [];
        foreach ($normalizedOptions as $optionIndex => $optionPayload) {
            $option = $this->createProductOptionWithRepair($product, [
                'name_ar' => $optionPayload['name_ar'],
                'name_en' => $optionPayload['name_en'],
                'sort_order' => $optionIndex,
                'is_required' => $optionPayload['is_required'],
            ]);

            foreach ($optionPayload['values'] as $valueIndex => $valuePayload) {
                $value = $this->createProductOptionValueWithRepair($option, [
                    'value_ar' => $valuePayload['value_ar'],
                    'value_en' => $valuePayload['value_en'],
                    'sort_order' => $valueIndex,
                ]);

                $valueIdByClientKey[$valuePayload['client_key']] = (int) $value->id;
            }
        }

        $combinationDefinitions = (array) $request->input('combination_definitions', []);
        $combinationExistingImageIds = (array) $request->input('combination_existing_image_ids', []);

        foreach ($combinationDefinitions as $rowKey => $definitionJson) {
            if (!is_string($definitionJson) || trim($definitionJson) === '') {
                continue;
            }

            $definition = json_decode($definitionJson, true);
            if (!is_array($definition)) {
                continue;
            }

            $clientKeys = array_values(array_filter((array) ($definition['client_keys'] ?? []), fn ($v) => is_string($v) && $v !== ''));
            if (empty($clientKeys)) {
                continue;
            }

            $valueIds = [];
            foreach ($clientKeys as $clientKey) {
                if (!isset($valueIdByClientKey[$clientKey])) {
                    continue 2;
                }
                $valueIds[] = $valueIdByClientKey[$clientKey];
            }

            sort($valueIds);
            $combinationKey = implode('-', $valueIds);
            if ($combinationKey === '') {
                continue;
            }

            $combination = $this->createProductOptionCombinationWithRepair($product, [
                'combination_key' => $combinationKey,
                'option_value_ids' => $valueIds,
            ]);

            $existingImageId = isset($combinationExistingImageIds[$rowKey])
                ? (int) $combinationExistingImageIds[$rowKey]
                : 0;

            $imagePayload = $this->prepareCombinationImagePayload($product, $existingImageId);
            if (!empty($imagePayload)) {
                $this->createProductOptionCombinationImageWithRepair($combination, $imagePayload);
            }
        }
    }

    private function createProductOptionWithRepair(Product $product, array $attributes): \App\Models\ProductOption
    {
        try {
            return $product->options()->create($attributes);
        } catch (QueryException $exception) {
            if (! RepairsPrimaryKeyAutoIncrement::isMissingAutoIncrementError($exception, 'product_options')) {
                throw $exception;
            }

            if (DB::transactionLevel() > 0) {
                $nextId = ((int) DB::table('product_options')->max('id')) + 1;

                return \App\Models\ProductOption::unguarded(function () use ($product, $attributes, $nextId) {
                    if ((int) $product->id <= 0) {
                        throw new \RuntimeException('Invalid product id while creating product option.');
                    }

                    return $this->createModelWithExplicitId(\App\Models\ProductOption::class, array_merge($attributes, [
                        'id' => $nextId,
                        'product_id' => (int) $product->id,
                    ]));
                });
            }

            RepairsPrimaryKeyAutoIncrement::ensure('product_options');
            return $product->options()->create($attributes);
        }
    }

    private function createProductOptionValueWithRepair(\App\Models\ProductOption $option, array $attributes): \App\Models\ProductOptionValue
    {
        try {
            return $option->values()->create($attributes);
        } catch (QueryException $exception) {
            if (! RepairsPrimaryKeyAutoIncrement::isMissingAutoIncrementError($exception, 'product_option_values')) {
                throw $exception;
            }

            if (DB::transactionLevel() > 0) {
                $nextId = ((int) DB::table('product_option_values')->max('id')) + 1;

                return \App\Models\ProductOptionValue::unguarded(function () use ($option, $attributes, $nextId) {
                    if ((int) $option->id <= 0) {
                        throw new \RuntimeException('Invalid product option id while creating option value.');
                    }

                    return $this->createModelWithExplicitId(\App\Models\ProductOptionValue::class, array_merge($attributes, [
                        'id' => $nextId,
                        'product_option_id' => (int) $option->id,
                    ]));
                });
            }

            RepairsPrimaryKeyAutoIncrement::ensure('product_option_values');
            return $option->values()->create($attributes);
        }
    }

    private function createProductOptionCombinationWithRepair(Product $product, array $attributes): \App\Models\ProductOptionCombination
    {
        try {
            return $product->optionCombinations()->create($attributes);
        } catch (QueryException $exception) {
            if (! RepairsPrimaryKeyAutoIncrement::isMissingAutoIncrementError($exception, 'product_option_combinations')) {
                throw $exception;
            }

            if (DB::transactionLevel() > 0) {
                $nextId = ((int) DB::table('product_option_combinations')->max('id')) + 1;

                return \App\Models\ProductOptionCombination::unguarded(function () use ($product, $attributes, $nextId) {
                    if ((int) $product->id <= 0) {
                        throw new \RuntimeException('Invalid product id while creating option combination.');
                    }

                    return $this->createModelWithExplicitId(\App\Models\ProductOptionCombination::class, array_merge($attributes, [
                        'id' => $nextId,
                        'product_id' => (int) $product->id,
                    ]));
                });
            }

            RepairsPrimaryKeyAutoIncrement::ensure('product_option_combinations');
            return $product->optionCombinations()->create($attributes);
        }
    }

    private function createProductOptionCombinationImageWithRepair(\App\Models\ProductOptionCombination $combination, array $attributes): \App\Models\ProductOptionCombinationImage
    {
        try {
            return $combination->images()->create($attributes);
        } catch (QueryException $exception) {
            if (! RepairsPrimaryKeyAutoIncrement::isMissingAutoIncrementError($exception, 'product_option_combination_images')) {
                throw $exception;
            }

            if (DB::transactionLevel() > 0) {
                $nextId = ((int) DB::table('product_option_combination_images')->max('id')) + 1;

                return \App\Models\ProductOptionCombinationImage::unguarded(function () use ($combination, $attributes, $nextId) {
                    if ((int) $combination->id <= 0) {
                        throw new \RuntimeException('Invalid option combination id while creating combination image.');
                    }

                    return $this->createModelWithExplicitId(\App\Models\ProductOptionCombinationImage::class, array_merge($attributes, [
                        'id' => $nextId,
                        'product_option_combination_id' => (int) $combination->id,
                    ]));
                });
            }

            RepairsPrimaryKeyAutoIncrement::ensure('product_option_combination_images');
            return $combination->images()->create($attributes);
        }
    }

    private function normalizeProductOptions(array $rawOptions): array
    {
        $normalized = [];

        foreach ($rawOptions as $optionIndex => $option) {
            if (!is_array($option)) {
                continue;
            }

            $nameAr = trim((string) ($option['name_ar'] ?? ''));
            $nameEn = trim((string) ($option['name_en'] ?? ''));
            $values = [];

            foreach ((array) ($option['values'] ?? []) as $valueIndex => $value) {
                if (!is_array($value)) {
                    continue;
                }

                $valueAr = trim((string) ($value['value_ar'] ?? ''));
                $valueEn = trim((string) ($value['value_en'] ?? ''));
                if ($valueAr === '' && $valueEn === '') {
                    continue;
                }

                $clientKey = trim((string) ($value['client_key'] ?? ''));
                if ($clientKey === '') {
                    $clientKey = 'o' . $optionIndex . '_v' . $valueIndex;
                }

                $values[] = [
                    'value_ar' => $valueAr !== '' ? $valueAr : $valueEn,
                    'value_en' => $valueEn !== '' ? $valueEn : null,
                    'client_key' => $clientKey,
                ];
            }

            if ($nameAr === '' && $nameEn === '') {
                continue;
            }

            if (empty($values)) {
                continue;
            }

            $normalized[] = [
                'name_ar' => $nameAr !== '' ? $nameAr : $nameEn,
                'name_en' => $nameEn !== '' ? $nameEn : null,
                'is_required' => (bool) ($option['is_required'] ?? true),
                'values' => $values,
            ];
        }

        return $normalized;
    }

    private function prepareCombinationImagePayload(Product $product, int $existingImageId): array
    {
        if ($existingImageId > 0 && $product->images()->whereKey($existingImageId)->exists()) {
            return [
                'product_image_id' => $existingImageId,
                'image_path' => null,
            ];
        }

        return [];
    }

    private function deleteExistingCombinationUploads(Product $product): void
    {
        $product->loadMissing('optionCombinations.images');
        foreach ($product->optionCombinations as $combination) {
            foreach ($combination->images as $image) {
                if (!empty($image->image_path) && Storage::disk('public')->exists($image->image_path)) {
                    Storage::disk('public')->delete($image->image_path);
                }
            }
        }
    }

    private function createProductImageWithRepair(Product $product, string $path): ?ProductImage
    {
        $nextSortOrder = $this->nextSortOrderForProduct($product);

        try {
            return $product->images()->create([
                'image_path' => $path,
                'sort_order' => $nextSortOrder,
            ]);
        } catch (Throwable $e) {
            if (! $this->isMissingDefaultIdError($e)) {
                throw $e;
            }

            if (DB::transactionLevel() > 0) {
                $nextId = ((int) DB::table('product_images')->max('id')) + 1;

                $this->createModelWithExplicitId(\App\Models\ProductImage::class, [
                        'id' => $nextId,
                        'product_id' => (int) $product->id,
                        'image_path' => $path,
                        'sort_order' => $nextSortOrder,
                    ]);
                return ProductImage::query()->find($nextId);
            }

            Log::warning('Product image insert failed with missing default id; attempting schema repair.', [
                'database' => DB::getDatabaseName(),
                'id_column' => DB::selectOne("SHOW COLUMNS FROM `product_images` WHERE Field = 'id'"),
                'error' => $e->getMessage(),
            ]);

            $this->repairProductImagesIdColumn();
            return $product->images()->create([
                'image_path' => $path,
                'sort_order' => $nextSortOrder,
            ]);
        }
    }

    private function nextSortOrderForProduct(Product $product): int
    {
        $maxSortOrder = ProductImage::query()
            ->where('product_id', $product->id)
            ->max('sort_order');

        return ((int) $maxSortOrder) + 1;
    }

    private function applyRequestedImageOrder(Product $product, array $requestedOrder): void
    {
        Log::info('applyRequestedImageOrder called', [
            'product_id' => $product->id,
            'requestedOrder' => $requestedOrder,
            'requestedOrder_count' => count($requestedOrder),
        ]);

        if (empty($requestedOrder)) {
            Log::info('applyRequestedImageOrder: requestedOrder is empty, skipping');
            return;
        }

        $existingIds = ProductImage::query()
            ->where('product_id', $product->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $existingLookup = array_flip($existingIds);

        $orderedIds = [];
        foreach ($requestedOrder as $id) {
            $id = (int) $id;
            if ($id > 0 && isset($existingLookup[$id])) {
                $orderedIds[] = $id;
                unset($existingLookup[$id]);
            }
        }

        foreach (array_keys($existingLookup) as $leftId) {
            $orderedIds[] = (int) $leftId;
        }

        $this->persistImageOrder($product, $orderedIds);
    }

    private function placeNewImages(Product $product, array $newImageIds, string $position): void
    {
        $newImageIds = array_values(array_unique(array_filter(array_map('intval', $newImageIds), fn ($id) => $id > 0)));
        if (empty($newImageIds)) {
            return;
        }

        $allIds = ProductImage::query()
            ->where('product_id', $product->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $newLookup = array_flip($newImageIds);
        $existingIds = array_values(array_filter($allIds, fn ($id) => !isset($newLookup[$id])));

        if ($position === 'first') {
            $finalIds = array_merge($newImageIds, $existingIds);
        } elseif ($position === 'middle') {
            $insertAt = (int) floor(count($existingIds) / 2);
            $finalIds = array_merge(
                array_slice($existingIds, 0, $insertAt),
                $newImageIds,
                array_slice($existingIds, $insertAt)
            );
        } else {
            $finalIds = array_merge($existingIds, $newImageIds);
        }

        $this->persistImageOrder($product, $finalIds);
    }

    private function reorderProductImages(Product $product): void
    {
        $orderedIds = ProductImage::query()
            ->where('product_id', $product->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $this->persistImageOrder($product, $orderedIds);
    }

    private function persistImageOrder(Product $product, array $orderedIds): void
    {
        Log::info('persistImageOrder called', [
            'product_id' => $product->id,
            'orderedIds' => $orderedIds,
            'count' => count($orderedIds),
        ]);

        if (empty($orderedIds)) {
            Log::warning('persistImageOrder: orderedIds is empty');
            return;
        }

        $order = 1;
        foreach ($orderedIds as $imageId) {
            $updated = ProductImage::query()
                ->where('product_id', $product->id)
                ->where('id', (int) $imageId)
                ->update(['sort_order' => $order]);
            Log::debug('Updated image sort_order', [
                'image_id' => $imageId,
                'sort_order' => $order,
                'rows_affected' => $updated,
            ]);
            $order++;
        }

        // Verify the update
        $saved = ProductImage::query()
            ->where('product_id', $product->id)
            ->orderBy('sort_order')
            ->get(['id', 'sort_order'])
            ->toArray();
        Log::info('persistImageOrder: Final state', $saved);
    }

    private function isMissingDefaultIdError(Throwable $e): bool
    {
        $errorInfo = property_exists($e, 'errorInfo') ? (array) $e->errorInfo : [];
        $code = (int) ($errorInfo[1] ?? 0);
        $message = strtolower((string) ($errorInfo[2] ?? $e->getMessage()));

        return ($code === 1364 || str_contains($message, 'doesn\'t have a default value'))
            && (str_contains($message, 'field') || str_contains($message, 'column'))
            && str_contains($message, 'id')
            && str_contains($message, 'default value');
    }

    private function repairProductImagesIdColumn(): void
    {
        if (! Schema::hasTable('product_images') || ! Schema::hasColumn('product_images', 'id')) {
            return;
        }

        $idColumn = DB::selectOne("SHOW COLUMNS FROM `product_images` WHERE Field = 'id'");

        if (! $idColumn) {
            return;
        }

        if (($idColumn->Key ?? '') !== 'PRI') {
            $primaryKey = DB::selectOne("SHOW INDEX FROM `product_images` WHERE Key_name = 'PRIMARY'");

            if (! $primaryKey) {
                DB::statement('ALTER TABLE `product_images` ADD PRIMARY KEY (`id`)');
            }
        }

        $extra = strtolower((string) ($idColumn->Extra ?? ''));
        if (! str_contains($extra, 'auto_increment')) {
            $nextId = ((int) DB::table('product_images')->max('id')) + 1;
            DB::statement('ALTER TABLE `product_images` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
            DB::statement('ALTER TABLE `product_images` AUTO_INCREMENT = ' . max($nextId, 1));
        }
    }

    /**
     * Persist a model with explicit id without using insertGetId.
     * This avoids returning id=0 when AUTO_INCREMENT is missing on imported schemas.
     */
    private function createModelWithExplicitId(string $modelClass, array $attributes): Model
    {
        return $modelClass::unguarded(function () use ($modelClass, $attributes) {
            /** @var Model $model */
            $model = new $modelClass($attributes);
            $model->incrementing = false;
            $model->save();

            return $model;
        });
    }
}
