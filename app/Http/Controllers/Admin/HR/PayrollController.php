<?php

namespace App\Http\Controllers\Admin\HR;

use App\Exports\TableExport;
use App\Http\Controllers\Controller;
use App\Models\Manager;
use App\Models\Payroll;
use App\Models\User;
use App\Services\HR\PayrollService;
use App\Support\Currency;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\Auth;

class PayrollController extends Controller
{
    public function __construct()
    {
        $permissionMiddleware = \Spatie\Permission\Middleware\PermissionMiddleware::class;

        $this->middleware($permissionMiddleware . ':view_payroll')->only(['index', 'show', 'export']);
        $this->middleware($permissionMiddleware . ':export-excel')->only(['export']);
        $this->middleware($permissionMiddleware . ':process_payroll')->only(['create', 'store']);
        $this->middleware($permissionMiddleware . ':revert_payroll')->only(['revert']);
    }

    public function index(): View
    {
        $payrolls = Payroll::withCount('items')->latest('period_start')->paginate(12);

        return view('admin.hr.payroll.index', compact('payrolls'));
    }

    public function create(): View
    {
        $employees = Manager::orderBy('name')->get();

        return view('admin.hr.payroll.create', [
            'employees' => $employees,
            'defaultMonth' => now()->format('Y-m'),
            'currencyOptions' => [
                Currency::IQD => 'الدينار العراقي (IQD)',
                Currency::USD => 'الدولار الأمريكي (USD)',
            ],
            'systemCurrency' => Currency::systemCurrency(),
            'exchangeRate' => Currency::iqdToUsdRate(),
        ]);
    }

    public function store(Request $request, PayrollService $payrollService): RedirectResponse
    {
        $data = $request->validate([
            'period_month' => ['required', 'date_format:Y-m'],
            'notes' => ['nullable', 'string'],
            'currency' => ['required', 'in:IQD,USD'],
            'exchange_rate' => ['nullable', 'numeric', 'gt:0'],
            'employees' => ['required', 'array'],
            'employees.*.base_salary' => ['nullable', 'numeric', 'min:0'],
            'employees.*.allowances' => ['nullable', 'numeric', 'min:0'],
            'employees.*.commissions' => ['nullable', 'numeric', 'min:0'],
            'employees.*.loan_installments' => ['nullable', 'numeric', 'min:0'],
            'employees.*.deductions' => ['nullable', 'numeric', 'min:0'],
        ]);

        $periodStart = Carbon::createFromFormat('Y-m', $data['period_month'])->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();

        $managerId = Auth::guard('admin')->id();
        $currency = $data['currency'];
        $exchangeRate = $data['exchange_rate'] ?? ($currency === Currency::USD ? Currency::iqdToUsdRate() : null);

        $manualEntries = collect($data['employees'])
            ->mapWithKeys(function ($values, $userId) {
                return [
                    (int) $userId => [
                        'base_salary' => isset($values['base_salary']) ? (float) $values['base_salary'] : 0.0,
                        'allowances' => isset($values['allowances']) ? (float) $values['allowances'] : 0.0,
                        'commissions' => isset($values['commissions']) ? (float) $values['commissions'] : 0.0,
                        'loan_installments' => isset($values['loan_installments']) ? (float) $values['loan_installments'] : 0.0,
                        'deductions' => isset($values['deductions']) ? (float) $values['deductions'] : 0.0,
                    ],
                ];
            })
            ->all();

        try {
            $payroll = $payrollService->process(
                $periodStart,
                $periodEnd,
                $managerId,
                $data['notes'] ?? null,
                $manualEntries,
                $currency,
                $exchangeRate
            );

            return redirect()->route('admin.hr.payroll.show', $payroll)
                ->with('status', __('تم تنفيذ مسير الرواتب بنجاح.'));
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors($e->getMessage());
        }
    }

    public function show(Payroll $payroll): View
    {
        $payroll->load(['items.employee', 'journalEntry', 'revertor']);

        return view('admin.hr.payroll.show', compact('payroll'));
    }

    public function revert(Request $request, Payroll $payroll, PayrollService $payrollService): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $payrollService->revert($payroll, Auth::guard('admin')->id(), $data['reason'] ?? null);
        } catch (\Throwable $e) {
            return back()->withErrors($e->getMessage())->withInput();
        }

        return redirect()->route('admin.hr.payroll.show', $payroll)
            ->with('status', __('تم التراجع عن مسير الرواتب بنجاح.'));
    }

    public function export(Payroll $payroll): BinaryFileResponse
    {
        if ($payroll->isReverted()) {
            abort(403, __('لا يمكن تصدير مسير رواتب تم التراجع عنه.'));
        }

        $payroll->load('items.employee');

        $headings = ['الموظف', 'الراتب الأساسي', 'البدلات', 'العمولات', 'أقساط السلف', 'خصومات إضافية', 'صافي الراتب'];
        $rows = $payroll->items->map(function ($item) {
            return [
                $item->employee?->name ?? '—',
                number_format((float) $item->base_salary, 2, '.', ''),
                number_format((float) $item->allowances, 2, '.', ''),
                number_format((float) $item->commissions, 2, '.', ''),
                number_format((float) $item->loan_installments, 2, '.', ''),
                number_format((float) $item->deductions, 2, '.', ''),
                number_format((float) $item->net_salary, 2, '.', ''),
            ];
        })->toArray();

        $periodCode = $payroll->original_period_code ?? $payroll->period_code;
        $fileName = 'payroll_' . $periodCode . '.xlsx';

        return Excel::download(new TableExport($headings, $rows), $fileName);
    }
}
