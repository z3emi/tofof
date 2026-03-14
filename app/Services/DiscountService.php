<?php

namespace App\Services;

use App\Models\DiscountCode;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class DiscountService
{
    /**
     * لا نغيّر التوقيع حتى يظل التوافق مع أكواد الطلبات.
     */
    public function apply(string $code, float $subtotal): array
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
            $discountAmount = min((float)$discount->value, $subtotal);
        } elseif ($discount->type === DiscountCode::TYPE_PERCENTAGE) {
            $raw = $subtotal * ((float)$discount->value / 100);
            $discountAmount = $discount->max_discount_amount
                ? min($raw, (float)$discount->max_discount_amount)
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
