<?php

namespace App\Policies;

use App\Models\DepositVoucher;
use App\Models\Manager;
use Illuminate\Auth\Access\HandlesAuthorization;

class DepositVoucherPolicy
{
    use HandlesAuthorization;

    public function viewAny(Manager $user): bool
    {
        return $user->can('view-deposit-vouchers');
    }

    public function view(Manager $user, DepositVoucher $voucher): bool
    {
        return $user->can('view-deposit-vouchers');
    }

    public function create(Manager $user): bool
    {
        return $user->can('create-deposit-voucher');
    }

    public function update(Manager $user, DepositVoucher $voucher): bool
    {
        return $user->can('edit-deposit-voucher');
    }

    public function delete(Manager $user, DepositVoucher $voucher): bool
    {
        return $user->can('delete-deposit-voucher');
    }
}
