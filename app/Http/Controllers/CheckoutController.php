<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Address;
use App\Models\Setting;
use App\Services\InventoryService;
use App\Models\DiscountCodeUsage;
use App\Models\User;
use App\Models\Manager;
use App\Notifications\NewOrderNotification;
use Illuminate\Support\Facades\Notification;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('shop')->with('info', 'عربة التسوق فارغة.');
        }

        $cartItems = [];
        $subtotal = 0;
        $productIds = array_keys($cart);
        $products = Product::whereIn('id', $productIds)->with('firstImage')->get()->keyBy('id');

        foreach ($cart as $id => $details) {
            if (isset($products[$id])) {
                $product = $products[$id];
                $price = $product->sale_price ?? $product->price;
                $cartItems[$id] = [
                    'product'  => $product,
                    'quantity' => $details['quantity'],
                    'price'    => $price,
                ];
                $subtotal += $price * $details['quantity'];
            }
        }

        $freeShippingThreshold = (int) config('shop.free_shipping_threshold', 85000);
        $baseShippingCost = Setting::shippingCost();
        $shippingCost  = ($subtotal >= $freeShippingThreshold) ? 0 : $baseShippingCost;

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
            'freeShippingThreshold'
        ));
    }

    public function store(Request $request, InventoryService $inventoryService)
    {
        $request->validate([
            'saved_address_id' => 'required|exists:addresses,id',
            'payment_method'   => 'required|string',
        ], [
            'saved_address_id.required' => 'يرجى اختيار أو إضافة عنوان شحن للمتابعة.'
        ]);

        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('shop')->with('error', 'عربة التسوق فارغة!');
        }

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $customer = Customer::firstOrCreate(['user_id' => $user->id], ['name' => $user->name, 'phone_number' => $user->phone_number, 'email' => $user->email]);

            $subtotal = 0;
            $productIds = array_keys($cart);
            $products   = Product::whereIn('id', $productIds)->get()->keyBy('id');

            foreach ($cart as $id => $details) {
                if (isset($products[$id])) {
                    $product = $products[$id];
                    $price   = $product->sale_price ?? $product->price;
                    $subtotal += $price * $details['quantity'];
                }
            }

            $address = Address::findOrFail($request->saved_address_id);
            
            $freeShippingThreshold = (int) config('shop.free_shipping_threshold', 85000);
            $baseShippingCost = Setting::shippingCost();
            $shippingCost   = ($subtotal >= $freeShippingThreshold) ? 0 : $baseShippingCost;
            
            $discountAmount = session('discount_value', 0);
            $discountCodeId = session('discount_code_id', null);
            $finalTotal = ($subtotal - $discountAmount) + $shippingCost;

            $order = Order::create([
                'user_id'          => $user->id,
                'customer_id'      => $customer->id,
                'total_amount'     => $finalTotal,
                'shipping_cost'    => $shippingCost,
                'discount_amount'  => $discountAmount,
                'discount_code_id' => $discountCodeId,
                'status'           => 'pending',
                'governorate'      => $address->governorate ?? '',
                'city'             => $address->city ?? '',
                'address_details'  => $address->address_details ?? '',
                'nearest_landmark' => $address->nearest_landmark ?? '',
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
            foreach ($cart as $id => $details) {
                if (!isset($products[$id])) continue;
                $product = $products[$id];
                $price   = $product->sale_price ?? $product->price;
                $qty     = (int) $details['quantity'];
                $itemCost = 0;
                $itemCost = $inventoryService->deductStock($product, $qty);
                $totalCost += (float) $itemCost;
                OrderItem::create([
                    'order_id' => $order->id, 'product_id' => $id,
                    'quantity' => $qty, 'price' => $price, 'cost' => $itemCost,
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
            $admins = Manager::role($adminRoleNames)->get();
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
}