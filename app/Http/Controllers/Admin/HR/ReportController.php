<?php

namespace App\Http\Controllers\Admin\HR;

use App\Exports\TableExport;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SalesCommission;
use App\Models\Manager;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function __construct()
    {
        $permissionMiddleware = \Spatie\Permission\Middleware\PermissionMiddleware::class;
        $this->middleware($permissionMiddleware . ':view_payroll');
    }

    public function salesPerformance(Request $request)
    {
        $from = $request->date('from') ?? Carbon::now()->startOfMonth();
        $to = $request->date('to') ?? Carbon::now()->endOfMonth();

        $salesRows = Order::select('salesperson_id',
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(total_amount) as total_sales'))
            ->whereNotNull('salesperson_id')
            ->where('status', 'delivered')
            ->whereBetween('updated_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->groupBy('salesperson_id')
            ->get()
            ->keyBy('salesperson_id');

        $commissionRows = SalesCommission::select('employee_id', DB::raw('SUM(amount) as total_commissions'))
            ->whereIn('status', [SalesCommission::STATUS_ACCRUED, SalesCommission::STATUS_PAID])
            ->whereBetween('earned_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->groupBy('employee_id')
            ->get()
            ->keyBy('employee_id');

        $managerIds = $salesRows->keys()->merge($commissionRows->keys())->unique();
        $employees = Manager::whereIn('id', $managerIds)->get()->keyBy('id');

        $report = $managerIds->map(function ($managerId) use ($salesRows, $commissionRows, $employees) {
            $employee = $employees->get($managerId);
            $sales = $salesRows->get($managerId);
            $commissions = $commissionRows->get($managerId);

            return [
                'employee' => $employee,
                'orders_count' => $sales->orders_count ?? 0,
                'total_sales' => (float) ($sales->total_sales ?? 0),
                'total_commissions' => (float) ($commissions->total_commissions ?? 0),
            ];
        });

        if ($request->boolean('export')) {
            abort_unless(optional(auth('admin')->user())->can('export-excel'), 403);

            $headings = ['الموظف', 'عدد الطلبات', 'إجمالي المبيعات', 'إجمالي العمولات'];
            $rows = $report->map(function ($row) {
                return [
                    $row['employee']->name ?? __('غير معروف'),
                    $row['orders_count'],
                    number_format((float) $row['total_sales'], 2, '.', ''),
                    number_format((float) $row['total_commissions'], 2, '.', ''),
                ];
            })->toArray();

            $fileName = 'sales_performance_' . $from->format('Ymd') . '_' . $to->format('Ymd') . '.xlsx';

            return Excel::download(new TableExport($headings, $rows), $fileName);
        }

        return view('admin.hr.reports.sales_performance', [
            'rows' => $report,
            'filters' => [
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
            ],
        ]);
    }
}
