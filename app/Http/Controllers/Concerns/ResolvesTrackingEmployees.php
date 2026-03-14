<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

trait ResolvesTrackingEmployees
{
    protected function trackingEmployeeTable(): string
    {
        return (string) config('tracking.employee_table', 'managers');
    }

    protected function trackingEmployeeModel(): string
    {
        return (string) config('tracking.employee_model', \App\Models\Manager::class);
    }

    protected function trackingEmployeeQuery(): Builder
    {
        $modelClass = $this->trackingEmployeeModel();

        /** @var class-string<\Illuminate\Database\Eloquent\Model> $modelClass */
        return $modelClass::query();
    }

    protected function trackingEmployeeColumns(): array
    {
        $table = $this->trackingEmployeeTable();
        $columns = ['id', 'name'];

        foreach (['department', 'position', 'job_title', 'title', 'manager_id', 'tracking_pin_hash'] as $column) {
            if (Schema::hasColumn($table, $column)) {
                $columns[] = $column;
            }
        }

        return array_values(array_unique($columns));
    }

    protected function trackingEmployeesList(): EloquentCollection
    {
        $columns = $this->trackingEmployeeColumns();
        $employees = $this->trackingEmployeeQuery()
            ->orderBy('name')
            ->get($columns);

        $this->loadAdditionalRelations($employees);

        return $this->decorateEmployees($employees);
    }

    protected function trackingEmployeesByIds(Collection $ids): EloquentCollection
    {
        if ($ids->isEmpty()) {
            return new EloquentCollection();
        }

        $columns = $this->trackingEmployeeColumns();
        $employees = $this->trackingEmployeeQuery()
            ->whereIn('id', $ids)
            ->get($columns);

        $this->loadAdditionalRelations($employees);

        return $this->decorateEmployees($employees)->keyBy('id');
    }

    protected function findTrackingEmployeeOrFail(int $employeeId)
    {
        $columns = $this->trackingEmployeeColumns();
        $employee = $this->trackingEmployeeQuery()
            ->select($columns)
            ->findOrFail($employeeId);

        $collection = new EloquentCollection([$employee]);
        $this->loadAdditionalRelations($collection);

        return $this->decorateEmployees($collection)->first();
    }

    protected function decorateEmployees(EloquentCollection $employees): EloquentCollection
    {
        return $employees->map(function ($employee) {
            $employee->setAttribute('department', $this->resolveTrackingDepartment($employee));

            return $employee;
        });
    }

    protected function resolveTrackingDepartment($employee): ?string
    {
        foreach (['department', 'position', 'job_title', 'title'] as $attribute) {
            $value = data_get($employee, $attribute);
            if (is_string($value) && trim($value) !== '') {
                return $value;
            }
        }

        if (method_exists($employee, 'department') && $employee->relationLoaded('department')) {
            $dept = $employee->getRelation('department');
            $label = data_get($dept, 'name') ?? data_get($dept, 'title');
            if ($label) {
                return $label;
            }
        }

        if (method_exists($employee, 'manager')) {
            $manager = $employee->relationLoaded('manager')
                ? $employee->getRelation('manager')
                : null;

            if (! $manager && method_exists($employee, 'manager')) {
                $manager = $employee->manager;
            }

            if ($manager && isset($manager->name)) {
                return $manager->name;
            }
        }

        return null;
    }

    protected function loadAdditionalRelations(EloquentCollection $employees): void
    {
        if ($employees->isEmpty()) {
            return;
        }

        $first = $employees->first();

        if ($first && method_exists($first, 'department')) {
            $employees->load('department');
        }

        if ($first && method_exists($first, 'manager')) {
            $employees->load('manager');
        }
    }
}
