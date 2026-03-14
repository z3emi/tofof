<?php

namespace App\Policies;

use App\Models\InternalTransfer;
use App\Models\Manager;
use Illuminate\Auth\Access\HandlesAuthorization;

class InternalTransferPolicy
{
    use HandlesAuthorization;

    public function viewAny(Manager $user): bool
    {
        return $user->can('view-any-internal-transfer') || $user->can('view-own-internal-transfer');
    }

    public function view(Manager $user, InternalTransfer $transfer): bool
    {
        if ($user->can('view-any-internal-transfer')) {
            return true;
        }

        if ($user->can('view-own-internal-transfer')) {
            return $transfer->manager_id === $user->getKey()
                || $transfer->created_by === $user->getKey();
        }

        return false;
    }

    public function create(Manager $user): bool
    {
        return $user->can('create-internal-transfer');
    }

    public function approve(Manager $user, InternalTransfer $transfer): bool
    {
        return $user->can('approve-internal-transfer');
    }
}
