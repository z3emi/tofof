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
        $cart = $this->normalizeCart(session()->get('cart', []));
        session()->put('cart', $cart);
        $cartItems = [];
        $total = 0;

        if (!empty($cart)) {
            $productIds = collect($cart)->pluck('product_id')->filter()->unique()->values()->all();
            $products = Product::whereIn('id', $productIds)->with('firstImage')->get()->keyBy('id');

            foreach ($cart as $rowId => $details) {
                $productId = (int) ($details['product_id'] ?? 0);
                if (isset($products[$productId])) {
                    $product = $products[$productId];
                    $price = $product->sale_price ?? $product->price;
                    $cartItems[$rowId] = [
                        'row_id' => $rowId,
                        'product_id' => $productId,
                        'product' => $product,
                        'quantity' => $details['quantity'],
                        'price' => $price,
                        'selected_options' => $details['selected_options'] ?? [],
                    ];
                    $total += $price * $details['quantity'];
                } else {
                    unset($cart[$rowId]);
                    session()->put('cart', $cart);
                }
            }
        }

        $freeShippingThreshold = Setting::freeShippingThreshold();
        $baseShippingCost = Setting::shippingCost();
        $isShippingEnabled = Setting::isShippingEnabled();
        $isFreeShippingEnabled = Setting::isFreeShippingEnabled();
        $shippingCost = 0;
        
        if ($isShippingEnabled) {
            $canUseFreeShipping = $isFreeShippingEnabled && $total >= $freeShippingThreshold;
            $shippingCost = $canUseFreeShipping ? 0 : $baseShippingCost;
        }

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
                'freeShippingThreshold',
                'isShippingEnabled',
                'isFreeShippingEnabled'
            )
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'selected_options' => 'nullable|array',
        ]);

        $product = Product::findOrFail($request->product_id);
        $selectedOptions = $this->sanitizeSelectedOptions($request->input('selected_options', []));
        $selectionKey = $this->buildSelectionKey($selectedOptions);
        $rowId = $this->buildRowId((int) $product->id, $selectionKey);

        $cart = $this->normalizeCart(session()->get('cart', []));

        if (isset($cart[$rowId])) {
            $cart[$rowId]['quantity'] += $request->quantity;
        } else {
            $cart[$rowId] = [
                'product_id' => (int) $product->id,
                'quantity' => $request->quantity,
                'selection_key' => $selectionKey,
                'selected_options' => $selectedOptions,
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
            'row_id' => 'nullable|string',
            'product_id' => 'nullable',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = $this->normalizeCart(session()->get('cart', []));
        $rowId = $request->input('row_id');

        if (!$rowId && $request->filled('product_id')) {
            $requestedProductId = (int) $request->input('product_id');
            $rowId = collect($cart)
                ->keys()
                ->first(fn ($key) => (int) ($cart[$key]['product_id'] ?? 0) === $requestedProductId);
        }

        if ($rowId && isset($cart[$rowId])) {
            $cart[$rowId]['quantity'] = $request->quantity;
            session()->put('cart', $cart);
        }

        return response()->json([
            'success' => true,
            'cartCount' => self::getCartCount()
        ]);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'row_id' => 'nullable|string',
            'product_id' => 'nullable',
        ]);

        $cart = $this->normalizeCart(session()->get('cart', []));
        $rowId = $request->input('row_id');

        if (!$rowId && $request->filled('product_id')) {
            $requestedProductId = (int) $request->input('product_id');
            $rowId = collect($cart)
                ->keys()
                ->first(fn ($key) => (int) ($cart[$key]['product_id'] ?? 0) === $requestedProductId);
        }

        if ($rowId && isset($cart[$rowId])) {
            unset($cart[$rowId]);
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

        $cart = $this->normalizeCart(session()->get('cart', []));
        $total = 0;
        $productIds = collect($cart)->pluck('product_id')->filter()->unique()->values()->all();
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        foreach ($cart as $details) {
            $productId = (int) ($details['product_id'] ?? 0);
            if (isset($products[$productId])) {
                $price = $products[$productId]->sale_price ?? $products[$productId]->price;
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
        return response()->json($this->normalizeCart(session()->get('cart', [])));
    }

    private function sanitizeSelectedOptions(array $selectedOptions): array
    {
        $clean = [];
        foreach ($selectedOptions as $label => $value) {
            $label = trim((string) $label);
            if ($label === '') {
                continue;
            }

            $value = is_scalar($value) ? trim((string) $value) : '';
            if ($value === '') {
                continue;
            }

            $clean[$label] = $value;
        }

        ksort($clean);
        return $clean;
    }

    private function buildSelectionKey(array $selectedOptions): string
    {
        if (empty($selectedOptions)) {
            return 'default';
        }

        return sha1(json_encode($selectedOptions, JSON_UNESCAPED_UNICODE));
    }

    private function buildRowId(int $productId, string $selectionKey): string
    {
        return $productId . ':' . $selectionKey;
    }

    private function normalizeCart(array $cart): array
    {
        $normalized = [];

        foreach ($cart as $key => $details) {
            if (!is_array($details)) {
                continue;
            }

            $productId = (int) ($details['product_id'] ?? (is_numeric($key) ? $key : 0));
            if ($productId <= 0) {
                continue;
            }

            $quantity = max(1, (int) ($details['quantity'] ?? 1));
            $selectedOptions = $this->sanitizeSelectedOptions((array) ($details['selected_options'] ?? []));
            $selectionKey = (string) ($details['selection_key'] ?? $this->buildSelectionKey($selectedOptions));
            $rowId = $this->buildRowId($productId, $selectionKey);

            if (!isset($normalized[$rowId])) {
                $normalized[$rowId] = [
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'selection_key' => $selectionKey,
                    'selected_options' => $selectedOptions,
                ];
                continue;
            }

            $normalized[$rowId]['quantity'] += $quantity;
        }

        return $normalized;
    }
}