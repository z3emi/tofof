<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::query()
            ->where('is_active', true)
            ->with(['firstImage:id,product_id,image_path', 'category:id,name_ar,name_en,slug']);

        if ($request->filled('q')) {
            $keyword = trim((string) $request->input('q'));
            $query->where(function (Builder $q) use ($keyword) {
                $q->where('name_ar', 'like', "%{$keyword}%")
                    ->orWhere('name_en', 'like', "%{$keyword}%")
                    ->orWhere('description_ar', 'like', "%{$keyword}%")
                    ->orWhere('description_en', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', (int) $request->input('category_id'));
        }

        $query->latest();

        $perPage = (int) $request->input('per_page', 20);
        $perPage = max(1, min($perPage, 60));

        $products = $query->paginate($perPage)->appends($request->query());

        return response()->json([
            'success' => true,
            'message' => __('تم جلب المنتجات بنجاح.'),
            'data' => $products->through(function (Product $product) {
                $imagePath = $product->firstImage?->image_path;

                return [
                    'id' => $product->id,
                    'name_ar' => $product->name_ar,
                    'name_en' => $product->name_en,
                    'name' => $product->name_translated,
                    'price' => (float) $product->price,
                    'sale_price' => $product->sale_price !== null ? (float) $product->sale_price : null,
                    'current_price' => (float) $product->current_price,
                    'stock_quantity' => (int) $product->stock_quantity,
                    'sku' => $product->sku,
                    'image_url' => $imagePath ? asset('storage/' . ltrim($imagePath, '/')) : null,
                    'category' => $product->category ? [
                        'id' => $product->category->id,
                        'name_ar' => $product->category->name_ar,
                        'name_en' => $product->category->name_en,
                        'slug' => $product->category->slug,
                    ] : null,
                ];
            })->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ], Response::HTTP_OK);
    }
}
