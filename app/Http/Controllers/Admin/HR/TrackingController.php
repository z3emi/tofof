<?php

namespace App\Http\Controllers\Admin\HR;

use App\Http\Controllers\Concerns\ResolvesTrackingEmployees;
use App\Http\Controllers\Controller;
use App\Models\TrackingLog;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class TrackingController extends Controller
{
    use ResolvesTrackingEmployees;

    public function index(): View
    {
        $hasEmployeesTable = Schema::hasTable($this->trackingEmployeeTable());
        $hasTrackingLogsTable = Schema::hasTable('employee_tracking_logs');

        return view('admin.tracking.index', [
            'employeesTableMissing' => ! $hasEmployeesTable,
            'trackingTableMissing' => ! $hasTrackingLogsTable,
            'trackingTablesReady' => $hasEmployeesTable && $hasTrackingLogsTable,
            'employeeTableName' => $this->trackingEmployeeTable(),
            'trackingTableName' => 'employee_tracking_logs',
        ]);
    }

    public function history(): View
    {
        $hasEmployeesTable = Schema::hasTable($this->trackingEmployeeTable());
        $hasTrackingLogsTable = Schema::hasTable('employee_tracking_logs');

        $employees = $hasEmployeesTable
            ? $this->trackingEmployeesList()
            : collect();

        return view('admin.tracking.history', [
            'employees' => $employees,
            'employeesTableMissing' => ! $hasEmployeesTable,
            'trackingTableMissing' => ! $hasTrackingLogsTable,
            'employeeTableName' => $this->trackingEmployeeTable(),
            'trackingTableName' => 'employee_tracking_logs',
        ]);
    }

    public function liveData(): JsonResponse
    {
        if (! Schema::hasTable('employee_tracking_logs')) {
            return response()->json([]);
        }

        $logs = TrackingLog::query()
            ->select('employee_tracking_logs.*')
            ->joinSub(
                TrackingLog::selectRaw('employee_id, MAX(recorded_at) as recorded_at, MAX(id) as id')
                    ->groupBy('employee_id'),
                'latest',
                function ($join) {
                    $join->on('employee_tracking_logs.employee_id', '=', 'latest.employee_id')
                        ->on('employee_tracking_logs.id', '=', 'latest.id');
                }
            )
            ->orderByDesc('employee_tracking_logs.recorded_at')
            ->get();

        $employees = $this->loadEmployeesForLogs($logs);

        return response()->json($logs->map(function (TrackingLog $log) use ($employees) {
            $employee = $employees->get($log->employee_id);

            return [
                'employee_id' => $log->employee_id,
                'name' => $employee->name ?? null,
                'department' => $employee->department ?? null,
                'gps_lat' => $log->gps_lat,
                'gps_long' => $log->gps_long,
                'action' => $log->action,
                'speed' => $log->speed,
                'battery_level' => $log->battery_level,
                'device_id' => $log->device_id,
                'recorded_at' => optional($log->recorded_at)->format('Y-m-d H:i:s'),
            ];
        })->values());
    }

    public function historyData(Request $request, int $employeeId): JsonResponse
    {
        $request->validate([
            'date' => ['nullable', 'date'],
        ]);

        if (! Schema::hasTable('employee_tracking_logs') || ! Schema::hasTable($this->trackingEmployeeTable())) {
            return response()->json([
                'message' => __('لم يتم تهيئة جداول التتبع والموظفين بعد. يرجى تشغيل ترحيلات قاعدة البيانات ذات الصلة.'),
            ], 503);
        }

        $employee = $this->findTrackingEmployeeOrFail($employeeId);

        $date = $request->query('date');
        $day = $date ? Carbon::parse($date) : now();

        $logs = TrackingLog::query()
            ->where('employee_id', $employee->id)
            ->whereBetween('recorded_at', [
                $day->copy()->startOfDay(),
                $day->copy()->endOfDay(),
            ])
            ->orderBy('recorded_at')
            ->get();

        return response()->json([
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'department' => $employee->department,
            ],
            'date' => $day->toDateString(),
            'logs' => $logs->map(function (TrackingLog $log) {
                return [
                    'gps_lat' => $log->gps_lat,
                    'gps_long' => $log->gps_long,
                    'address' => $log->address,
                    'action' => $log->action,
                    'speed' => $log->speed,
                    'battery_level' => $log->battery_level,
                    'device_id' => $log->device_id,
                    'recorded_at' => optional($log->recorded_at)->format('Y-m-d H:i:s'),
                ];
            })->values(),
        ]);
    }

    private function loadEmployeesForLogs(Collection $logs): Collection
    {
        if (! Schema::hasTable($this->trackingEmployeeTable())) {
            return collect();
        }

        $ids = $logs
            ->pluck('employee_id')
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        return $this->trackingEmployeesByIds($ids);
    }
}
