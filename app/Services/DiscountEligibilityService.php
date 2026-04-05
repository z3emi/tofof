<?php

namespace App\Services;

use App\Models\DiscountCode;
use App\Models\Order;
use App\Models\User;

class DiscountEligibilityService
{
    public function isUserEligibleForDiscount(DiscountCode $discountCode, User $user): bool
    {
        $ordersQuery = Order::query()
            ->where('user_id', $user->id)
            ->where('status', 'delivered');

        if ($discountCode->order_count_threshold !== null && $discountCode->order_count_operator) {
            $deliveredCount = (int) $ordersQuery->count();
            if (! $this->matchesOperator((float) $deliveredCount, (float) $discountCode->order_count_threshold, (string) $discountCode->order_count_operator)) {
                return false;
            }
        }

        if ($discountCode->amount_threshold !== null && $discountCode->amount_operator) {
            $totalSpent = (float) $ordersQuery->sum('total_amount');
            if (! $this->matchesOperator($totalSpent, (float) $discountCode->amount_threshold, (string) $discountCode->amount_operator)) {
                return false;
            }
        }

        return true;
    }

    public function matchesOperator(float $actual, float $threshold, string $operator): bool
    {
        if ($operator === 'gte') {
            return $actual >= $threshold;
        }

        if ($operator === 'lte') {
            return $actual <= $threshold;
        }

        return true;
    }
}
