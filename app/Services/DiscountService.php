<?php

namespace App\Services;

use App\Models\DiscountCode;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class DiscountService
{
    public function __construct(private DiscountEligibilityService $eligibilityService)
    {
    }

    /**
     * لا نغيّر التوقيع حتى يظل التوافق مع أكواد الطلبات.
     */
    public function apply(string $code, float $subtotal, array $items = []): array
    {
        $discount = DiscountCode::with(['products:id', 'categories:id', 'targetUsers:id', 'targetPrimaryCategories:id'])
            ->where('code', trim($code))
            ->first();

        if (!$discount || !$discount->is_active) {
            throw ValidationException::withMessages(['code' => 'الكود غير موجود أو غير مفعل.']);
        }

        if ($discount->isExpired()) {
            throw ValidationException::withMessages(['code' => 'الكود منتهي الصلاحية.']);
        }

        // 1) حد الاستخدام الكلي — محسوب من الطلبات التي استخدمت الكود
        $totalUses = Order::where('discount_code_id', $discount->id)->count();
        if ($discount->max_uses && $totalUses >= $discount->max_uses) {
            throw ValidationException::withMessages(['code' => 'تم بلوغ الحد الأقصى لاستخدام هذا الكود.']);
        }

        // 2) حد لكل مستخدم (إن كان المستخدم مسجلاً)
        $authUserId = optional(Auth::user())->id;
        if ($authUserId && $discount->max_uses_per_user) {
            $userUses = Order::where('discount_code_id', $discount->id)
                ->where('user_id', $authUserId)
                ->count();
            if ($userUses >= $discount->max_uses_per_user) {
                throw ValidationException::withMessages(['code' => 'لقد استخدمت هذا الكود مسبقًا.']);
            }
        }

        if ($discount->targetUsers->isNotEmpty()) {
            if (! $authUserId || ! $discount->targetUsers->contains('id', $authUserId)) {
                throw ValidationException::withMessages(['code' => 'هذا الكود مخصص لمستخدمين محددين فقط.']);
            }
        }

        if ($discount->order_count_threshold !== null || $discount->amount_threshold !== null) {
            $authUser = Auth::user();
            if (! $authUser) {
                throw ValidationException::withMessages(['code' => 'يرجى تسجيل الدخول لاستخدام هذا الكود.']);
            }

            if (! $this->eligibilityService->isUserEligibleForDiscount($discount, $authUser)) {
                throw ValidationException::withMessages(['code' => 'هذا الكود غير متاح لك بناءً على شروط الأهلية.']);
            }
        }

        $eligibleSubtotal = $subtotal;
        $hasScopedProducts = $discount->products->isNotEmpty();
        $hasScopedCategories = $discount->categories->isNotEmpty();

        if (($hasScopedProducts || $hasScopedCategories) && ! empty($items)) {
            $allowedProductIds = $discount->products->pluck('id')->map(fn ($id) => (int) $id)->all();
            $allowedCategoryIds = $discount->categories->pluck('id')->map(fn ($id) => (int) $id)->all();

            $itemProductIds = collect($items)
                ->pluck('product_id')
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values()
                ->all();

            $products = Product::query()
                ->select(['id', 'category_id', 'price', 'sale_price', 'sale_starts_at', 'sale_ends_at'])
                ->whereIn('id', $itemProductIds)
                ->get()
                ->keyBy('id');

            $eligibleSubtotal = 0.0;

            foreach ($items as $item) {
                $productId = (int) ($item['product_id'] ?? 0);
                $quantity = max(1, (int) ($item['quantity'] ?? 1));

                if (! isset($products[$productId])) {
                    continue;
                }

                $product = $products[$productId];
                $eligibleByProduct = in_array($productId, $allowedProductIds, true);
                $eligibleByCategory = in_array((int) ($product->category_id ?? 0), $allowedCategoryIds, true);

                if (! $eligibleByProduct && ! $eligibleByCategory) {
                    continue;
                }

                $eligibleSubtotal += ((float) $product->current_price) * $quantity;
            }

            if ($eligibleSubtotal <= 0) {
                throw ValidationException::withMessages(['code' => 'هذا الكود غير مشمول على المنتجات الموجودة في السلة.']);
            }
        }

        if ($discount->targetPrimaryCategories->isNotEmpty()) {
            $requiredPrimaryCategoryIds = $discount->targetPrimaryCategories->pluck('id')->map(fn ($id) => (int) $id)->all();
            $eligibleItemProductIds = collect($items)
                ->pluck('product_id')
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values()
                ->all();

            if (empty($eligibleItemProductIds)) {
                throw ValidationException::withMessages(['code' => 'هذا الكود صالح فقط على براندات محددة.']);
            }

            $matchedBrandCount = DB::table('primary_category_product')
                ->whereIn('product_id', $eligibleItemProductIds)
                ->whereIn('primary_category_id', $requiredPrimaryCategoryIds)
                ->count();

            if ($matchedBrandCount <= 0) {
                throw ValidationException::withMessages(['code' => 'هذا الكود صالح فقط على براندات محددة.']);
            }
        }

        // 3) حساب قيمة الخصم
        $discountAmount = 0.0;

        if ($discount->isFreeShipping()) {
            // شحن مجاني: لا نلمس الشحن من هنا (احترامًا لطلبك بعدم تعديل الطلبات)
            // فقط نعيد 0 خصم، والـ controller يبقى يتصرف بالشحن كما هو الآن.
            return [
                'discount_code_id' => $discount->id,
                'discount_amount'  => 0.0,
            ];
        }

        if ($discount->type === DiscountCode::TYPE_FIXED) {
            $discountAmount = min((float) $discount->value, $eligibleSubtotal);
        } elseif ($discount->type === DiscountCode::TYPE_PERCENTAGE) {
            $raw = $eligibleSubtotal * ((float) $discount->value / 100);
            $discountAmount = $discount->max_discount_amount
                ? min($raw, (float) $discount->max_discount_amount)
                : $raw;
        }

        // أمان إضافي
        $discountAmount = max(0, floor($discountAmount));

        return [
            'discount_code_id' => $discount->id,
            'discount_amount'  => $discountAmount,
        ];
    }
}
