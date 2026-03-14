<?php

namespace App\Policies;

use App\Models\Manager;
use App\Models\PaymentVoucher;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentVoucherPolicy
{
    use HandlesAuthorization;

    public function viewAny(Manager $user): bool
    {
        return $user->can('view-any-payment-voucher') || $user->can('view-own-payment-voucher');
    }

    public function view(Manager $user, PaymentVoucher $voucher): bool
    {
        if ($user->can('view-any-payment-voucher')) {
            return true;
        }

        if ($user->can('view-own-payment-voucher') && $voucher->manager_id === $user->getKey()) {
            return true;
        }

        return false;
    }

    public function create(Manager $user): bool
    {
        return $user->can('create-payment-voucher');
    }

    public function approve(Manager $user, PaymentVoucher $voucher): bool
    {
        return $user->can('approve-payment-voucher');
    }

    public function delete(Manager $user, PaymentVoucher $voucher): bool
    {
        return $user->can('delete-payment-voucher');
    }
}
