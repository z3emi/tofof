<?php

namespace App\Observers;

use App\Models\CashBox;
use App\Models\CashBoxTransaction;
use App\Models\DepositVoucher;
use App\Models\Manager;
use App\Services\Wallet\WalletLedgerService;
use Illuminate\Support\Facades\DB;

class DepositVoucherObserver
{
    public function __construct(private WalletLedgerService $ledger)
    {
    }

    public function created(DepositVoucher $voucher): void
    {
        $this->applyLedger($voucher);
    }

    public function updated(DepositVoucher $voucher): void
    {
        $this->removeLedger($voucher->getOriginal());
        $this->applyLedger($voucher);
    }

    public function deleted(DepositVoucher $voucher): void
    {
        $this->removeLedger($voucher->getOriginal());
    }

    protected function applyLedger(DepositVoucher $voucher): void
    {
        $voucher->loadMissing('cashBox', 'manager');

        if ($voucher->manager) {
            $voucher->manager->decrement('cash_on_hand', (float) $voucher->amount);
        }

        if (!$voucher->cash_box_id) {
            return;
        }

        $cashBox = $voucher->cashBox ?: CashBox::find($voucher->cash_box_id);
        if (!$cashBox) {
            return;
        }

        $description = $voucher->description
            ?: __('سند إيداع #:number', ['number' => $voucher->number ?? $voucher->id]);

        $this->ledger->recordCashBoxCredit(
            $cashBox,
            (float) $voucher->amount,
            $voucher,
            $description,
            $voucher->voucher_date ?? $voucher->created_at
        );

        $this->recalculateCashBoxLedger($cashBox->getKey());
    }

    /**
     * @param  array<string, mixed>  $original
     */
    protected function removeLedger(array $original): void
    {
        if (!isset($original['id'])) {
            return;
        }

        if (!empty($original['manager_id'])) {
            $manager = Manager::find($original['manager_id']);
            if ($manager) {
                $manager->increment('cash_on_hand', (float) ($original['amount'] ?? 0));
            }
        }

        if (empty($original['cash_box_id'])) {
            return;
        }

        CashBoxTransaction::query()
            ->where('related_model_type', DepositVoucher::class)
            ->where('related_model_id', $original['id'])
            ->delete();

        $this->recalculateCashBoxLedger((int) $original['cash_box_id']);
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
