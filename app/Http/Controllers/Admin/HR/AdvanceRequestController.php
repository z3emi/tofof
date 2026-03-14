<?php

namespace App\Http\Controllers\Admin\HR;

use App\Http\Controllers\Controller;
use App\Models\AdvanceRequest;
use App\Models\Manager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class AdvanceRequestController extends Controller
{
    public function __construct()
    {
        $permissionMiddleware = \Spatie\Permission\Middleware\PermissionMiddleware::class;
        $this->middleware($permissionMiddleware . ':approve_advance_requests');
    }

    public function index(Request $request): View
    {
        $query = AdvanceRequest::with(['employee', 'manager', 'reviewer'])->latest();

        if ($request->filled('status') && in_array($request->status, [AdvanceRequest::STATUS_PENDING, AdvanceRequest::STATUS_APPROVED, AdvanceRequest::STATUS_REJECTED, AdvanceRequest::STATUS_SETTLED], true)) {
            $query->where('status', $request->status);
        }

        $advanceRequests = $query->paginate(20)->withQueryString();

        return view('admin.hr.advance_requests.index', [
            'requests' => $advanceRequests,
            'status' => $request->input('status'),
        ]);
    }

    public function create(): View
    {
        return view('admin.hr.advance_requests.create', [
            'employees' => Manager::orderBy('name')->get(),
            'managers' => Manager::orderBy('name')->get(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateAdvanceRequest($request);

        $employee = Manager::findOrFail($data['employee_id']);
        $status = $data['status'];

        $reviewedBy = null;
        $reviewedAt = null;

        if ($status !== AdvanceRequest::STATUS_PENDING) {
            $reviewedBy = Auth::guard('admin')->id();
            $reviewedAt = now();
        }

        AdvanceRequest::create([
            'employee_id' => $employee->id,
            'manager_id' => $data['manager_id'] ?? $employee->manager_id,
            'amount' => $data['amount'],
            'repayment_date' => $data['repayment_date'],
            'reason' => $data['reason'] ?? null,
            'status' => $status,
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => $reviewedAt,
        ]);

        return Redirect::route('admin.hr.advance-requests.index')
            ->with('status', __('تم تسجيل طلب السلفة يدوياً.'));
    }

    public function edit(AdvanceRequest $advanceRequest): View
    {
        return view('admin.hr.advance_requests.edit', [
            'advanceRequest' => $advanceRequest,
            'employees' => Manager::orderBy('name')->get(),
            'managers' => Manager::orderBy('name')->get(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function update(Request $request, AdvanceRequest $advanceRequest)
    {
        if ($request->input('update_mode') === 'status_only') {
            $data = $request->validate([
                'update_mode' => ['nullable', 'in:status_only'],
                'status' => ['required', 'in:' . implode(',', [AdvanceRequest::STATUS_APPROVED, AdvanceRequest::STATUS_REJECTED, AdvanceRequest::STATUS_SETTLED])],
            ]);

            $advanceRequest->update([
                'status' => $data['status'],
                'reviewed_by' => Auth::guard('admin')->id(),
                'reviewed_at' => now(),
            ]);

            return Redirect::route('admin.hr.advance-requests.index')
                ->with('status', __('تم تحديث حالة طلب السلفة.'));
        }

        $data = $this->validateAdvanceRequest($request);

        $employee = Manager::findOrFail($data['employee_id']);
        $status = $data['status'];

        $reviewedBy = null;
        $reviewedAt = null;

        if ($status !== AdvanceRequest::STATUS_PENDING) {
            $reviewedBy = Auth::guard('admin')->id();
            $reviewedAt = now();
        }

        $advanceRequest->update([
            'employee_id' => $employee->id,
            'manager_id' => $data['manager_id'] ?? $employee->manager_id,
            'amount' => $data['amount'],
            'repayment_date' => $data['repayment_date'],
            'reason' => $data['reason'] ?? null,
            'status' => $status,
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => $reviewedAt,
        ]);

        return Redirect::route('admin.hr.advance-requests.index')
            ->with('status', __('تم تحديث بيانات طلب السلفة.'));
    }

    public function destroy(AdvanceRequest $advanceRequest)
    {
        $advanceRequest->delete();

        return Redirect::route('admin.hr.advance-requests.index')
            ->with('status', __('تم حذف طلب السلفة.'));
    }

    private function statusOptions(): array
    {
        return [
            AdvanceRequest::STATUS_PENDING => 'قيد المراجعة',
            AdvanceRequest::STATUS_APPROVED => 'موافق عليه',
            AdvanceRequest::STATUS_REJECTED => 'مرفوض',
            AdvanceRequest::STATUS_SETTLED => 'مسدد',
        ];
    }

    private function validateAdvanceRequest(Request $request): array
    {
        return $request->validate([
            'employee_id' => ['required', 'exists:managers,id'],
            'manager_id' => ['nullable', 'exists:managers,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'repayment_date' => ['required', 'date'],
            'reason' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:' . implode(',', array_keys($this->statusOptions()))],
        ]);
    }
}
