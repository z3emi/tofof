<?php

namespace App\Services\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

class PinAuthenticationService
{
    private ?string $detectedModelClass = null;

    private ?string $detectedTable = null;

    private ?string $detectedColumn = null;

    /**
     * Resolve the employee model class from configuration.
     */
    public function detectEmployeeModel(): string
    {
        if ($this->detectedModelClass) {
            return $this->detectedModelClass;
        }

        $modelClass = config('tracking.employee_model', \App\Models\Manager::class);

        if (! class_exists($modelClass)) {
            throw new RuntimeException('Employee model class not found: '.$modelClass);
        }

        if (! is_subclass_of($modelClass, Model::class)) {
            throw new RuntimeException('Configured employee model must extend '.Model::class);
        }

        $this->detectedModelClass = $modelClass;

        return $modelClass;
    }

    /**
     * Determine the table associated with the employee model.
     */
    public function detectEmployeeTable(): string
    {
        if ($this->detectedTable) {
            return $this->detectedTable;
        }

        $model = $this->resolveModelInstance();
        $table = $model->getTable();

        if (! Schema::hasTable($table)) {
            throw new RuntimeException('Employee table does not exist: '.$table);
        }

        $this->detectedTable = $table;

        return $table;
    }

    /**
     * Determine the column used to store the employee PIN or code.
     */
    public function detectPinColumn(): string
    {
        if ($this->detectedColumn) {
            return $this->detectedColumn;
        }

        $model = $this->resolveModelInstance();

        if (method_exists($model, 'getPinColumnName')) {
            $column = $model->getPinColumnName();
            if (! Schema::hasColumn($model->getTable(), $column)) {
                throw new RuntimeException('Invalid pin column declared on model: '.$column);
            }

            return $this->detectedColumn = $column;
        }

        $columns = Schema::getColumnListing($this->detectEmployeeTable());

        $candidates = [
            'tracking_pin_hash',
            'tracking_pin',
            'pin_hash',
            'pin_code_hash',
            'pin_code',
            'employee_pin',
            'employee_code',
            'access_code',
            'pin',
            'code',
            'password',
        ];

        foreach ($candidates as $candidate) {
            if (in_array($candidate, $columns, true)) {
                $this->detectedColumn = $candidate;

                return $candidate;
            }
        }

        throw new RuntimeException('Unable to detect employee pin column automatically.');
    }

    /**
     * Attempt to authenticate an employee by the provided PIN.
     */
    public function findEmployeeByPin(string $pin): ?Model
    {
        $modelClass = $this->detectEmployeeModel();

        if (method_exists($modelClass, 'findByTrackingPin')) {
            $employee = $modelClass::findByTrackingPin($pin);

            if ($employee && $this->employeeHasPin($employee)) {
                return $employee;
            }

            return null;
        }

        $pinColumn = $this->detectPinColumn();
        $query = $modelClass::query()->whereNotNull($pinColumn);

        $candidate = null;

        if (! $this->isHashedColumn($pinColumn)) {
            $candidate = $query->where($pinColumn, $pin)->first();

            return $candidate ?: null;
        }

        $candidate = $query->where($pinColumn, $pin)->first();

        if ($candidate && $this->verifyPinForModel($candidate, $pin, $pinColumn)) {
            return $candidate;
        }

        foreach ($query->cursor() as $record) {
            if ($this->verifyPinForModel($record, $pin, $pinColumn)) {
                return $record;
            }
        }

        return null;
    }

    /**
     * Create an API token for the authenticated employee if available.
     */
    public function createApiToken(Model $employee): array
    {
        $tokenType = 'Bearer';
        $tokenValue = Str::random(60);

        if ($employee instanceof Authenticatable && method_exists($employee, 'createToken')) {
            try {
                $token = $employee->createToken('pin-login');
                $tokenValue = $token->plainTextToken;
            } catch (RuntimeException $exception) {
                $tokenType = 'plain';
                $tokenValue = Str::random(60);
            }
        }

        return [$tokenValue, $tokenType];
    }

    public function employeeHasPin(Model $employee): bool
    {
        if (method_exists($employee, 'hasTrackingPin')) {
            return (bool) $employee->hasTrackingPin();
        }

        $pinColumn = $this->detectPinColumn();

        return (bool) $employee->getAttribute($pinColumn);
    }

    private function resolveModelInstance(): Model
    {
        $modelClass = $this->detectEmployeeModel();

        return new $modelClass();
    }

    private function verifyPinForModel(Model $employee, string $pin, string $column): bool
    {
        if (method_exists($employee, 'verifyTrackingPin')) {
            return (bool) $employee->verifyTrackingPin($pin);
        }

        $stored = (string) $employee->getAttribute($column);

        if ($stored === '') {
            return false;
        }

        if ($this->isHashedColumn($column)) {
            if (Hash::check($pin, $stored)) {
                return true;
            }

            if (! str_starts_with($stored, '$2y$') && ! str_starts_with($stored, '$argon2')) {
                return hash_equals($stored, $pin);
            }

            return false;
        }

        return hash_equals($stored, $pin);
    }

    private function isHashedColumn(string $column): bool
    {
        return in_array($column, [
            'tracking_pin_hash',
            'pin_hash',
            'pin_code_hash',
            'password',
        ], true);
    }
}
