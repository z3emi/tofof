<?php

namespace App\Services\Wallet;

use App\Models\CashBox;
use App\Models\CashBoxTransaction;
use App\Models\Customer;
use App\Models\CustomerTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use DateTimeInterface;

class WalletLedgerService
{
    public function recordCustomerDebit(Customer $customer, float $amount, Model $related, ?string $description = null, ?DateTimeInterface $when = null): CustomerTransaction
    {
        return $this->recordCustomerMovement(CustomerTransaction::TYPE_DEBIT, $customer, $amount, $related, $description, $when);
    }

    public function recordCustomerCredit(Customer $customer, float $amount, Model $related, ?string $description = null, ?DateTimeInterface $when = null): CustomerTransaction
    {
        return $this->recordCustomerMovement(CustomerTransaction::TYPE_CREDIT, $customer, $amount, $related, $description, $when);
    }

    public function recordCashBoxCredit(CashBox $cashBox, float $amount, Model $related, ?string $description = null, ?DateTimeInterface $when = null): CashBoxTransaction
    {
        return $this->recordCashBoxMovement(CashBoxTransaction::TYPE_CREDIT, $cashBox, $amount, $related, $description, $when);
    }

    public function recordCashBoxDebit(CashBox $cashBox, float $amount, Model $related, ?string $description = null, ?DateTimeInterface $when = null): CashBoxTransaction
    {
        return $this->recordCashBoxMovement(CashBoxTransaction::TYPE_DEBIT, $cashBox, $amount, $related, $description, $when);
    }

    public function recalculateCashBoxLedger(int $cashBoxId): void
    {
        DB::transaction(function () use ($cashBoxId) {
            /** @var CashBox|null $cashBox */
            $cashBox = CashBox::query()->lockForUpdate()->find($cashBoxId);

            if (! $cashBox) {
                return;
            }

            $transactions = CashBoxTransaction::query()
                ->where('cash_box_id', $cashBoxId)
                ->orderBy('transaction_date')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $balance = 0.0;

            $transactions->each(function (CashBoxTransaction $transaction) use (&$balance) {
                $amount = (float) $transaction->amount;

                if ($transaction->type === CashBoxTransaction::TYPE_CREDIT) {
                    $balance += $amount;
                } else {
                    $balance -= $amount;
                }

                $transaction->forceFill(['balance_after' => $balance])->save();
            });

            $cashBox->forceFill(['balance' => $balance])->save();
        });
    }

    protected function recordCustomerMovement(string $type, Customer $customer, float $amount, Model $related, ?string $description, ?DateTimeInterface $when): CustomerTransaction
    {
        return DB::transaction(function () use ($type, $customer, $amount, $related, $description, $when) {
            $customer = Customer::query()->whereKey($customer->getKey())->lockForUpdate()->first();

            $newBalance = $type === CustomerTransaction::TYPE_DEBIT
                ? (float) $customer->balance + $amount
                : (float) $customer->balance - $amount;

            $customer->forceFill(['balance' => $newBalance])->save();

            return $customer->transactions()->create([
                'type' => $type,
                'amount' => $amount,
                'balance_after' => $newBalance,
                'description' => $description,
                'transaction_date' => $when ?? now(),
                'related_model_type' => get_class($related),
                'related_model_id' => $related->getKey(),
            ]);
        });
    }

    protected function recordCashBoxMovement(string $type, CashBox $cashBox, float $amount, Model $related, ?string $description, ?DateTimeInterface $when): CashBoxTransaction
    {
        return DB::transaction(function () use ($type, $cashBox, $amount, $related, $description, $when) {
            $cashBox = CashBox::query()->whereKey($cashBox->getKey())->lockForUpdate()->first();

            $newBalance = $type === CashBoxTransaction::TYPE_CREDIT
                ? (float) $cashBox->balance + $amount
                : (float) $cashBox->balance - $amount;

            $cashBox->forceFill(['balance' => $newBalance])->save();

            return $cashBox->transactions()->create([
                'type' => $type,
                'amount' => $amount,
                'balance_after' => $newBalance,
                'description' => $description,
                'transaction_date' => $when ?? now(),
                'related_model_type' => get_class($related),
                'related_model_id' => $related->getKey(),
            ]);
        });
    }
}
