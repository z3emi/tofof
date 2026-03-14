<?php

namespace App\Http\Controllers\Accounting;

use App\Exports\TableExport;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AllowanceVoucher;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\JournalEntryLine;
use App\Models\Manager;
use App\Models\PaymentVoucher;
use App\Models\ReceiptVoucher;
use App\Models\CustomerTransaction;
use App\Models\SystemAccount;
use App\Models\Order;
use App\Models\CashAccount;
use App\Models\InternalTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Spatie\Permission\Middleware\PermissionMiddleware;

class ReportController extends Controller
{
    public function __construct()
    {
        $permissionMiddleware = PermissionMiddleware::class;

        $this->middleware($permissionMiddleware . ':access-accounting');
        $this->middleware($permissionMiddleware . ':view-accounting-report-trial-balance')->only(['trialBalance']);
        $this->middleware($permissionMiddleware . ':view-accounting-report-income')->only(['incomeStatement']);
        $this->middleware($permissionMiddleware . ':view-accounting-report-ledger')->only(['accountStatement']);
        $this->middleware($permissionMiddleware . ':view-accounting-report-sales')->only(['salesByCustomer']);
        $this->middleware($permissionMiddleware . ':view-accounting-report-aging')->only(['customerAging']);
        $this->middleware($permissionMiddleware . ':view-accounting-report-customer')->only(['customerStatement']);
        $this->middleware($permissionMiddleware . ':view-accounting-report-manager')->only(['managerActivity']);
        $this->middleware($permissionMiddleware . ':view-cash-account-statement')->only(['cashAccountStatement']);
        $this->middleware($permissionMiddleware . ':view-collector-balances-report')->only(['collectorBalances']);
        $this->middleware($permissionMiddleware . ':view-customer-collection-report')->only(['customerCollections']);
        $this->middleware($permissionMiddleware . ':view-representative-delivery-report')->only(['representativeDeliveries']);
    }

    public function trialBalance(Request $request)
    {
        $from = $request->date('from');
        $to = $request->date('to');

        $query = JournalEntryLine::select('account_id',
            DB::raw('SUM(debit) as total_debit_iqd'),
            DB::raw('SUM(credit) as total_credit_iqd'),
            DB::raw("SUM(CASE WHEN currency_code = 'USD' THEN currency_debit ELSE 0 END) as total_debit_usd"),
            DB::raw("SUM(CASE WHEN currency_code = 'USD' THEN currency_credit ELSE 0 END) as total_credit_usd"))
            ->when($from, fn ($q) => $q->whereHas('entry', fn ($sub) => $sub->whereDate('entry_date', '>=', $from)))
            ->when($to, fn ($q) => $q->whereHas('entry', fn ($sub) => $sub->whereDate('entry_date', '<=', $to)))
            ->groupBy('account_id');

        $rows = [];
        $totals = ['debit_iqd' => 0, 'credit_iqd' => 0, 'debit_usd' => 0, 'credit_usd' => 0];

        $lines = $query->get();
        $lines->load('account');

        foreach ($lines as $line) {
            $balance = $line->total_debit_iqd - $line->total_credit_iqd;
            $rows[] = [
                $line->account->code,
                $line->account->name,
                number_format((float) $line->total_debit_iqd, 2, '.', ''),
                number_format((float) $line->total_credit_iqd, 2, '.', ''),
                number_format((float) $line->total_debit_usd, 2, '.', ''),
                number_format((float) $line->total_credit_usd, 2, '.', ''),
                number_format((float) $balance, 2, '.', ''),
            ];

            $totals['debit_iqd'] += $line->total_debit_iqd;
            $totals['credit_iqd'] += $line->total_credit_iqd;
            $totals['debit_usd'] += $line->total_debit_usd;
            $totals['credit_usd'] += $line->total_credit_usd;
        }

        if ($request->boolean('export')) {
            $this->ensureCanExport();
            return $this->export(__('ميزان المراجعة'), ['الكود', 'الحساب', 'مدين (IQD)', 'دائن (IQD)', 'مدين (USD)', 'دائن (USD)', 'الرصيد (IQD)'], $rows);
        }

        return view('accounting.reports.trial_balance', [
            'rows' => $rows,
            'totals' => $totals,
            'filters' => ['from' => $from?->format('Y-m-d'), 'to' => $to?->format('Y-m-d')],
        ]);
    }

