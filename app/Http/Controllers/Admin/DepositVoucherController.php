<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashBox;
use App\Models\DepositVoucher;
use App\Models\Manager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DepositVoucherController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user('admin');
        abort_unless($user && $user->can('view-deposit-vouchers'), 403);

        $perPageOptions = [10, 15, 20, 25, 50, 100];
        $perPage = $request->integer('per_page', 20);
        if (!in_array($perPage, $perPageOptions, true)) {
            $perPage = 20;
        }

        $searchTerm = $request->string('search')->trim();
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
            if ($value === null) {
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

        $vouchers = DepositVoucher::query()
            ->with(['cashBox', 'manager'])
            ->when($searchTerm->isNotEmpty(), function ($query) use ($searchTerm) {
                $term = $searchTerm->toString();
                $normalizedNumeric = Str::of($term)->replace([',', ' '], '')->__toString();

                $query->where(function ($inner) use ($term, $normalizedNumeric) {
                    $inner->where('number', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%")
                        ->orWhereHas('manager', fn ($managerQuery) => $managerQuery->where('name', 'like', "%{$term}%"))
                        ->orWhereHas('cashBox', fn ($cashBoxQuery) => $cashBoxQuery->where('name', 'like', "%{$term}%"));

                    if (is_numeric($normalizedNumeric)) {
                        $inner->orWhere('amount', (float) $normalizedNumeric);
                    }
                });
            })
            ->when($request->filled('manager_id'), fn ($query) => $query->where('manager_id', $request->integer('manager_id')))
            ->when($request->filled('cash_box_id'), fn ($query) => $query->where('cash_box_id', $request->integer('cash_box_id')))
            ->when($dateFrom instanceof Carbon, fn ($query) => $query->whereDate('voucher_date', '>=', $dateFrom))
            ->when($dateTo instanceof Carbon, fn ($query) => $query->whereDate('voucher_date', '<=', $dateTo))
            ->when($minAmount !== null, fn ($query) => $query->where('amount', '>=', $minAmount))
            ->when($maxAmount !== null, fn ($query) => $query->where('amount', '<=', $maxAmount))
            ->when($request->boolean('with_notes'), fn ($query) => $query->whereNotNull('description')->where('description', '!=', ''))
            ->orderByDesc('voucher_date')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.deposit_vouchers.index', [
            'vouchers' => $vouchers,
            'managers' => Manager::orderBy('name')->get(['id', 'name']),
            'cashBoxes' => CashBox::orderBy('name')->get(['id', 'name']),
            'perPage' => $perPage,
            'perPageOptions' => $perPageOptions,
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user('admin');
        abort_unless($user && $user->can('create-deposit-voucher'), 403);

        return view('admin.deposit_vouchers.create', [
            'managers' => Manager::orderBy('name')->get(['id', 'name', 'cash_on_hand']),
            'cashBoxes' => CashBox::orderBy('name')->get(['id', 'name', 'balance']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user('admin');
        abort_unless($user && $user->can('create-deposit-voucher'), 403);

        $data = $request->validate([
            'number' => ['nullable', 'string', 'max:255', 'unique:deposit_vouchers,number'],
            'voucher_date' => ['required', 'date'],
            'manager_id' => ['required', 'exists:managers,id'],
            'cash_box_id' => ['required', 'exists:cash_boxes,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['number'] = $data['number'] ?? sprintf('DV-%06d', (DepositVoucher::max('id') ?? 0) + 1);

        DepositVoucher::create($data);

        return redirect()
            ->route('admin.finance.deposit-vouchers.index')
            ->with('status', __('تم تسجيل سند الإيداع بنجاح.'));
    }

    public function edit(Request $request, DepositVoucher $depositVoucher): View
    {
        $user = $request->user('admin');
        abort_unless($user, 403);

        $this->authorize('update', $depositVoucher);

        return view('admin.deposit_vouchers.edit', [
            'voucher' => $depositVoucher,
            'managers' => Manager::orderBy('name')->get(['id', 'name', 'cash_on_hand']),
            'cashBoxes' => CashBox::orderBy('name')->get(['id', 'name', 'balance']),
        ]);
    }

    public function update(Request $request, DepositVoucher $depositVoucher): RedirectResponse
    {
        $user = $request->user('admin');
        abort_unless($user, 403);

        $this->authorize('update', $depositVoucher);

        $data = $request->validate([
            'number' => ['nullable', 'string', 'max:255', Rule::unique('deposit_vouchers', 'number')->ignore($depositVoucher->getKey())],
            'voucher_date' => ['required', 'date'],
            'manager_id' => ['required', 'exists:managers,id'],
            'cash_box_id' => ['required', 'exists:cash_boxes,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $depositVoucher->update($data);

        return redirect()
            ->route('admin.finance.deposit-vouchers.index')
            ->with('status', __('تم تحديث سند الإيداع بنجاح.'));
    }

    public function destroy(Request $request, DepositVoucher $depositVoucher): RedirectResponse
    {
        $user = $request->user('admin');
        abort_unless($user, 403);

        $this->authorize('delete', $depositVoucher);

        $depositVoucher->delete();

        return redirect()
            ->route('admin.finance.deposit-vouchers.index')
            ->with('status', __('تم حذف سند الإيداع بنجاح.'));
    }
}
