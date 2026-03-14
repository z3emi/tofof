<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Apply permission middleware to protect controller actions.
     */
    public function __construct()
    {
        $permissionMiddleware = \Spatie\Permission\Middleware\PermissionMiddleware::class;

        // Please ensure these permissions exist in your seeder.
        // I am assuming the permission names follow this pattern.
        $this->middleware($permissionMiddleware . ':view-suppliers', ['only' => ['index']]);
        $this->middleware($permissionMiddleware . ':create-suppliers', ['only' => ['create', 'store']]);
        $this->middleware($permissionMiddleware . ':edit-suppliers', ['only' => ['edit', 'update']]);
        $this->middleware($permissionMiddleware . ':delete-suppliers', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of all suppliers.
     */
    public function index()
    {
        $suppliers = Supplier::latest()->paginate(10);
        return view('admin.suppliers.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new supplier.
     */
    public function create()
    {
        return view('admin.suppliers.create');
    }

    /**
     * Store a newly created supplier in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:suppliers,email',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        Supplier::create($request->all());

        return redirect()->route('admin.suppliers.index')->with('success', 'تم إضافة المورد بنجاح.');
    }

    /**
     * Show the form for editing the specified supplier.
     */
    public function edit(Supplier $supplier)
    {
        return view('admin.suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified supplier in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:suppliers,email,' . $supplier->id,
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $supplier->update($request->all());

        return redirect()->route('admin.suppliers.index')->with('success', 'تم تحديث المورد بنجاح.');
    }

    /**
     * Remove the specified supplier from storage.
     */
    public function destroy(Supplier $supplier)
    {
        // You might want to add a check here to prevent deleting a supplier
        // if they have associated purchase invoices.
        if ($supplier->purchaseInvoices()->exists()) {
            return redirect()->route('admin.suppliers.index')->with('error', 'لا يمكن حذف المورد لأنه مرتبط بفواتير شراء.');
        }

        $supplier->delete();
        return redirect()->route('admin.suppliers.index')->with('success', 'تم حذف المورد بنجاح.');
    }
}