    public function incomeStatement(Request $request)
    {
        $from = $request->date('from');
        $to = $request->date('to');

        $lines = JournalEntryLine::with('account')
            ->when($from, fn ($q) => $q->whereHas('entry', fn ($sub) => $sub->whereDate('entry_date', '>=', $from)))
            ->when($to, fn ($q) => $q->whereHas('entry', fn ($sub) => $sub->whereDate('entry_date', '<=', $to)))
            ->get()
            ->groupBy(fn ($line) => $line->account->type);

        $revenues = $lines->get('revenue', collect());
        $expenses = $lines->get('expense', collect());

        $revenueTotal = $revenues->sum(fn ($line) => $line->credit - $line->debit);
        $expenseTotal = $expenses->sum(fn ($line) => $line->debit - $line->credit);
        $netIncome = $revenueTotal - $expenseTotal;

        $systemAccounts = SystemAccount::first();
        $orderRevenue = 0;
        $orderCogs = 0;

        if ($systemAccounts && ($systemAccounts->sales_account_id || $systemAccounts->cogs_account_id)) {
            $orderLines = JournalEntryLine::with('entry')
                ->whereHas('entry', fn ($query) => $query->whereNotNull('order_id'))
                ->when($from, fn ($q) => $q->whereHas('entry', fn ($sub) => $sub->whereDate('entry_date', '>=', $from)))
                ->when($to, fn ($q) => $q->whereHas('entry', fn ($sub) => $sub->whereDate('entry_date', '<=', $to)))
                ->get();

            if ($systemAccounts->sales_account_id) {
                $orderRevenue = $orderLines
                    ->where('account_id', $systemAccounts->sales_account_id)
                    ->sum(fn (JournalEntryLine $line) => $line->credit - $line->debit);
            }

            if ($systemAccounts->cogs_account_id) {
                $orderCogs = $orderLines
                    ->where('account_id', $systemAccounts->cogs_account_id)
                    ->sum(fn (JournalEntryLine $line) => $line->debit - $line->credit);
            }
        }

        $orderGrossProfit = $orderRevenue - $orderCogs;

        $revenueTotalUsd = $revenues->where('currency_code', 'USD')->sum(fn ($line) => $line->currency_credit - $line->currency_debit);
        $expenseTotalUsd = $expenses->where('currency_code', 'USD')->sum(fn ($line) => $line->currency_debit - $line->currency_credit);
        $netIncomeUsd = $revenueTotalUsd - $expenseTotalUsd;

        $rows = [
            ['إجمالي الإيرادات', number_format((float) $revenueTotal, 2, '.', ''), number_format((float) $revenueTotalUsd, 2, '.', '')],
            ['إجمالي المصروفات', number_format((float) $expenseTotal, 2, '.', ''), number_format((float) $expenseTotalUsd, 2, '.', '')],
            ['صافي الربح', number_format((float) $netIncome, 2, '.', ''), number_format((float) $netIncomeUsd, 2, '.', '')],
        ];

        if ($request->boolean('export')) {
            $this->ensureCanExport();
            return $this->export(__('الأرباح والخسائر'), ['البند', 'القيمة (IQD)', 'القيمة (USD)'], $rows);
        }

        return view('accounting.reports.income_statement', [
            'rows' => $rows,
            'from' => $from,
            'to' => $to,
            'netIncome' => $netIncome,
            'netIncomeUsd' => $netIncomeUsd,
            'orderRevenue' => $orderRevenue,
            'orderCogs' => $orderCogs,
            'orderGrossProfit' => $orderGrossProfit,
        ]);
    }

