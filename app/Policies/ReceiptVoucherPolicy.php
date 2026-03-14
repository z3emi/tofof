<?php

namespace App\Policies;

use App\Models\Manager;
use App\Models\ReceiptVoucher;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReceiptVoucherPolicy
{
    use HandlesAuthorization;

    public function viewAny(Manager $user): bool
    {
        return $user->can('view-any-receipt-voucher') || $user->can('view-own-receipt-voucher');
    }

    public function view(Manager $user, ReceiptVoucher $voucher): bool
    {
        if ($user->can('view-any-receipt-voucher')) {
            return true;
        }

        if ($user->can('view-own-receipt-voucher') && $voucher->manager_id === $user->getKey()) {
            return true;
        }

        return false;
    }

    public function create(Manager $user): bool
    {
        return $user->can('create-receipt-voucher');
    }

    public function update(Manager $user, ReceiptVoucher $voucher): bool
    {
        if ($user->can('edit-receipt-voucher')) {
            return true;
        }

        if ($user->can('view-own-receipt-voucher') && $voucher->manager_id === $user->getKey()) {
            return true;
        }

        return false;
    }

    public function approve(Manager $user, ReceiptVoucher $voucher): bool
    {
        return $user->can('approve-receipt-voucher');
    }

    public function delete(Manager $user, ReceiptVoucher $voucher): bool
    {
        return $user->can('delete-receipt-voucher');
    }
}
