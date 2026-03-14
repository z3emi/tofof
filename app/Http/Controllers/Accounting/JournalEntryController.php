<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Middleware\PermissionMiddleware;

class JournalEntryController extends Controller
{
    public function __construct()
    {
        $permissionMiddleware = PermissionMiddleware::class;

        $this->middleware($permissionMiddleware . ':access-accounting');
        $this->middleware($permissionMiddleware . ':view-accounting-journal-entries')->only(['index']);
        $this->middleware($permissionMiddleware . ':create-accounting-journal-entries')->only(['create', 'store']);
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $entriesQuery = JournalEntry::with(['lines.account', 'manager'])->latest('entry_date');

        if ($search !== '') {
            $escaped = addcslashes($search, '\\%_');
            $likePattern = "%{$escaped}%";

            $entriesQuery->where(function ($query) use ($search, $likePattern) {
                $query->where('reference', 'like', $likePattern)
                    ->orWhere('description', 'like', $likePattern);

                if (is_numeric($search)) {
                    $query->orWhere('id', (int) $search);
                }
            });
        }

        $entries = $entriesQuery->paginate(15)->withQueryString();

        return view('accounting.journal_entries.index', [
            'entries' => $entries,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        $accounts = Account::orderBy('code')->get();

        return view('accounting.journal_entries.create', compact('accounts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'reference' => ['nullable', 'string', 'max:255'],
            'entry_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.account_id' => ['required', 'exists:accounts,id'],
            'lines.*.description' => ['nullable', 'string'],
            'lines.*.debit' => ['required_without:lines.*.credit', 'numeric', 'min:0'],
            'lines.*.credit' => ['required_without:lines.*.debit', 'numeric', 'min:0'],
            'lines.*.currency_code' => ['required', Rule::in(['IQD', 'USD'])],
            'lines.*.exchange_rate' => ['nullable', 'numeric', 'min:0.0001'],
        ]);

        $preparedLines = collect($validated['lines'])->map(function (array $line) {
            $currencyCode = $line['currency_code'];
            $exchangeRate = $currencyCode === 'USD'
                ? (float) ($line['exchange_rate'] ?? 0)
                : 1.0;

            if ($currencyCode === 'USD' && $exchangeRate <= 0) {
                throw ValidationException::withMessages([
                    'lines' => __('يجب تحديد سعر الصرف للعمليات بالدولار الأمريكي.'),
                ]);
            }

            $currencyDebit = (float) ($line['debit'] ?? 0);
            $currencyCredit = (float) ($line['credit'] ?? 0);

            $debitBase = round($currencyDebit * $exchangeRate, 2);
            $creditBase = round($currencyCredit * $exchangeRate, 2);

            return [
                'account_id' => $line['account_id'],
                'description' => $line['description'] ?? null,
                'currency_code' => $currencyCode,
                'currency_debit' => $currencyDebit,
                'currency_credit' => $currencyCredit,
                'exchange_rate' => $exchangeRate,
                'debit' => $debitBase,
                'credit' => $creditBase,
            ];
        });

        $totalDebit = $preparedLines->sum('debit');
        $totalCredit = $preparedLines->sum('credit');

        if (bccomp($totalDebit, $totalCredit, 2) !== 0) {
            return back()->withInput()->withErrors(__('يجب أن يتساوى مجموع المدين والدائن.'));
        }

        $managerId = $this->currentManagerId();

        DB::transaction(function () use ($validated, $managerId, $preparedLines) {
            $entry = JournalEntry::create([
                'reference' => $validated['reference'] ?? null,
                'entry_date' => $validated['entry_date'],
                'description' => $validated['description'] ?? null,
                'manager_id' => $managerId,
            ]);

            foreach ($preparedLines as $line) {
                $entry->lines()->create($line);
            }
        });

        return redirect()->route('admin.accounting.journal-entries.index')->with('status', __('تم حفظ القيد بنجاح'));
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
