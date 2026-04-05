<?php

namespace App\Http\Controllers\Admin;

use App\Exports\DiscountCodesExport;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\DiscountCode;
use App\Models\DiscountCodeDeliveryLog;
use App\Models\Order;
use App\Models\PrimaryCategory;
use App\Models\Product;
use App\Models\User;
use App\Notifications\DiscountCodeAssignedNotification;
use App\Services\DiscountEligibilityService;
use App\Support\RepairsPrimaryKeyAutoIncrement;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;

class DiscountCodeController extends Controller
{
    public function __construct(private DiscountEligibilityService $eligibilityService)
    {
        $permissionMiddleware = \Spatie\Permission\Middleware\PermissionMiddleware::class;

        $this->middleware($permissionMiddleware . ':view-discount-codes', ['only' => ['index', 'show', 'trash']]);
        $this->middleware($permissionMiddleware . ':create-discount-codes', ['only' => ['create', 'store']]);
        $this->middleware($permissionMiddleware . ':edit-discount-codes', ['only' => ['edit', 'update', 'toggleStatus', 'restore', 'sendToEligibleUsers']]);
        $this->middleware($permissionMiddleware . ':delete-discount-codes', ['only' => ['destroy', 'forceDelete']]);
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);

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

        $totalDiscount = Order::where('discount_code_id', $discount_code->id)->sum('discount_amount');
        $uniqueUsersCount = Order::where('discount_code_id', $discount_code->id)
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count('user_id');

        $discount_code->load(['targetUsers:id,name,phone_number', 'targetPrimaryCategories:id,name_ar']);
        $deliveryLogs = $discount_code->deliveryLogs()->latest()->take(20)->with('user:id,name,phone_number')->get();

