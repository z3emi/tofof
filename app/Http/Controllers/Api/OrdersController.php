<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrdersController extends Controller
{
    /**
     * Get all orders for authenticated user
     */
    public function index(Request $request)
    {
        try {
            $validated = $request->validate([
                'page' => 'integer|min:1',
                'per_page' => 'integer|min:1|max:50',
                'status' => 'in:pending,processing,shipped,delivered,cancelled,returned',
                'payment_status' => 'in:pending,paid,failed',
            ]);

            $user = $request->user();
            $perPage = $validated['per_page'] ?? 20;

            $query = Order::where('user_id', $user->id)
                ->with('items.product')
                ->latest();

            if (isset($validated['status'])) {
                $query->where('status', $validated['status']);
            }

            if (isset($validated['payment_status'])) {
                $query->where('payment_status', $validated['payment_status']);
            }

            $orders = $query->paginate($perPage);

            $items = $orders->map(fn($order) => [
                'id' => $order->id,
                'total_amount' => (float) $order->total_amount,
                'discount_amount' => (float) $order->discount_amount,
                'shipping_cost' => (float) $order->shipping_cost,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'status' => $order->status,
                'items_count' => $order->items->count(),
                'created_at' => $order->created_at,
                'is_gift' => $order->is_gift,
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'orders' => $items,
                    'total' => $orders->total(),
                    'current_page' => $orders->current_page(),
                    'per_page' => $orders->per_page(),
                    'last_page' => $orders->last_page(),
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
     * Get single order details
     */
    public function show(Request $request, $orderId)
    {
        try {
            $user = $request->user();
            
            $order = Order::where('id', $orderId)
                ->where('user_id', $user->id)
                ->with('items.product', 'discountCode')
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'الطلب غير موجود',
                ], 404);
            }

            $walletAmount = WalletTransaction::where('order_id', $order->id)
                ->where('type', 'debit')
                ->sum('amount');

            $items = $order->items->map(fn($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name_translated,
                'product_image' => $item->product->images->first()?->image_path,
                'quantity' => $item->quantity,
                'price' => (float) $item->price,
                'total' => (float) ($item->price * $item->quantity),
                'cost' => (float) $item->cost,
                'option_selections' => $item->normalizedOptionSelections(),
            ]);

            $statusTimeline = $this->getStatusTimeline($order);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $order->id,
                    'total_amount' => (float) $order->total_amount,
                    'discount_amount' => (float) $order->discount_amount,
                    'shipping_cost' => (float) $order->shipping_cost,
                    'subtotal' => (float) ($order->total_amount + $order->discount_amount - $order->shipping_cost),
                    'wallet_paid' => (float) $walletAmount,
                    'remaining_amount' => (float) ($order->total_amount - $walletAmount),
                    'payment_method' => $order->payment_method,
                    'payment_status' => $order->payment_status,
                    'status' => $order->status,
                    'discount_code' => $order->discountCode?->code,
                    'governorate' => $order->governorate,
                    'city' => $order->city,
                    'address_details' => $order->address_details,
                    'nearest_landmark' => $order->nearest_landmark,
                    'is_gift' => $order->is_gift,
                    'gift_recipient_name' => $order->gift_recipient_name,
                    'gift_recipient_phone' => $order->gift_recipient_phone,
                    'gift_message' => $order->gift_message,
                    'items' => $items,
                    'status_timeline' => $statusTimeline,
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
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
     * Cancel order (if eligible)
     */
    public function cancel(Request $request, $orderId)
    {
        try {
            $user = $request->user();
            
            $order = Order::where('id', $orderId)
                ->where('user_id', $user->id)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'الطلب غير موجود',
                ], 404);
            }

            if (in_array($order->status, ['delivered', 'cancelled', 'returned'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن إلغاء هذا الطلب',
                ], 422);
            }

            // Restore stock
            foreach ($order->items as $item) {
                $item->product->increment('stock_quantity', $item->quantity);
            }

            // Refund wallet if payment was made
            $walletAmount = WalletTransaction::where('order_id', $order->id)
                ->where('type', 'debit')
                ->sum('amount');

            if ($walletAmount > 0) {
                $user->increment('wallet_balance', $walletAmount);
                
                WalletTransaction::create([
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'amount' => $walletAmount,
                    'type' => 'credit',
                    'description' => 'استرجاع الدفع من المحفظة بعد إلغاء الطلب #' . $order->id,
                    'balance_after' => $user->wallet_balance,
                ]);
            }

            $order->update(['status' => 'cancelled']);

            return response()->json([
                'success' => true,
                'message' => 'تم إلغاء الطلب بنجاح',
                'data' => [
                    'order_id' => $order->id,
                    'status' => $order->status,
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
     * Get status timeline for order
     */
    private function getStatusTimeline($order)
    {
        $timeline = [];

        $statusMap = [
            'pending' => 'قيد الانتظار',
            'processing' => 'قيد المعالجة',
            'shipped' => 'تم الشحن',
            'delivered' => 'تم التسليم',
            'cancelled' => 'ملغى',
            'returned' => 'مرتجع',
        ];

        $statuses = ['pending', 'processing', 'shipped', 'delivered'];

        foreach ($statuses as $status) {
            $timeline[] = [
                'status' => $status,
                'label' => $statusMap[$status],
                'is_current' => $order->status === $status,
                'is_completed' => in_array($order->status, $statuses) && 
                                 array_search($order->status, $statuses) >= array_search($status, $statuses),
            ];
        }

        return $timeline;
    }
}
