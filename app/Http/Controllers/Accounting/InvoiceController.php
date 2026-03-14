<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\CashAccount;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Manager;
use App\Models\JournalEntry;
use App\Models\SystemAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Spatie\Permission\Middleware\PermissionMiddleware;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $permissionMiddleware = PermissionMiddleware::class;

        $this->middleware($permissionMiddleware . ':access-accounting');
        $this->middleware($permissionMiddleware . ':view-accounting-invoices')->only(['index', 'show']);
        $this->middleware($permissionMiddleware . ':create-accounting-invoices')->only(['create', 'store']);
    }

    public function index(): View
    {
        $invoices = Invoice::with(['customer', 'manager'])
            ->latest('invoice_date')
            ->paginate(20);

        return view('accounting.invoices.index', [
            'invoices' => $invoices,
            'saleTypeLabels' => Invoice::saleTypeLabels(),
        ]);
    }

    public function create(): View
    {
        $canAssignManager = auth()->user()?->can('assign-accounting-invoice-manager') ?? false;
        $currentManagerId = $this->currentManagerId();
        $currentManager = $currentManagerId ? Manager::find($currentManagerId) : null;

        return view('accounting.invoices.create', [
            'customers' => Customer::where('origin', Customer::ORIGIN_ADMIN)->orderBy('name')->get(),
            'cashAccounts' => CashAccount::orderBy('name')->get(),
            'saleTypeLabels' => Invoice::saleTypeLabels(),
            'canAssignManager' => $canAssignManager,
            'managerOptions' => $canAssignManager ? Manager::orderBy('name')->get() : collect(),
            'currentManager' => $currentManager,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'number' => ['required', 'string', 'max:50', 'unique:invoices,number'],
            'invoice_date' => ['required', 'date'],
            'payment_type' => ['required', 'in:cash,credit'],
            'sale_type' => ['required', Rule::in(Invoice::SALE_TYPES)],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'cash_account_id' => ['nullable', 'exists:cash_accounts,id'],
            'tax_total' => ['nullable', 'numeric', 'min:0'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'manager_id' => ['nullable', Rule::exists('managers', 'id')],
        ]);

        if ($validated['payment_type'] === 'credit' && empty($validated['customer_id'])) {
            return back()->withInput()->withErrors(__('يجب اختيار العميل للفواتير الآجلة.'));
        }

        if (!empty($validated['customer_id'])) {
            $customer = Customer::where('id', $validated['customer_id'])
                ->where('origin', Customer::ORIGIN_ADMIN)
                ->first();

            if (!$customer) {
                return back()->withInput()->withErrors([
                    'customer_id' => __('لا يمكن ربط الفاتورة بعميل من الموقع.'),
                ]);
            }
        }

        $managerId = $this->currentManagerId();
        $canAssignManager = $request->user()?->can('assign-accounting-invoice-manager') ?? false;

        if ($canAssignManager && !empty($validated['manager_id'])) {
            $managerId = (int) $validated['manager_id'];
        }
        $systemAccounts = SystemAccount::first();

        if (!$systemAccounts || !$systemAccounts->sales_account_id) {
            return back()->withInput()->withErrors(__('يرجى تعيين حسابات النظام الافتراضية أولاً.'));
        }

        $items = collect($validated['items'])->map(function (array $item) {
            $quantity = (float) $item['quantity'];
            $unitPrice = (float) $item['unit_price'];
            $lineTotal = round($quantity * $unitPrice, 2);

            return [
                'description' => $item['description'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
            ];
        });

        $subtotal = $items->sum('line_total');
        $taxTotal = isset($validated['tax_total']) ? (float) $validated['tax_total'] : 0.0;
        $total = round($subtotal + $taxTotal, 2);

        if ($total <= 0) {
            return back()->withInput()->withErrors(__('قيمة الفاتورة يجب أن تكون أكبر من صفر.'));
        }

        $cashAccount = null;
        $inputAmountPaid = isset($validated['amount_paid']) ? (float) $validated['amount_paid'] : 0.0;

        if ($validated['payment_type'] === 'cash') {
            if (empty($validated['cash_account_id'])) {
                return back()->withInput()->withErrors(__('يجب اختيار حساب الصندوق/البنك للفواتير النقدية.'));
            }

            $cashAccount = CashAccount::findOrFail($validated['cash_account_id']);
            $amountPaid = $total;
        } else {
            if (!$systemAccounts->receivable_account_id) {
                return back()->withInput()->withErrors(__('يرجى تعيين حساب العملاء (المدينون) في إعدادات النظام.'));
            }

            $amountPaid = min($total, max(0, $inputAmountPaid));

            if ($amountPaid > 0) {
                if (empty($validated['cash_account_id'])) {
                    return back()->withInput()->withErrors(__('يرجى اختيار حساب الصندوق لتسجيل الدفعة المستلمة.'));
                }

                $cashAccount = CashAccount::findOrFail($validated['cash_account_id']);
            }
        }

        $remainingDue = round($total - $amountPaid, 2);

        if ($amountPaid < $inputAmountPaid) {
            return back()->withInput()->withErrors(__('المبلغ المدفوع يجب أن يكون أقل من أو يساوي إجمالي الفاتورة.'));
        }

        if ($remainingDue < 0) {
            $remainingDue = 0;
        }

        if ($remainingDue > 0 && $validated['payment_type'] === 'credit' && empty($validated['customer_id'])) {
            return back()->withInput()->withErrors(__('يجب اختيار العميل للفواتير الآجلة.'));
        }

        if ($remainingDue > 0 && !$systemAccounts->receivable_account_id) {
            return back()->withInput()->withErrors(__('يرجى تعيين حساب العملاء (المدينون) في إعدادات النظام.'));
        }

        DB::transaction(function () use ($validated, $managerId, $systemAccounts, $items, $subtotal, $taxTotal, $total, $cashAccount, $amountPaid, $remainingDue) {
            $invoice = Invoice::create([
                'number' => $validated['number'],
                'invoice_date' => $validated['invoice_date'],
                'payment_type' => $validated['payment_type'],
                'sale_type' => $validated['sale_type'],
                'customer_id' => $validated['customer_id'] ?? null,
                'subtotal' => $subtotal,
                'tax_total' => $taxTotal,
                'total' => $total,
                'amount_paid' => $amountPaid,
                'cash_account_id' => $cashAccount?->id,
                'manager_id' => $managerId,
            ]);

            $invoice->items()->createMany($items->toArray());

            $entry = JournalEntry::create([
                'reference' => $invoice->number,
                'entry_date' => $invoice->invoice_date,
                'description' => __('فاتورة مبيعات :number', ['number' => $invoice->number]),
                'manager_id' => $managerId,
                'customer_id' => $invoice->customer_id,
                'reference_type' => Invoice::class,
            ]);

            $invoice->update(['journal_entry_id' => $entry->id]);

            $lines = [];

            if ($amountPaid > 0 && $cashAccount) {
                $lines[] = [
                    'account_id' => $cashAccount->account_id,
                    'manager_id' => $managerId,
                    'customer_id' => $invoice->customer_id,
                    'debit' => $amountPaid,
                    'credit' => 0,
                    'currency_code' => 'IQD',
                    'currency_debit' => $amountPaid,
                    'currency_credit' => 0,
                    'exchange_rate' => 1,
                    'description' => __('قبض نقدي من الفاتورة'),
                ];
            }

            if ($remainingDue > 0 && $systemAccounts->receivable_account_id) {
                $lines[] = [
                    'account_id' => $systemAccounts->receivable_account_id,
                    'manager_id' => $managerId,
                    'customer_id' => $invoice->customer_id,
                    'debit' => $remainingDue,
                    'credit' => 0,
                    'currency_code' => 'IQD',
                    'currency_debit' => $remainingDue,
                    'currency_credit' => 0,
                    'exchange_rate' => 1,
                    'description' => __('فاتورة آجلة'),
                ];
            }

            $lines[] = [
                'account_id' => $systemAccounts->sales_account_id,
                'manager_id' => $managerId,
                'customer_id' => $invoice->customer_id,
                'debit' => 0,
                'credit' => $total,
                'currency_code' => 'IQD',
                'currency_debit' => 0,
                'currency_credit' => $total,
                'exchange_rate' => 1,
                'description' => __('إيرادات المبيعات'),
            ];

            $entry->lines()->createMany($lines);
            $entry->update(['reference_id' => $invoice->id]);

            if ($amountPaid > 0 && $cashAccount) {
                $invoice->payments()->create([
                    'cash_account_id' => $cashAccount->id,
                    'journal_entry_id' => $entry->id,
                    'manager_id' => $managerId,
                    'payment_date' => $invoice->invoice_date,
                    'amount' => $amountPaid,
                    'currency_code' => 'IQD',
                    'currency_amount' => $amountPaid,
                    'exchange_rate' => 1,
                    'reference' => $invoice->number,
                    'notes' => __('دفعة عند إنشاء الفاتورة'),
                ]);
                $invoice->refreshPaymentTotals();
            }

            if ($remainingDue > 0 && $invoice->customer) {
                $invoice->customer->increment('receivable_balance', $remainingDue);
            }
        });

        return redirect()->route('admin.accounting.invoices.index')->with('status', __('تم إنشاء الفاتورة بنجاح'));
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load([
            'items',
            'customer',
            'manager',
            'payments' => fn ($query) => $query->orderByDesc('payment_date')->orderByDesc('id'),
            'payments.cashAccount',
        ]);

        return view('accounting.invoices.show', [
            'invoice' => $invoice,
            'cashAccounts' => CashAccount::orderBy('name')->get(),
            'saleTypeLabels' => Invoice::saleTypeLabels(),
        ]);
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
}
