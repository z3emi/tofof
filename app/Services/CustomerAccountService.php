<?php

namespace App\Services;

use App\Models\Customer;

class CustomerAccountService
{
    public static function ensureWalletAccount(Customer $customer): void
    {
        if ($customer->balance === null) {
            $customer->forceFill(['balance' => 0])->saveQuietly();
        }
    }

    public static function ensureReceivableAccount(Customer $customer): void
    {
        static::ensureWalletAccount($customer);
    }
}
