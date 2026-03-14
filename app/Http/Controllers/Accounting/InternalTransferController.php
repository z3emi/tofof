<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\CashAccount;
use App\Models\InternalTransfer;
use App\Models\JournalEntry;
use App\Models\Manager;
use App\Support\Currency;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Middleware\PermissionMiddleware;

class InternalTransferController extends Controller
{
    public function __construct()
    {
        $permissionMiddleware = PermissionMiddleware::class;

        $this->middleware($permissionMiddleware . ':access-accounting');
        $this->middleware($permissionMiddleware . ':view-any-internal-transfer|view-own-internal-transfer')->only(['index']);
        $this->middleware($permissionMiddleware . ':create-internal-transfer')->only(['create', 'store']);
    }

    public function index(): View
    {
        $actor = $this->currentActor();

        if (!$actor->can('view-any-internal-transfer') && !$actor->can('view-own-internal-transfer')) {
            abort(403);
        }

        $transfersQuery = InternalTransfer::with(['creator', 'journalEntry', 'manager'])
            ->latest('transfer_date');

        if (!$actor->can('view-any-internal-transfer')) {
            $transfersQuery->where(function ($query) use ($actor) {
                $query->where('manager_id', $actor->getKey())
                    ->orWhere('created_by', $actor->getKey());
            });
        }

        $transfers = $transfersQuery->paginate(20);

        $collection = $transfers->getCollection();
        $collection->loadMorph('source', [
            CashAccount::class => ['account'],
            Manager::class => ['salesRepresentativeAccount'],
        ]);
        $collection->loadMorph('destination', [
            CashAccount::class => ['account'],
            Manager::class => ['salesRepresentativeAccount'],
        ]);

        return view('accounting.internal_transfers.index', compact('transfers'));
    }

