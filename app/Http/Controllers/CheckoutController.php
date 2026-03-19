<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Setting;
use App\Services\InventoryService;
use App\Models\DiscountCodeUsage;
use App\Models\User;
use App\Models\Manager;
use App\Notifications\NewOrderNotification;
use App\Support\RepairsPrimaryKeyAutoIncrement;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = $this->normalizeCart(session()->get('cart', []));
        session()->put('cart', $cart);
        if (empty($cart)) {
            return redirect()->route('shop')->with('info', 'عربة التسوق فارغة.');
        }

        $cartItems = [];
        $subtotal = 0;
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
                    'product'  => $product,
                    'quantity' => $details['quantity'],
                    'price'    => $price,
                    'selected_options' => $details['selected_options'] ?? [],
                ];
                $subtotal += $price * $details['quantity'];
            }
        }

        $freeShippingThreshold = Setting::freeShippingThreshold();
        $baseShippingCost = Setting::shippingCost();
        $isShippingEnabled = Setting::isShippingEnabled();
        $isFreeShippingEnabled = Setting::isFreeShippingEnabled();
        $shippingCost  = 0;
        
        if ($isShippingEnabled) {
            $canUseFreeShipping = $isFreeShippingEnabled && $subtotal >= $freeShippingThreshold;
            $shippingCost = $canUseFreeShipping ? 0 : $baseShippingCost;
        }

        $discountValue = session('discount_value', 0);
        $finalTotal    = ($subtotal - $discountValue) + $shippingCost;

        $addresses      = Auth::user()->addresses()->latest()->get();
        $walletBalance  = (float)(Auth::user()->wallet_balance ?? 0);

        return view('frontend.checkout.index', compact(
            'cartItems',
            'addresses',
            'subtotal',
            'shippingCost',
            'discountValue',
            'finalTotal',
            'walletBalance',
            'baseShippingCost',
            'freeShippingThreshold',
            'isShippingEnabled',
            'isFreeShippingEnabled'
        ));
    }

    public function store(Request $request, InventoryService $inventoryService)
    {
        $isGift = $request->boolean('is_gift');

        $request->validate([
            'saved_address_id' => ['nullable', 'integer', Rule::requiredIf(! $isGift), Rule::exists('addresses', 'id')],
            'payment_method'   => 'required|string',
            'is_gift' => 'nullable|boolean',
            'gift_recipient_name' => 'required_if:is_gift,1|nullable|string|max:255',
            'gift_recipient_phone' => 'required_if:is_gift,1|nullable|string|max:50',
            'gift_recipient_address_details' => 'required_if:is_gift,1|nullable|string|max:1000',
            'gift_message' => 'nullable|string|max:1000',
        ], [
            'saved_address_id.required' => 'يرجى اختيار أو إضافة عنوان شحن للمتابعة.',
            'gift_recipient_name.required_if' => 'يرجى إدخال اسم مستلم الهدية.',
            'gift_recipient_phone.required_if' => 'يرجى إدخال رقم هاتف مستلم الهدية.',
            'gift_recipient_address_details.required_if' => 'يرجى إدخال عنوان مستلم الهدية.',
        ]);

        $cart = $this->normalizeCart(session()->get('cart', []));
        if (empty($cart)) {
            return redirect()->route('shop')->with('error', 'عربة التسوق فارغة!');
        }

        $user = Auth::user();
        $address = null;

        if ($request->filled('saved_address_id')) {
            $address = $user->addresses()->find($request->saved_address_id);

            if (! $address) {
                return redirect()->back()->withInput()->withErrors([
                    'saved_address_id' => 'العنوان المحدد غير صالح.',
                ]);
            }
        }

        DB::beginTransaction();
        try {
            $customer = Customer::firstOrCreate(['user_id' => $user->id], ['name' => $user->name, 'phone_number' => $user->phone_number, 'email' => $user->email]);

            $subtotal = 0;
            $productIds = collect($cart)->pluck('product_id')->filter()->unique()->values()->all();
            $products   = Product::whereIn('id', $productIds)->get()->keyBy('id');

            foreach ($cart as $details) {
                $productId = (int) ($details['product_id'] ?? 0);
                if (isset($products[$productId])) {
                    $product = $products[$productId];
                    $price   = $product->sale_price ?? $product->price;
                    $subtotal += $price * $details['quantity'];
                }
            }

            $freeShippingThreshold = Setting::freeShippingThreshold();
            $baseShippingCost = Setting::shippingCost();
            $shippingCost   = 0;
            
            if (Setting::isShippingEnabled()) {
                $canUseFreeShipping = Setting::isFreeShippingEnabled() && $subtotal >= $freeShippingThreshold;
                $shippingCost = $canUseFreeShipping ? 0 : $baseShippingCost;
            }
            
            $discountAmount = session('discount_value', 0);
            $discountCodeId = session('discount_code_id', null);
            $finalTotal = ($subtotal - $discountAmount) + $shippingCost;
            $shippingAddressDetails = $isGift
                ? trim((string) $request->gift_recipient_address_details)
                : ($address->address_details ?? '');

            $order = $this->createOrderWithRepair([
                'user_id'          => $user->id,
                'customer_id'      => $customer->id,
                'total_amount'     => $finalTotal,
                'shipping_cost'    => $shippingCost,
                'discount_amount'  => $discountAmount,
                'discount_code_id' => $discountCodeId,
                'status'           => 'pending',
                'governorate'      => $isGift ? '' : ($address->governorate ?? ''),
                'city'             => $isGift ? '' : ($address->city ?? ''),
                'address_details'  => $shippingAddressDetails,
                'nearest_landmark' => $isGift ? '' : ($address->nearest_landmark ?? ''),
                'is_gift' => $isGift,
                'gift_recipient_name' => $isGift ? $request->gift_recipient_name : null,
                'gift_recipient_phone' => $isGift ? $request->gift_recipient_phone : null,
                'gift_recipient_address_details' => $isGift ? $request->gift_recipient_address_details : null,
                'gift_message' => $isGift ? $request->gift_message : null,
                'payment_method'   => $request->payment_method,
            ]);

            if ($discountCodeId) {
                DiscountCodeUsage::create([
                    'discount_code_id' => $discountCodeId,
                    'order_id'         => $order->id,
                    'user_id'          => $user->id,
                ]);
            }

            $totalCost = 0;
            foreach ($cart as $details) {
                $productId = (int) ($details['product_id'] ?? 0);
                if (!isset($products[$productId])) {
                    continue;
                }

                $product = $products[$productId];
                $price   = $product->sale_price ?? $product->price;
                $qty     = (int) $details['quantity'];
                $itemCost = 0;
                $itemCost = $inventoryService->deductStock($product, $qty);
                $totalCost += (float) $itemCost;

                $selectedOptions = $this->sanitizeSelectedOptions((array) ($details['selected_options'] ?? []));
                $this->createOrderItemWithRepair([
                    'order_id' => $order->id, 'product_id' => $productId,
                    'quantity' => $qty, 'price' => $price, 'cost' => $itemCost,
                    'option_selections' => empty($selectedOptions) ? null : $selectedOptions,
                ]);
            }
            $order->update(['total_cost' => $totalCost]);

            $useWallet     = $request->boolean('use_wallet');
            $walletBalance = (float)($user->wallet_balance ?? 0);
            if ($useWallet && $walletBalance > 0 && $finalTotal > 0) {
                $walletUsed = min($walletBalance, $finalTotal);
                if ($walletUsed > 0) {
                    $newBalance = $walletBalance - $walletUsed;
                    WalletTransaction::create([
                        'user_id'     => $user->id, 'type' => 'debit',
                        'amount'      => $walletUsed,
                        'description' => 'استخدام رصيد للطلب #' . $order->id,
                        'balance_after' => $newBalance,
                    ]);
                    $user->update(['wallet_balance' => $newBalance]);
                    $amountDue = max(0, $finalTotal - $walletUsed);
                    $order->update([
                        'total_amount'   => $amountDue,
                        'payment_method' => $amountDue > 0 ? 'wallet+cod' : 'wallet',
                        'payment_status' => $amountDue > 0 ? 'partially_paid' : 'paid',
                    ]);
                }
            } else {
                $order->update(['payment_status' => 'unpaid']);
            }

            $adminRoleNames = ['Super-Admin', 'Order-Manager'];
            $admins = Manager::query()
                ->whereHas('roles', function ($query) use ($adminRoleNames) {
                    $query->where('guard_name', 'admin')
                        ->whereIn('name', $adminRoleNames);
                })
                ->get();
            if ($admins->isNotEmpty()) {
                Notification::send($admins, new NewOrderNotification($order));
            }
            
            DB::commit();

            session()->forget(['cart', 'discount_code', 'discount_value', 'discount_code_id']);
            return redirect()->route('checkout.success')->with('order_id', $order->id);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'حدث خطأ أثناء إنشاء الطلب: ' . $e->getMessage());
        }
    }

    public function success()
    {
        $orderId = session('order_id');
        $order = $orderId ? Order::find($orderId) : null;
        if (!$order) {
            return redirect()->route('homepage');
        }
        return view('frontend.checkout.success', compact('order'));
    }

    private function createOrderWithRepair(array $attributes): Order
    {
        try {
            return Order::create($attributes);
        } catch (QueryException $exception) {
            if (! RepairsPrimaryKeyAutoIncrement::isMissingAutoIncrementError($exception, 'orders')) {
                throw $exception;
            }

            RepairsPrimaryKeyAutoIncrement::ensure('orders');

            return Order::create($attributes);
        }
    }

    private function createOrderItemWithRepair(array $attributes): OrderItem
    {
        try {
            return OrderItem::create($attributes);
        } catch (QueryException $exception) {
            if (! RepairsPrimaryKeyAutoIncrement::isMissingAutoIncrementError($exception, 'order_items')) {
                throw $exception;
            }

            RepairsPrimaryKeyAutoIncrement::ensure('order_items');

            return OrderItem::create($attributes);
        }
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