<?php

namespace App\Http\Controllers\Admin\Manufacturing;

use App\Http\Controllers\Controller;
use App\Models\InventoryWarehouse;
use App\Models\ManufacturingOrder;
use App\Models\ManufacturingShipment;
use App\Support\Sort;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Permission\Middleware\PermissionMiddleware;

class ShipmentController extends Controller
{
    public function __construct()
    {
        $permissionMiddleware = PermissionMiddleware::class;

        $this->middleware($permissionMiddleware . ':view-inventory');
        $this->middleware($permissionMiddleware . ':manage-inventory')->except(['index']);
    }

    public function index(Request $request): View
    {
        [$sortBy, $sortDirection] = Sort::resolve(
            $request,
            ['order_id', 'warehouse_id', 'shipped_at', 'shipped_quantity', 'created_at'],
            'shipped_at'
        );

        $filters = [
            'search'       => trim((string) $request->query('search', '')),
            'order_id'     => $request->query('order_id') ?: null,
            'warehouse_id' => $request->query('warehouse_id') ?: null,
            'date_from'    => $request->query('date_from') ?: null,
            'date_to'      => $request->query('date_to') ?: null,
            'tracking'     => $request->query('tracking') ?: null,
        ];

        $perPage = (int) $request->query('per_page', 20);
        $perPage = max(5, min(100, $perPage));

        $shipmentsQuery = ManufacturingShipment::query()->with(['order', 'warehouse']);

        if ($filters['search'] !== '') {
            $shipmentsQuery->where(function ($query) use ($filters) {
                $query->where('tracking_number', 'LIKE', '%' . $filters['search'] . '%')
                    ->orWhere('notes', 'LIKE', '%' . $filters['search'] . '%')
                    ->orWhereHas('order', function ($orderQuery) use ($filters) {
                        $orderQuery->where('reference', 'LIKE', '%' . $filters['search'] . '%');
                    });
            });
        }

        if ($filters['order_id']) {
            $shipmentsQuery->where('order_id', $filters['order_id']);
        }

        if ($filters['warehouse_id']) {
            $shipmentsQuery->where('warehouse_id', $filters['warehouse_id']);
        }

        if ($filters['date_from']) {
            $shipmentsQuery->whereDate('shipped_at', '>=', $filters['date_from']);
        }

        if ($filters['date_to']) {
            $shipmentsQuery->whereDate('shipped_at', '<=', $filters['date_to']);
        }

        if ($filters['tracking'] === 'with') {
            $shipmentsQuery->whereNotNull('tracking_number')->where('tracking_number', '!=', '');
        } elseif ($filters['tracking'] === 'without') {
            $shipmentsQuery->where(function ($query) {
                $query->whereNull('tracking_number')->orWhere('tracking_number', '');
            });
        }

        $shipments = $shipmentsQuery
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage)
            ->withQueryString();

        $orderOptions = ManufacturingOrder::orderByDesc('created_at')->pluck('reference', 'id');
        $warehouseOptions = InventoryWarehouse::orderBy('name')->pluck('name', 'id');

        return view('admin.manufacturing.shipments.index', [
            'shipments' => $shipments,
            'filters' => $filters,
            'orderOptions' => $orderOptions,
            'warehouseOptions' => $warehouseOptions,
            'allowedSorts' => ['order_id', 'warehouse_id', 'shipped_at', 'shipped_quantity', 'created_at'],
            'defaultSortColumn' => 'shipped_at',
            'defaultSortDirection' => 'desc',
            'perPage' => $perPage,
        ]);
    }

    public function create(): View
    {
        return view('admin.manufacturing.shipments.create', [
            'orders' => ManufacturingOrder::orderByDesc('created_at')->get(),
            'warehouses' => InventoryWarehouse::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'order_id' => ['required', 'exists:manufacturing_orders,id'],
            'warehouse_id' => ['required', 'exists:inventory_warehouses,id'],
            'shipped_quantity' => ['required', 'numeric', 'min:0'],
            'shipped_at' => ['required', 'date'],
            'tracking_number' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($data) {
            $shipment = ManufacturingShipment::create($data);
            $this->recalculateOrderCompletion($shipment->order_id);
        });

        return redirect()
            ->route('admin.manufacturing.shipments.index')
            ->with('status', __('تم تسجيل الشحنة بنجاح.'));
    }

    public function edit(ManufacturingShipment $shipment): View
    {
        return view('admin.manufacturing.shipments.edit', [
            'shipment' => $shipment,
            'orders' => ManufacturingOrder::orderByDesc('created_at')->get(),
            'warehouses' => InventoryWarehouse::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, ManufacturingShipment $shipment): RedirectResponse
    {
        $data = $request->validate([
            'order_id' => ['required', 'exists:manufacturing_orders,id'],
            'warehouse_id' => ['required', 'exists:inventory_warehouses,id'],
            'shipped_quantity' => ['required', 'numeric', 'min:0'],
            'shipped_at' => ['required', 'date'],
            'tracking_number' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($shipment, $data) {
            $shipment->update($data);
            $this->recalculateOrderCompletion($shipment->order_id);
        });

        return redirect()
            ->route('admin.manufacturing.shipments.index')
            ->with('status', __('تم تحديث بيانات الشحنة.'));
    }

    public function destroy(ManufacturingShipment $shipment): RedirectResponse
    {
        DB::transaction(function () use ($shipment) {
            $orderId = $shipment->order_id;
            $shipment->delete();
            $this->recalculateOrderCompletion($orderId);
        });

        return redirect()
            ->route('admin.manufacturing.shipments.index')
            ->with('status', __('تم حذف الشحنة.'));
    }

    protected function recalculateOrderCompletion(int $orderId): void
    {
        $total = ManufacturingShipment::where('order_id', $orderId)->sum('shipped_quantity');
        ManufacturingOrder::where('id', $orderId)->update(['completed_quantity' => $total]);
    }
}
