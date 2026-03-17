<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category; // الأقسام القديمة
use App\Models\ProductImage;
use App\Models\PrimaryCategory; // 👈 الفئة الجديدة (النسخة الثانية)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Traits\HandlesImageUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductsExport;

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
        $perPage = $request->input('per_page', 5);

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

        $products = $query->latest()->paginate($perPage)->withQueryString();

        // للفلترة في الواجهة (اختياري)
        $categories = Category::all();
        $primaryCategories = PrimaryCategory::active()->ordered()->get();

        return view('admin.products.index', compact('products', 'categories', 'primaryCategories'));
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
                    $product->images()->create(['image_path' => $path]);
                }
            }

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
                    $product->images()->create(['image_path' => $path]);
                }
            }

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
        $perPage = $request->input('per_page', 5);

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
        
        // حذف الصور من التخزين
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->image_path);
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
}
