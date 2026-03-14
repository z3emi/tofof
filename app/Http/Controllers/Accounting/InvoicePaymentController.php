<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\CashAccount;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\JournalEntry;
use App\Models\SystemAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Middleware\PermissionMiddleware;

class InvoicePaymentController extends Controller
{
    public function __construct()
    {
        $permissionMiddleware = PermissionMiddleware::class;

        $this->middleware($permissionMiddleware . ':access-accounting');
        $this->middleware($permissionMiddleware . ':record-accounting-invoice-payments')->only(['store']);
    }

    public function store(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'payment_date' => ['required', 'date'],
            'cash_account_id' => ['required', 'exists:cash_accounts,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($validated['amount'] > $invoice->remaining_due) {
            return back()->withInput()->withErrors([
                'amount' => __('المبلغ المدفوع يتجاوز الرصيد المتبقي على الفاتورة.'),
            ]);
        }

        $managerId = Auth::guard('admin')->id()
            ?? (Auth::check() ? Auth::id() : null);
        $cashAccount = CashAccount::findOrFail($validated['cash_account_id']);
        $systemAccounts = SystemAccount::first();

        if (!$systemAccounts || !$systemAccounts->receivable_account_id) {
            return back()->withInput()->withErrors(__('يرجى تعيين حساب العملاء (المدينون) في إعدادات النظام.'));
        }

        DB::transaction(function () use ($invoice, $validated, $managerId, $cashAccount, $systemAccounts) {
            $reference = $validated['reference'] ?? $invoice->number;
            $paymentDate = $validated['payment_date'];
            $amount = $validated['amount'];

            $entry = JournalEntry::create([
                'reference' => $reference,
                'entry_date' => $paymentDate,
                'description' => __('سداد فاتورة :number', ['number' => $invoice->number]),
                'manager_id' => $managerId,
                'customer_id' => $invoice->customer_id,
                'reference_type' => InvoicePayment::class,
            ]);

            $entry->lines()->createMany([
                [
                    'account_id' => $cashAccount->account_id,
                    'manager_id' => $managerId,
                    'customer_id' => $invoice->customer_id,
                    'debit' => $amount,
                    'credit' => 0,
                    'currency_code' => 'IQD',
                    'currency_debit' => $amount,
                    'currency_credit' => 0,
                    'exchange_rate' => 1,
                    'description' => __('تحصيل من العميل'),
                ],
                [
                    'account_id' => $systemAccounts->receivable_account_id,
                    'manager_id' => $managerId,
                    'customer_id' => $invoice->customer_id,
                    'debit' => 0,
                    'credit' => $amount,
                    'currency_code' => 'IQD',
                    'currency_debit' => 0,
                    'currency_credit' => $amount,
                    'exchange_rate' => 1,
                    'description' => __('سداد فاتورة'),
                ],
            ]);

            $payment = InvoicePayment::create([
                'invoice_id' => $invoice->id,
                'cash_account_id' => $cashAccount->id,
                'journal_entry_id' => $entry->id,
                'manager_id' => $managerId,
                'payment_date' => $paymentDate,
                'amount' => $amount,
                'currency_code' => 'IQD',
                'currency_amount' => $amount,
                'exchange_rate' => 1,
                'reference' => $validated['reference'] ?? $invoice->number,
                'notes' => $validated['notes'] ?? null,
            ]);

            $entry->update(['reference_id' => $payment->id]);

            $invoice->refreshPaymentTotals();

            if (!$invoice->cash_account_id) {
                $invoice->updateQuietly(['cash_account_id' => $cashAccount->id]);
            }

            if ($invoice->customer) {
                $invoice->customer->decrement('receivable_balance', $payment->amount);
                if ($invoice->customer->receivable_balance < 0) {
                    $invoice->customer->update(['receivable_balance' => 0]);
                }
            }
        });

        return redirect()
            ->route('admin.accounting.invoices.show', $invoice)
            ->with('status', __('تم تسجيل الدفعة بنجاح.'));
    }
}
