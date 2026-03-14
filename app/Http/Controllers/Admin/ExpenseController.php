<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ExpenseController extends Controller
{
    /**
     * Apply permission middleware to protect controller actions.
     */
    public function __construct()
    {
        $permissionMiddleware = \Spatie\Permission\Middleware\PermissionMiddleware::class;

        // Please ensure these permissions exist in your seeder.
        $this->middleware($permissionMiddleware . ':view-expenses', ['only' => ['index']]);
        $this->middleware($permissionMiddleware . ':create-expenses', ['only' => ['create', 'store']]);
        $this->middleware($permissionMiddleware . ':edit-expenses', ['only' => ['edit', 'update']]);
        $this->middleware($permissionMiddleware . ':delete-expenses', ['only' => ['destroy']]);
    }

    /**
     * Display expenses with filtering by month and year.
     */
    public function index(Request $request)
    {
        // Determine the time period (month and year)
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('m'));

        // Build the base query for the selected period
        $expensesQuery = Expense::whereYear('expense_date', $year)->whereMonth('expense_date', $month);
        
        // **Optimization**: Clone the query to get the total before pagination.
        $totalExpenses = $expensesQuery->clone()->sum('amount');
        
        // Fetch the paginated expenses while preserving filters
        $expenses = $expensesQuery->latest()->paginate(15)->withQueryString();
        
        return view('admin.expenses.index', compact(
            'expenses',
            'totalExpenses',
            'year',
            'month'
        ));
    }

    /**
     * Show the form for creating a new expense.
     */
    public function create()
    {
        return view('admin.expenses.create');
    }

    /**
     * Store a new expense.
     */
    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        Expense::create($request->all());

        return redirect()->route('admin.expenses.index')->with('success', 'تم تسجيل المصروف بنجاح.');
    }

    /**
     * Show the form for editing an expense.
     */
    public function edit(Expense $expense)
    {
        return view('admin.expenses.edit', compact('expense'));
    }

    /**
     * Update an existing expense.
     */
    public function update(Request $request, Expense $expense)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $expense->update($request->all());

        return redirect()->route('admin.expenses.index')->with('success', 'تم تحديث المصروف بنجاح.');
    }

    /**
     * Delete an expense.
     */
    public function destroy(Expense $expense)
    {
        $expense->delete();
        return redirect()->route('admin.expenses.index')->with('success', 'تم حذف المصروف بنجاح.');
    }
}
