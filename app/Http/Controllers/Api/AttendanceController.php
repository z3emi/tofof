<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class AttendanceController extends Controller
{
    public function checkin(Request $request): JsonResponse
    {
        if (! Schema::hasTable('hr_attendance_records')) {
            return response()->json([
                'message' => __('لم يتم تهيئة جدول الحضور حتى الآن.'),
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $validated = $request->validate([
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $employee = $request->user();
        $today = Carbon::now()->toDateString();

        $record = AttendanceRecord::query()
            ->where('employee_id', $employee->id)
            ->whereDate('attendance_date', $today)
            ->latest('id')
            ->first();

        if ($record && $record->check_in_at && ! $record->check_out_at) {
            return response()->json([
                'message' => __('لا يمكن تسجيل الدخول مرتين في نفس اليوم قبل تسجيل الخروج.'),
            ], Response::HTTP_CONFLICT);
        }

        if (! $record || ($record->check_in_at && $record->check_out_at)) {
            $record = new AttendanceRecord();
            $record->employee_id = $employee->id;
            $record->attendance_date = $today;
            $record->recorded_by = $employee->id;
        }

        $record->check_in_at = Carbon::now()->format('H:i:s');
        $record->check_in_latitude = $validated['latitude'] ?? null;
        $record->check_in_longitude = $validated['longitude'] ?? null;
        $record->check_out_at = null;
        $record->check_out_latitude = null;
        $record->check_out_longitude = null;
        $record->status = 'present';
        $record->save();

        return response()->json([
            'message' => __('تم تسجيل الدخول بنجاح.'),
            'record' => [
                'id' => $record->id,
                'attendance_date' => $record->attendance_date?->toDateString() ?? $today,
                'check_in_at' => $record->check_in_at,
            ],
        ], Response::HTTP_CREATED);
    }

    public function checkout(Request $request): JsonResponse
    {
        if (! Schema::hasTable('hr_attendance_records')) {
            return response()->json([
                'message' => __('لم يتم تهيئة جدول الحضور حتى الآن.'),
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $validated = $request->validate([
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $employee = $request->user();
        $today = Carbon::now()->toDateString();

        $record = AttendanceRecord::query()
            ->where('employee_id', $employee->id)
            ->whereDate('attendance_date', $today)
            ->whereNotNull('check_in_at')
            ->whereNull('check_out_at')
            ->latest('id')
            ->first();

        if (! $record) {
            return response()->json([
                'message' => __('لا يوجد تسجيل دخول مفتوح لهذا اليوم.'),
            ], Response::HTTP_CONFLICT);
        }

        $record->check_out_at = Carbon::now()->format('H:i:s');
        $record->check_out_latitude = $validated['latitude'] ?? null;
        $record->check_out_longitude = $validated['longitude'] ?? null;
        $record->status = 'completed';
        $record->save();

        return response()->json([
            'message' => __('تم تسجيل الخروج بنجاح.'),
            'record' => [
                'id' => $record->id,
                'attendance_date' => $record->attendance_date?->toDateString() ?? $today,
                'check_in_at' => $record->check_in_at,
                'check_out_at' => $record->check_out_at,
            ],
        ]);
    }

    public function status(Request $request): JsonResponse
    {
        if (! Schema::hasTable('hr_attendance_records')) {
            return response()->json([
                'status' => 'unavailable',
                'message' => __('لم يتم تهيئة جدول الحضور حتى الآن.'),
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $employee = $request->user();
        $today = Carbon::now()->toDateString();

        $record = AttendanceRecord::query()
            ->where('employee_id', $employee->id)
            ->whereDate('attendance_date', $today)
            ->latest('id')
            ->first();

        $checkedIn = $record && $record->check_in_at && ! $record->check_out_at;

        return response()->json([
            'status' => $checkedIn ? 'checked_in' : 'checked_out',
            'attendance_date' => $record?->attendance_date?->toDateString() ?? $today,
            'last_check_in_at' => $record?->check_in_at,
            'last_check_out_at' => $record?->check_out_at,
        ]);
    }
}
