<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WishlistController extends Controller
{
    /**
     * Get user's wishlist
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $favorites = $user->favorites()
            ->with('product')
            ->paginate(20);

        $items = $favorites->map(fn($fav) => $this->formatProduct($fav->product, true));

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'total' => $favorites->total(),
                'current_page' => $favorites->current_page(),
                'per_page' => $favorites->per_page(),
                'last_page' => $favorites->last_page(),
            ]
        ]);
    }

    /**
     * Toggle product in wishlist
     */
    public function toggle(Request $request, $productId)
    {
        try {
            $user = $request->user();

            // Verify product exists and is active
            $product = Product::where('id', $productId)
                ->where('is_active', true)
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'المنتج غير متاح',
                ], 404);
            }

            $favorite = Favorite::where('user_id', $user->id)
                ->where('product_id', $productId)
                ->first();

            if ($favorite) {
                $favorite->delete();
                $isFavorited = false;
                $message = 'تم إزالة المنتج من قائمة الرغبات';
            } else {
                Favorite::create([
                    'user_id' => $user->id,
                    'product_id' => $productId,
                ]);
                $isFavorited = true;
                $message = 'تم إضافة المنتج إلى قائمة الرغبات';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'is_favorited' => $isFavorited,
                    'count' => $user->favorites()->count(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Add product to wishlist
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
            ]);

            $user = $request->user();

            // Verify product exists and is active
            $product = Product::where('id', $validated['product_id'])
                ->where('is_active', true)
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'المنتج غير متاح',
                ], 404);
            }

            // Check if already favorited
            if ($user->hasFavorited($product)) {
                return response()->json([
                    'success' => false,
                    'message' => 'المنتج موجود بالفعل في قائمة الرغبات',
                ], 422);
            }

            Favorite::create([
                'user_id' => $user->id,
                'product_id' => $validated['product_id'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة المنتج إلى قائمة الرغبات',
                'data' => [
                    'count' => $user->favorites()->count(),
                ]
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في البيانات المدخلة',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Remove product from wishlist
     */
    public function destroy(Request $request, $productId)
    {
        try {
            $user = $request->user();

            $favorite = Favorite::where('user_id', $user->id)
                ->where('product_id', $productId)
                ->first();

            if (!$favorite) {
                return response()->json([
                    'success' => false,
                    'message' => 'المنتج غير موجود في قائمة الرغبات',
                ], 404);
            }

            $favorite->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم إزالة المنتج من قائمة الرغبات',
                'data' => [
                    'count' => $user->favorites()->count(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get wishlist count
     */
    public function count(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'success' => true,
            'data' => [
                'count' => $user->favorites()->count(),
            ]
        ]);
    }

    /**
     * Check if product is favorited
     */
    public function isFavorited(Request $request, $productId)
    {
        try {
            $user = $request->user();

            $isFavorited = $user->hasFavorited(Product::find($productId));

            return response()->json([
                'success' => true,
                'data' => [
                    'is_favorited' => $isFavorited,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Format product for response
     */
    private function formatProduct($product, $isFavorited = false)
    {
        return [
            'id' => $product->id,
            'name' => $product->name_translated,
            'description' => $product->description_translated,
            'price' => (float) $product->price,
            'sale_price' => $product->sale_price ? (float) $product->sale_price : null,
            'current_price' => (float) $product->getCurrentPrice(),
            'is_on_sale' => $product->isOnSale(),
            'stock_quantity' => $product->stock_quantity,
            'rating' => (float) ($product->getAverageRating() ?? 0),
            'reviews_count' => $product->getReviewsCount(),
            'image' => $product->images->first()?->image_path,
            'is_favorited' => $isFavorited,
        ];
    }
}
