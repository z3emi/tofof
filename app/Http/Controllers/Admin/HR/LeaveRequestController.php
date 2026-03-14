<?php

namespace App\Http\Controllers\Admin\HR;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\Manager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    public function __construct()
    {
        $permissionMiddleware = \Spatie\Permission\Middleware\PermissionMiddleware::class;
        $this->middleware($permissionMiddleware . ':approve_leave_requests');
    }

    public function index(Request $request): View
    {
        $query = LeaveRequest::with(['employee', 'manager', 'reviewer'])->latest();

        if ($request->filled('status') && in_array($request->status, [LeaveRequest::STATUS_PENDING, LeaveRequest::STATUS_APPROVED, LeaveRequest::STATUS_REJECTED], true)) {
            $query->where('status', $request->status);
        }

        $leaveRequests = $query->paginate(20)->withQueryString();

        return view('admin.hr.leave_requests.index', [
            'requests' => $leaveRequests,
            'status' => $request->input('status'),
        ]);
    }

    public function create(): View
    {
        return view('admin.hr.leave_requests.create', [
            'employees' => Manager::orderBy('name')->get(),
            'managers' => Manager::orderBy('name')->get(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateLeaveRequest($request);

        $employee = Manager::findOrFail($data['employee_id']);
        $status = $data['status'];

        $reviewedBy = null;
        $reviewedAt = null;

        if ($status !== LeaveRequest::STATUS_PENDING) {
            $reviewedBy = Auth::guard('admin')->id();
            $reviewedAt = now();
        }

        LeaveRequest::create([
            'employee_id' => $employee->id,
            'manager_id' => $data['manager_id'] ?? $employee->manager_id,
            'leave_type' => $data['leave_type'],
            'days' => $data['days'],
            'start_date' => $data['start_date'] ?? null,
            'reason' => $data['reason'] ?? null,
            'status' => $status,
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => $reviewedAt,
        ]);

        return Redirect::route('admin.hr.leave-requests.index')
            ->with('status', __('تم إضافة طلب الإجازة يدوياً.'));
    }

    public function edit(LeaveRequest $leaveRequest): View
    {
        return view('admin.hr.leave_requests.edit', [
            'leaveRequest' => $leaveRequest,
            'employees' => Manager::orderBy('name')->get(),
            'managers' => Manager::orderBy('name')->get(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        if ($request->input('update_mode') === 'status_only') {
            $data = $request->validate([
                'update_mode' => ['nullable', 'in:status_only'],
                'status' => ['required', 'in:' . implode(',', [LeaveRequest::STATUS_APPROVED, LeaveRequest::STATUS_REJECTED])],
            ]);

            $leaveRequest->update([
                'status' => $data['status'],
                'reviewed_by' => Auth::guard('admin')->id(),
                'reviewed_at' => now(),
            ]);

            return Redirect::route('admin.hr.leave-requests.index')
                ->with('status', __('تم تحديث حالة طلب الإجازة.'));
        }

        $data = $this->validateLeaveRequest($request);

        $employee = Manager::findOrFail($data['employee_id']);
        $status = $data['status'];

        $reviewedBy = null;
        $reviewedAt = null;

        if ($status !== LeaveRequest::STATUS_PENDING) {
            $reviewedBy = Auth::guard('admin')->id();
            $reviewedAt = now();
        }

        $leaveRequest->update([
            'employee_id' => $employee->id,
            'manager_id' => $data['manager_id'] ?? $employee->manager_id,
            'leave_type' => $data['leave_type'],
            'days' => $data['days'],
            'start_date' => $data['start_date'] ?? null,
            'reason' => $data['reason'] ?? null,
            'status' => $status,
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => $reviewedAt,
        ]);

        return Redirect::route('admin.hr.leave-requests.index')
            ->with('status', __('تم تحديث بيانات طلب الإجازة.'));
    }

    public function destroy(LeaveRequest $leaveRequest)
    {
        $leaveRequest->delete();

        return Redirect::route('admin.hr.leave-requests.index')
            ->with('status', __('تم حذف طلب الإجازة.'));
    }

    private function statusOptions(): array
    {
        return [
            LeaveRequest::STATUS_PENDING => 'قيد المراجعة',
            LeaveRequest::STATUS_APPROVED => 'موافق عليه',
            LeaveRequest::STATUS_REJECTED => 'مرفوض',
        ];
    }

    private function validateLeaveRequest(Request $request): array
    {
        return $request->validate([
            'employee_id' => ['required', 'exists:managers,id'],
            'manager_id' => ['nullable', 'exists:managers,id'],
            'leave_type' => ['required', 'string', 'max:255'],
            'days' => ['required', 'integer', 'min:1'],
            'start_date' => ['nullable', 'date'],
            'reason' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:' . implode(',', array_keys($this->statusOptions()))],
        ]);
    }
}
