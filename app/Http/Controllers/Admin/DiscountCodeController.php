<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiscountCode;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use App\Support\RepairsPrimaryKeyAutoIncrement;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DiscountCodesExport;

class DiscountCodeController extends Controller
{
    public function __construct()
    {
        $permissionMiddleware = \Spatie\Permission\Middleware\PermissionMiddleware::class;

        $this->middleware($permissionMiddleware . ':view-discount-codes',   ['only' => ['index']]);
        $this->middleware($permissionMiddleware . ':create-discount-codes', ['only' => ['create', 'store']]);
        $this->middleware($permissionMiddleware . ':edit-discount-codes',   ['only' => ['edit', 'update', 'toggleStatus']]);
        $this->middleware($permissionMiddleware . ':delete-discount-codes', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);

        // نعد الاستخدام من الطلبات فعليًا
        $discountCodes = DiscountCode::withCount(['usages', 'orders'])
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.discount_codes.index', compact('discountCodes'));
    }

    public function show(Request $request, DiscountCode $discount_code)
    {
        $perPage = $request->input('per_page', 15);

        $orders = Order::with(['customer', 'user'])
            ->where('discount_code_id', $discount_code->id)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $totalDiscount  = Order::where('discount_code_id', $discount_code->id)->sum('discount_amount');
        $uniqueUsersCount = Order::where('discount_code_id', $discount_code->id)
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count('user_id');

        return view('admin.discount_codes.show', compact('discount_code', 'orders', 'totalDiscount', 'uniqueUsersCount'));
    }

    public function create()
    {
        $categories = Category::all();
        $products   = Product::all();
        return view('admin.discount_codes.create', compact('categories', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code'   => 'required|string|unique:discount_codes,code',
            'type'   => 'required|in:fixed,percentage,free_shipping',
            // القيمة مطلوبة فقط للمبلغ والنسبة
            'value'  => 'nullable|numeric|min:0|required_if:type,fixed,percentage',
            // حد أقصى للخصم فقط عند النسبة
            'max_discount_amount' => 'nullable|numeric|min:0|required_if:type,percentage',
            'max_uses'           => 'nullable|integer|min:1',
            'max_uses_per_user'  => 'nullable|integer|min:1',
            'expires_at'         => 'nullable|date',
            'categories'         => 'array',
            'categories.*'       => 'exists:categories,id',
            'products'           => 'array',
            'products.*'         => 'exists:products,id',
        ]);

        try {
            $discountCode = $this->createDiscountCodeFromRequest($request);
        } catch (QueryException $e) {
            if (! $this->isMissingIdDefaultError($e)) {
                throw $e;
            }

            $this->repairDiscountCodesPrimaryKeyIfNeeded();
            $discountCode = $this->createDiscountCodeFromRequest($request);
        }

        $this->syncDiscountCodeRelations($discountCode, $request);

        return redirect()->route('admin.discount-codes.index')->with('success', 'تم إنشاء كود الخصم بنجاح.');
    }

    public function edit(DiscountCode $discount_code)
    {
        $categories = Category::all();
        $products   = Product::all();
        $discount_code->load(['categories', 'products']);
        return view('admin.discount_codes.edit', compact('discount_code', 'categories', 'products'));
    }

    public function update(Request $request, DiscountCode $discount_code)
    {
        $request->validate([
            'code'   => 'required|string|unique:discount_codes,code,' . $discount_code->id,
            'type'   => 'required|in:fixed,percentage,free_shipping',
            'value'  => 'nullable|numeric|min:0|required_if:type,fixed,percentage',
            'max_discount_amount' => 'nullable|numeric|min:0|required_if:type,percentage',
            'max_uses'           => 'nullable|integer|min:1',
            'max_uses_per_user'  => 'nullable|integer|min:1',
            'expires_at'         => 'nullable|date',
            'categories'         => 'array',
            'categories.*'       => 'exists:categories,id',
            'products'           => 'array',
            'products.*'         => 'exists:products,id',
        ]);

        $discount_code->update([
            'code'   => $request->code,
            'type'   => $request->type,
            'value'  => in_array($request->type, ['fixed','percentage']) ? $request->value : null,
            'max_discount_amount' => $request->type === 'percentage' ? $request->max_discount_amount : null,
            'max_uses'           => $request->max_uses,
            'max_uses_per_user'  => $request->max_uses_per_user,
            'expires_at'         => $request->expires_at,
        ]);

        $this->syncDiscountCodeRelations($discount_code, $request);

        return redirect()->route('admin.discount-codes.index')->with('success', 'تم تحديث كود الخصم بنجاح.');
    }

