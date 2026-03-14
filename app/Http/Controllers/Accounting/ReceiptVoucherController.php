<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\CashAccount;
use App\Models\Customer;
use App\Models\JournalEntry;
use App\Models\ReceiptVoucher;
use App\Models\Manager;
use App\Services\CustomerAccountService;
use App\Models\Order;
use App\Services\OrderAccountingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Middleware\PermissionMiddleware;

class ReceiptVoucherController extends Controller
{
    public function __construct()
    {
        $permissionMiddleware = PermissionMiddleware::class;

        $this->middleware($permissionMiddleware . ':access-accounting');
        $this->middleware($permissionMiddleware . ':view-any-receipt-voucher|view-own-receipt-voucher')->only(['index']);
        $this->middleware($permissionMiddleware . ':create-receipt-voucher')->only(['create', 'store']);
    }

    public function index(): View
    {
        $actor = $this->currentActor();

        if (!$actor->can('view-any-receipt-voucher') && !$actor->can('view-own-receipt-voucher')) {
            abort(403);
        }

        $vouchersQuery = ReceiptVoucher::with(['cashAccount', 'account', 'customer', 'manager', 'collector'])
            ->latest('voucher_date');

        if (!$actor->can('view-any-receipt-voucher')) {
            $vouchersQuery->where('manager_id', $actor->getKey());
        }

        $vouchers = $vouchersQuery->paginate(20);

        return view('accounting.receipt_vouchers.index', compact('vouchers'));
    }

