<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashBox;
use App\Models\Customer;
use App\Models\Manager;
use App\Models\Order;
use App\Models\ReceiptVoucher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class ReceiptVoucherController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user('admin');
        abort_unless($user && ($user->can('view-any-receipt-voucher') || $user->can('view-own-receipt-voucher')), 403);

        $perPageOptions = [10, 15, 20, 25, 50, 100];
        $perPage = $request->integer('per_page', 20);
        if (!in_array($perPage, $perPageOptions, true)) {
            $perPage = 20;
        }

        $searchTerm = $request->string('search')->trim();
        $transactionChannel = $request->string('transaction_channel')->trim();
        $orderScope = $request->input('order_scope');

        $dateFromInput = $request->input('date_from');
        $dateToInput = $request->input('date_to');

        $dateFrom = null;
        if (is_string($dateFromInput) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFromInput)) {
            $parsed = Carbon::createFromFormat('Y-m-d', $dateFromInput);
            if ($parsed !== false) {
                $dateFrom = $parsed;
            }
        }

        $dateTo = null;
        if (is_string($dateToInput) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateToInput)) {
            $parsed = Carbon::createFromFormat('Y-m-d', $dateToInput);
            if ($parsed !== false) {
                $dateTo = $parsed;
            }
        }

        $normalizeAmount = static function ($value): ?float {
            if (is_null($value)) {
                return null;
            }

            if (!is_string($value) && !is_numeric($value)) {
                return null;
            }

            $normalized = Str::of((string) $value)->replace([',', ' '], '')->toString();

            return is_numeric($normalized) ? (float) $normalized : null;
        };

        $minAmount = $normalizeAmount($request->input('min_amount'));
        $maxAmount = $normalizeAmount($request->input('max_amount'));

        $vouchers = ReceiptVoucher::query()
            ->with(['customer', 'manager', 'cashBox'])
            ->when(!$user->can('view-any-receipt-voucher'), fn ($query) => $query->where('manager_id', $user->id))
            ->when($searchTerm->isNotEmpty(), function ($query) use ($searchTerm) {
                $term = $searchTerm->toString();
                $normalizedNumeric = Str::of($term)->replace([',', ' '], '')->__toString();

                $query->where(function ($innerQuery) use ($term, $normalizedNumeric) {
                    $innerQuery->where('number', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%")
                        ->orWhere('transaction_channel', 'like', "%{$term}%")
                        ->orWhereHas('customer', fn ($customerQuery) => $customerQuery->where('name', 'like', "%{$term}%"))
                        ->orWhereHas('manager', fn ($managerQuery) => $managerQuery->where('name', 'like', "%{$term}%"));

                    if (is_numeric($normalizedNumeric)) {
                        $innerQuery->orWhere('amount', (float) $normalizedNumeric);
                    }
                });
            })
            ->when($request->filled('customer_id'), fn ($query) => $query->where('customer_id', $request->integer('customer_id')))
            ->when($request->filled('manager_id'), fn ($query) => $query->where('manager_id', $request->integer('manager_id')))
            ->when($request->filled('cash_box_id'), fn ($query) => $query->where('cash_box_id', $request->integer('cash_box_id')))
            ->when($transactionChannel->isNotEmpty(), fn ($query) => $query->where('transaction_channel', $transactionChannel->toString()))
            ->when($dateFrom instanceof Carbon, fn ($query) => $query->whereDate('voucher_date', '>=', $dateFrom))
            ->when($dateTo instanceof Carbon, fn ($query) => $query->whereDate('voucher_date', '<=', $dateTo))
            ->when($minAmount !== null, fn ($query) => $query->where('amount', '>=', $minAmount))
            ->when($maxAmount !== null, fn ($query) => $query->where('amount', '<=', $maxAmount))
            ->when($orderScope === 'with_order', fn ($query) => $query->whereNotNull('order_id'))
            ->when($orderScope === 'without_order', fn ($query) => $query->whereNull('order_id'))
            ->when($request->boolean('with_notes'), fn ($query) => $query->whereNotNull('description')->where('description', '!=', ''))
            ->orderByDesc('voucher_date')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $managerQuery = Manager::orderBy('name');
        if (!$user->can('view-any-receipt-voucher')) {
            $managerQuery->where('id', $user->id);
        }

        return view('admin.receipt_vouchers.index', [
            'vouchers' => $vouchers,
            'customers' => Customer::orderBy('name')->get(['id', 'name']),
            'managers' => $managerQuery->get(['id', 'name']),
            'cashBoxes' => CashBox::orderBy('name')->get(['id', 'name']),
            'transactionChannels' => ReceiptVoucher::query()
                ->select('transaction_channel')
                ->whereNotNull('transaction_channel')
                ->where('transaction_channel', '!=', '')
                ->distinct()
                ->orderBy('transaction_channel')
                ->pluck('transaction_channel'),
            'perPage' => $perPage,
            'perPageOptions' => $perPageOptions,
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user('admin');
        abort_unless($user && $user->can('create-receipt-voucher'), 403);

        $managerQuery = Manager::orderBy('name');
        if (!$user->can('view-any-receipt-voucher')) {
            $managerQuery->where('id', $user->id);
        }

        return view('admin.receipt_vouchers.create', [
            'customers' => Customer::orderBy('name')->get(['id', 'name']),
            'managers' => $managerQuery->get(['id', 'name']),
            'cashBoxes' => CashBox::orderBy('name')->get(['id', 'name', 'balance']),
            'orders' => Order::latest()->limit(20)->get(['id', 'customer_id', 'total_amount']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user('admin');
        abort_unless($user && $user->can('create-receipt-voucher'), 403);

        $data = $request->validate([
            'number' => ['nullable', 'string', 'max:255', 'unique:receipt_vouchers,number'],
            'voucher_date' => ['required', 'date'],
            'customer_id' => ['required', 'exists:customers,id'],
            'manager_id' => ['nullable', 'exists:managers,id'],
            'cash_box_id' => ['nullable', 'exists:cash_boxes,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:1000'],
            'transaction_channel' => ['nullable', 'string', 'max:255'],
            'order_id' => ['nullable', 'exists:orders,id'],
        ]);

        if (empty($data['manager_id']) && empty($data['cash_box_id'])) {
            return back()->withInput()->withErrors([
                'manager_id' => __('يجب اختيار المندوب أو الصندوق لاستلام المبلغ.'),
            ]);
        }

        if (!empty($data['manager_id']) && !empty($data['cash_box_id'])) {
            return back()->withInput()->withErrors([
                'cash_box_id' => __('يرجى اختيار المندوب أو الصندوق، وليس كليهما.'),
            ]);
        }

        if (!$user->can('view-any-receipt-voucher')) {
            $data['manager_id'] = $user->id;
            unset($data['cash_box_id']);
        }

        $data['number'] = $data['number'] ?? sprintf('RV-%06d', (ReceiptVoucher::max('id') ?? 0) + 1);

        $voucher = ReceiptVoucher::create($data);

        return redirect()
            ->route('admin.finance.receipt-vouchers.index')
            ->with('status', __('تم إنشاء سند القبض #:number بنجاح.', ['number' => $voucher->number]));
    }

    public function edit(Request $request, ReceiptVoucher $receiptVoucher): View
    {
        $user = $request->user('admin');
        abort_unless($user, 403);

        $this->authorize('update', $receiptVoucher);

        $managerQuery = Manager::orderBy('name');
        if (!$user->can('view-any-receipt-voucher')) {
            $managerQuery->where('id', $user->id);
        }

        $orders = Order::latest()->limit(20)->get(['id', 'customer_id', 'total_amount']);
        if ($receiptVoucher->order_id && !$orders->firstWhere('id', $receiptVoucher->order_id)) {
            $orders->push(
                Order::whereKey($receiptVoucher->order_id)->first(['id', 'customer_id', 'total_amount'])
            );
        }

        return view('admin.receipt_vouchers.edit', [
            'voucher' => $receiptVoucher,
            'customers' => Customer::orderBy('name')->get(['id', 'name']),
            'managers' => $managerQuery->get(['id', 'name']),
            'cashBoxes' => CashBox::orderBy('name')->get(['id', 'name', 'balance']),
            'orders' => $orders->filter(),
        ]);
    }

    public function update(Request $request, ReceiptVoucher $receiptVoucher): RedirectResponse
    {
        $user = $request->user('admin');
        abort_unless($user, 403);

        $this->authorize('update', $receiptVoucher);

        $data = $request->validate([
            'number' => ['nullable', 'string', 'max:255', Rule::unique('receipt_vouchers', 'number')->ignore($receiptVoucher->getKey())],
            'voucher_date' => ['required', 'date'],
            'customer_id' => ['required', 'exists:customers,id'],
            'manager_id' => ['nullable', 'exists:managers,id'],
            'cash_box_id' => ['nullable', 'exists:cash_boxes,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:1000'],
            'transaction_channel' => ['nullable', 'string', 'max:255'],
            'order_id' => ['nullable', 'exists:orders,id'],
        ]);

        if (empty($data['manager_id']) && empty($data['cash_box_id'])) {
            return back()->withInput()->withErrors([
                'manager_id' => __('يجب اختيار المندوب أو الصندوق لاستلام المبلغ.'),
            ]);
        }

        if (!empty($data['manager_id']) && !empty($data['cash_box_id'])) {
            return back()->withInput()->withErrors([
                'cash_box_id' => __('يرجى اختيار المندوب أو الصندوق، وليس كليهما.'),
            ]);
        }

        if (!$user->can('view-any-receipt-voucher')) {
            $data['manager_id'] = $user->id;
            unset($data['cash_box_id']);
        }

        $data['number'] = $data['number'] ?: $receiptVoucher->number;

        $receiptVoucher->update($data);

        return redirect()
            ->route('admin.finance.receipt-vouchers.index')
            ->with('status', __('تم تحديث سند القبض #:number بنجاح.', ['number' => $receiptVoucher->number]));
    }

    public function destroy(Request $request, ReceiptVoucher $receiptVoucher): RedirectResponse
    {
        $user = $request->user('admin');
        abort_unless($user, 403);

        $this->authorize('delete', $receiptVoucher);

        $receiptVoucher->delete();

        return redirect()
            ->route('admin.finance.receipt-vouchers.index')
            ->with('status', __('تم حذف سند القبض #:number.', ['number' => $receiptVoucher->number]));
    }
}
