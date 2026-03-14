<?php

namespace App\Http\Controllers\Admin\Manufacturing;

use App\Http\Controllers\Controller;
use App\Models\ManufacturingMaterial;
use App\Support\Sort;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Middleware\PermissionMiddleware;

class MaterialController extends Controller
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
            ['name', 'sku', 'unit', 'cost_per_unit', 'updated_at', 'created_at'],
            'name',
            'asc'
        );

        $filters = [
            'search'    => trim((string) $request->query('search', '')),
            'unit'      => $request->query('unit') ?: null,
            'notes'     => $request->query('notes') ?: null,
            'cost_min'  => $request->query('cost_min'),
            'cost_max'  => $request->query('cost_max'),
        ];

        $perPage = (int) $request->query('per_page', 20);
        $perPage = max(5, min(100, $perPage));

        $materialsQuery = ManufacturingMaterial::query();

        if ($filters['search'] !== '') {
            $materialsQuery->where(function ($query) use ($filters) {
                $query->where('name', 'LIKE', '%' . $filters['search'] . '%')
                    ->orWhere('sku', 'LIKE', '%' . $filters['search'] . '%')
                    ->orWhere('notes', 'LIKE', '%' . $filters['search'] . '%');
            });
        }

        if ($filters['unit']) {
            $materialsQuery->where('unit', $filters['unit']);
        }

        if ($filters['notes'] === 'with') {
            $materialsQuery->whereNotNull('notes')->where('notes', '!=', '');
        } elseif ($filters['notes'] === 'without') {
            $materialsQuery->where(function ($query) {
                $query->whereNull('notes')->orWhere('notes', '');
            });
        }

        $costMin = is_numeric($filters['cost_min']) ? (float) $filters['cost_min'] : null;
        $costMax = is_numeric($filters['cost_max']) ? (float) $filters['cost_max'] : null;

        if ($costMin !== null) {
            $materialsQuery->where('cost_per_unit', '>=', $costMin);
        }

        if ($costMax !== null) {
            $materialsQuery->where('cost_per_unit', '<=', $costMax);
        }

        $materials = $materialsQuery
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage)
            ->withQueryString();

        $unitOptions = ManufacturingMaterial::query()
            ->whereNotNull('unit')
            ->where('unit', '!=', '')
            ->select('unit')
            ->distinct()
            ->orderBy('unit')
            ->pluck('unit');

        return view('admin.manufacturing.materials.index', [
            'materials' => $materials,
            'filters' => $filters,
            'unitOptions' => $unitOptions,
            'allowedSorts' => ['name', 'sku', 'unit', 'cost_per_unit', 'updated_at', 'created_at'],
            'defaultSortColumn' => 'name',
            'defaultSortDirection' => 'asc',
            'perPage' => $perPage,
        ]);
    }

    public function create(): View
    {
        return view('admin.manufacturing.materials.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:100', 'unique:manufacturing_materials,sku'],
            'unit' => ['nullable', 'string', 'max:50'],
            'cost_per_unit' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        ManufacturingMaterial::create($data);

        return redirect()
            ->route('admin.manufacturing.materials.index')
            ->with('status', __('تمت إضافة المادة الخام بنجاح.'));
    }

    public function edit(ManufacturingMaterial $material): View
    {
        return view('admin.manufacturing.materials.edit', compact('material'));
    }

    public function update(Request $request, ManufacturingMaterial $material): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:100', 'unique:manufacturing_materials,sku,' . $material->id],
            'unit' => ['nullable', 'string', 'max:50'],
            'cost_per_unit' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $material->update($data);

        return redirect()
            ->route('admin.manufacturing.materials.index')
            ->with('status', __('تم تحديث المادة الخام بنجاح.'));
    }

    public function destroy(ManufacturingMaterial $material): RedirectResponse
    {
        $material->delete();

        return redirect()
            ->route('admin.manufacturing.materials.index')
            ->with('status', __('تم حذف المادة الخام.'));
    }
}
