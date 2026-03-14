<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseInvoice;
use App\Models\Supplier;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseInvoiceController extends Controller
{
    /**
     * Apply permission middleware to protect controller actions.
     */
    public function __construct()
    {
        $permissionMiddleware = \Spatie\Permission\Middleware\PermissionMiddleware::class;

        $this->middleware($permissionMiddleware . ':view-purchases', ['only' => ['index', 'show']]);
        $this->middleware($permissionMiddleware . ':create-purchases', ['only' => ['create', 'store']]);
        $this->middleware($permissionMiddleware . ':edit-purchases', ['only' => ['edit', 'update']]);
        $this->middleware($permissionMiddleware . ':delete-purchases', ['only' => ['destroy']]);
    }

    public function index()
    {
        $purchases = PurchaseInvoice::with('supplier')->latest()->paginate(15);
        return view('admin.purchases.index', compact('purchases'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::orderBy('name_ar')->get();
        return view('admin.purchases.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_date' => 'required|date',
            'invoice_number' => 'nullable|string|max:255|unique:purchase_invoices,invoice_number',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.purchase_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $invoice = PurchaseInvoice::create($request->only('supplier_id', 'invoice_date', 'invoice_number', 'notes'));
            $totalAmount = 0;

            foreach ($request->items as $item) {
                $invoice->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'quantity_remaining' => $item['quantity'],
                    'purchase_price' => $item['purchase_price'],
                ]);
                $totalAmount += $item['quantity'] * $item['purchase_price'];
            }

            $invoice->update(['total_amount' => $totalAmount]);
            DB::commit();

            return redirect()->route('admin.purchases.index')->with('success', 'تم إنشاء فاتورة الشراء بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'حدث خطأ: ' . $e->getMessage())->withInput();
        }
    }

    public function show(PurchaseInvoice $purchase)
    {
        $purchase->load('supplier', 'items.product');
        return view('admin.purchases.show', compact('purchase'));
    }

    public function edit(PurchaseInvoice $purchase)
    {
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::orderBy('name_ar')->get();
        return view('admin.purchases.edit', compact('purchase', 'suppliers', 'products'));
    }

public function update(Request $request, PurchaseInvoice $purchase)
{
    $request->validate([
        'supplier_id' => 'required|exists:suppliers,id',
        'invoice_date' => 'required|date',
        'invoice_number' => 'nullable|string|max:255|unique:purchase_invoices,invoice_number,' . $purchase->id,
        'items' => 'sometimes|array', // sometimes, in case the array is empty after deleting all items
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1',
        'items.*.purchase_price' => 'required|numeric|min:0',
    ]);

    DB::beginTransaction();
    try {
        // --- منطق الحماية: التأكد من عدم تعديل كميات تم بيعها ---
        $existingItems = $purchase->items()->with('product')->get()->keyBy('product_id');
        $submittedItems = collect($request->items ?? [])->keyBy('product_id');

        foreach ($existingItems as $productId => $existingItem) {
            $unitsSold = $existingItem->quantity - $existingItem->quantity_remaining;
            
            if ($unitsSold > 0) {
                // الحالة 1: محاولة حذف بند تم بيع جزء منه
                if (!$submittedItems->has($productId)) {
                    throw new \Exception("لا يمكن حذف منتج '{$existingItem->product->name_ar}' لأنه تم بيع كمية منه بالفعل.");
                }
                
                $newQuantity = (int) $submittedItems[$productId]['quantity'];
                // الحالة 2: محاولة تقليل الكمية إلى أقل من الكمية المباعة
                if ($newQuantity < $unitsSold) {
                    throw new \Exception("لا يمكن تقليل كمية منتج '{$existingItem->product->name_ar}' إلى أقل من {$unitsSold} قطعة (الكمية المباعة).");
                }
            }
        }
        // --- نهاية منطق الحماية ---

        // تحديث البيانات الأساسية للفاتورة
        $purchase->update($request->only('supplier_id', 'invoice_date', 'invoice_number', 'notes'));
        
        // حذف البنود القديمة لإعادة بنائها
        $purchase->items()->delete();

        // إضافة البنود الجديدة وحساب الإجمالي
        $totalAmount = 0;
        if ($request->has('items')) {
            foreach ($request->items as $itemData) {
                $newQuantity = (int) $itemData['quantity'];
                
                $existingItem = $existingItems->get($itemData['product_id']);
                $unitsSold = $existingItem ? ($existingItem->quantity - $existingItem->quantity_remaining) : 0;
                
                // الكمية المتبقية الجديدة = الكمية الإجمالية الجديدة - الكمية المباعة سابقاً
                $newRemainingQty = $newQuantity - $unitsSold;

                $purchase->items()->create([
                    'product_id' => $itemData['product_id'],
                    'quantity' => $newQuantity,
                    'quantity_remaining' => $newRemainingQty,
                    'purchase_price' => $itemData['purchase_price'],
                ]);
                $totalAmount += $newQuantity * $itemData['purchase_price'];
            }
        }

        // تحديث المبلغ الإجمالي للفاتورة
        $purchase->update(['total_amount' => $totalAmount]);

        DB::commit();

        return redirect()->route('admin.purchases.index')->with('success', 'تم تحديث فاتورة الشراء بنجاح.');

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'حدث خطأ: ' . $e->getMessage())->withInput();
    }
}
    public function destroy(PurchaseInvoice $purchase)
    {
        DB::beginTransaction();
        try {
            // تحميل البنود والمنتجات المرتبطة بها للحصول على الاسم في رسالة الخطأ
            $purchase->load('items.product');

            // التحقق مما إذا تم استخدام أي كمية من هذه الفاتورة في المبيعات
            foreach ($purchase->items as $item) {
                if ($item->quantity > $item->quantity_remaining) {
                    // إذا كانت الكمية المتبقية أقل من الأصلية، فهذا يعني أنه تم بيع جزء منها
                    throw new \Exception("لا يمكن حذف الفاتورة. تم بيع كمية من منتج '{$item->product->name_ar}' من هذه الفاتورة.");
                }
            }

            // إذا اكتملت الحلقة بدون أخطاء، فهذا يعني أنه من الآمن حذف الفاتورة
            // سيقوم قيد المفتاح الخارجي في قاعدة البيانات بحذف البنود تلقائيًا
            $purchase->delete();

            DB::commit();

            return redirect()->route('admin.purchases.index')->with('success', 'تم حذف فاتورة الشراء بنجاح.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
