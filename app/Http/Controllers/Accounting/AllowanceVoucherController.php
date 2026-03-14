<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AllowanceVoucher;
use App\Models\Customer;
use App\Models\Manager;
use App\Services\Wallet\WalletLedgerService;
use App\Support\Sort;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class AllowanceVoucherController extends Controller
{
    public function index(Request $request): View
    {
        $actor = $this->currentActor();
        abort_unless($actor, 403);
        $this->authorizeForUser($actor, 'viewAny', AllowanceVoucher::class);

        $allowedSorts = [
            'voucher_date',
            'number',
            'amount',
            'type',
            'created_at',
            'id',
        ];

        $defaultSortColumn = 'voucher_date';
        $defaultSortDirection = 'desc';

        [$sortBy, $sortDir] = Sort::resolve(
            $request,
            $allowedSorts,
            $defaultSortColumn,
            $defaultSortDirection
        );

        $query = AllowanceVoucher::with(['customer', 'manager'])
            ->orderBy($sortBy, $sortDir);

        if ($sortBy !== 'voucher_date') {
            $query->orderBy('voucher_date', 'desc');
        }

        if ($sortBy !== 'id') {
            $query->orderBy('id', 'desc');
        }

        if (!$actor->can('view-any-allowance-voucher')) {
            $query->where('manager_id', $actor->getKey());
        }

        $vouchers = $query->paginate(20)->appends($request->query());

        return view('accounting.allowance_vouchers.index', [
            'vouchers' => $vouchers,
            'allowedSorts' => $allowedSorts,
            'defaultSortColumn' => $defaultSortColumn,
            'defaultSortDirection' => $defaultSortDirection,
        ]);
    }

    public function create(): View
    {
        $actor = $this->currentActor();
        abort_unless($actor, 403);
        $this->authorizeForUser($actor, 'create', AllowanceVoucher::class);

        return view('accounting.allowance_vouchers.create', [
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'balance']),
            'managers' => Manager::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request, WalletLedgerService $walletLedger): RedirectResponse
    {
        $actor = $this->currentActor();
        abort_unless($actor, 403);
        $this->authorizeForUser($actor, 'create', AllowanceVoucher::class);

        $validated = $request->validate([
            'number' => ['required', 'string', 'max:50', 'unique:allowance_vouchers,number'],
            'voucher_date' => ['required', 'date_format:Y-m-d\TH:i'],
            'customer_id' => ['required', 'exists:customers,id'],
            'type' => ['required', Rule::in([AllowanceVoucher::TYPE_INCREASE, AllowanceVoucher::TYPE_DECREASE])],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string'],
        ]);

        $managerId = $this->currentManagerId();
        $customer = Customer::findOrFail($validated['customer_id']);
        $voucherDate = Carbon::createFromFormat('Y-m-d\TH:i', $validated['voucher_date'], config('app.timezone'))
            ->setSecond(0);

        DB::transaction(function () use ($validated, $customer, $voucherDate, $walletLedger, $managerId) {
            /** @var AllowanceVoucher $voucher */
            $voucher = AllowanceVoucher::create([
                'number' => $validated['number'],
                'voucher_date' => $voucherDate,
                'customer_id' => $customer->getKey(),
                'manager_id' => $managerId,
                'type' => $validated['type'],
                'amount' => $validated['amount'],
                'description' => $validated['description'] ?? null,
            ]);

            $description = match ($validated['type']) {
                AllowanceVoucher::TYPE_INCREASE => __('سند سماح له رقم :number', ['number' => $voucher->number]),
                AllowanceVoucher::TYPE_DECREASE => __('سند سماح عليه رقم :number', ['number' => $voucher->number]),
            };

            $transaction = $validated['type'] === AllowanceVoucher::TYPE_INCREASE
                ? $walletLedger->recordCustomerDebit($customer, (float) $validated['amount'], $voucher, $description, $voucherDate)
                : $walletLedger->recordCustomerCredit($customer, (float) $validated['amount'], $voucher, $description, $voucherDate);

            $voucher->forceFill(['customer_transaction_id' => $transaction->getKey()])->save();
        });

        return redirect()
            ->route('admin.finance.allowance-vouchers.index')
            ->with('status', __('تم تسجيل سند السماح بنجاح.'));
    }

    protected function currentActor()
    {
        if (Auth::guard('admin')->check()) {
            return Auth::guard('admin')->user();
        }

        return Auth::user();
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
