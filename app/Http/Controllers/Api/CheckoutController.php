<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Address;
use App\Models\DiscountCodeUsage;
use App\Services\InventoryService;
use App\Services\DiscountService;
use App\Support\RepairsPrimaryKeyAutoIncrement;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    protected $inventoryService;
    protected $discountService;

    public function __construct(InventoryService $inventoryService, DiscountService $discountService)
    {
        $this->inventoryService = $inventoryService;
        $this->discountService = $discountService;
    }

    /**
     * Get checkout details
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $cart = session('cart', []);

        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'العربة فارغة',
            ], 422);
        }

        $items = $this->normalizeCart($cart);
        $subtotal = collect($items)->sum('total');
        $discount = session('discount_value', 0);
        $shipping = 0;
        $total = $subtotal - $discount + $shipping;

        $addresses = $user->addresses()->get();
        $defaultAddress = $user->addresses()->where('is_default', true)->first();

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'subtotal' => (float) $subtotal,
                'discount' => (float) $discount,
                'shipping' => (float) $shipping,
                'total' => (float) $total,
                'wallet_balance' => (float) $user->wallet_balance,
                'addresses' => $addresses->map(fn($addr) => [
                    'id' => $addr->id,
                    'governorate' => $addr->governorate,
                    'city' => $addr->city,
                    'address_details' => $addr->address_details,
                    'nearest_landmark' => $addr->nearest_landmark,
                    'is_default' => $addr->is_default,
                ]),
                'default_address' => $defaultAddress ? [
                    'id' => $defaultAddress->id,
                    'governorate' => $defaultAddress->governorate,
                    'city' => $defaultAddress->city,
                    'address_details' => $defaultAddress->address_details,
                    'nearest_landmark' => $defaultAddress->nearest_landmark,
                ] : null,
            ]
        ]);
    }

    /**
     * Create order from cart
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'address_id' => 'required|exists:addresses,id',
                'payment_method' => 'required|in:cod,wallet,wallet+cod',
                'use_wallet_amount' => 'nullable|numeric|min:0',
                'is_gift' => 'boolean',
                'gift_recipient_name' => 'required_if:is_gift,true|string',
                'gift_recipient_phone' => 'required_if:is_gift,true|string',
                'gift_message' => 'nullable|string|max:500',
            ]);

            $user = $request->user();
            $cart = session('cart', []);

            if (empty($cart)) {
                return response()->json([
                    'success' => false,
                    'message' => 'العربة فارغة',
                ], 422);
            }

            // Verify address belongs to user
            $address = Address::where('id', $validated['address_id'])
                ->where('user_id', $user->id)
                ->first();

            if (!$address) {
                return response()->json([
                    'success' => false,
                    'message' => 'العنوان المختار غير صحيح',
                ], 404);
            }

            $items = $this->normalizeCart($cart);
            $subtotal = collect($items)->sum('total');
            $discountAmount = session('discount_value', 0);
            $shippingCost = 0;
            $totalAmount = $subtotal - $discountAmount + $shippingCost;

            // Begin transaction
            return DB::transaction(function () use (
                $user,
                $address,
                $cart,
                $items,
                $validated,
                $subtotal,
                $discountAmount,
                $shippingCost,
                $totalAmount
            ) {
                // Verify stock for all items
                foreach ($items as $item) {
                    $product = \App\Models\Product::find($item['product_id']);
                    if ($item['quantity'] > $product->stock_quantity) {
                        throw new \Exception('الكمية المطلوبة غير متوفرة لـ ' . $item['name']);
                    }
                }

                try {
                    // Create order
                    $order = Order::create([
                        'user_id' => $user->id,
                        'customer_id' => $user->id,
                        'governorate' => $address->governorate,
                        'city' => $address->city,
                        'address_details' => $address->address_details,
                        'nearest_landmark' => $address->nearest_landmark,
                        'total_amount' => $totalAmount,
                        'shipping_cost' => $shippingCost,
                        'discount_amount' => $discountAmount,
                        'discount_code_id' => session('discount_code_id'),
                        'payment_method' => $validated['payment_method'],
                        'payment_status' => 'pending',
                        'is_gift' => $validated['is_gift'] ?? false,
                        'gift_recipient_name' => $validated['gift_recipient_name'] ?? null,
                        'gift_recipient_phone' => $validated['gift_recipient_phone'] ?? null,
                        'gift_recipient_address_details' => $validated['gift_recipient_address_details'] ?? null,
                        'gift_message' => $validated['gift_message'] ?? null,
                        'status' => 'pending',
                    ]);
                } catch (\Exception $e) {
                    if (RepairsPrimaryKeyAutoIncrement::isMissingAutoIncrementError($e)) {
                        RepairsPrimaryKeyAutoIncrement::ensure('orders');
                        $order = Order::create([
                            'user_id' => $user->id,
                            'customer_id' => $user->id,
                            'governorate' => $address->governorate,
                            'city' => $address->city,
                            'address_details' => $address->address_details,
                            'nearest_landmark' => $address->nearest_landmark,
                            'total_amount' => $totalAmount,
                            'shipping_cost' => $shippingCost,
                            'discount_amount' => $discountAmount,
                            'discount_code_id' => session('discount_code_id'),
                            'payment_method' => $validated['payment_method'],
                            'payment_status' => 'pending',
                            'is_gift' => $validated['is_gift'] ?? false,
                            'gift_recipient_name' => $validated['gift_recipient_name'] ?? null,
                            'gift_recipient_phone' => $validated['gift_recipient_phone'] ?? null,
                            'gift_message' => $validated['gift_message'] ?? null,
                            'status' => 'pending',
                        ]);
                    } else {
                        throw $e;
                    }
                }

                // Create order items and deduct stock
                $totalCost = 0;
                foreach ($items as $item) {
                    $product = \App\Models\Product::find($item['product_id']);

                    $cost = $this->inventoryService->deductStock($product, $item['quantity']);
                    $totalCost += $cost;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'cost' => $cost,
                        'option_selections' => json_encode($item['selected_options'] ?? []),
                    ]);
                }

                // Record discount code usage
                if (session('discount_code_id')) {
                    DiscountCodeUsage::create([
                        'discount_code_id' => session('discount_code_id'),
                        'user_id' => $user->id,
                        'order_id' => $order->id,
                        'used_value' => $discountAmount,
                    ]);
                }

                // Handle wallet payment
                $walletAmount = 0;
                if (in_array($validated['payment_method'], ['wallet', 'wallet+cod'])) {
                    $useWalletAmount = min(
                        $validated['use_wallet_amount'] ?? 0,
                        $user->wallet_balance
                    );

                    if ($useWalletAmount > 0) {
                        $user = User::where('id', $user->id)->lockForUpdate()->first();
                        $user->decrement('wallet_balance', $useWalletAmount);
                        
                        \App\Models\WalletTransaction::create([
                            'user_id' => $user->id,
                            'order_id' => $order->id,
                            'amount' => $useWalletAmount,
                            'type' => 'debit',
                            'description' => 'دفع عن طريق المحفظة للطلب #' . $order->id,
                            'balance_after' => $user->wallet_balance,
                        ]);

                        $walletAmount = $useWalletAmount;
                    }
                }

                // Send notifications
                $this->sendOrderNotifications($order, $user);

                // Clear session cart
                session()->forget(['cart', 'discount_code', 'discount_value', 'discount_code_id']);

                return response()->json([
                    'success' => true,
                    'message' => 'تم إنشاء الطلب بنجاح',
                    'data' => [
                        'order_id' => $order->id,
                        'total_amount' => (float) $order->total_amount,
                        'items_count' => count($items),
                        'payment_method' => $validated['payment_method'],
                        'wallet_paid' => (float) $walletAmount,
                        'remaining_amount' => (float) ($order->total_amount - $walletAmount),
                    ]
                ], 201);
            });
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في البيانات المدخلة',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Normalize cart items
     */
    private function normalizeCart(array $cart)
    {
        $items = [];

        foreach ($cart as $item) {
            $product = \App\Models\Product::find($item['product_id']);

            if (!$product) {
                continue;
            }

            $itemTotal = $product->getCurrentPrice() * $item['quantity'];

            $items[] = [
                'product_id' => $product->id,
                'name' => $product->name_translated,
                'price' => (float) $product->getCurrentPrice(),
                'quantity' => $item['quantity'],
                'total' => (float) $itemTotal,
                'selected_options' => $item['selected_options'] ?? [],
            ];
        }

        return $items;
    }

    /**
     * Send order notifications
     */
    private function sendOrderNotifications($order, $user)
    {
        // Send notification to admin managers
        $managers = \App\Models\Manager::where('active', true)
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['admin', 'manager']);
            })
            ->get();

        foreach ($managers as $manager) {
            // You can add notification logic here
            // Example: $manager->notify(new OrderCreatedNotification($order));
        }
    }
}
