<?php

namespace App\Observers;

use App\Models\Customer;
use App\Services\CustomerAccountService;
use Illuminate\Support\Facades\Log;

class CustomerObserver
{
    public bool $afterCommit = true;

    public function created(Customer $customer): void
    {
        try {
            CustomerAccountService::ensureReceivableAccount($customer);
        } catch (\Throwable $exception) {
            Log::warning('Failed to create receivable account for customer', [
                'customer_id' => $customer->id,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
