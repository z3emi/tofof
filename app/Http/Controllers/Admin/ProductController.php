<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category; // الأقسام القديمة
use App\Models\ProductImage;
use App\Models\PrimaryCategory; // 👈 الفئة الجديدة (النسخة الثانية)
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use App\Traits\HandlesImageUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductsExport;
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
            'name_ar'        => 'required|string|max:255',
            'name_en'        => 'nullable|string|max:255',
            'description_en' => 'nullable|string',
            'sku'            => 'required|string|max:255|unique:products,sku',
            'sale_price'     => 'nullable|numeric|min:0',
            'sale_starts_at' => 'nullable|date',
            'sale_ends_at'   => 'nullable|date|after_or_equal:sale_starts_at',
            'images'         => 'required|array',
            'images.*'       => 'image|mimes:jpeg,png,jpg,gif,svg,webp',

            // 👇 تدعم الطريقتين: متعدد أو واحدة
            'primary_categories'    => 'nullable|array',
            'primary_categories.*'  => 'integer|exists:primary_categories,id',
            'primary_category_id'   => 'nullable|integer|exists:primary_categories,id',

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
            // لا ندخل primary_categories مع create
            $data = $request->except(['images', 'primary_categories', 'primary_category_id']);
            $product = Product::create($data);

            // ربط الفئات الجديدة (يدعم primary_categories[] أو primary_category_id الواحدة)
            $pcIds = $request->input('primary_categories', []);
            if (empty($pcIds) && $request->filled('primary_category_id')) {
                $pcIds = [$request->primary_category_id];
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

        $categories = Category::all(); // القديمة
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
            'name_ar'        => 'required|string|max:255',
            'name_en'        => 'nullable|string|max:255',
            'description_en' => 'nullable|string',
            'sku'            => 'required|string|max:255|unique:products,sku,' . $product->id,
            'sale_price'     => 'nullable|numeric|min:0',
            'sale_starts_at' => 'nullable|date',
            'sale_ends_at'   => 'nullable|date|after_or_equal:sale_starts_at',
            'images'         => 'nullable|array',
            'images.*'       => 'image|mimes:jpeg,png,jpg,gif,svg,webp',

            // 👇 تدعم الطريقتين: متعدد أو واحدة
            'primary_categories'    => 'nullable|array',
            'primary_categories.*'  => 'integer|exists:primary_categories,id',
            'primary_category_id'   => 'nullable|integer|exists:primary_categories,id',

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
            $data = $request->except(['images', 'primary_categories', 'primary_category_id']);
            $product->update($data);

            // تحديث ربط الفئات الجديدة (يدعم الطريقتين)
            $pcIds = $request->input('primary_categories', []);
            if (empty($pcIds) && $request->filled('primary_category_id')) {
                $pcIds = [$request->primary_category_id];
            }
            $pcIds = array_values(array_unique(array_filter(array_map('intval', (array) $pcIds))));
            $product->primaryCategories()->sync($pcIds);

            // صور جديدة (إن وُجدت)
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $imageFile) {
                    $path = $this->uploadAndConvertImage($imageFile, 'products');
                    $this->createProductImageWithRepair($product, $path);
                }
            }

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
    public function toggleStatus(Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);
        $message = $product->is_active ? 'تم تفعيل المنتج بنجاح.' : 'تم إيقاف المنتج بنجاح.';
        return redirect()->route('admin.products.index')->with('success', $message);
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
        return view('admin.products.show', compact('product'));
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
            $option = $product->options()->create([
                'name_ar' => $optionPayload['name_ar'],
                'name_en' => $optionPayload['name_en'],
                'sort_order' => $optionIndex,
                'is_required' => $optionPayload['is_required'],
            ]);

            foreach ($optionPayload['values'] as $valueIndex => $valuePayload) {
                $value = $option->values()->create([
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

            $combination = $product->optionCombinations()->create([
                'combination_key' => $combinationKey,
                'option_value_ids' => $valueIds,
            ]);

            $existingImageId = isset($combinationExistingImageIds[$rowKey])
                ? (int) $combinationExistingImageIds[$rowKey]
                : 0;

            $imagePayload = $this->prepareCombinationImagePayload($product, $existingImageId);
            if (!empty($imagePayload)) {
                $combination->images()->create($imagePayload);
            }
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

    private function createProductImageWithRepair(Product $product, string $path): void
    {
        try {
            $product->images()->create(['image_path' => $path]);
        } catch (Throwable $e) {
            if (! $this->isMissingDefaultIdError($e)) {
                throw $e;
            }

            Log::warning('Product image insert failed with missing default id; attempting schema repair.', [
                'database' => DB::getDatabaseName(),
                'id_column' => DB::selectOne("SHOW COLUMNS FROM `product_images` WHERE Field = 'id'"),
                'error' => $e->getMessage(),
            ]);

            $this->repairProductImagesIdColumn();
            $product->images()->create(['image_path' => $path]);
        }
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
}
