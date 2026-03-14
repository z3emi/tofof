<?php

namespace App\Policies;

use App\Models\AllowanceVoucher;
use App\Models\Manager;
use Illuminate\Auth\Access\HandlesAuthorization;

class AllowanceVoucherPolicy
{
    use HandlesAuthorization;

    public function viewAny(Manager $user): bool
    {
        return $user->can('view-any-allowance-voucher') || $user->can('view-own-allowance-voucher');
    }

    public function view(Manager $user, AllowanceVoucher $voucher): bool
    {
        if ($user->can('view-any-allowance-voucher')) {
            return true;
        }

        if ($user->can('view-own-allowance-voucher') && $voucher->manager_id === $user->getKey()) {
            return true;
        }

        return false;
    }

    public function create(Manager $user): bool
    {
        return $user->can('create-allowance-voucher');
    }
}
