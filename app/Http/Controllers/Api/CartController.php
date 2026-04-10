<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\DiscountService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    protected $discountService;

    public function __construct(DiscountService $discountService)
    {
        $this->discountService = $discountService;
    }

    /**
     * Get cart contents
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        $cacheKey = 'cart_user_' . $user->id;
        $cart = cache($cacheKey, []);
        
        if (empty($cart)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'items' => [],
                    'subtotal' => 0,
                    'discount' => 0,
                    'shipping' => 0,
                    'total' => 0,
                    'count' => 0,
                ]
            ]);
        }

        $items = $this->normalizeCart($cart);
        $subtotal = collect($items)->sum('total');
        $discount = (float) cache('cart_discount_val_user_' . $user->id, 0);
        $shipping = 0;

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'subtotal' => (float) $subtotal,
                'discount' => (float) $discount,
                'shipping' => (float) $shipping,
                'total' => (float) ($subtotal - $discount + $shipping),
                'count' => count($items),
                'discount_code' => cache('cart_discount_code_user_' . $user->id),
            ]
        ]);
    }

    /**
     * Add product to cart
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
                'selected_options' => 'array',
            ]);

            $product = Product::find($validated['product_id']);

            if (!$product || !$product->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'المنتج غير متاح',
                ], 404);
            }

            if ($validated['quantity'] > $product->stock_quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'الكمية المطلوبة غير متوفرة',
                ], 422);
            }

            $user = $request->user();
            $cacheKey = 'cart_user_' . $user->id;
            $cart = cache($cacheKey, []);
            $selectionKey = $this->generateSelectionKey($validated['product_id'], $validated['selected_options'] ?? []);
            
            if (isset($cart[$selectionKey])) {
                $cart[$selectionKey]['quantity'] += $validated['quantity'];
            } else {
                $cart[$selectionKey] = [
                    'product_id' => $validated['product_id'],
                    'quantity' => $validated['quantity'],
                    'selection_key' => $selectionKey,
                    'selected_options' => $validated['selected_options'] ?? [],
                ];
            }

            cache([$cacheKey => $cart], now()->addDays(7));

            $items = $this->normalizeCart($cart);

            return response()->json([
                'success' => true,
                'message' => 'تمت إضافة المنتج للعربة',
                'data' => [
                    'items' => $items,
                    'count' => count($items),
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
     * Update cart item quantity
     */
    public function update(Request $request, $selectionKey)
    {
        try {
            $validated = $request->validate([
                'quantity' => 'required|integer|min:1',
            ]);

            $user = $request->user();
            $cacheKey = 'cart_user_' . $user->id;
            $cart = cache($cacheKey, []);

            if (!isset($cart[$selectionKey])) {
                return response()->json([
                    'success' => false,
                    'message' => 'العنصر غير موجود في العربة',
                ], 404);
            }

            $product = Product::find($cart[$selectionKey]['product_id']);

            if ($validated['quantity'] > $product->stock_quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'الكمية المطلوبة غير متوفرة',
                ], 422);
            }

            $cart[$selectionKey]['quantity'] = $validated['quantity'];
            cache([$cacheKey => $cart], now()->addDays(7));

            $items = $this->normalizeCart($cart);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث العربة',
                'data' => [
                    'items' => $items,
                    'count' => count($items),
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في البيانات المدخلة',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Remove item from cart
     */
    public function destroy($selectionKey)
    {
        $user = request()->user();
        $cacheKey = 'cart_user_' . $user->id;
        $cart = cache($cacheKey, []);

        if (!isset($cart[$selectionKey])) {
            return response()->json([
                'success' => false,
                'message' => 'العنصر غير موجود في العربة',
            ], 404);
        }

        unset($cart[$selectionKey]);
        cache([$cacheKey => $cart], now()->addDays(7));

        $items = $this->normalizeCart($cart);

        return response()->json([
            'success' => true,
            'message' => 'تم حذف العنصر من العربة',
            'data' => [
                'items' => $items,
                'count' => count($items),
            ]
        ]);
    }

    /**
     * Apply discount code to cart
     */
    public function applyDiscount(Request $request)
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string',
            ]);

            $user = $request->user();
            $user = $request->user();
            $cacheKey = 'cart_user_' . $user->id;
            $cart = cache($cacheKey, []);

            if (empty($cart)) {
                return response()->json([
                    'success' => false,
                    'message' => 'العربة فارغة',
                ], 422);
            }

            $items = $this->normalizeCart($cart);
            $subtotal = collect($items)->sum('total');

            $discount = $this->discountService->apply(
                $validated['code'],
                $subtotal,
                $items,
                $user
            );

            cache([
                'cart_discount_code_user_' . $user->id => $validated['code'],
                'cart_discount_val_user_' . $user->id => $discount,
                'cart_discount_id_user_' . $user->id => $discount ? $this->getDiscountCodeId($validated['code']) : null,
            ], now()->addDays(7));

            return response()->json([
                'success' => true,
                'message' => 'تم تطبيق الكود بنجاح',
                'data' => [
                    'discount' => (float) $discount,
                    'total' => (float) ($subtotal - $discount),
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
     * Remove discount code from cart
     */
    public function removeDiscount()
    {
        $user = request()->user();
        cache()->forget('cart_discount_code_user_' . $user->id);
        cache()->forget('cart_discount_val_user_' . $user->id);
        cache()->forget('cart_discount_id_user_' . $user->id);

        $cart = cache('cart_user_' . $user->id, []);
        $items = $this->normalizeCart($cart);
        $subtotal = collect($items)->sum('total');

        return response()->json([
            'success' => true,
            'message' => 'تم إزالة الكود',
            'data' => [
                'total' => (float) $subtotal,
            ]
        ]);
    }

    /**
     * Clear entire cart
     */
    public function clear()
    {
        $user = request()->user();
        cache()->forget('cart_user_' . $user->id);
        cache()->forget('cart_discount_code_user_' . $user->id);
        cache()->forget('cart_discount_val_user_' . $user->id);
        cache()->forget('cart_discount_id_user_' . $user->id);

        return response()->json([
            'success' => true,
            'message' => 'تم مسح العربة',
        ]);
    }

    /**
     * Normalize cart items with product details
     */
    private function normalizeCart(array $cart)
    {
        $items = [];

        foreach ($cart as $item) {
            $product = Product::find($item['product_id']);

            if (!$product) {
                continue;
            }

            $itemTotal = $product->getCurrentPrice() * $item['quantity'];

            $items[] = [
                'selection_key' => $item['selection_key'],
                'product_id' => $product->id,
                'name' => $product->name_translated,
                'price' => (float) $product->getCurrentPrice(),
                'quantity' => $item['quantity'],
                'total' => (float) $itemTotal,
                'image' => $product->images->first()?->image_path,
                'selected_options' => $item['selected_options'] ?? [],
            ];
        }

        return $items;
    }

    /**
     * Generate unique selection key for product variants
     */
    private function generateSelectionKey($productId, array $options = [])
    {
        ksort($options);
        $key = $productId . '-' . md5(json_encode($options));
        return $key;
    }

    /**
     * Get discount code ID from code string
     */
    private function getDiscountCodeId($code)
    {
        return \App\Models\DiscountCode::where('code', $code)->value('id');
    }
}
