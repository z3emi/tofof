<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesTrackingEmployees;
use App\Http\Controllers\Controller;
use App\Models\TrackingLog;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class TrackingController extends Controller
{
    use ResolvesTrackingEmployees;

    public function track(Request $request): JsonResponse
    {
        if (! Schema::hasTable('employee_tracking_logs') || ! Schema::hasTable($this->trackingEmployeeTable())) {
            return response()->json([
                'message' => __('لم يتم تهيئة جداول التتبع والموظفين بعد. يرجى تشغيل ترحيلات قاعدة البيانات ذات الصلة.'),
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $validated = $request->validate([
            'gps_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'gps_long' => ['nullable', 'numeric', 'between:-180,180'],
            'address' => ['nullable', 'string', 'max:255'],
            'speed' => ['nullable', 'numeric', 'min:0'],
            'battery_level' => ['nullable', 'numeric', 'between:0,100'],
            'action' => ['nullable', 'in:checkin,checkout,move'],
            'device_id' => ['nullable', 'string', 'max:50'],
            'recorded_at' => ['nullable', 'date'],
        ]);

        $employee = $request->user();
        $employeeId = $employee?->getKey();

        if (! $employeeId) {
            return response()->json([
                'message' => __('تعذر تحديد هوية الموظف.'),
            ], Response::HTTP_UNAUTHORIZED);
        }

        $log = TrackingLog::create([
            'employee_id' => $employeeId,
            'gps_lat' => $validated['gps_lat'] ?? null,
            'gps_long' => $validated['gps_long'] ?? null,
            'address' => $validated['address'] ?? null,
            'action' => $validated['action'] ?? 'move',
            'speed' => $validated['speed'] ?? null,
            'battery_level' => $validated['battery_level'] ?? null,
            'device_id' => $validated['device_id'] ?? null,
            'recorded_at' => isset($validated['recorded_at'])
                ? Carbon::parse($validated['recorded_at'])
                : now(),
        ]);

        TrackingLog::where('recorded_at', '<', now()->subDays(90))->delete();

        return response()->json([
            'message' => 'تم تسجيل موقع الحركة بنجاح ✅',
            'id' => $log->id,
        ], Response::HTTP_CREATED);
    }

    public function liveLocations(): JsonResponse
    {
        if (! Schema::hasTable('employee_tracking_logs')) {
            return response()->json([
                'message' => __('لم يتم تهيئة جدول سجلات التتبع بعد.'),
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $latestLogs = TrackingLog::query()
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

        $employees = $this->loadEmployees($latestLogs);

        $response = $latestLogs->map(function (TrackingLog $log) use ($employees) {
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
        })->values();

        return response()->json($response);
    }

    public function trackingHistory(Request $request, int $employeeId): JsonResponse
    {
        $request->validate([
            'date' => ['nullable', 'date'],
        ]);

        if (! Schema::hasTable('employee_tracking_logs') || ! Schema::hasTable($this->trackingEmployeeTable())) {
            return response()->json([
                'message' => __('لم يتم تهيئة جداول التتبع والموظفين بعد. يرجى تشغيل ترحيلات قاعدة البيانات ذات الصلة.'),
            ], Response::HTTP_SERVICE_UNAVAILABLE);
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

        $response = $logs->map(function (TrackingLog $log) {
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
        })->values();

        return response()->json([
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'department' => $employee->department,
            ],
            'date' => $day->toDateString(),
            'logs' => $response,
        ]);
    }

    private function loadEmployees(Collection $logs): Collection
    {
        if (! Schema::hasTable($this->trackingEmployeeTable())) {
            return collect();
        }

        $ids = $logs->pluck('employee_id')->filter()->unique();

        if ($ids->isEmpty()) {
            return collect();
        }

        return $this->trackingEmployeesByIds($ids);
    }
}
