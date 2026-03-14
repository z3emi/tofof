<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AllowanceVoucher;
use App\Models\CashBox;
use App\Models\CashBoxTransaction;
use App\Models\Customer;
use App\Models\CustomerTransaction;
use App\Models\Manager;
use App\Models\Order;
use App\Models\ReceiptVoucher;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Str;

class WalletReportController extends Controller
{
    public function customerStatement(Request $request): View
    {
        abort_unless($request->user('admin')?->can('view-customer-wallet-report'), 403);

        $customer = null;
        $transactions = collect();
        $totals = [
            'debit' => 0.0,
            'credit' => 0.0,
            'opening' => null,
            'closing' => null,
        ];

        if ($request->filled('customer_id')) {
            $customer = Customer::find($request->integer('customer_id'));
            if ($customer) {
                $transactions = CustomerTransaction::query()
                    ->where('customer_id', $customer->id)
                    ->when($request->filled('scope'), function ($query) use ($request) {
                        return match ($request->input('scope')) {
                            'orders' => $query->where('related_model_type', Order::class),
                            'receipts' => $query->where('related_model_type', ReceiptVoucher::class),
                            'allowances' => $query->where('related_model_type', AllowanceVoucher::class),
                            default => $query,
                        };
                    })
                    ->when($request->filled('from_date'), fn ($query) => $query->whereDate('transaction_date', '>=', $request->date('from_date')))
                    ->when($request->filled('to_date'), fn ($query) => $query->whereDate('transaction_date', '<=', $request->date('to_date')))
                    ->orderBy('transaction_date')
                    ->orderBy('id')
                    ->get();

                $transactions->load('relatedModel');

                $transactions = $transactions
                    ->unique(function (CustomerTransaction $transaction) {
                        $typeKey = $transaction->related_model_type ?? '';
                        if (class_exists($typeKey)) {
                            $typeKey = class_basename($typeKey);
                        }

                        $typeKey = Str::lower(preg_replace('/[^a-z0-9]/i', '', (string) $typeKey) ?: 'model');

                        return $typeKey . ':' . (string) $transaction->related_model_id . ':' . $transaction->type;
                    })
                    ->values();

                $runningBalance = null;

                if ($transactions->isNotEmpty()) {
                    $first = $transactions->first();
                    $delta = $first->type === CustomerTransaction::TYPE_DEBIT
                        ? (float) $first->amount
                        : -(float) $first->amount;
                    $runningBalance = (float) $first->balance_after - $delta;
                }

                if ($runningBalance === null) {
                    $runningBalance = (float) ($customer->balance ?? 0);
                }

                $totals['opening'] = $runningBalance;

                $transactions = $transactions->map(function (CustomerTransaction $transaction) use (&$runningBalance, &$totals) {
                    $before = $runningBalance;
                    $change = $transaction->type === CustomerTransaction::TYPE_DEBIT
                        ? (float) $transaction->amount
                        : -(float) $transaction->amount;

                    if ($transaction->type === CustomerTransaction::TYPE_DEBIT) {
                        $totals['debit'] += (float) $transaction->amount;
                    } else {
                        $totals['credit'] += (float) $transaction->amount;
                    }

                    $runningBalance += $change;

                    $transaction->computed_balance_before = $before;
                    $transaction->computed_balance_after = $runningBalance;

                    return $transaction;
                });

                $lastTransaction = $transactions->last();
                $totals['closing'] = $lastTransaction?->computed_balance_after ?? $runningBalance;
            }
        }

        return view('admin.wallet_reports.customer_statement', [
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'balance']),
            'selectedCustomer' => $customer,
            'transactions' => $transactions,
            'filters' => $request->only(['scope', 'from_date', 'to_date']),
            'totals' => $totals,
        ]);
    }

    public function customerBalances(): View
    {
        abort_unless(auth('admin')->user()?->can('view-customer-wallet-report'), 403);

        return view('admin.wallet_reports.customer_balances', [
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'balance', 'phone_number']),
        ]);
    }

    public function collectorBalances(): View
    {
        abort_unless(auth('admin')->user()?->can('view-collector-balances-report'), 403);

        return view('admin.wallet_reports.collector_balances', [
            'managers' => Manager::orderBy('name')->get(['id', 'name', 'cash_on_hand', 'phone_number']),
        ]);
    }

    public function cashBoxStatement(Request $request): View
    {
        abort_unless($request->user('admin')?->can('view-cash-account-statement'), 403);

        $cashBox = null;
        $transactions = collect();

        if ($request->filled('cash_box_id')) {
            $cashBox = CashBox::find($request->integer('cash_box_id'));
            if ($cashBox) {
                $transactions = CashBoxTransaction::query()
                    ->where('cash_box_id', $cashBox->id)
                    ->when($request->filled('from_date'), fn ($query) => $query->whereDate('transaction_date', '>=', $request->date('from_date')))
                    ->when($request->filled('to_date'), fn ($query) => $query->whereDate('transaction_date', '<=', $request->date('to_date')))
                    ->orderBy('transaction_date')
                    ->orderBy('id')
                    ->get();
            }
        }

        return view('admin.wallet_reports.cash_box_statement', [
            'cashBoxes' => CashBox::orderBy('name')->get(['id', 'name', 'balance']),
            'selectedCashBox' => $cashBox,
            'transactions' => $transactions,
            'filters' => $request->only(['from_date', 'to_date']),
        ]);
    }
}
