<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\CashAccount;
use App\Models\JournalEntry;
use App\Models\PaymentVoucher;
use App\Models\Manager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Middleware\PermissionMiddleware;

class PaymentVoucherController extends Controller
{
    public function __construct()
    {
        $permissionMiddleware = PermissionMiddleware::class;

        $this->middleware($permissionMiddleware . ':access-accounting');
        $this->middleware($permissionMiddleware . ':view-any-payment-voucher|view-own-payment-voucher')->only(['index']);
        $this->middleware($permissionMiddleware . ':create-payment-voucher')->only(['create', 'store']);
    }

    public function index(): View
    {
        $actor = $this->currentActor();

        if (!$actor->can('view-any-payment-voucher') && !$actor->can('view-own-payment-voucher')) {
            abort(403);
        }

        $vouchersQuery = PaymentVoucher::with(['cashAccount', 'expenseAccount', 'manager'])
            ->latest('voucher_date');

        if (!$actor->can('view-any-payment-voucher')) {
            $vouchersQuery->where('manager_id', $actor->getKey());
        }

        $vouchers = $vouchersQuery->paginate(20);

        return view('accounting.payment_vouchers.index', compact('vouchers'));
    }

    public function create(): View
    {
        $this->authorize('create', PaymentVoucher::class);

        return view('accounting.payment_vouchers.create', [
            'cashAccounts' => CashAccount::orderBy('name')->get(),
            'expenseAccounts' => Account::where('type', 'expense')->orderBy('code')->get(),
            'managers' => Manager::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', PaymentVoucher::class);

        $validated = $request->validate([
            'number' => ['required', 'string', 'max:50', 'unique:payment_vouchers,number'],
            'voucher_date' => ['required', 'date'],
            'cash_account_id' => ['required', 'exists:cash_accounts,id'],
            'expense_account_id' => ['required', 'exists:accounts,id'],
            'currency_code' => ['required', Rule::in(['IQD', 'USD'])],
            'currency_amount' => ['required', 'numeric', 'min:0.01'],
            'exchange_rate' => [
                Rule::requiredIf(fn () => $request->input('currency_code') === 'USD'),
                'nullable',
                'numeric',
                'min:0.0001',
            ],
            'description' => ['nullable', 'string'],
            'responsible_manager_id' => ['nullable', 'exists:managers,id'],
        ]);

        $managerId = $this->currentManagerId();
        $cashAccount = CashAccount::findOrFail($validated['cash_account_id']);

        if ($cashAccount->currency_code !== $validated['currency_code']) {
            throw ValidationException::withMessages([
                'currency_code' => __('عملة السند يجب أن تتطابق مع عملة القاصة المحددة.'),
            ]);
        }

        $exchangeRate = $validated['currency_code'] === 'USD'
            ? $validated['exchange_rate']
            : 1;

        $baseAmount = round($validated['currency_amount'] * $exchangeRate, 2);

        $validated['exchange_rate'] = $exchangeRate;
        $validated['amount'] = $baseAmount;

        $responsibleManagerId = $validated['responsible_manager_id'] ?? null;

        DB::transaction(function () use ($validated, $managerId, $cashAccount, $responsibleManagerId) {
            $entry = JournalEntry::create([
                'reference' => $validated['number'],
                'entry_date' => $validated['voucher_date'],
                'description' => __('سند صرف رقم :number', ['number' => $validated['number']]),
                'manager_id' => $responsibleManagerId ?? $managerId,
                'reference_type' => PaymentVoucher::class,
            ]);

            $entry->lines()->createMany([
                [
                    'account_id' => $validated['expense_account_id'],
                    'manager_id' => $responsibleManagerId ?? $managerId,
                    'debit' => $validated['amount'],
                    'currency_code' => $validated['currency_code'],
                    'currency_debit' => $validated['currency_amount'],
                    'exchange_rate' => $validated['exchange_rate'],
                    'credit' => 0,
                    'description' => __('مصروف'),
                ],
                [
                    'account_id' => $cashAccount->account_id,
                    'manager_id' => $responsibleManagerId ?? $managerId,
                    'debit' => 0,
                    'credit' => $validated['amount'],
                    'currency_code' => $validated['currency_code'],
                    'currency_credit' => $validated['currency_amount'],
                    'exchange_rate' => $validated['exchange_rate'],
                    'description' => __('دفع نقدي'),
                ],
            ]);

            $payload = [
                ...$validated,
                'manager_id' => $responsibleManagerId ?? $managerId,
                'journal_entry_id' => $entry->id,
            ];

            unset($payload['responsible_manager_id']);

            $voucher = PaymentVoucher::create($payload);

            $entry->update(['reference_id' => $voucher->id]);
        });

        return redirect()->route('admin.accounting.payment-vouchers.index')->with('status', __('تم تسجيل سند الصرف بنجاح'));
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

        if (!$actor instanceof Manager) {
            abort(403);
        }

        return $actor;
    }
}
