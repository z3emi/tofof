<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\PayrollItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeePortalController extends Controller
{
    public function leaveIndex(Request $request): View
    {
        $employee = $request->user()->manager;

        abort_unless($employee, 403);

        $requests = $employee->leaveRequests()->latest()->paginate(10);

        return view('frontend.profile.hr.leave_requests', compact('requests'));
    }

    public function leaveStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'leave_type' => ['required', 'string', 'max:255'],
            'days' => ['required', 'integer', 'min:1'],
            'start_date' => ['nullable', 'date'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $employee = $request->user()->manager;

        abort_unless($employee, 403);

        $employee->leaveRequests()->create([
            'employee_id' => $employee->id,
            'manager_id' => $employee->manager_id,
            'leave_type' => $data['leave_type'],
            'days' => $data['days'],
            'start_date' => $data['start_date'] ?? null,
            'reason' => $data['reason'] ?? null,
        ]);

        return redirect()->route('hr.leave-requests.index')->with('status', __('تم إرسال طلب الإجازة بنجاح.'));
    }

    public function advanceIndex(Request $request): View
    {
        $employee = $request->user()->manager;

        abort_unless($employee, 403);

        $requests = $employee->advanceRequests()->latest()->paginate(10);

        return view('frontend.profile.hr.advance_requests', compact('requests'));
    }

    public function advanceStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'repayment_date' => ['required', 'date', 'after_or_equal:today'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $employee = $request->user()->manager;

        abort_unless($employee, 403);

        $employee->advanceRequests()->create([
            'employee_id' => $employee->id,
            'manager_id' => $employee->manager_id,
            'amount' => $data['amount'],
            'repayment_date' => $data['repayment_date'],
            'reason' => $data['reason'] ?? null,
        ]);

        return redirect()->route('hr.advance-requests.index')->with('status', __('تم إرسال طلب السلفة بنجاح.'));
    }

    public function payslips(Request $request): View
    {
        if (!$request->user()->can('view_own_payslip')) {
            abort(403);
        }

        $employee = $request->user()->manager;

        abort_unless($employee, 403);

        $payslips = PayrollItem::with('payroll')
            ->where('employee_id', $employee->id)
            ->latest('created_at')
            ->paginate(6);

        return view('frontend.profile.hr.payslips', compact('payslips'));
    }
}