    public function create(): View
    {
        $this->authorize('create', ReceiptVoucher::class);

        return view('accounting.receipt_vouchers.create', [
            'cashAccounts' => CashAccount::orderBy('name')->get(),
            'accounts' => Account::orderBy('code')->get(),
            'customers' => Customer::orderBy('name')->get(),
            'salesReps' => Manager::permission('sales-rep')->orderBy('name')->get(),
            'orders' => Order::with(['customer', 'receiptVouchers'])
                ->whereIn('sale_type', [Order::SALE_TYPE_CREDIT, Order::SALE_TYPE_PARTIAL_PAYMENT])
                ->where('payment_status', '!=', Order::PAYMENT_STATUS_PAID)
                ->latest('id')
                ->take(100)
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', ReceiptVoucher::class);

        $validated = $request->validate([
            'number' => ['required', 'string', 'max:50', 'unique:receipt_vouchers,number'],
            'voucher_date' => ['required', 'date'],
            'receiver_type' => ['required', Rule::in(['cash_account', 'sales_rep'])],
            'cash_account_id' => [
                Rule::requiredIf(fn () => $request->input('receiver_type') === 'cash_account'),
                'nullable',
                'exists:cash_accounts,id',
            ],
            'collector_id' => [
                Rule::requiredIf(fn () => $request->input('receiver_type') === 'sales_rep'),
                'nullable',
                'exists:managers,id',
            ],
            'account_id' => [
                Rule::requiredIf(fn () => !$request->filled('customer_id')),
                'nullable',
                'exists:accounts,id',
            ],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'order_id' => ['nullable', 'exists:orders,id'],
            'currency_code' => ['required', Rule::in(['IQD', 'USD'])],
            'currency_amount' => ['required', 'numeric', 'min:0.01'],
            'exchange_rate' => [
                Rule::requiredIf(fn () => $request->input('currency_code') === 'USD'),
                'nullable',
                'numeric',
                'min:0.0001',
            ],
            'description' => ['nullable', 'string'],
        ]);

        $exchangeRate = $validated['currency_code'] === 'USD'
            ? $validated['exchange_rate']
            : 1;

        $baseAmount = round($validated['currency_amount'] * $exchangeRate, 2);

        $validated['exchange_rate'] = $exchangeRate;
        $validated['amount'] = $baseAmount;

        $managerId = $this->currentManagerId();

        $receiverType = $validated['receiver_type'];
        $cashAccount = null;
        $collector = null;
        $debitAccountId = null;
        $order = null;

        if (!empty($validated['order_id'])) {
            $order = Order::with(['customer'])->findOrFail($validated['order_id']);
            $validated['customer_id'] = $order->customer_id;
        }

        if ($receiverType === 'cash_account') {
            $cashAccount = CashAccount::findOrFail($validated['cash_account_id']);
            if ($cashAccount->currency_code !== $validated['currency_code']) {
                throw ValidationException::withMessages([
                    'currency_code' => __('عملة السند يجب أن تتطابق مع عملة القاصة المحددة.'),
                ]);
            }
            $debitAccountId = $cashAccount->account_id;
        } else {
            $collector = Manager::permission('sales-rep')->find($validated['collector_id']);

            if (!$collector) {
                throw ValidationException::withMessages([
                    'collector_id' => __('المستخدم المختار ليس لديه صلاحية مندوب مبيعات.'),
                ]);
            }

            if (!$collector->sales_representative_account_id) {
                throw ValidationException::withMessages([
                    'collector_id' => __('يجب ربط حساب محاسبي بالمندوب قبل استخدامه كسند قبض.'),
                ]);
            }

            $debitAccountId = $collector->sales_representative_account_id;
        }
        $accountId = $validated['account_id'] ?? null;
        $customer = null;

        if ($order) {
            $customer = $order->customer;
            if ($customer) {
                $receivableAccount = CustomerAccountService::ensureReceivableAccount($customer);
                $accountId = $receivableAccount?->id ?? $accountId;
            }
        } elseif (!empty($validated['customer_id'])) {
            $customer = Customer::findOrFail($validated['customer_id']);
            $walletAccount = CustomerAccountService::ensureWalletAccount($customer);
            $accountId = $walletAccount->id;
        }

        if (!$accountId) {
            return back()->withInput()->withErrors(__('يرجى اختيار الحساب المقابل أو تحديد العميل.'));
        }

        $customerForBalance = $customer;

        if ($order && $customerForBalance && !$accountId) {
            $receivableAccount = CustomerAccountService::ensureReceivableAccount($customerForBalance);
            $accountId = $receivableAccount?->id ?? $accountId;
        }

        if ($order && !$accountId) {
            return back()->withInput()->withErrors(__('يتعذر تحديد حساب العميل المرتبط بالطلب المحدد.'));
        }

        $baseAmount = $validated['amount'];

        if ($order && $baseAmount > ($order->outstandingAmountSystem() + 0.01)) {
            return back()->withInput()->withErrors([
                'currency_amount' => __('المبلغ المدفوع يتجاوز الرصيد المتبقي على الطلب.'),
            ]);
        }

        $responsibleManagerId = $receiverType === 'sales_rep'
            ? optional($collector)->getKey()
            : $managerId;

        $voucher = DB::transaction(function () use (
            $validated,
            $managerId,
            $cashAccount,
            $collector,
            $debitAccountId,
            $accountId,
            $customerForBalance,
            $receiverType,
            $order,
            $responsibleManagerId
        ) {
            $entry = JournalEntry::create([
                'reference' => $validated['number'],
                'entry_date' => $validated['voucher_date'],
                'description' => __('سند قبض رقم :number', ['number' => $validated['number']]),
                'manager_id' => $responsibleManagerId ?? $managerId,
                'customer_id' => $validated['customer_id'] ?? null,
                'reference_type' => ReceiptVoucher::class,
                'order_id' => $order?->id,
            ]);

            $entry->lines()->createMany([
                [
                    'account_id' => $debitAccountId,
                    'manager_id' => $responsibleManagerId ?? $managerId,
                    'customer_id' => $validated['customer_id'] ?? null,
                    'debit' => $validated['amount'],
                    'currency_code' => $validated['currency_code'],
                    'currency_debit' => $validated['currency_amount'],
                    'exchange_rate' => $validated['exchange_rate'],
                    'credit' => 0,
                    'description' => $receiverType === 'cash_account'
                        ? __('قبض نقدي')
                        : __('قبض بواسطة المندوب :name', ['name' => $collector->name]),
                ],
                [
                    'account_id' => $accountId,
                    'manager_id' => $collector?->getKey(),
                    'customer_id' => $validated['customer_id'] ?? null,
                    'debit' => 0,
                    'credit' => $validated['amount'],
                    'currency_code' => $validated['currency_code'],
                    'currency_credit' => $validated['currency_amount'],
                    'exchange_rate' => $validated['exchange_rate'],
                    'description' => __('مقابل سند قبض'),
                ],
            ]);

            $voucher = ReceiptVoucher::create([
                ...$validated,
                'receiver_type' => $receiverType,
                'cash_account_id' => $cashAccount?->id,
                'collector_id' => $collector?->id,
                'account_id' => $accountId,
                'manager_id' => $responsibleManagerId ?? $managerId,
                'journal_entry_id' => $entry->id,
                'order_id' => $order?->id,
            ]);

            $entry->update(['reference_id' => $voucher->id]);

            if ($customerForBalance) {
                $customerForBalance->decrement('receivable_balance', $validated['amount']);
                $customerForBalance->refresh();
                if ($customerForBalance->receivable_balance < 0) {
                    $customerForBalance->update(['receivable_balance' => 0]);
                }
            }

            return $voucher;
        });

        if ($order) {
            try {
                app(OrderAccountingService::class)->syncPaymentStatus($order->refresh());
            } catch (\Throwable $e) {
                Log::warning('Unable to sync order payment status after receipt voucher', [
                    'order_id' => $order->id,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return redirect()->route('admin.accounting.receipt-vouchers.index')->with('status', __('تم تسجيل سند القبض بنجاح'));
    }

    protected function currentManagerId(): ?int
    {
        if (Auth::guard('admin')->check()) {
            return Auth::guard('admin')->id();
        }

        if (Auth::check()) {
            return Auth::id();
        }

        return null;
    }

    protected function currentActor(): Manager
    {
        $actor = Auth::guard('admin')->user();

        if (!$actor && Auth::check()) {
            $actor = Manager::find(Auth::id());
        }

        if (!$actor) {
            abort(403);
        }

        return $actor;
    }
}