    public function accountStatement(Request $request)
    {
        $accounts = Account::orderBy('code')->get();
        $accountId = $request->get('account_id') ?? $accounts->first()?->id;

        if (!$accountId) {
            return view('accounting.reports.account_statement', [
                'accounts' => $accounts,
                'account' => null,
                'rows' => [],
                'from' => null,
                'to' => null,
            ]);
        }

        $account = Account::findOrFail($accountId);
        $from = $request->date('from');
        $to = $request->date('to');

        $lines = JournalEntryLine::with('entry')
            ->where('account_id', $account->id)
            ->when($from, fn ($q) => $q->whereHas('entry', fn ($sub) => $sub->whereDate('entry_date', '>=', $from)))
            ->when($to, fn ($q) => $q->whereHas('entry', fn ($sub) => $sub->whereDate('entry_date', '<=', $to)))
            ->get()
            ->sortByDesc(fn ($line) => $line->entry->entry_date);

        $rows = $lines->map(function ($line) {
            return [
                $line->entry->entry_date->format('Y-m-d'),
                $line->entry->reference,
                $line->entry->description,
                number_format((float) $line->debit, 2, '.', ''),
                number_format((float) $line->credit, 2, '.', ''),
                $line->currency_code,
                number_format((float) $line->currency_debit, 2, '.', ''),
                number_format((float) $line->currency_credit, 2, '.', ''),
                $line->exchange_rate ? number_format((float) $line->exchange_rate, 4, '.', '') : '—',
            ];
        })->values()->toArray();

        if ($request->boolean('export')) {
            $this->ensureCanExport();
            return $this->export(__('كشف حساب') . ' ' . $account->name, ['التاريخ', 'المرجع', 'الوصف', 'مدين (IQD)', 'دائن (IQD)', 'العملة', 'مدين (Currency)', 'دائن (Currency)', 'سعر الصرف'], $rows);
        }

        return view('accounting.reports.account_statement', [
            'accounts' => $accounts,
            'account' => $account,
            'rows' => $rows,
            'from' => $from?->format('Y-m-d'),
            'to' => $to?->format('Y-m-d'),
        ]);
    }

