<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Apply permission middleware to protect controller actions.
     */
    public function __construct()
    {
        $permissionMiddleware = \Spatie\Permission\Middleware\PermissionMiddleware::class;

        // Protect the inventory page with the 'view-inventory' permission.
        $this->middleware($permissionMiddleware . ':view-inventory', ['only' => ['index', 'updateStock']]);
    }

    /**
     * Display the detailed inventory page.
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'firstImage']);
        
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name_ar', 'like', "%{$searchTerm}%")
                  ->orWhere('name_en', 'like', "%{$searchTerm}%")
                  ->orWhere('name_ku', 'like', "%{$searchTerm}%")
                  ->orWhere('sku', 'like', "%{$searchTerm}%");
            });
        }
        
        $products = $query->paginate(20)->withQueryString();

        // --- Summary Calculations ---
        $grandTotalValue = 0;
        $grandTotalQuantity = 0;
        
        $allProducts = Product::select('stock_quantity', 'price')->get();
        foreach ($allProducts as $item) {
            $grandTotalValue += $item->stock_quantity * $item->price;
            $grandTotalQuantity += $item->stock_quantity;
        }

        $uniqueProductsCount = count($allProducts);
        
        return view('admin.inventory.index', [
            'products' => $products,
            'grandTotalValue' => $grandTotalValue,
            'grandTotalQuantity' => $grandTotalQuantity,
            'uniqueProductsCount' => $uniqueProductsCount,
        ]);
    }
    
    /**
     * Update stock directly.
     */
    public function updateStock(Request $request, Product $product)
    {
        $request->validate([
            'stock_quantity' => 'required|integer|min:0'
        ]);
        
        $product->update([
            'stock_quantity' => $request->stock_quantity
        ]);
        
        return back()->with('success', 'تم تحديث كمية المنتج بنجاح.');
    }
}
