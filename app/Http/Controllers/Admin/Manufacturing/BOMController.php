<?php

namespace App\Http\Controllers\Admin\Manufacturing;

use App\Http\Controllers\Controller;
use App\Models\ManufacturingBOM;
use App\Models\ManufacturingBOMItem;
use App\Models\ManufacturingMaterial;
use App\Models\Product;
use App\Support\Sort;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Permission\Middleware\PermissionMiddleware;

class BOMController extends Controller
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
            ['id', 'product_id', 'items_count', 'updated_at', 'created_at'],
            'updated_at'
        );

        $filters = [
            'search'       => trim((string) $request->query('search', '')),
            'product_id'   => $request->query('product_id') ?: null,
            'has_notes'    => $request->query('has_notes') ?: null,
            'updated_from' => $request->query('updated_from') ?: null,
            'updated_to'   => $request->query('updated_to') ?: null,
        ];

        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(5, min(100, $perPage));

        $bomsQuery = ManufacturingBOM::query()
            ->with('product')
            ->withCount('items');

        if ($filters['search'] !== '') {
            $bomsQuery->where(function ($query) use ($filters) {
                $query->where('variant_name', 'LIKE', '%' . $filters['search'] . '%')
                    ->orWhereHas('product', function ($productQuery) use ($filters) {
                        $productQuery->where('name', 'LIKE', '%' . $filters['search'] . '%')
                            ->orWhere('name_ar', 'LIKE', '%' . $filters['search'] . '%');
                    })
                    ->orWhere('id', $filters['search']);
            });
        }

        if ($filters['product_id']) {
            $bomsQuery->where('product_id', $filters['product_id']);
        }

        if ($filters['has_notes'] === 'with') {
            $bomsQuery->whereNotNull('notes')->where('notes', '!=', '');
        } elseif ($filters['has_notes'] === 'without') {
            $bomsQuery->where(function ($query) {
                $query->whereNull('notes')->orWhere('notes', '');
            });
        }

        if ($filters['updated_from']) {
            $bomsQuery->whereDate('updated_at', '>=', $filters['updated_from']);
        }

        if ($filters['updated_to']) {
            $bomsQuery->whereDate('updated_at', '<=', $filters['updated_to']);
        }

        $boms = $bomsQuery
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage)
            ->withQueryString();

        $productOptions = Product::orderBy('name_ar')->pluck('name_ar', 'id');

        return view('admin.manufacturing.boms.index', [
            'boms' => $boms,
            'filters' => $filters,
            'productOptions' => $productOptions,
            'allowedSorts' => ['id', 'product_id', 'items_count', 'updated_at', 'created_at'],
            'defaultSortColumn' => 'updated_at',
            'defaultSortDirection' => 'desc',
            'perPage' => $perPage,
        ]);
    }

    public function create(): View
    {
        return view('admin.manufacturing.boms.create', [
            'products' => Product::orderBy('name_ar')->get(),
            'materials' => ManufacturingMaterial::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'variant_name' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.material_id' => ['required', 'exists:manufacturing_materials,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
        ]);

        DB::transaction(function () use ($data) {
            $bom = ManufacturingBOM::create([
                'product_id' => $data['product_id'],
                'variant_name' => $data['variant_name'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                ManufacturingBOMItem::create([
                    'bom_id' => $bom->id,
                    'material_id' => $item['material_id'],
                    'quantity' => $item['quantity'],
                ]);
            }
        });

        return redirect()
            ->route('admin.manufacturing.boms.index')
            ->with('status', __('تم إنشاء وصفة التصنيع بنجاح.'));
    }

    public function show(ManufacturingBOM $bom): View
    {
        $bom->load(['product', 'items.material']);

        return view('admin.manufacturing.boms.show', compact('bom'));
    }

    public function edit(ManufacturingBOM $bom): View
    {
        $bom->load('items');

        return view('admin.manufacturing.boms.edit', [
            'bom' => $bom,
            'products' => Product::orderBy('name_ar')->get(),
            'materials' => ManufacturingMaterial::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, ManufacturingBOM $bom): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'variant_name' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.material_id' => ['required', 'exists:manufacturing_materials,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
        ]);

        DB::transaction(function () use ($bom, $data) {
            $bom->update([
                'product_id' => $data['product_id'],
                'variant_name' => $data['variant_name'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $bom->items()->delete();

            foreach ($data['items'] as $item) {
                ManufacturingBOMItem::create([
                    'bom_id' => $bom->id,
                    'material_id' => $item['material_id'],
                    'quantity' => $item['quantity'],
                ]);
            }
        });

        return redirect()
            ->route('admin.manufacturing.boms.index')
            ->with('status', __('تم تحديث وصفة التصنيع بنجاح.'));
    }

    public function destroy(ManufacturingBOM $bom): RedirectResponse
    {
        $bom->delete();

        return redirect()
            ->route('admin.manufacturing.boms.index')
            ->with('status', __('تم حذف وصفة التصنيع.'));
    }
}
