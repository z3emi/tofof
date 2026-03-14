<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashBox;
use App\Models\CashBoxTransaction;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CashBoxController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth('admin')->user()?->can('view-cash-boxes'), 403);

        $month = (int) $request->input('month', Carbon::now()->month);
        $year = (int) $request->input('year', Carbon::now()->year);

        $from = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $to = (clone $from)->endOfMonth();

        $cashBoxes = CashBox::query()
            ->withSum(['transactions as month_debits' => function ($query) use ($from, $to) {
                $query->where('type', CashBoxTransaction::TYPE_DEBIT)
                    ->whereBetween('transaction_date', [$from, $to]);
            }], 'amount')
            ->withSum(['transactions as month_credits' => function ($query) use ($from, $to) {
                $query->where('type', CashBoxTransaction::TYPE_CREDIT)
                    ->whereBetween('transaction_date', [$from, $to]);
            }], 'amount')
            ->with(['transactions' => function ($query) {
                $query->orderByDesc('transaction_date')->orderByDesc('id')->limit(5);
            }])
            ->orderBy('is_primary', 'desc')
            ->orderBy('name')
            ->get();

        $totals = [
            'balance' => (float) CashBox::sum('balance'),
            'credits' => (float) CashBoxTransaction::whereBetween('transaction_date', [$from, $to])
                ->where('type', CashBoxTransaction::TYPE_CREDIT)
                ->sum('amount'),
            'debits' => (float) CashBoxTransaction::whereBetween('transaction_date', [$from, $to])
                ->where('type', CashBoxTransaction::TYPE_DEBIT)
                ->sum('amount'),
        ];

        return view('admin.cash_boxes.index', [
            'cashBoxes' => $cashBoxes,
            'totals' => $totals,
            'month' => $month,
            'year' => $year,
            'types' => CashBox::types(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth('admin')->user()?->can('create-cash-boxes'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:32', 'unique:cash_boxes,code'],
            'type' => ['required', Rule::in(array_keys(CashBox::types()))],
            'currency' => ['nullable', 'string', 'max:3'],
            'balance' => ['nullable', 'numeric', 'min:0'],
            'is_primary' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['code'] = $data['code'] ?: $this->generateCode();
        $data['currency'] = strtoupper($data['currency'] ?? 'IQD');
        $data['balance'] = $data['balance'] ?? 0;
        $data['is_primary'] = !empty($data['is_primary']);

        if ($data['is_primary']) {
            CashBox::query()->update(['is_primary' => false]);
        }

        CashBox::create($data);

        return redirect()->route('admin.finance.cash-boxes.index')->with('status', __('تم إنشاء الصندوق بنجاح.'));
    }

    public function update(Request $request, CashBox $cashBox): RedirectResponse
    {
        abort_unless(auth('admin')->user()?->can('create-cash-boxes'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:32', Rule::unique('cash_boxes', 'code')->ignore($cashBox->id)],
            'type' => ['required', Rule::in(array_keys(CashBox::types()))],
            'currency' => ['nullable', 'string', 'max:3'],
            'notes' => ['nullable', 'string'],
            'is_primary' => ['nullable', 'boolean'],
        ]);

        $data['currency'] = strtoupper($data['currency'] ?? $cashBox->currency ?? 'IQD');
        $data['is_primary'] = !empty($data['is_primary']);

        if ($data['is_primary']) {
            CashBox::query()->where('id', '!=', $cashBox->id)->update(['is_primary' => false]);
        }

        $cashBox->update($data);

        return redirect()->route('admin.finance.cash-boxes.index')->with('status', __('تم تحديث الصندوق بنجاح.'));
    }

    protected function generateCode(): string
    {
        $nextNumber = (int) CashBox::max('id') + 1;

        do {
            $code = sprintf('CB-%04d', $nextNumber++);
        } while (CashBox::where('code', $code)->exists());

        return $code;
    }
}
