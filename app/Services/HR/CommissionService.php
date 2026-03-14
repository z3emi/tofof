<?php

namespace App\Services\HR;

use App\Models\Order;
use App\Models\SalesCommission;
use Carbon\Carbon;

class CommissionService
{
    public function handleStatusChange(Order $order, string $oldStatus, string $newStatus): void
    {
        if ($newStatus === 'delivered') {
            $this->accrueCommission($order);
        }

        if (in_array($newStatus, ['cancelled', 'returned'], true)) {
            $this->voidCommission($order);
        }
    }

    public function accrueCommission(Order $order): void
    {
        if (!$order->salesperson) {
            return;
        }

        $rate = (float) $order->salesperson->commission_rate;
        if ($rate <= 0) {
            return;
        }

        $commissionAmount = round((float) $order->total_amount * $rate, 2);
        if ($commissionAmount <= 0) {
            return;
        }

        $commission = SalesCommission::firstOrNew(['order_id' => $order->id]);

        if ($commission->status === SalesCommission::STATUS_PAID) {
            return;
        }

        $commission->fill([
            'employee_id' => $order->salesperson_id,
            'amount' => $commissionAmount,
            'status' => SalesCommission::STATUS_ACCRUED,
            'earned_at' => Carbon::now(),
        ]);

        $commission->voided_at = null;
        $commission->payroll_item_id = null;
        $commission->save();
    }

    public function voidCommission(Order $order): void
    {
        $commission = SalesCommission::where('order_id', $order->id)->first();

        if (!$commission) {
            return;
        }

        if ($commission->status === SalesCommission::STATUS_PAID) {
            return;
        }

        $commission->status = SalesCommission::STATUS_VOID;
        $commission->voided_at = Carbon::now();
        $commission->earned_at = $commission->earned_at ?? Carbon::now();
        $commission->payroll_item_id = null;
        $commission->save();
    }
}
