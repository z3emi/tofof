<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryWarehouse;
use App\Models\PurchaseInvoiceItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InventoryWarehouseController extends Controller
{
    public function __construct()
    {
        $permissionMiddleware = \Spatie\Permission\Middleware\PermissionMiddleware::class;

        $this->middleware($permissionMiddleware . ':view-inventory', ['only' => ['index', 'show']]);
        $this->middleware($permissionMiddleware . ':manage-inventory', ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);
    }

    public function index(): View
    {
        $totalsSubquery = PurchaseInvoiceItem::query()
            ->selectRaw('
                warehouse_id,
                SUM(COALESCE(quantity_remaining, 0)) as total_quantity,
                SUM(COALESCE(quantity_remaining, 0) * COALESCE(purchase_price, 0)) as total_value,
                COUNT(DISTINCT product_id) as distinct_products
            ')
            ->groupBy('warehouse_id');

        $warehouses = InventoryWarehouse::query()
            ->leftJoinSub($totalsSubquery, 'inventory_totals', 'inventory_totals.warehouse_id', '=', 'inventory_warehouses.id')
            ->select([
                'inventory_warehouses.*',
                DB::raw('COALESCE(inventory_totals.total_quantity, 0) as total_quantity'),
                DB::raw('COALESCE(inventory_totals.total_value, 0) as total_value'),
                DB::raw('COALESCE(inventory_totals.distinct_products, 0) as distinct_products'),
            ])
            ->orderBy('inventory_warehouses.name')
            ->get();

        $grandTotals = [
            'quantity' => $warehouses->sum('total_quantity'),
            'value' => $warehouses->sum('total_value'),
        ];

        return view('admin.warehouses.index', [
            'warehouses' => $warehouses,
            'grandTotals' => $grandTotals,
        ]);
    }

    public function create(): View
    {
        return view('admin.warehouses.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateWarehouse($request);

        $prepared = $this->prepareWarehousePayload($validated);

        InventoryWarehouse::create($prepared);

        return redirect()
            ->route('admin.warehouses.index')
            ->with('status', __('تم إنشاء المخزن بنجاح.'));
    }

    public function show(InventoryWarehouse $warehouse): View
    {
        $warehouse->load(['purchaseItems' => function ($query) {
            $query->with(['product'])->orderByDesc('created_at');
        }]);

        $productGroups = $this->groupInventoryByProduct($warehouse->purchaseItems);

        $totals = [
            'quantity' => $productGroups->sum('total_quantity'),
            'value' => $productGroups->sum('total_value'),
        ];

        return view('admin.warehouses.show', [
            'warehouse' => $warehouse,
            'productGroups' => $productGroups,
            'totals' => $totals,
        ]);
    }

    public function edit(InventoryWarehouse $warehouse): View
    {
        return view('admin.warehouses.edit', [
            'warehouse' => $warehouse,
        ]);
    }

    public function update(Request $request, InventoryWarehouse $warehouse): RedirectResponse
    {
        $validated = $this->validateWarehouse($request, $warehouse);

        $prepared = $this->prepareWarehousePayload($validated, $warehouse);

        $warehouse->update($prepared);

        return redirect()
            ->route('admin.warehouses.index')
            ->with('status', __('تم تحديث بيانات المخزن بنجاح.'));
    }

    public function destroy(InventoryWarehouse $warehouse): RedirectResponse
    {
        if ($warehouse->purchaseItems()->exists()) {
            return redirect()
                ->route('admin.warehouses.index')
                ->withErrors(['warehouse' => __('لا يمكن حذف المخزن لأنه مرتبط بسجلات مخزون.')] );
        }

        $warehouse->delete();

        return redirect()
            ->route('admin.warehouses.index')
            ->with('status', __('تم حذف المخزن بنجاح.'));
    }

    private function validateWarehouse(Request $request, ?InventoryWarehouse $warehouse = null): array
    {
        $warehouseId = $warehouse?->id;

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('inventory_warehouses', 'code')->ignore($warehouseId),
            ],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    private function prepareWarehousePayload(array $validated, ?InventoryWarehouse $warehouse = null): array
    {
        $validated['code'] = $this->resolveWarehouseCode(
            $validated['code'] ?? null,
            $validated['name'] ?? '',
            $warehouse
        );

        return $validated;
    }

    private function resolveWarehouseCode(?string $code, string $name, ?InventoryWarehouse $warehouse = null): string
    {
        $code = $code !== null ? trim($code) : '';

        if ($code !== '') {
            return $code;
        }

        if ($warehouse && $warehouse->code) {
            return $warehouse->code;
        }

        $existingId = $warehouse?->id;
        $base = Str::upper(Str::slug($name, '-')) ?: 'WH';
        $base = mb_substr($base, 0, 45);
        $candidate = $base;
        $suffix = 1;

        while (
            InventoryWarehouse::query()
                ->when($existingId, fn ($query) => $query->where('id', '!=', $existingId))
                ->where('code', $candidate)
                ->exists()
        ) {
            $candidate = $base . '-' . $suffix;
            $suffix++;
        }

        return $candidate;
    }

    private function groupInventoryByProduct(Collection $items): Collection
    {
        return $items
            ->groupBy(function (PurchaseInvoiceItem $item) {
                $productKey = $item->product_id ?: 'productless';
                $variantKey = $item->variant_sku ?: 'default';

                return $productKey . ':' . $variantKey;
            })
            ->map(function (Collection $group) {
                /** @var PurchaseInvoiceItem $first */
                $first = $group->first();
                $product = $first->product;

                $totalQuantity = (float) $group->sum('quantity_remaining');
                $totalValue = $group->sum(function (PurchaseInvoiceItem $item) {
                    return (float) $item->quantity_remaining * (float) $item->purchase_price;
                });

                return [
                    'product' => $product,
                    'variant_name' => $first->variant_name,
                    'sku' => $first->variant_sku ?: ($product->sku ?? null),
                    'total_quantity' => $totalQuantity,
                    'total_value' => $totalValue,
                    'batches' => $group->map(function (PurchaseInvoiceItem $item) {
                        return [
                            'id' => $item->id,
                            'batch_number' => $item->batch_number,
                            'quantity' => (float) $item->quantity_remaining,
                            'purchase_price' => (float) $item->purchase_price,
                            'reorder_point' => $item->reorder_point,
                            'expires_at' => optional($item->expires_at)->format('Y-m-d'),
                            'created_at' => optional($item->created_at)->format('Y-m-d'),
                        ];
                    })->values(),
                ];
            })
            ->sortBy(function (array $data) {
                $product = $data['product'];
                $name = '';

                if ($product) {
                    $name = $product->name_ar
                        ?? $product->name_en
                        ?? $product->name
                        ?? '';
                }

                $variant = $data['variant_name'] ?? '';

                return trim($name . ' ' . $variant);
            })
            ->values();
    }
}