        return view('admin.discount_codes.show', compact('discount_code', 'orders', 'totalDiscount', 'uniqueUsersCount', 'deliveryLogs'));
    }

    public function create()
    {
        $categories = Category::all();
        $products = Product::all();
        $users = User::select(['id', 'name', 'phone_number'])->orderBy('name')->get();
        $primaryCategories = PrimaryCategory::active()->ordered()->get();

        return view('admin.discount_codes.create', compact('categories', 'products', 'users', 'primaryCategories'));
    }

    public function store(Request $request)
    {
        $request->validate($this->discountValidationRules());

        if ($request->input('audience_mode') === 'selected' && empty($request->input('users', []))) {
            return redirect()->back()->withErrors(['users' => 'يرجى اختيار مستخدم واحد على الأقل عند تحديد جمهور مخصص.'])->withInput();
        }

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
        $products = Product::all();
        $users = User::select(['id', 'name', 'phone_number'])->orderBy('name')->get();
        $primaryCategories = PrimaryCategory::active()->ordered()->get();

        $discount_code->load(['categories', 'products', 'targetUsers', 'targetPrimaryCategories']);

        return view('admin.discount_codes.edit', compact('discount_code', 'categories', 'products', 'users', 'primaryCategories'));
    }

    public function update(Request $request, DiscountCode $discount_code)
    {
        $rules = $this->discountValidationRules();
        $rules['code'] = 'required|string|unique:discount_codes,code,' . $discount_code->id;
        $request->validate($rules);

        if ($request->input('audience_mode') === 'selected' && empty($request->input('users', []))) {
            return redirect()->back()->withErrors(['users' => 'يرجى اختيار مستخدم واحد على الأقل عند تحديد جمهور مخصص.'])->withInput();
        }

        $discount_code->update($this->extractDiscountData($request));
        $this->syncDiscountCodeRelations($discount_code, $request);

        return redirect()->route('admin.discount-codes.index')->with('success', 'تم تحديث كود الخصم بنجاح.');
    }

    public function sendToEligibleUsers(DiscountCode $discount_code)
    {
        $users = $this->resolveAudienceUsers($discount_code);

        if ($users->isEmpty()) {
            return redirect()->back()->with('error', 'لا يوجد مستخدمون مطابقون لشروط الإرسال.');
        }

        $sent = 0;
        $failed = 0;

        foreach ($users as $user) {
            if (! $discount_code->notify_via_bell && ! $discount_code->notify_via_push) {
                continue;
            }

            $payloadHash = hash('sha256', $discount_code->id . '|' . $user->id . '|notification');
            $alreadySent = DiscountCodeDeliveryLog::where('discount_code_id', $discount_code->id)
                ->where('user_id', $user->id)
                ->where('channel', 'notification')
                ->where('payload_hash', $payloadHash)
                ->exists();

            if ($alreadySent) {
                continue;
            }

            try {
                $user->notify(new DiscountCodeAssignedNotification(
                    $discount_code,
                    (bool) $discount_code->notify_via_bell,
                    (bool) $discount_code->notify_via_push
                ));

                DiscountCodeDeliveryLog::create([
                    'discount_code_id' => $discount_code->id,
                    'user_id' => $user->id,
                    'channel' => 'notification',
                    'status' => 'sent',
                    'payload_hash' => $payloadHash,
                    'sent_at' => now(),
                ]);

                $sent++;
            } catch (\Throwable $exception) {
                DiscountCodeDeliveryLog::create([
                    'discount_code_id' => $discount_code->id,
                    'user_id' => $user->id,
                    'channel' => 'notification',
                    'status' => 'failed',
                    'payload_hash' => $payloadHash,
                    'error' => mb_substr($exception->getMessage(), 0, 1000),
                ]);

                $failed++;
            }
        }

        $discount_code->update(['sent_at' => now()]);

        return redirect()->back()->with('success', "تم إرسال الكود. نجاح: {$sent} | فشل: {$failed}");
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
        $discount_code->targetUsers()->detach();
        $discount_code->targetPrimaryCategories()->detach();
        $discount_code->forceDelete();

        return redirect()->back()->with('success', 'تم حذف كود الخصم نهائيًا.');
    }

    public function toggleStatus(DiscountCode $discount_code)
    {
        $discount_code->is_active = ! $discount_code->is_active;
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
        $data = $this->extractDiscountData($request);
        $data['is_active'] = true;

        return DiscountCode::create($data);
    }

    private function extractDiscountData(Request $request): array
    {
        return [
            'code' => $request->code,
            'type' => $request->type,
            'value' => in_array($request->type, ['fixed', 'percentage']) ? $request->value : null,
            'max_discount_amount' => $request->type === 'percentage' ? $request->max_discount_amount : null,
            'max_uses' => $request->max_uses,
            'max_uses_per_user' => $request->max_uses_per_user,
            'expires_at' => $request->expires_at,
            'audience_mode' => $request->input('audience_mode', 'all'),
            'order_count_operator' => $request->input('order_count_operator') ?: null,
            'order_count_threshold' => $request->filled('order_count_threshold') ? (int) $request->input('order_count_threshold') : null,
            'amount_operator' => $request->input('amount_operator') ?: null,
            'amount_threshold' => $request->filled('amount_threshold') ? (float) $request->input('amount_threshold') : null,
            'notify_via_bell' => $request->boolean('notify_via_bell'),
            'notify_via_push' => $request->boolean('notify_via_push'),
        ];
    }

    private function discountValidationRules(): array
    {
        return [
            'code' => 'required|string|unique:discount_codes,code',
            'type' => 'required|in:fixed,percentage,free_shipping',
            'value' => 'nullable|numeric|min:0|required_if:type,fixed,percentage',
            'max_discount_amount' => 'nullable|numeric|min:0|required_if:type,percentage',
            'max_uses' => 'nullable|integer|min:1',
            'max_uses_per_user' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'audience_mode' => 'required|in:all,eligible,selected',
            'order_count_operator' => 'nullable|in:gte,lte',
            'order_count_threshold' => 'nullable|integer|min:0',
            'amount_operator' => 'nullable|in:gte,lte',
            'amount_threshold' => 'nullable|numeric|min:0',
            'notify_via_bell' => 'nullable|boolean',
            'notify_via_push' => 'nullable|boolean',
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',
            'products' => 'array',
            'products.*' => 'exists:products,id',
            'users' => 'array',
            'users.*' => 'exists:users,id',
            'primary_categories' => 'array',
            'primary_categories.*' => 'exists:primary_categories,id',
        ];
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

        try {
            $discountCode->targetUsers()->sync($request->input('users', []));
        } catch (QueryException $exception) {
            if (! RepairsPrimaryKeyAutoIncrement::isMissingAutoIncrementError($exception, 'discount_code_user')) {
                throw $exception;
            }

            RepairsPrimaryKeyAutoIncrement::ensure('discount_code_user');
            $discountCode->targetUsers()->sync($request->input('users', []));
        }

        try {
            $discountCode->targetPrimaryCategories()->sync($request->input('primary_categories', []));
        } catch (QueryException $exception) {
            if (! RepairsPrimaryKeyAutoIncrement::isMissingAutoIncrementError($exception, 'discount_code_primary_category')) {
                throw $exception;
            }

            RepairsPrimaryKeyAutoIncrement::ensure('discount_code_primary_category');
            $discountCode->targetPrimaryCategories()->sync($request->input('primary_categories', []));
        }
    }

    private function resolveAudienceUsers(DiscountCode $discountCode)
    {
        $base = User::query()->whereNull('banned_at');

        if ($discountCode->audience_mode === 'selected') {
            return $base->whereIn('id', $discountCode->targetUsers()->pluck('users.id'))->get();
        }

        if ($discountCode->audience_mode === 'eligible') {
            return $base->get()->filter(fn (User $user) => $this->eligibilityService->isUserEligibleForDiscount($discountCode, $user))->values();
        }

        $targetedIds = $discountCode->targetUsers()->pluck('users.id');
        if ($targetedIds->isNotEmpty()) {
            return $base->whereIn('id', $targetedIds)->get();
        }

        return $base->get();
    }
}
