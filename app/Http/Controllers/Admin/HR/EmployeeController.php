<?php

namespace App\Http\Controllers\Admin\HR;

use App\Http\Controllers\Controller;
use App\Models\Manager;
use App\Support\Currency;
use App\Support\Sort;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Traits\HandlesImageUploads;

class EmployeeController extends Controller
{
    use HandlesImageUploads;
    public function __construct()
    {
        $permissionMiddleware = \Spatie\Permission\Middleware\PermissionMiddleware::class;

        $this->middleware($permissionMiddleware . ':manage_employee_profiles');
    }

    public function index(Request $request): View
    {
        $query = Manager::query()->with('manager');

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('manager_id')) {
            if ($request->input('manager_id') === 'unassigned') {
                $query->whereNull('manager_id');
            } else {
                $query->where('manager_id', (int) $request->input('manager_id'));
            }
        }

        $allowedSorts = ['id', 'name', 'phone_number', 'manager_id', 'base_salary', 'allowances', 'commission_rate', 'created_at'];
        $defaultSortColumn = 'created_at';
        $defaultSortDirection = 'desc';

        [$sortBy, $sortDir] = Sort::resolve($request, $allowedSorts, $defaultSortColumn, $defaultSortDirection);

        $query->orderBy($sortBy, $sortDir);

        $perPage = (int) $request->input('per_page', 15);
        if ($perPage < 5) {
            $perPage = 5;
        } elseif ($perPage > 100) {
            $perPage = 100;
        }

        $employees = $query->paginate($perPage)->withQueryString();

        $managerOptions = Manager::orderBy('name')->pluck('name', 'id');