    public function create(): View
    {
        $this->authorize('create', InternalTransfer::class);

        return view('accounting.internal_transfers.create', [
            'cashAccounts' => CashAccount::orderBy('name')->get(),
            'salesReps' => Manager::permission('sales-rep')
                ->whereNotNull('sales_representative_account_id')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', InternalTransfer::class);

        $validated = $request->validate([
            'transfer_date' => ['required', 'date'],
            'source_type' => ['required', Rule::in(['cash_account', 'sales_rep'])],
            'source_id' => ['required', 'integer'],
            'destination_type' => ['required', Rule::in(['cash_account', 'sales_rep'])],
            'destination_id' => ['required', 'integer'],
            'currency_code' => ['required', Rule::in(['IQD', 'USD'])],
            'currency_amount' => ['required', 'numeric', 'min:0.01'],
            'exchange_rate' => [
                Rule::requiredIf(fn () => strtoupper((string) $request->input('currency_code')) === 'USD'),
                'nullable',
                'numeric',
                'min:0.0001',
            ],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validated['source_type'] === $validated['destination_type']
            && (int) $validated['source_id'] === (int) $validated['destination_id']) {
            throw ValidationException::withMessages([
                'destination_id' => __('لا يمكن التحويل إلى نفس الجهة المختارة.'),
            ]);
        }

        $currencyCode = Currency::normalize($validated['currency_code']);
        $exchangeRate = $currencyCode === Currency::USD
            ? (float) $validated['exchange_rate']
            : (Currency::systemCurrency() === Currency::USD ? Currency::iqdToUsdRate() : 1.0);

        $currencyAmount = Currency::roundForCurrency($validated['currency_amount'], $currencyCode);
        $systemAmount = Currency::convert($currencyAmount, $currencyCode, Currency::systemCurrency(), $exchangeRate);
        $systemAmount = Currency::roundForCurrency($systemAmount, Currency::systemCurrency());

        [$sourceModel, $sourceAccountId] = $this->resolveParticipant(
            $validated['source_type'],
            (int) $validated['source_id'],
            $currencyCode,
            'source_id'
        );

        [$destinationModel, $destinationAccountId] = $this->resolveParticipant(
            $validated['destination_type'],
            (int) $validated['destination_id'],
            $currencyCode,
            'destination_id'
        );

        $managerId = $this->currentManagerId();
        $reference = $this->generateReference();

        DB::transaction(function () use (
            $reference,
            $validated,
            $currencyCode,
            $currencyAmount,
            $exchangeRate,
            $systemAmount,
            $sourceModel,
            $sourceAccountId,
            $destinationModel,
            $destinationAccountId,
            $managerId
        ) {
            $responsibleManagerId = $sourceModel instanceof Manager
                ? $sourceModel->getKey()
                : ($destinationModel instanceof Manager ? $destinationModel->getKey() : null);

            $entry = JournalEntry::create([
                'reference' => $reference,
                'entry_date' => $validated['transfer_date'],
                'description' => __('تحويل داخلي من :from إلى :to', [
                    'from' => $this->participantName($validated['source_type'], $sourceModel),
                    'to' => $this->participantName($validated['destination_type'], $destinationModel),
                ]),
                'manager_id' => $responsibleManagerId ?? $managerId,
                'reference_type' => InternalTransfer::class,
            ]);

            $entry->lines()->createMany([
                [
                    'account_id' => $destinationAccountId,
                    'manager_id' => $destinationModel instanceof Manager ? $destinationModel->getKey() : null,
                    'description' => __('تحويل داخلي - جهة مستلمة'),
                    'debit' => $systemAmount,
                    'credit' => 0,
                    'currency_code' => $currencyCode,
                    'currency_debit' => $currencyAmount,
                    'currency_credit' => 0,
                    'exchange_rate' => $exchangeRate,
                ],
                [
                    'account_id' => $sourceAccountId,
                    'manager_id' => $sourceModel instanceof Manager ? $sourceModel->getKey() : null,
                    'description' => __('تحويل داخلي - جهة محولة'),
                    'debit' => 0,
                    'credit' => $systemAmount,
                    'currency_code' => $currencyCode,
                    'currency_debit' => 0,
                    'currency_credit' => $currencyAmount,
                    'exchange_rate' => $exchangeRate,
                ],
            ]);

            $transfer = InternalTransfer::create([
                'reference' => $reference,
                'transfer_date' => $validated['transfer_date'],
                'source_type' => $sourceModel::class,
                'source_id' => $sourceModel->getKey(),
                'destination_type' => $destinationModel::class,
                'destination_id' => $destinationModel->getKey(),
                'currency_code' => $currencyCode,
                'currency_amount' => $currencyAmount,
                'exchange_rate' => $exchangeRate,
                'system_amount' => $systemAmount,
                'notes' => $validated['notes'] ?? null,
                'manager_id' => $responsibleManagerId,
                'journal_entry_id' => $entry->id,
                'created_by' => $managerId,
            ]);

            $entry->update(['reference_id' => $transfer->id]);
        });

        return redirect()
            ->route('admin.accounting.internal-transfers.index')
            ->with('status', __('تم تسجيل التحويل الداخلي بنجاح.'));
    }

    protected function resolveParticipant(string $type, int $id, string $currencyCode, string $field): array
    {
        if ($type === 'cash_account') {
            $account = CashAccount::findOrFail($id);

            if ($account->currency_code !== $currencyCode) {
                throw ValidationException::withMessages([
                    'currency_code' => __('عملة التحويل يجب أن تطابق عملة القاصة المحددة.'),
                ]);
            }

            if (!$account->account_id) {
                throw ValidationException::withMessages([
                    $field => __('يجب ربط الحساب المحاسبي بالقاصة المختارة.'),
                ]);
            }

            return [$account, (int) $account->account_id];
        }

        $manager = Manager::permission('sales-rep')
            ->whereNotNull('sales_representative_account_id')
            ->findOrFail($id);

        if (!$manager->sales_representative_account_id) {
            throw ValidationException::withMessages([
                $field => __('يجب ربط حساب محاسبي بالمندوب المختار.'),
            ]);
        }

        return [$manager, (int) $manager->sales_representative_account_id];
    }

    protected function generateReference(): string
    {
        do {
            $reference = 'TRF-' . now()->format('Ymd') . '-' . Str::upper(Str::random(4));
        } while (InternalTransfer::where('reference', $reference)->exists());

        return $reference;
    }

    protected function participantName(string $type, $model): string
    {
        if ($type === 'cash_account' && $model instanceof CashAccount) {
            return $model->name;
        }

        if ($type === 'sales_rep' && $model instanceof Manager) {
            return $model->name;
        }

        return __('جهة غير معروفة');
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
