<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Customer;
use App\Models\Manager;
use App\Models\Product;
use App\Models\Address;
use App\Services\InventoryService;
use App\Services\DiscountService;
use App\Notifications\NewOrderNotification;
use App\Notifications\OrderStatusUpdated;
use App\Notifications\AdminOrderStatusUpdated;
use App\Notifications\ReferralBonusReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;
use App\Models\DiscountCode;
use App\Support\RepairsPrimaryKeyAutoIncrement;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use App\Services\WalletService;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Notification;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OrdersExport;

class OrderController extends Controller
{
    protected function preferFilled(...$values)
    {
        foreach ($values as $value) {
            if (! is_null($value) && trim((string) $value) !== '') {
                return $value;
            }
        }

        return null;
    }

    protected function defaultShippingCost(): float
    {
        return Setting::shippingCost();
    }

    public function __construct()
    {
        $permissionMiddleware = \Spatie\Permission\Middleware\PermissionMiddleware::class;

        $this->middleware($permissionMiddleware . ':view-orders', ['only' => ['index', 'show', 'invoice']]);
        $this->middleware($permissionMiddleware . ':create-orders', ['only' => ['create', 'store', 'applyDiscount']]);
        $this->middleware($permissionMiddleware . ':edit-orders', ['only' => ['edit', 'update', 'updateStatus']]);
        $this->middleware($permissionMiddleware . ':delete-orders', ['only' => ['destroy']]);
        $this->middleware($permissionMiddleware . ':view-trashed-orders', ['only' => ['trash']]);
        $this->middleware($permissionMiddleware . ':restore-orders', ['only' => ['restore']]);
        $this->middleware($permissionMiddleware . ':force-delete-orders', ['only' => ['forceDelete']]);
    }

