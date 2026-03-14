<?php

namespace App\Http\Controllers\Admin\Manufacturing;

use App\Http\Controllers\Controller;
use App\Models\ManufacturingBOM;
use App\Models\ManufacturingMaterial;
use App\Models\ManufacturingOrder;
use App\Models\ManufacturingOrderMaterial;
use App\Models\ManufacturingShipment;
use App\Models\Product;
use App\Support\Sort;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Middleware\PermissionMiddleware;

class OrderController extends Controller
{
    public function __construct()
    {
        $permissionMiddleware = PermissionMiddleware::class;

        $this->middleware($permissionMiddleware . ':view-inventory');
        $this->middleware($permissionMiddleware . ':manage-inventory')->except(['index', 'show']);
    }

    public function index(Request $request): View
    {
        [$sortBy, $sortDirection] = Sort::resolve(
            $request,
            ['reference', 'planned_quantity', 'completed_quantity', 'status', 'starts_at', 'due_at', 'total_cost', 'created_at'],
            'created_at'
        );

        $filters = [
            'search'      => trim((string) $request->query('search', '')),
            'status'      => $request->query('status') ?: null,
            'product_id'  => $request->query('product_id') ?: null,
            'starts_from' => $request->query('starts_from') ?: null,
            'starts_to'   => $request->query('starts_to') ?: null,
            'due_from'    => $request->query('due_from') ?: null,
            'due_to'      => $request->query('due_to') ?: null,
            'shipments'   => $request->query('shipments') ?: null,
        ];

        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(5, min(100, $perPage));

        $ordersQuery = ManufacturingOrder::query()->with(['product', 'shipments.warehouse']);

        if ($filters['search'] !== '') {
            $ordersQuery->where(function ($query) use ($filters) {
                $query->where('reference', 'LIKE', '%' . $filters['search'] . '%')
                    ->orWhere('variant_name', 'LIKE', '%' . $filters['search'] . '%')
                    ->orWhereHas('product', function ($productQuery) use ($filters) {
                        $productQuery->where('name', 'LIKE', '%' . $filters['search'] . '%')
                            ->orWhere('name_ar', 'LIKE', '%' . $filters['search'] . '%');
                    });
            });
        }

        if ($filters['status']) {
            $ordersQuery->where('status', $filters['status']);
        }

        if ($filters['product_id']) {
            $ordersQuery->where('product_id', $filters['product_id']);
        }

        if ($filters['starts_from']) {
            $ordersQuery->whereDate('starts_at', '>=', $filters['starts_from']);
        }

        if ($filters['starts_to']) {
            $ordersQuery->whereDate('starts_at', '<=', $filters['starts_to']);
        }

        if ($filters['due_from']) {
            $ordersQuery->whereDate('due_at', '>=', $filters['due_from']);
        }

        if ($filters['due_to']) {
            $ordersQuery->whereDate('due_at', '<=', $filters['due_to']);
        }

        if ($filters['shipments'] === 'with') {
            $ordersQuery->whereHas('shipments');
        } elseif ($filters['shipments'] === 'without') {
            $ordersQuery->whereDoesntHave('shipments');
        }

        $orders = $ordersQuery
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage)
            ->withQueryString();

        $productOptions = Product::orderBy('name_ar')->pluck('name_ar', 'id');