        return view('admin.hr.employees.index', [
            'employees' => $employees,
            'search' => $request->input('search'),
            'allowedSorts' => $allowedSorts,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
            'defaultSortColumn' => $defaultSortColumn,
            'defaultSortDirection' => $defaultSortDirection,
            'perPage' => $perPage,
            'managerFilter' => $request->input('manager_id'),
            'managerOptions' => $managerOptions,
        ]);
    }

    public function create(): View
    {
        $managers = Manager::orderBy('name')->get();
        $salaryCurrency = Currency::IQD;
        $exchangeRate = Currency::iqdToUsdRate();

        return view('admin.hr.employees.create', [
            'managers' => $managers,
            'salaryCurrency' => $salaryCurrency,
            'salaryCurrencyOptions' => [
                Currency::IQD => 'الدينار العراقي (IQD)',
                Currency::USD => 'الدولار الأمريكي (USD)',
            ],
            'exchangeRate' => $exchangeRate,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:managers,email'],
            'phone_number' => ['required', 'string', 'max:30', 'unique:managers,phone_number'],
            'secondary_phone_number' => ['nullable', 'string', 'max:30'],
            'nationality' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:500'],
            'base_salary' => ['nullable', 'numeric', 'min:0'],
            'allowances' => ['nullable', 'numeric', 'min:0'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'bank_account_details' => ['nullable', 'string', 'max:1000'],
            'manager_id' => ['nullable', 'exists:managers,id'],
            'salary_currency' => ['required', 'in:IQD,USD'],
            'photo' => ['nullable', 'image'],
            'housing_card' => ['nullable', 'image'],
            'nationality_card' => ['nullable', 'image'],
            'tracking_pin' => ['nullable', 'regex:/^\d{4,8}$/'],
        ]);

        $salaryCurrency = $data['salary_currency'];

        $pin = $data['tracking_pin'] ?? null;
        unset($data['tracking_pin']);

        $baseSalary = isset($data['base_salary'])
            ? Currency::convertToSystem($data['base_salary'], $salaryCurrency)
            : 0;
        $allowances = isset($data['allowances'])
            ? Currency::convertToSystem($data['allowances'], $salaryCurrency)
            : 0;

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone_number' => $data['phone_number'],
            'secondary_phone_number' => $data['secondary_phone_number'] ?? null,
            'nationality' => $data['nationality'] ?? null,
            'address' => $data['address'] ?? null,
            'base_salary' => $baseSalary,
            'allowances' => $allowances,
            'commission_rate' => $data['commission_rate'] ?? 0,
            'bank_account_details' => $data['bank_account_details'] ?? null,
            'manager_id' => $data['manager_id'] ?? null,
            'salary_currency' => $salaryCurrency,
            'password' => Hash::make(Str::random(40)),
        ];

        if ($request->hasFile('photo')) {
            $payload['profile_photo_path'] = $this->uploadAndConvertImage($request->file('photo'), 'employees/photos');
        }

        if ($request->hasFile('housing_card')) {
            $payload['housing_card_path'] = $this->uploadAndConvertImage($request->file('housing_card'), 'employees/housing-cards');
        }

        if ($request->hasFile('nationality_card')) {
            $payload['nationality_card_path'] = $this->uploadAndConvertImage($request->file('nationality_card'), 'employees/nationality-cards');
        }

        $employee = Manager::create($payload);

        $this->applyTrackingPin($employee, $pin);

        return Redirect::route('admin.hr.employees.edit', $employee)
            ->with('status', __('تم إضافة الموظف بنجاح.'));
    }

    public function edit(Manager $employee): View
    {
        $managers = Manager::orderBy('name')->get();
        $salaryCurrency = $employee->salary_currency ?? Currency::IQD;
        $exchangeRate = Currency::iqdToUsdRate();

        $displayBaseSalary = Currency::convertFromSystem($employee->base_salary, $salaryCurrency);
        $displayAllowances = Currency::convertFromSystem($employee->allowances, $salaryCurrency);

        return view('admin.hr.employees.edit', [
            'employee' => $employee,
            'managers' => $managers,
            'salaryCurrency' => $salaryCurrency,
            'salaryCurrencyOptions' => [
                Currency::IQD => 'الدينار العراقي (IQD)',
                Currency::USD => 'الدولار الأمريكي (USD)',
            ],
            'exchangeRate' => $exchangeRate,
            'displayBaseSalary' => $displayBaseSalary,
            'displayAllowances' => $displayAllowances,
            'missingFields' => $employee->missingProfileFields(),
        ]);
    }

    public function update(Request $request, Manager $employee)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('managers', 'email')->ignore($employee->id)],
            'phone_number' => ['required', 'string', 'max:30', Rule::unique('managers', 'phone_number')->ignore($employee->id)],
            'secondary_phone_number' => ['nullable', 'string', 'max:30'],
            'nationality' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:500'],
            'base_salary' => ['nullable', 'numeric', 'min:0'],
            'allowances' => ['nullable', 'numeric', 'min:0'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'bank_account_details' => ['nullable', 'string', 'max:1000'],
            'manager_id' => ['nullable', 'exists:managers,id'],
            'salary_currency' => ['required', 'in:IQD,USD'],
            'photo' => ['nullable', 'image'],
            'housing_card' => ['nullable', 'image'],
            'nationality_card' => ['nullable', 'image'],
            'tracking_pin' => ['nullable', 'regex:/^\d{4,8}$/'],
            'reset_tracking_pin' => ['sometimes', 'boolean'],
        ]);

        $salaryCurrency = $data['salary_currency'];

        $pin = $data['tracking_pin'] ?? null;
        $resetPin = (bool) ($data['reset_tracking_pin'] ?? false);
        unset($data['tracking_pin'], $data['reset_tracking_pin']);

        $baseSalary = isset($data['base_salary'])
            ? Currency::convertToSystem($data['base_salary'], $salaryCurrency)
            : 0;
        $allowances = isset($data['allowances'])
            ? Currency::convertToSystem($data['allowances'], $salaryCurrency)
            : 0;

        $updatePayload = [
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone_number' => $data['phone_number'],
            'secondary_phone_number' => $data['secondary_phone_number'] ?? null,
            'nationality' => $data['nationality'] ?? null,
            'address' => $data['address'] ?? null,
            'base_salary' => $baseSalary,
            'allowances' => $allowances,
            'commission_rate' => $data['commission_rate'] ?? 0,
            'bank_account_details' => $data['bank_account_details'] ?? null,
            'manager_id' => $data['manager_id'] ?? null,
            'salary_currency' => $salaryCurrency,
        ];

        if ($request->hasFile('photo')) {
            if ($employee->profile_photo_path) {
                Storage::disk('public')->delete($employee->profile_photo_path);
            }

            $updatePayload['profile_photo_path'] = $this->uploadAndConvertImage($request->file('photo'), 'employees/photos');
        }

        if ($request->hasFile('housing_card')) {
            if ($employee->housing_card_path) {
                Storage::disk('public')->delete($employee->housing_card_path);
            }

            $updatePayload['housing_card_path'] = $this->uploadAndConvertImage($request->file('housing_card'), 'employees/housing-cards');
        }

        if ($request->hasFile('nationality_card')) {
            if ($employee->nationality_card_path) {
                Storage::disk('public')->delete($employee->nationality_card_path);
            }

            $updatePayload['nationality_card_path'] = $this->uploadAndConvertImage($request->file('nationality_card'), 'employees/nationality-cards');
        }

        $employee->update($updatePayload);

        $this->applyTrackingPin($employee, $pin, $resetPin);

        return Redirect::route('admin.hr.employees.edit', $employee)
            ->with('status', __('تم تحديث ملف الموظف بنجاح.'));
    }

    private function applyTrackingPin(Manager $employee, ?string $pin, bool $reset = false): void
    {
        if ($reset) {
            $employee->setTrackingPin(null);
            $employee->save();

            return;
        }

        if ($pin === null || $pin === '') {
            return;
        }

        $employee->setTrackingPin($pin);
        $employee->save();
    }
}