    public function destroy(DiscountCode $discount_code)
    {
        $discount_code->delete();
        return redirect()->route('admin.discount-codes.index')->with('success', 'تم نقل كود الخصم إلى سلة المحذوفات.');
    }

    public function trash(Request $request)
    {
        $perPage = $request->input('per_page', 15);

        $discountCodes = DiscountCode::onlyTrashed()
            ->withCount(['usages', 'orders'])
            ->latest('deleted_at')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.discount_codes.trash', compact('discountCodes'));
    }

    public function restore($id)
    {
        $discount_code = DiscountCode::onlyTrashed()->findOrFail($id);
        $discount_code->restore();
        return redirect()->back()->with('success', 'تم استرجاع كود الخصم بنجاح.');
    }

    public function forceDelete($id)
    {
        $discount_code = DiscountCode::onlyTrashed()->findOrFail($id);
        $discount_code->categories()->detach();
        $discount_code->products()->detach();
        $discount_code->forceDelete();
        return redirect()->back()->with('success', 'تم حذف كود الخصم نهائيًا.');
    }

    public function toggleStatus(DiscountCode $discount_code)
    {
        $discount_code->is_active = !$discount_code->is_active;
        $discount_code->save();
        return redirect()->route('admin.discount-codes.index')->with('success', 'تم تحديث حالة الكود.');
    }

    public function exportExcel()
    {
        $codes = DiscountCode::withCount(['usages', 'orders'])->get();
        $data = $codes->map(function ($c) {
            return [
                $c->code,
                $c->type,
                $c->value ?? '-',
                $c->usages_count,
                $c->max_uses ?? 'غير محدود',
                $c->is_active ? 'مفعّل' : 'معطّل',
                $c->expires_at ? $c->expires_at->format('Y-m-d') : 'لا يوجد',
            ];
        })->toArray();

        return Excel::download(new DiscountCodesExport($data), 'discount-codes.xlsx');
    }

    private function createDiscountCodeFromRequest(Request $request): DiscountCode
    {
        return DiscountCode::create([
            'code'   => $request->code,
            'type'   => $request->type,
            'value'  => in_array($request->type, ['fixed', 'percentage']) ? $request->value : null,
            'max_discount_amount' => $request->type === 'percentage' ? $request->max_discount_amount : null,
            'max_uses'           => $request->max_uses,
            'max_uses_per_user'  => $request->max_uses_per_user,
            'expires_at'         => $request->expires_at,
            'is_active'          => true,
        ]);
    }

    private function isMissingIdDefaultError(QueryException $e): bool
    {
        $mysqlErrorCode = $e->errorInfo[1] ?? null;
        $message = strtolower($e->getMessage());

        return (int) $mysqlErrorCode === 1364
            && str_contains($message, "field 'id' doesn't have a default value");
    }

    private function repairDiscountCodesPrimaryKeyIfNeeded(): void
    {
        if (! Schema::hasTable('discount_codes') || ! Schema::hasColumn('discount_codes', 'id')) {
            return;
        }

        $primaryIndex = DB::select("SHOW INDEX FROM `discount_codes` WHERE Key_name = 'PRIMARY'");
        if (empty($primaryIndex)) {
            DB::statement('ALTER TABLE `discount_codes` ADD PRIMARY KEY (`id`)');
        }

        $columnDefinition = collect(DB::select("SHOW COLUMNS FROM `discount_codes` WHERE Field = ?", ['id']))->first();
        if (! $columnDefinition) {
            return;
        }

        $extra = strtolower((string) ($columnDefinition->Extra ?? ''));
        if (! str_contains($extra, 'auto_increment')) {
            DB::statement('ALTER TABLE `discount_codes` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        }

        $nextId = ((int) DB::table('discount_codes')->max('id')) + 1;
        DB::statement('ALTER TABLE `discount_codes` AUTO_INCREMENT = ' . max($nextId, 1));
    }

    private function syncDiscountCodeRelations(DiscountCode $discountCode, Request $request): void
    {
        try {
            $discountCode->categories()->sync($request->input('categories', []));
        } catch (QueryException $exception) {
            if (! RepairsPrimaryKeyAutoIncrement::isMissingAutoIncrementError($exception, 'category_discount_code')) {
                throw $exception;
            }

            RepairsPrimaryKeyAutoIncrement::ensure('category_discount_code');
            $discountCode->categories()->sync($request->input('categories', []));
        }

        try {
            $discountCode->products()->sync($request->input('products', []));
        } catch (QueryException $exception) {
            if (! RepairsPrimaryKeyAutoIncrement::isMissingAutoIncrementError($exception, 'discount_code_product')) {
                throw $exception;
            }

            RepairsPrimaryKeyAutoIncrement::ensure('discount_code_product');
            $discountCode->products()->sync($request->input('products', []));
        }
    }
}
