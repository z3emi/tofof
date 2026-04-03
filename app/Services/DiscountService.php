<?php

namespace App\Services;

use App\Models\DiscountCode;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class DiscountService
{
    /**
     * لا نغيّر التوقيع حتى يظل التوافق مع أكواد الطلبات.
     */
    public function apply(string $code, float $subtotal, array $items = []): array
    {
        $discount = DiscountCode::with(['products:id', 'categories:id'])
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
