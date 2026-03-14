<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryWarehouse;
use App\Models\ManufacturingMaterial;
use App\Models\ManufacturingBOM;
use App\Models\ManufacturingOrder;
use App\Models\ManufacturingShipment;
use Illuminate\View\View;

class ManufacturingController extends Controller
{
    public function __construct()
    {
        $permissionMiddleware = \Spatie\Permission\Middleware\PermissionMiddleware::class;
        $this->middleware($permissionMiddleware . ':view-inventory');
    }

    public function index(): View
    {
        $materials = ManufacturingMaterial::orderBy('name')->get();
        $boms = ManufacturingBOM::with(['product', 'items.material'])->orderByDesc('updated_at')->get();
        $orders = ManufacturingOrder::with(['product', 'shipments.warehouse'])->latest('starts_at')->paginate(10);
        $shipments = ManufacturingShipment::with(['order', 'warehouse'])->latest('shipped_at')->take(10)->get();

        $activeOrders = ManufacturingOrder::whereIn('status', ['planned', 'in_progress'])->count();
        $completedOrders = ManufacturingOrder::where('status', 'completed')->count();
        $totalProductionCost = ManufacturingOrder::sum('total_cost');

        return view('admin.manufacturing.index', compact(
            'materials',
            'boms',
            'orders',
            'shipments',
            'activeOrders',
            'completedOrders',
            'totalProductionCost'
        ));
    }
}
