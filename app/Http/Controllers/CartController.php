<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Setting;
use App\Services\DiscountService;

class CartController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);
        $cartItems = [];
        $total = 0;

        if (!empty($cart)) {
            $productIds = array_keys($cart);
            $products = Product::whereIn('id', $productIds)->with('firstImage')->get()->keyBy('id');

            foreach ($cart as $id => $details) {
                if (isset($products[$id])) {
                    $product = $products[$id];
                    $price = $product->sale_price ?? $product->price;
                    $cartItems[$id] = [
                        'product' => $product,
                        'quantity' => $details['quantity'],
                        'price' => $price,
                    ];
                    $total += $price * $details['quantity'];
                } else {
                    unset($cart[$id]);
                    session()->put('cart', $cart);
                }
            }
        }

        $freeShippingThreshold = (int) config('shop.free_shipping_threshold', 85000);
        $baseShippingCost = Setting::shippingCost();
        $shippingCost = ($total >= $freeShippingThreshold) ? 0 : $baseShippingCost;

        $discountValue = session()->get('discount_value', 0);
        $finalTotal = ($total - $discountValue) + $shippingCost;

        return view(
            'frontend.cart.index',
            compact(
                'cartItems',
                'total',
                'discountValue',
                'finalTotal',
                'shippingCost',
                'baseShippingCost',
                'freeShippingThreshold'
            )
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $product = Product::findOrFail($request->product_id);
        $cart = session()->get('cart', []);

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] += $request->quantity;
        } else {
            $cart[$product->id] = [
                'quantity' => $request->quantity,
            ];
        }

        session()->put('cart', $cart);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم إضافة المنتج إلى السلة.',
                'cartCount' => self::getCartCount()
            ]);
        }

        return redirect()->back()->with('success', 'تمت إضافة المنتج إلى السلة.');
    }

    public function update(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = session()->get('cart', []);

        if (isset($cart[$request->product_id])) {
            $cart[$request->product_id]['quantity'] = $request->quantity;
            session()->put('cart', $cart);
        }

        return response()->json([
            'success' => true,
            'cartCount' => self::getCartCount()
        ]);
    }

    public function destroy(Request $request)
    {
        $request->validate(['product_id' => 'required']);

        $cart = session()->get('cart', []);

        if (isset($cart[$request->product_id])) {
            unset($cart[$request->product_id]);
            session()->put('cart', $cart);
        }

        return response()->json([
            'success' => true,
            'cartCount' => self::getCartCount()
        ]);
    }

    public function applyDiscount(Request $request, DiscountService $discountService)
    {
        $request->validate(['discount_code' => 'required|string']);

        $cart = session()->get('cart', []);
        $total = 0;
        $productIds = array_keys($cart);
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        foreach ($cart as $id => $details) {
            if (isset($products[$id])) {
                $price = $products[$id]->sale_price ?? $products[$id]->price;
                $total += $price * $details['quantity'];
            }
        }

        try {
            $result = $discountService->apply($request->discount_code, $total);
            session([
                'discount_code'    => $request->discount_code,
                'discount_value'   => $result['discount_amount'],
                'discount_code_id' => $result['discount_code_id'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم تطبيق كود الخصم بنجاح.',
                'discount_value' => $result['discount_amount'],
                'discount_code' => $request->discount_code
            ]);

        } catch (\Exception $e) {
            session()->forget(['discount_code', 'discount_value', 'discount_code_id']);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
    
    public function removeDiscount(Request $request)
    {
        session()->forget(['discount_code', 'discount_value', 'discount_code_id']);
        return response()->json(['success' => true, 'message' => 'تمت إزالة كوبون الخصم.']);
    }

    public function count()
    {
        return response()->json(['count' => self::getCartCount()]);
    }
    
    public static function getCartCount(): int
    {
        $cart = session()->get('cart', []);
        return array_sum(array_column($cart, 'quantity'));
    }

    public function content()
    {
        return response()->json(session()->get('cart', []));
    }
}