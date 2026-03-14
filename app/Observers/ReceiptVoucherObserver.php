<?php

namespace App\Observers;

use App\Models\CashBox;
use App\Models\CashBoxTransaction;
use App\Models\Customer;
use App\Models\CustomerTransaction;
use App\Models\Manager;
use App\Models\ReceiptVoucher;
use App\Services\Wallet\WalletLedgerService;
use Illuminate\Support\Facades\DB;

class ReceiptVoucherObserver
{
    public function __construct(private WalletLedgerService $ledger)
    {
    }

    public function created(ReceiptVoucher $voucher): void
    {
        $this->applyLedger($voucher);
    }

    public function updated(ReceiptVoucher $voucher): void
    {
        $this->removeLedger($voucher->getOriginal());
        $this->applyLedger($voucher);
    }

    public function deleted(ReceiptVoucher $voucher): void
    {
        $this->removeLedger($voucher->getOriginal());
    }

    protected function applyLedger(ReceiptVoucher $voucher): void
    {
        $voucher->loadMissing('customer', 'cashBox', 'manager');

        if ($voucher->customer) {
            $description = __('سند قبض #:number', ['number' => $voucher->number ?? $voucher->id]);
            $this->ledger->recordCustomerCredit(
                $voucher->customer,
                (float) $voucher->amount,
                $voucher,
                $description,
                $voucher->voucher_date ?? $voucher->created_at
            );

            $this->recalculateCustomerLedger($voucher->customer_id);
        }

        if ($voucher->cash_box_id) {
            $cashBox = $voucher->cashBox ?: CashBox::find($voucher->cash_box_id);
            if ($cashBox) {
                $this->ledger->recordCashBoxCredit(
                    $cashBox,
                    (float) $voucher->amount,
                    $voucher,
                    $voucher->description,
                    $voucher->voucher_date ?? $voucher->created_at
                );

                $this->recalculateCashBoxLedger($cashBox->getKey());
            }

            return;
        }

        if ($voucher->manager_id) {
            $manager = $voucher->manager ?: Manager::find($voucher->manager_id);
            if ($manager) {
                $manager->increment('cash_on_hand', (float) $voucher->amount);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $original
     */
    protected function removeLedger(array $original): void
    {
        if (!isset($original['id'])) {
            return;
        }

        if (!empty($original['customer_id'])) {
            CustomerTransaction::query()
                ->where('related_model_type', ReceiptVoucher::class)
                ->where('related_model_id', $original['id'])
                ->delete();

            $this->recalculateCustomerLedger((int) $original['customer_id']);
        }

        if (!empty($original['cash_box_id'])) {
            CashBoxTransaction::query()
                ->where('related_model_type', ReceiptVoucher::class)
                ->where('related_model_id', $original['id'])
                ->delete();

            $this->recalculateCashBoxLedger((int) $original['cash_box_id']);

            return;
        }

        if (!empty($original['manager_id'])) {
            $manager = Manager::find($original['manager_id']);
            if ($manager) {
                $manager->decrement('cash_on_hand', (float) $original['amount']);
            }
        }
    }

    protected function recalculateCustomerLedger(int $customerId): void
    {
        DB::transaction(function () use ($customerId) {
            $customer = Customer::query()->lockForUpdate()->find($customerId);

            if (!$customer) {
                return;
            }

            /** @var \Illuminate\Support\Collection<int, CustomerTransaction> $transactions */
            $transactions = CustomerTransaction::query()
                ->where('customer_id', $customerId)
                ->orderBy('transaction_date')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $balance = 0.0;

            $seen = [];

            $transactions->each(function (CustomerTransaction $transaction) use (&$balance, &$seen) {
                $key = $transaction->related_model_type . ':' . $transaction->related_model_id . ':' . $transaction->type;

                if (isset($seen[$key])) {
                    if ((float) $transaction->balance_after !== $balance) {
                        $transaction->forceFill(['balance_after' => $balance])->save();
                    }

                    return;
                }

                $seen[$key] = true;

                $balance += $transaction->type === CustomerTransaction::TYPE_DEBIT
                    ? (float) $transaction->amount
                    : -(float) $transaction->amount;

                if ((float) $transaction->balance_after !== $balance) {
                    $transaction->forceFill(['balance_after' => $balance])->save();
                }
            });

            if ((float) $customer->balance !== $balance) {
                $customer->forceFill(['balance' => $balance])->save();
            }
        });
    }

    protected function recalculateCashBoxLedger(int $cashBoxId): void
    {
        DB::transaction(function () use ($cashBoxId) {
            $cashBox = CashBox::query()->lockForUpdate()->find($cashBoxId);

            if (!$cashBox) {
                return;
            }

            /** @var \Illuminate\Support\Collection<int, CashBoxTransaction> $transactions */
            $transactions = CashBoxTransaction::query()
                ->where('cash_box_id', $cashBoxId)
                ->orderBy('transaction_date')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $balance = 0.0;

            $transactions->each(function (CashBoxTransaction $transaction) use (&$balance) {
                $balance += $transaction->type === CashBoxTransaction::TYPE_CREDIT
                    ? (float) $transaction->amount
                    : -(float) $transaction->amount;

                if ((float) $transaction->balance_after !== $balance) {
                    $transaction->forceFill(['balance_after' => $balance])->save();
                }
            });

            if ((float) $cashBox->balance !== $balance) {
                $cashBox->forceFill(['balance' => $balance])->save();
            }
        });
    }
}
