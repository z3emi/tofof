<?php

namespace App\Http\Controllers\Admin\HR;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Manager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function __construct()
    {
        $permissionMiddleware = \Spatie\Permission\Middleware\PermissionMiddleware::class;

        $this->middleware($permissionMiddleware . ':manage_employee_profiles');
    }

    public function index(Request $request): View
    {
        $query = AttendanceRecord::with(['employee', 'recorder'])->orderByDesc('attendance_date');

        if ($request->filled('employee_id')) {
            $query->where('employee_id', (int) $request->input('employee_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('attendance_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('attendance_date', '<=', $request->input('date_to'));
        }

        $records = $query->paginate(20)->withQueryString();
        $employees = Manager::orderBy('name')->get();
        $statuses = [
            'present' => 'حاضر',
            'absent' => 'غائب',
            'leave' => 'إجازة',
            'late' => 'تأخير',
        ];

        return view('admin.hr.attendance.index', compact('records', 'employees', 'statuses'));
    }

    public function create(): View
    {
        $employees = Manager::orderBy('name')->get();
        $statuses = [
            'present' => 'حاضر',
            'absent' => 'غائب',
            'leave' => 'إجازة',
            'late' => 'تأخير',
        ];

        return view('admin.hr.attendance.create', compact('employees', 'statuses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateAttendance($request);

        $managerId = Auth::guard('admin')->id();

        AttendanceRecord::create([
            'employee_id' => $data['employee_id'],
            'recorded_by' => $managerId,
            'attendance_date' => $data['attendance_date'],
            'check_in_at' => $data['check_in_at'] ?? null,
            'check_out_at' => $data['check_out_at'] ?? null,
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('admin.hr.attendance.index')->with('status', __('تم تسجيل الحضور بنجاح.'));
    }

    public function edit(AttendanceRecord $attendance): View
    {
        $employees = Manager::orderBy('name')->get();
        $statuses = [
            'present' => 'حاضر',
            'absent' => 'غائب',
            'leave' => 'إجازة',
            'late' => 'تأخير',
        ];

        return view('admin.hr.attendance.edit', [
            'attendance' => $attendance,
            'employees' => $employees,
            'statuses' => $statuses,
        ]);
    }

    public function update(Request $request, AttendanceRecord $attendance): RedirectResponse
    {
        $data = $this->validateAttendance($request);

        $attendance->update([
            'employee_id' => $data['employee_id'],
            'attendance_date' => $data['attendance_date'],
            'check_in_at' => $data['check_in_at'] ?? null,
            'check_out_at' => $data['check_out_at'] ?? null,
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
            'recorded_by' => Auth::guard('admin')->id(),
        ]);

        return redirect()->route('admin.hr.attendance.index')->with('status', __('تم تحديث سجل الحضور.'));
    }

    public function destroy(AttendanceRecord $attendance): RedirectResponse
    {
        $attendance->delete();

        return redirect()->route('admin.hr.attendance.index')->with('status', __('تم حذف سجل الحضور.'));
    }

    private function validateAttendance(Request $request): array
    {
        return $request->validate([
            'employee_id' => ['required', 'exists:managers,id'],
            'attendance_date' => ['required', 'date'],
            'check_in_at' => ['nullable', 'date_format:H:i'],
            'check_out_at' => ['nullable', 'date_format:H:i', 'after_or_equal:check_in_at'],
            'status' => ['required', 'in:present,absent,leave,late'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