    public function index(Request $request)
    {
        // عرض الطلبات المرتبطة بمستخدمين نشطين فقط (غير محذوفين)
        $query = Order::with('customer', 'user');
        $sortBy = $request->input('sort_by', 'id');
        $sortDir = $request->input('sort_dir', 'desc');

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('id', 'like', "%{$searchTerm}%")
                  ->orWhereHas('customer', function ($subQ) use ($searchTerm) {
                      $subQ->where('name', 'like', "%{$searchTerm}%")
                           ->orWhere('phone_number', 'like', "%{$searchTerm}%");
                  });
            });
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('min_price')) {
            $query->where('total_amount', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('total_amount', '<=', $request->max_price);
        }
        
        if ($request->filled('city')) {
            $query->where('city', 'like', "%{$request->city}%");
        }
        if ($request->filled('governorate')) {
            $query->where('governorate', $request->governorate);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $allowedSorts = ['id', 'user_name', 'total_amount', 'status', 'created_at', 'city', 'governorate'];
        [$sortBy, $sortDir] = \App\Support\Sort::resolve($request, $allowedSorts, 'id');

        if ($sortBy === 'user_name') {
            $query->join('customers', 'orders.customer_id', '=', 'customers.id')
                  ->orderBy('customers.name', $sortDir)
                  ->select('orders.*');
        } else {
            $query->orderBy($sortBy, $sortDir);
        }

        $perPage = (int) $request->get('per_page', 10);
        $orders = $query->paginate($perPage)->withQueryString();

        return view('admin.orders.index', compact('orders', 'sortBy', 'sortDir', 'allowedSorts'));
    }

    public function create()
    {
        // جلب العملاء الذين لا يملكون حساب مستخدم محذوف (أي جلب كل العملاء، مع استثناء من تم حذف حسابهم)
        $customers = Customer::whereDoesntHave('user', function($q) {
            $q->onlyTrashed();
        })->orderBy('name')->get();

        // جلب المنتجات التي يتوفر منها مخزون
        $products = Product::where('is_active', true)
            ->where('stock_quantity', '>', 0)
            ->get();

        $defaultShippingCost = $this->defaultShippingCost();

        return view('admin.orders.create', compact('customers', 'products', 'defaultShippingCost'));
    }


    public function store(Request $request, InventoryService $inventoryService, DiscountService $discountService)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'governorate' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'nearest_landmark' => 'required|string|max:255',
            'address_details' => 'required|string|max:500',
            'notes' => 'nullable|string',
            'is_gift' => 'nullable|boolean',
            'gift_recipient_name' => 'required_if:is_gift,1|nullable|string|max:255',
            'gift_recipient_phone' => 'required_if:is_gift,1|nullable|string|max:50',
            'gift_recipient_address_details' => 'required_if:is_gift,1|nullable|string|max:1000',
            'gift_message' => 'nullable|string|max:1000',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.price' => 'required|numeric|min:0',
            'discount_code' => 'nullable|string',
            'free_shipping' => 'nullable',
            'saved_address_id' => 'nullable|exists:addresses,id',
        ]);

        $customer = Customer::findOrFail($request->customer_id);

        $addressPayload = [
            'governorate' => $request->governorate,
            'city' => $request->city,
            'address_details' => $request->address_details,
            'nearest_landmark' => $request->nearest_landmark,
        ];

        if ($request->filled('saved_address_id') && ! $customer->user) {
            return redirect()->back()->withInput()->withErrors([
                'saved_address_id' => 'لا توجد عناوين محفوظة لهذا العميل.',
            ]);
        }

        if ($request->filled('saved_address_id') && $customer->user) {
            $addressRecord = Address::where('id', $request->saved_address_id)
                ->where('user_id', $customer->user->id)
                ->first();

            if (! $addressRecord) {
                return redirect()->back()->withInput()->withErrors([
                    'saved_address_id' => 'العنوان المحدد غير مرتبط بهذا العميل.',
                ]);
            }

            $addressPayload = [
                'governorate' => $this->preferFilled($request->governorate, $addressRecord->governorate),
                'city' => $this->preferFilled($request->city, $addressRecord->city),
                'address_details' => $this->preferFilled($request->address_details, $addressRecord->address_details),
                'nearest_landmark' => $this->preferFilled($request->nearest_landmark, $addressRecord->nearest_landmark),
            ];
        }

        DB::beginTransaction();

        try {
            // تحقق توفّر
            $subtotal = 0;
            foreach ($request->products as $productData) {
                $product = Product::find($productData['id']);
                if ($product->stock_quantity < $productData['quantity']) {
                    throw new \Exception("الكمية المطلوبة للمنتج '{$product->name_ar}' غير متوفرة. المتاح: {$product->stock_quantity}");
                }
                $subtotal += $productData['price'] * $productData['quantity'];
            }
            
            $defaultShippingCost = $this->defaultShippingCost();
            $shippingCost = 0;
            
            if (Setting::isShippingEnabled() && Setting::isFreeShippingEnabled() && !$request->boolean('free_shipping')) {
                $shippingCost = $defaultShippingCost;
            } elseif (Setting::isShippingEnabled() && !Setting::isFreeShippingEnabled()) {
                $shippingCost = $defaultShippingCost;
            } else {
                $shippingCost = 0;
            }

            $discountAmount = 0;
            $discountCodeId = null;
            if ($request->filled('discount_code')) {
                $result = $discountService->apply($request->discount_code, $subtotal);
                $discountAmount = $result['discount_amount'];
                $discountCodeId = $result['discount_code_id'];
            }
            
            $finalTotal = ($subtotal - $discountAmount) + $shippingCost;
            $isGift = $request->boolean('is_gift');

            $order = $this->createOrderWithRepair([
                'user_id' => auth()->id(),
                'customer_id' => $request->customer_id,
                'governorate' => $addressPayload['governorate'],
                'city' => $addressPayload['city'],
                'address_details' => $addressPayload['address_details'],
                'nearest_landmark' => $addressPayload['nearest_landmark'],
                'notes' => $request->notes,
                'is_gift' => $isGift,
                'gift_recipient_name' => $isGift ? $request->gift_recipient_name : null,
                'gift_recipient_phone' => $isGift ? $request->gift_recipient_phone : null,
                'gift_recipient_address_details' => $isGift ? $request->gift_recipient_address_details : null,
                'gift_message' => $isGift ? $request->gift_message : null,
                'total_amount' => $finalTotal,
                'shipping_cost' => $shippingCost,
                'discount_amount' => $discountAmount,
                'discount_code_id' => $discountCodeId,
                'status' => 'processing',
            ]);
            
            // بناء عناصر الطلب
            $orderItemsData = [];
            foreach ($request->products as $productId => $productData) {
                $orderItemsData[] = [
                    'product_id' => $productId,
                    'quantity' => $productData['quantity'],
                    'price' => $productData['price'],
                ];
            }
            $this->createOrderItemsWithRepair($order, $orderItemsData);

            // =========================================================
            // ✅ خصم الكميات من المخزون مباشرة
            // =========================================================
            foreach ($order->items as $item) {
                $neededQty = (int) $item->quantity;
                $product = $item->product;

                if ($product->stock_quantity < $neededQty) {
                    throw new \Exception("نفاد المخزون أثناء الحجز للمنتج: {$product->name_ar}. الرجاء المحاولة مجدداً.");
                }

                $product->decrement('stock_quantity', $neededQty);
            }
            // =========================================================

            // خصم تلقائي من المحفظة إن وجد
            try {
                WalletService::autoApplyToOrder($order);
            } catch (\Throwable $e) {}

            // الاستدعاء اليدوي لحدث الإنشاء (موجود عندك مسبقاً)
            try {
                (new \App\Observers\OrderObserver())->created($order);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('--- Error during manual "created" observer call ---', [
                    'message' => $e->getMessage()
                ]);
            }

            DB::commit();

            return redirect()->route('admin.orders.show', $order->id)
                             ->with('success', 'تم إنشاء الطلب بنجاح.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function edit(Order $order)
    {
        $products = Product::query()
            ->get()
            ->filter(function ($product) use ($order) {
                // يظهر إذا عنده مخزون متوفر أو إذا هو موجود أصلاً في الطلب
                return ($product->stock_quantity ?? 0) > 0 || $order->items->contains('product_id', $product->id);
            });

        $defaultShippingCost = $this->defaultShippingCost();

        return view('admin.orders.edit', compact('order', 'products', 'defaultShippingCost'));
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

    private function createOrderItemsWithRepair(Order $order, array $items): void
    {
        try {
            $order->items()->createMany($items);
        } catch (QueryException $exception) {
            if (! RepairsPrimaryKeyAutoIncrement::isMissingAutoIncrementError($exception, 'order_items')) {
                throw $exception;
            }

            RepairsPrimaryKeyAutoIncrement::ensure('order_items');

            $order->items()->createMany($items);
        }
    }

    public function update(Request $request, Order $order, InventoryService $inventoryService, DiscountService $discountService)
    {
        $request->validate([
            'governorate' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'nearest_landmark' => 'required|string|max:255',
            'address_details' => 'required|string|max:500',
            'notes' => 'nullable|string',
            'is_gift' => 'nullable|boolean',
            'gift_recipient_name' => 'required_if:is_gift,1|nullable|string|max:255',
            'gift_recipient_phone' => 'required_if:is_gift,1|nullable|string|max:50',
            'gift_recipient_address_details' => 'required_if:is_gift,1|nullable|string|max:1000',
            'gift_message' => 'nullable|string|max:1000',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.price' => 'required|numeric|min:0',
            'discount_code' => 'nullable|string',
            'free_shipping' => 'nullable',
        ]);

        DB::beginTransaction();

        try {
            // حذف عناصر الطلب الحالية وبناء جديدة
            $order->items()->delete();

            $subtotal = 0;
            foreach ($request->products as $productData) {
                $subtotal += $productData['price'] * $productData['quantity'];
            }

            $defaultShippingCost = $this->defaultShippingCost();
            $shippingCost = 0;
            
            if (Setting::isShippingEnabled() && Setting::isFreeShippingEnabled() && !$request->boolean('free_shipping')) {
                $shippingCost = $defaultShippingCost;
            } elseif (Setting::isShippingEnabled() && !Setting::isFreeShippingEnabled()) {
                $shippingCost = $defaultShippingCost;
            } else {
                $shippingCost = 0;
            }

            $discountAmount = 0;
            $discountCodeId = null;

            if ($request->filled('discount_code')) {
                $result = $discountService->apply($request->discount_code, $subtotal);
                $discountAmount = $result['discount_amount'];
                $discountCodeId = $result['discount_code_id'];
            }
            
            $finalTotal = ($subtotal - $discountAmount) + $shippingCost;
            $isGift = $request->boolean('is_gift');

            $newOrderItemsData = [];
            foreach ($request->products as $productId => $productData) {
                $newOrderItemsData[] = [
                    'product_id' => $productId,
                    'quantity' => $productData['quantity'],
                    'price' => $productData['price'],
                ];
            }
            $this->createOrderItemsWithRepair($order, $newOrderItemsData);

            $order->update([
                'governorate' => $request->governorate,
                'city' => $request->city,
                'address_details' => $request->address_details,
                'nearest_landmark' => $request->nearest_landmark,
                'notes' => $request->notes,
                'is_gift' => $isGift,
                'gift_recipient_name' => $isGift ? $request->gift_recipient_name : null,
                'gift_recipient_phone' => $isGift ? $request->gift_recipient_phone : null,
                'gift_recipient_address_details' => $isGift ? $request->gift_recipient_address_details : null,
                'gift_message' => $isGift ? $request->gift_message : null,
                'total_amount' => $finalTotal,
                'shipping_cost' => $shippingCost,
                'discount_amount' => $discountAmount,
                'discount_code_id' => $discountCodeId,
            ]);

            try {
                WalletService::autoApplyToOrder($order);
            } catch (\Throwable $e) {}

            DB::commit();

            return redirect()->route('admin.orders.show', $order->id)->with('success', 'تم تحديث الطلب بنجاح.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'حدث خطأ أثناء تحديث الطلب: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $order = Order::withTrashed()
                      ->with([
                          'items.product.firstImage',
                          'customer.user.addresses',
                          'user',
                          'discountCode'
                      ])
                      ->findOrFail($id);

        $subtotal = $order->items->sum(function ($item) {
            return ($item->price ?? 0) * ($item->quantity ?? 0);
        });

        // هل يوجد كوبون مفعّل؟
        $appliedDiscountCode = $order->discountCode;

        // المبلغ الذي استُخدم من المحفظة (إن كنت تسجّله بهذه الصيغة)
        $walletUsed = 0.0;
        if (class_exists(WalletTransaction::class)) {
            $walletUsed = WalletTransaction::where('order_id', $order->id)
                ->where('type', 'debit')
                ->where('description', 'LIKE', '%Order#' . $order->id . '%')
                ->sum('amount');
        }

        $originalTotalBeforeWallet = (float) $order->total_amount + $walletUsed;

        $primaryAddress = optional($order->customer?->user?->addresses)->first();

        $resolvedGovernorate = $this->preferFilled(
            $order->governorate,
            $primaryAddress?->governorate,
            $order->customer?->governorate
        );

        $resolvedCity = $this->preferFilled(
            $order->city,
            $primaryAddress?->city,
            $order->customer?->city
        );

        $addressDetails = $this->preferFilled(
            $order->address_details,
            $primaryAddress?->address_details,
            $order->customer?->address_details
        );

        $nearestLandmark = $this->preferFilled(
            $order->nearest_landmark,
            $primaryAddress?->nearest_landmark
        );

        $addressNotes = $this->preferFilled(
            $order->notes,
            $order->customer?->notes
        );

        return view('admin.orders.show', compact(
            'order',
            'subtotal',
            'appliedDiscountCode',
            'walletUsed',
            'originalTotalBeforeWallet',
            'primaryAddress',
            'resolvedGovernorate',
            'resolvedCity',
            'addressDetails',
            'nearestLandmark',
            'addressNotes'
        ));
    }

public function updateStatus(Request $request, Order $order, InventoryService $inventoryService)
{
    $request->validate(['status' => 'required|in:pending,processing,shipped,delivered,cancelled,returned']);

    $oldStatus = $order->status;
    $newStatus = $request->status;

    if ($oldStatus === $newStatus) {
        return redirect()->route('admin.orders.show', $order)->with('info', 'لم تتغير حالة الطلب.');
    }

    DB::beginTransaction();
    try {
        $statusesThatRestoreStock = ['cancelled', 'returned'];

        // إن أُلغي/أُرجع: استرجع المخزون
        if (in_array($newStatus, $statusesThatRestoreStock) && !in_array($oldStatus, $statusesThatRestoreStock)) {
            foreach ($order->items as $item) {
                $inventoryService->restoreStock($item->product, $item->quantity);
            }
        }

        $order->update(['status' => $newStatus]);

        // 1. الحفاظ على دالة إشعار التليجرام الخاصة بك
        try {
            (new \App\Observers\OrderObserver())->forceRunUpdate($order);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('--- Error during manual forceRunUpdate call ---', [
                'message' => $e->getMessage()
            ]);
        }

        // 2. ✅ [تصحيح] تفعيل مكافأة الدعوة بالشكل الصحيح
        if ($newStatus === 'delivered' && $oldStatus !== 'delivered') {
            // استدعاء دالة الخدمة التي تتأكد أنه أول طلب وتعطي المكافأة
            WalletService::creditReferralBonusForFirstOrder($order);
        }

        // 3. الحفاظ على بقية الإشعارات
        if ($order->customer && $order->customer->user && ! $order->wasCreatedManually()) {
            $order->customer->user->notify(new OrderStatusUpdated($order));
        }
        $adminRoleNames = ['Super-Admin', 'Order-Manager'];
        $admins = Manager::query()
            ->whereHas('roles', function ($query) use ($adminRoleNames) {
                $query->where('guard_name', 'admin')
                    ->whereIn('name', $adminRoleNames);
            })
            ->get();

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new AdminOrderStatusUpdated($order));
        }

        DB::commit();

        return redirect()->route('admin.orders.show', $order)->with('success', 'تم تحديث حالة الطلب بنجاح.');

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'حدث خطأ: ' . $e->getMessage());
    }
}

    protected function issueReferralReward(Order $order)
    {
        // منطق مكافأة الإحالة (كما هو لديك)
        try {
            $customerUser = optional($order->customer)->user;
            if ($customerUser && method_exists($customerUser, 'canReceiveReferralReward') && $customerUser->canReceiveReferralReward()) {
                if (class_exists(WalletService::class)) {
                    WalletService::creditReferralBonus($customerUser, $order);
                }
                if (method_exists($customerUser, 'notify')) {
                    $customerUser->notify(new ReferralBonusReceived($order));
                }
                if (method_exists($customerUser, 'update')) {
                    $customerUser->update(['referral_reward_claimed' => true]);
                }
            }
        } catch (\Throwable $e) {
            \Log::error('Referral reward error', ['message' => $e->getMessage()]);
        }
    }

    public function destroy(Order $order)
    {
        if (!in_array($order->status, ['cancelled', 'returned'])) {
            return redirect()->back()->with('error', 'لا يمكن حذف الطلب إلا إذا كانت حالته "ملغي" أو "مرتجع".');
        }

        $order->delete();
        return redirect()->route('admin.orders.index')->with('success', 'تم نقل الطلب إلى سلة المحذوفات بنجاح.');
    }

    public function trash(Request $request)
    {
        $query = Order::onlyTrashed()->with('customer');
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->whereHas('customer', function ($subQ) use ($searchTerm) {
                $subQ->where('name', 'like', "%{$searchTerm}%")
                     ->orWhere('phone_number', 'like', "%{$searchTerm}%");
            })->orWhere('id', 'like', "%{$searchTerm}%");
        }
        $trashedOrders = $query->latest()->paginate(10)->withQueryString();
        return view('admin.orders.trash', compact('trashedOrders'));
    }

    public function restore($id)
    {
        Order::onlyTrashed()->findOrFail($id)->restore();
        return redirect()->route('admin.orders.trash')->with('success', 'تم استعادة الطلب بنجاح.');
    }

    public function forceDelete($id)
    {
        $order = Order::onlyTrashed()->findOrFail($id);
        $order->forceDelete();
        return redirect()->route('admin.orders.trash')->with('success', 'تم حذف الطلب نهائياً.');
    }

    public function invoice(Order $order)
    {
        $order->load('items.product', 'customer');
        return view('admin.orders.invoice', compact('order'));
    }

    public function applyDiscount(Request $request, DiscountService $discountService)
    {
        $request->validate(['code' => 'required|string', 'subtotal' => 'required|numeric|min:0']);
        try {
            $result = $discountService->apply($request->code, $request->subtotal);
            return response()->json(['success' => true, 'discount_amount' => $result['discount_amount'], 'message' => 'تم تطبيق الخصم بنجاح.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function exportExcel()
    {
        $orders = Order::with('customer')->latest()->get();
        $data = $orders->map(function ($order) {
            return [
                $order->id,
                $order->customer?->name ?? '-',
                $order->customer?->phone_number ?? '-',
                $order->governorate ?? '-',
                $order->total_amount,
                $order->status,
                $order->created_at->format('Y-m-d'),
            ];
        })->toArray();

        return Excel::download(new OrdersExport($data), 'orders.xlsx');
    }
}