    public function salesByCustomer(Request $request)
    {
        $from = $request->date('from');
        $to = $request->date('to');

        $query = Invoice::with('customer')
            ->when($from, fn ($q) => $q->whereDate('invoice_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('invoice_date', '<=', $to));

        $data = $query->get()->groupBy('customer_id')->map(function ($invoices) {
            $customer = $invoices->first()->customer;
            $total = $invoices->sum('total');
            $paid = $invoices->sum('amount_paid');
            $due = $invoices->sum(fn ($invoice) => $invoice->remaining_due);

            return [
                'customer' => $customer?->display_name ?? __('عميل نقدي'),
                'total' => $total,
                'paid' => $paid,
                'due' => $due,
                'count' => $invoices->count(),
            ];
        })->values();

        $rows = $data->map(fn ($row) => [
            $row['customer'],
            number_format((float) $row['total'], 2, '.', ''),
            number_format((float) $row['paid'], 2, '.', ''),
            number_format((float) $row['due'], 2, '.', ''),
            $row['count'],
        ])->toArray();

        if ($request->boolean('export')) {
            $this->ensureCanExport();
            return $this->export(
                __('تقرير المبيعات حسب العميل'),
                ['العميل', 'إجمالي المبيعات', 'المدفوع', 'المتبقي', 'عدد الفواتير'],
                $rows
            );
        }

        return view('accounting.reports.sales_by_customer', [
            'rows' => $rows,
            'from' => $from?->format('Y-m-d'),
            'to' => $to?->format('Y-m-d'),
        ]);
    }

    public function customerAging(Request $request)
    {
        $customers = Customer::withTrashed()->with(['invoices' => function ($query) {
            $query->where('payment_type', 'credit')->orderBy('invoice_date');
        }])->get();

        $buckets = [
            '0-30' => 0,
            '31-60' => 0,
            '61-90' => 0,
            '90+' => 0,
        ];

        $rows = [];
        $today = now();

        foreach ($customers as $customer) {
            $totalDue = $customer->invoices->sum(fn ($invoice) => $invoice->remaining_due);

            if ($totalDue <= 0) {
                continue;
            }

            $ageBuckets = ['0-30' => 0, '31-60' => 0, '61-90' => 0, '90+' => 0];

            foreach ($customer->invoices as $invoice) {
                $remaining = $invoice->remaining_due;

                if ($remaining <= 0) {
                    continue;
                }

                $days = $invoice->invoice_date->diffInDays($today);

                if ($days <= 30) {
                    $ageBuckets['0-30'] += $remaining;
                } elseif ($days <= 60) {
                    $ageBuckets['31-60'] += $remaining;
                } elseif ($days <= 90) {
                    $ageBuckets['61-90'] += $remaining;
                } else {
                    $ageBuckets['90+'] += $remaining;
                }
            }

            foreach ($ageBuckets as $bucket => $amount) {
                $buckets[$bucket] += $amount;
            }

            $rows[] = [
                $customer->display_name,
                number_format((float) $ageBuckets['0-30'], 2, '.', ''),
                number_format((float) $ageBuckets['31-60'], 2, '.', ''),
                number_format((float) $ageBuckets['61-90'], 2, '.', ''),
                number_format((float) $ageBuckets['90+'], 2, '.', ''),
                number_format((float) array_sum($ageBuckets), 2, '.', ''),
            ];
        }

        if ($request->boolean('export')) {
            $this->ensureCanExport();
            return $this->export(__('تقرير أعمار الديون'), ['العميل', '0-30', '31-60', '61-90', '90+', 'الإجمالي'], $rows);
        }

        return view('accounting.reports.customer_aging', compact('rows', 'buckets'));
    }

    public function customerStatement(Request $request)
    {
        $customers = Customer::withTrashed()->orderBy('name')->get();
        $customerId = $request->get('customer_id') ?? $customers->first()?->id;
        $from = $request->date('from');
        $to = $request->date('to');
        $direction = $request->get('direction', 'desc');
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc';
        $search = trim((string) $request->get('q', ''));

        if (!$customerId) {
            return view('accounting.reports.customer_statement', [
                'customers' => $customers,
                'customer' => null,
                'rows' => collect(),
                'totals' => ['debit' => 0, 'credit' => 0, 'balance' => 0],
                'from' => null,
                'to' => null,
            ]);
        }

        $customer = Customer::withTrashed()->findOrFail($customerId);

        $baseQuery = CustomerTransaction::query()
            ->where('customer_id', $customer->id);

        $transactions = (clone $baseQuery)
            ->when($from, fn ($query) => $query->whereDate('transaction_date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('transaction_date', '<=', $to))
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $transactions->load('relatedModel');

        $normalizeType = static function (?string $type): string {
            if (!$type) {
                return '';
            }

            if (class_exists($type)) {
                $type = class_basename($type);
            } else {
                $type = Str::studly(str_replace(['\\', '/', '-', '_'], ' ', $type));
            }
        }

            $type = preg_replace('/[^a-z0-9]/i', '', (string) $type) ?: 'model';

            return Str::lower($type);
        };

        $fingerprint = static function (CustomerTransaction $transaction) use ($normalizeType): string {
            $modelKey = $normalizeType($transaction->related_model_type);

            return $modelKey . ':' . (string) $transaction->related_model_id . ':' . $transaction->type;
        };

        $sortKey = static fn (CustomerTransaction $transaction) => ($transaction->transaction_date?->format('Y-m-d H:i:s')
            ?? $transaction->created_at?->format('Y-m-d H:i:s')
            ?? '0000-00-00 00:00:00') . ':' . str_pad((string) $transaction->id, 10, '0', STR_PAD_LEFT);

        $transactions = $transactions
            ->sortByDesc($sortKey)
            ->unique(fn (CustomerTransaction $transaction) => $fingerprint($transaction))
            ->values()
            ->sortBy($sortKey)
            ->values();

        $missingOrderIds = $transactions
            ->where('related_model_type', Order::class)
            ->filter(fn ($transaction) => !$transaction->relatedModel)
            ->pluck('related_model_id')
            ->all();

        if (!empty($missingOrderIds)) {
            $orders = Order::withTrashed()->whereIn('id', $missingOrderIds)->get()->keyBy('id');
            foreach ($transactions as $transaction) {
                if ($transaction->related_model_type === Order::class && !$transaction->relatedModel) {
                    $transaction->setRelation('relatedModel', $orders->get($transaction->related_model_id));
                }
            }
        }

        $openingBalance = null;

        if ($from) {
            $previousTransaction = (clone $baseQuery)
                ->whereDate('transaction_date', '<', $from)
                ->orderByDesc('transaction_date')
                ->orderByDesc('id')
                ->get()
                ->sortByDesc($sortKey)
                ->unique(fn (CustomerTransaction $transaction) => $fingerprint($transaction))
                ->values()
                ->first();

            $openingBalance = $previousTransaction?->balance_after;
        }

        $startingBalance = $openingBalance;

        if ($startingBalance === null) {
            $firstTransaction = $transactions->first();
            if ($firstTransaction) {
                $delta = $firstTransaction->type === CustomerTransaction::TYPE_DEBIT
                    ? (float) $firstTransaction->amount
                    : -(float) $firstTransaction->amount;
                $startingBalance = (float) $firstTransaction->balance_after - $delta;
            } else {
                $startingBalance = 0.0;
            }
        }

        $rows = collect();
        $runningBalance = (float) $startingBalance;

        $seenTransactions = [];

        foreach ($transactions as $transaction) {
            $transactionKey = $fingerprint($transaction);

            if (isset($seenTransactions[$transactionKey])) {
                continue;
            }

            $seenTransactions[$transactionKey] = true;

            $related = $transaction->relatedModel;

            $typeLabel = match ($transaction->related_model_type) {
                Order::class => $related
                    ? (Order::saleTypeLabels()[$related->sale_type] ?? __('طلب آجل'))
                    : __('طلب آجل'),
                ReceiptVoucher::class => __('سند قبض'),
                AllowanceVoucher::class => match ($related?->type) {
                    AllowanceVoucher::TYPE_INCREASE => __('سند سماح له'),
                    AllowanceVoucher::TYPE_DECREASE => __('سند سماح عليه'),
                    default => __('سند سماح'),
                },
                default => $transaction->type === CustomerTransaction::TYPE_DEBIT ? __('مدين') : __('دائن'),
            };

            $reference = match ($transaction->related_model_type) {
                Order::class => $related ? ('#' . $related->id) : '#' . $transaction->related_model_id,
                ReceiptVoucher::class => $related?->number ?? '#' . $transaction->related_model_id,
                AllowanceVoucher::class => $related?->number ?? '#' . $transaction->related_model_id,
                default => '#' . $transaction->related_model_id,
            };

            $details = $transaction->description;

            if (!$details && $related) {
                if ($transaction->related_model_type === Order::class) {
                    $labels = Order::saleTypeLabels();
                    $details = $labels[$related->sale_type] ?? null;
                }

                if ($transaction->related_model_type === ReceiptVoucher::class) {
                    $details = $related->receiver_type === 'sales_rep'
                        ? __('مندوب التحصيل: :name', ['name' => optional($related->manager)->name ?? optional($related->collector)->name ?? '—'])
                        : __('تم التحصيل في :cash', ['cash' => optional($related->cashAccount)->name ?? __('الصندوق الرئيسي')]);
                }

                if ($transaction->related_model_type === AllowanceVoucher::class) {
                    $details = AllowanceVoucher::typeLabels()[$related->type] ?? __('سند سماح');
                }
            }

            $amountChange = $transaction->type === CustomerTransaction::TYPE_DEBIT
                ? (float) $transaction->amount
                : -(float) $transaction->amount;

            $runningBalance += $amountChange;

            $rows->push([
                'date' => optional($transaction->transaction_date)->format('Y-m-d') ?? '—',
                'type' => $typeLabel,
                'reference' => $reference,
                'debit' => $transaction->type === CustomerTransaction::TYPE_DEBIT ? (float) $transaction->amount : 0.0,
                'credit' => $transaction->type === CustomerTransaction::TYPE_CREDIT ? (float) $transaction->amount : 0.0,
                'balance' => $runningBalance,
                'details' => $details,
                'sort_key' => $sortKey($transaction),
            ]);
        }

        $rows = $rows->sortBy('sort_key')->values();

        if ($search !== '') {
            $searchLower = Str::lower($search);
            $rows = $rows
                ->filter(fn ($row) => Str::contains(Str::lower($row['type'] ?? ''), $searchLower)
                    || Str::contains(Str::lower($row['reference'] ?? ''), $searchLower)
                    || Str::contains(Str::lower($row['details'] ?? ''), $searchLower))
                ->values();
        }

        $totals = [
            'debit' => $rows->sum(fn ($row) => $row['debit']),
            'credit' => $rows->sum(fn ($row) => $row['credit']),
            'balance' => $rows->last()['balance'] ?? (float) $startingBalance,
        ];

        $displayRows = $direction === 'desc'
            ? $rows->reverse()->values()
            : $rows;

        if ($request->boolean('export')) {
            $this->ensureCanExport();
            $data = $rows->map(fn ($row) => [
                $row['date'],
                $row['type'],
                $row['reference'],
                number_format($row['debit'], 2, '.', ''),
                number_format($row['credit'], 2, '.', ''),
                number_format($row['balance'], 2, '.', ''),
                $row['details'] ?? '',
            ])->toArray();

            return $this->export(
                __('كشف حساب عميل') . ' ' . $customer->display_name,
                ['التاريخ', 'النوع', 'المرجع', 'مدين', 'دائن', 'الرصيد بعد الحركة', 'تفاصيل'],
                $data
            );
        }

        return view('accounting.reports.customer_statement', [
            'customers' => $customers,
            'customer' => $customer,
            'rows' => $displayRows,
            'totals' => $totals,
            'from' => $from?->format('Y-m-d'),
            'to' => $to?->format('Y-m-d'),
            'search' => $search,
            'direction' => $direction,
            'openingBalance' => $startingBalance,
        ]);
    }

    public function managerActivity(Request $request)
    {
        $from = $request->date('from');
        $to = $request->date('to');
        $managerId = $request->get('manager_id');

        $managers = Manager::orderBy('name')->get();

        $invoiceQuery = Invoice::with('customer')
            ->when($managerId, fn ($q) => $q->where('manager_id', $managerId))
            ->when($from, fn ($q) => $q->whereDate('invoice_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('invoice_date', '<=', $to));

        $receiptQuery = ReceiptVoucher::with('customer')
            ->when($managerId, fn ($q) => $q->where('manager_id', $managerId))
            ->when($from, fn ($q) => $q->whereDate('voucher_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('voucher_date', '<=', $to));

        $paymentQuery = PaymentVoucher::with('cashAccount')
            ->when($managerId, fn ($q) => $q->where('manager_id', $managerId))
            ->when($from, fn ($q) => $q->whereDate('voucher_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('voucher_date', '<=', $to));

        $invoices = $invoiceQuery->get();
        $receipts = $receiptQuery->get();
        $payments = $paymentQuery->get();

        $summary = [
            'invoices_total' => $invoices->sum('total'),
            'invoices_paid_total' => $invoices->sum('amount_paid'),
            'invoices_due_total' => $invoices->sum(fn ($invoice) => $invoice->remaining_due),
            'receipts_total' => $receipts->sum('amount'),
            'payments_total' => $payments->sum('amount'),
        ];

        if ($request->boolean('export')) {
            $this->ensureCanExport();
            $rows = [];

            foreach ($invoices as $invoice) {
                $rows[] = [
                    'Invoice',
                    $invoice->invoice_date->format('Y-m-d'),
                    $invoice->number,
                    $invoice->customer?->display_name,
                    number_format((float) $invoice->total, 2, '.', ''),
                    number_format((float) $invoice->amount_paid, 2, '.', ''),
                    number_format((float) $invoice->remaining_due, 2, '.', ''),
                ];
            }

            foreach ($receipts as $receipt) {
                $rows[] = [
                    'Receipt',
                    $receipt->voucher_date->format('Y-m-d'),
                    $receipt->number,
                    $receipt->customer?->display_name,
                    number_format((float) $receipt->amount, 2, '.', ''),
                    number_format((float) $receipt->amount, 2, '.', ''),
                    number_format(0, 2, '.', ''),
                ];
            }

            foreach ($payments as $payment) {
                $rows[] = [
                    'Payment',
                    $payment->voucher_date->format('Y-m-d'),
                    $payment->number,
                    $payment->cashAccount?->name,
                    number_format((float) $payment->amount, 2, '.', ''),
                    number_format(0, 2, '.', ''),
                    number_format((float) $payment->amount, 2, '.', ''),
                ];
            }

            return $this->export(
                __('تقرير نشاط المدير'),
                ['النوع', 'التاريخ', 'المرجع', 'التفاصيل', 'الإجمالي', 'المدفوع', 'المتبقي'],
                $rows
            );
        }

        return view('accounting.reports.manager_activity', compact('managers', 'invoices', 'receipts', 'payments', 'summary', 'from', 'to', 'managerId'));
    }

    public function collectorBalances(Request $request)
    {
        $from = $request->date('from');
        $to = $request->date('to');

        $managers = Manager::permission('sales-rep')
            ->with('salesRepresentativeAccount')
            ->orderBy('name')
            ->get();

        $accountIds = $managers->pluck('sales_representative_account_id')->filter()->values();

        $lineBalances = collect();

        if ($accountIds->isNotEmpty()) {
            $lineBalances = JournalEntryLine::query()
                ->selectRaw('manager_id, SUM(debit - credit) as balance')
                ->whereIn('account_id', $accountIds)
                ->whereNotNull('manager_id')
                ->when($from, fn ($query) => $query->whereHas('entry', fn ($sub) => $sub->whereDate('entry_date', '>=', $from)))
                ->when($to, fn ($query) => $query->whereHas('entry', fn ($sub) => $sub->whereDate('entry_date', '<=', $to)))
                ->groupBy('manager_id')
                ->pluck('balance', 'manager_id');
        }

        $rows = $managers->map(function (Manager $manager) use ($lineBalances) {
            $balance = (float) ($lineBalances[$manager->getKey()] ?? 0);

            return [
                'manager' => $manager,
                'balance' => $balance,
            ];
        });

        $total = $rows->sum('balance');

        if ($request->boolean('export')) {
            $this->ensureCanExport();

            $data = $rows->map(fn ($row) => [
                $row['manager']->name,
                number_format($row['balance'], 2, '.', ''),
            ])->toArray();

            return $this->export(__('أرصدة عهد المندوبين'), ['المندوب', 'الرصيد (IQD)'], $data);
        }

        return view('accounting.reports.collector_balances', [
            'rows' => $rows,
            'total' => $total,
            'from' => $from?->format('Y-m-d'),
            'to' => $to?->format('Y-m-d'),
        ]);
    }

    public function customerCollections(Request $request)
    {
        $from = $request->date('from');
        $to = $request->date('to');
        $customerId = $request->get('customer_id');
        $managerId = $request->get('manager_id');

        $customers = Customer::orderBy('name')->get();
        $managers = Manager::orderBy('name')->get();

        $receipts = ReceiptVoucher::with(['customer', 'manager', 'collector', 'cashAccount'])
            ->when($from, fn ($query) => $query->whereDate('voucher_date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('voucher_date', '<=', $to))
            ->when($customerId, fn ($query) => $query->where('customer_id', $customerId))
            ->when($managerId, fn ($query) => $query->where('manager_id', $managerId))
            ->orderBy('voucher_date')
            ->get();

        $rows = $receipts->map(function (ReceiptVoucher $voucher) {
            return [
                'date' => $voucher->voucher_date?->format('Y-m-d'),
                'number' => $voucher->number,
                'customer' => $voucher->customer?->display_name,
                'amount' => $voucher->amount,
                'currency_amount' => $voucher->currency_amount,
                'currency_code' => $voucher->currency_code,
                'manager' => $voucher->manager?->name,
                'collector' => $voucher->collector?->name,
                'receiver_type' => $voucher->receiver_type,
                'cash_account' => $voucher->cashAccount?->name,
            ];
        });

        $totals = [
            'amount' => $receipts->sum('amount'),
            'by_manager' => $receipts->groupBy('manager_id')->map->sum('amount'),
            'by_customer' => $receipts->groupBy('customer_id')->map->sum('amount'),
        ];

        if ($request->boolean('export')) {
            $this->ensureCanExport();

            $data = $rows->map(fn ($row) => [
                $row['date'],
                $row['number'],
                $row['customer'] ?? '—',
                number_format($row['amount'], 2, '.', ''),
                $row['manager'] ?? '—',
                $row['collector'] ?? '—',
                $row['receiver_type'] === 'sales_rep' ? __('مندوب') : __('قاصة/بنك'),
            ])->toArray();

            return $this->export(
                __('تقرير تحصيلات العملاء'),
                ['التاريخ', 'رقم السند', 'العميل', 'المبلغ (IQD)', 'المسؤول', 'المستلم', 'نوع الاستلام'],
                $data
            );
        }

        return view('accounting.reports.customer_collections', [
            'rows' => $rows,
            'totals' => $totals,
            'customers' => $customers,
            'managers' => $managers,
            'selectedCustomer' => $customerId,
            'selectedManager' => $managerId,
            'from' => $from?->format('Y-m-d'),
            'to' => $to?->format('Y-m-d'),
        ]);
    }

    public function representativeDeliveries(Request $request)
    {
        $from = $request->date('from');
        $to = $request->date('to');
        $managerId = $request->get('manager_id');

        $managers = Manager::permission('sales-rep')->orderBy('name')->get();

        $transfers = InternalTransfer::with([
            'source',
            'destination',
            'creator',
            'manager',
            'journalEntry',
        ])
            ->where('source_type', Manager::class)
            ->where('destination_type', CashAccount::class)
            ->when($from, fn ($query) => $query->whereDate('transfer_date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('transfer_date', '<=', $to))
            ->when($managerId, fn ($query) => $query->where('manager_id', $managerId))
            ->orderBy('transfer_date')
            ->get();

        $rows = $transfers->map(function (InternalTransfer $transfer) {
            $destinationLabel = $transfer->destination instanceof CashAccount
                ? $transfer->destination->name
                : optional($transfer->destination)->name;

            return [
                'date' => $transfer->transfer_date?->format('Y-m-d'),
                'reference' => $transfer->reference,
                'manager' => $transfer->manager?->name ?? optional($transfer->source)->name,
                'cash_account' => $destinationLabel,
                'amount' => $transfer->system_amount,
                'currency_amount' => $transfer->currency_amount,
                'currency_code' => $transfer->currency_code,
                'created_by' => $transfer->creator?->name,
            ];
        });

        $total = $transfers->sum('system_amount');

        if ($request->boolean('export')) {
            $this->ensureCanExport();

            $data = $rows->map(fn ($row) => [
                $row['date'],
                $row['reference'],
                $row['manager'] ?? '—',
                $row['cash_account'] ?? '—',
                number_format($row['amount'], 2, '.', ''),
            ])->toArray();

            return $this->export(
                __('تقرير تسليمات المندوبين'),
                ['التاريخ', 'المرجع', 'المندوب', 'الصندوق المستقبل', 'المبلغ (IQD)'],
                $data
            );
        }

        return view('accounting.reports.representative_deliveries', [
            'rows' => $rows,
            'managers' => $managers,
            'selectedManager' => $managerId,
            'from' => $from?->format('Y-m-d'),
            'to' => $to?->format('Y-m-d'),
            'total' => $total,
        ]);
    }

    public function cashAccountStatement(Request $request)
    {
        $from = $request->date('from');
        $to = $request->date('to');

        $systemAccounts = SystemAccount::with('cashAccount')->first();
        $account = $systemAccounts?->cashAccount;
        $cashAccount = null;

        if ($account) {
            $cashAccount = CashAccount::with('account')->where('account_id', $account->id)->first();
        }

        if (!$account) {
            $cashAccount = CashAccount::with('account')->whereNotNull('account_id')->orderBy('name')->first();
            $account = $cashAccount?->account;
        }

        if (!$account) {
            return view('accounting.reports.cash_account_statement', [
                'account' => null,
                'rows' => collect(),
                'from' => $from?->format('Y-m-d'),
                'to' => $to?->format('Y-m-d'),
                'balance' => 0,
            ]);
        }

        $lines = JournalEntryLine::with(['entry', 'manager'])
            ->where('account_id', $account->id)
            ->when($from, fn ($query) => $query->whereHas('entry', fn ($sub) => $sub->whereDate('entry_date', '>=', $from)))
            ->when($to, fn ($query) => $query->whereHas('entry', fn ($sub) => $sub->whereDate('entry_date', '<=', $to)))
            ->get()
            ->sortBy(fn (JournalEntryLine $line) => sprintf('%s-%06d', $line->entry->entry_date?->format('Ymd') ?? '00000000', $line->id));

        $running = 0;

        $rows = $lines->map(function (JournalEntryLine $line) use (&$running) {
            $running += (float) $line->debit - (float) $line->credit;

            return [
                'date' => $line->entry->entry_date?->format('Y-m-d'),
                'reference' => $line->entry->reference,
                'description' => $line->entry->description,
                'debit' => $line->debit,
                'credit' => $line->credit,
                'balance' => $running,
                'manager' => $line->manager?->name,
                'link' => $line->entry->reference_type,
            ];
        });

        if ($request->boolean('export')) {
            $this->ensureCanExport();

            $data = $rows->map(fn ($row) => [
                $row['date'],
                $row['reference'],
                $row['description'],
                number_format($row['debit'], 2, '.', ''),
                number_format($row['credit'], 2, '.', ''),
                number_format($row['balance'], 2, '.', ''),
            ])->toArray();

            return $this->export(
                __('كشف حساب الصندوق الرئيسي'),
                ['التاريخ', 'المرجع', 'الوصف', 'مدين', 'دائن', 'الرصيد'],
                $data
            );
        }

        return view('accounting.reports.cash_account_statement', [
            'account' => $account,
            'cashAccount' => $cashAccount,
            'rows' => $rows,
            'from' => $from?->format('Y-m-d'),
            'to' => $to?->format('Y-m-d'),
            'balance' => $rows->last()['balance'] ?? 0,
        ]);
    }

    protected function ensureCanExport(): void
    {
        $user = auth('admin')->user() ?? auth('manager')->user();

        abort_unless($user && $user->can('export-excel'), 403);
    }

    protected function export(string $name, array $headings, array $rows): BinaryFileResponse
    {
        return Excel::download(new TableExport($headings, $rows), str_replace(' ', '_', $name) . '.xlsx');
    }

}