        return view('admin.manufacturing.orders.index', [
            'orders' => $orders,
            'filters' => $filters,
            'productOptions' => $productOptions,
            'allowedSorts' => ['reference', 'planned_quantity', 'completed_quantity', 'status', 'starts_at', 'due_at', 'total_cost', 'created_at'],
            'defaultSortColumn' => 'created_at',
            'defaultSortDirection' => 'desc',
            'perPage' => $perPage,
        ]);
    }

    public function create(): View
    {
        return view('admin.manufacturing.orders.create', [
            'products' => Product::orderBy('name_ar')->get(),
            'boms' => ManufacturingBOM::with('product')->orderBy('product_id')->get(),
            'materials' => ManufacturingMaterial::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        DB::transaction(function () use ($data) {
            $order = ManufacturingOrder::create([
                'reference' => $data['reference'],
                'product_id' => $data['product_id'],
                'variant_name' => $data['variant_name'] ?? null,
                'planned_quantity' => $data['planned_quantity'],
                'completed_quantity' => $data['completed_quantity'] ?? 0,
                'status' => $data['status'],
                'starts_at' => $data['starts_at'] ?? null,
                'due_at' => $data['due_at'] ?? null,
                'total_cost' => 0,
                'notes' => $data['notes'] ?? null,
            ]);

            $totalCost = 0;
            foreach ($data['materials'] as $material) {
                $record = ManufacturingOrderMaterial::create([
                    'order_id' => $order->id,
                    'material_id' => $material['material_id'],
                    'quantity_used' => $material['quantity_used'],
                    'cost' => $material['cost'],
                ]);
                $totalCost += (float) $record->cost;
            }

            $order->update(['total_cost' => $totalCost]);
        });

        return redirect()
            ->route('admin.manufacturing.orders.index')
            ->with('status', __('تم إنشاء أمر التصنيع بنجاح.'));
    }

    public function show(ManufacturingOrder $order): View
    {
        $order->load(['product', 'materials.material', 'shipments.warehouse']);

        return view('admin.manufacturing.orders.show', compact('order'));
    }

    public function edit(ManufacturingOrder $order): View
    {
        $order->load('materials');

        return view('admin.manufacturing.orders.edit', [
            'order' => $order,
            'products' => Product::orderBy('name_ar')->get(),
            'boms' => ManufacturingBOM::with('product')->orderBy('product_id')->get(),
            'materials' => ManufacturingMaterial::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, ManufacturingOrder $order): RedirectResponse
    {
        $data = $this->validateData($request, $order->id);

        DB::transaction(function () use ($order, $data) {
            $order->update([
                'reference' => $data['reference'],
                'product_id' => $data['product_id'],
                'variant_name' => $data['variant_name'] ?? null,
                'planned_quantity' => $data['planned_quantity'],
                'completed_quantity' => $data['completed_quantity'] ?? 0,
                'status' => $data['status'],
                'starts_at' => $data['starts_at'] ?? null,
                'due_at' => $data['due_at'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $order->materials()->delete();

            $totalCost = 0;
            foreach ($data['materials'] as $material) {
                $record = ManufacturingOrderMaterial::create([
                    'order_id' => $order->id,
                    'material_id' => $material['material_id'],
                    'quantity_used' => $material['quantity_used'],
                    'cost' => $material['cost'],
                ]);
                $totalCost += (float) $record->cost;
            }

            $order->update(['total_cost' => $totalCost]);
            $this->refreshCompletion($order);
        });

        return redirect()
            ->route('admin.manufacturing.orders.index')
            ->with('status', __('تم تحديث أمر التصنيع.'));
    }

    public function destroy(ManufacturingOrder $order): RedirectResponse
    {
        $order->delete();

        return redirect()
            ->route('admin.manufacturing.orders.index')
            ->with('status', __('تم حذف أمر التصنيع.'));
    }

    protected function validateData(Request $request, ?int $orderId = null): array
    {
        $validated = $request->validate([
            'reference' => ['required', 'string', 'max:50', Rule::unique('manufacturing_orders', 'reference')->ignore($orderId)],
            'product_id' => ['required', 'exists:products,id'],
            'variant_name' => ['nullable', 'string', 'max:150'],
            'planned_quantity' => ['required', 'numeric', 'min:1'],
            'completed_quantity' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['planned', 'in_progress', 'completed', 'cancelled'])],
            'starts_at' => ['nullable', 'date'],
            'due_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'notes' => ['nullable', 'string'],
            'materials' => ['nullable', 'array'],
            'materials.*.material_id' => ['nullable', 'exists:manufacturing_materials,id'],
            'materials.*.quantity_used' => ['nullable', 'numeric', 'min:0'],
            'materials.*.cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $validated['materials'] = collect($validated['materials'] ?? [])
            ->filter(fn ($row) => !empty($row['material_id']))
            ->map(fn ($row) => [
                'material_id' => $row['material_id'],
                'quantity_used' => $row['quantity_used'] ?? 0,
                'cost' => $row['cost'] ?? 0,
            ])
            ->values()
            ->all();

        return $validated;
    }

    protected function refreshCompletion(ManufacturingOrder $order): void
    {
        $completed = ManufacturingShipment::where('order_id', $order->id)->sum('shipped_quantity');
        $order->forceFill(['completed_quantity' => $completed])->save();
    }
}
