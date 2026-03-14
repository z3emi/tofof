<?php

namespace App\Services\HR;

use App\Models\Manager;
use App\Models\Payroll;
use App\Support\Currency;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PayrollService
{
    /**
     * @param  array<int, array<string, float>>  $manualEntries
     */
    public function process(Carbon $periodStart, Carbon $periodEnd, ?int $processedBy = null, ?string $notes = null, array $manualEntries = [], string $currency = Currency::IQD, ?float $exchangeRate = null): Payroll
    {
        return DB::transaction(function () use ($periodStart, $periodEnd, $processedBy, $notes, $manualEntries, $currency, $exchangeRate) {
            $periodCode = $periodStart->format('Y-m');

            if (Payroll::where('period_code', $periodCode)->whereNull('reverted_at')->exists()) {
                throw new RuntimeException(__('تم تنفيذ مسير الرواتب لهذا الشهر مسبقاً.'));
            }

            $manualEntries = collect($manualEntries)
                ->mapWithKeys(function ($values, $employeeId) {
                    return [
                        (int) $employeeId => [
                            'base_salary' => (float) ($values['base_salary'] ?? 0),
                            'allowances' => (float) ($values['allowances'] ?? 0),
                            'commissions' => (float) ($values['commissions'] ?? 0),
                            'loan_installments' => (float) ($values['loan_installments'] ?? 0),
                            'deductions' => (float) ($values['deductions'] ?? 0),
                        ],
                    ];
                })
                ->filter(function ($values) {
                    $gross = $values['base_salary'] + $values['allowances'] + $values['commissions'];
                    $deductions = $values['loan_installments'] + $values['deductions'];

                    return $gross > 0 || $deductions > 0;
                });

            if ($manualEntries->isEmpty()) {
                throw new RuntimeException(__('يرجى إدخال مبالغ الرواتب يدوياً قبل تشغيل المسير.'));
            }

            $currency = strtoupper($currency);
            if (!in_array($currency, [Currency::IQD, Currency::USD], true)) {
                $currency = Currency::IQD;
            }

            $exchangeRate = $exchangeRate !== null && $exchangeRate > 0
                ? $exchangeRate
                : Currency::iqdToUsdRate();

            $employees = Manager::whereIn('id', $manualEntries->keys())->get()->keyBy('id');

            if ($employees->isEmpty()) {
                throw new RuntimeException(__('لم يتم العثور على الموظفين المحددين للرواتب.'));
            }

            $payroll = Payroll::create([
                'period_code' => $periodCode,
                'original_period_code' => $periodCode,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'processed_at' => Carbon::now(),
                'processed_by' => $processedBy,
                'currency' => $currency,
                'exchange_rate_used' => $currency === Currency::USD ? $exchangeRate : null,
                'notes' => $notes,
            ]);

            $totalGross = 0.0;
            $totalLoanInstallments = 0.0;
            $totalOtherDeductions = 0.0;
            $totalNet = 0.0;

            foreach ($manualEntries as $employeeId => $values) {
                $employee = $employees->get($employeeId);

                if (!$employee) {
                    continue;
                }

                $baseSalaryInput = $values['base_salary'];
                $allowancesInput = $values['allowances'];
                $commissionsInput = $values['commissions'];
                $loanInstallmentsInput = $values['loan_installments'];
                $additionalDeductionsInput = $values['deductions'];

                $baseSalary = Currency::convertToSystem($baseSalaryInput, $currency, $exchangeRate);
                $allowances = Currency::convertToSystem($allowancesInput, $currency, $exchangeRate);
                $commissions = Currency::convertToSystem($commissionsInput, $currency, $exchangeRate);
                $loanInstallments = Currency::convertToSystem($loanInstallmentsInput, $currency, $exchangeRate);
                $additionalDeductions = Currency::convertToSystem($additionalDeductionsInput, $currency, $exchangeRate);

                $gross = $baseSalary + $allowances + $commissions;
                $net = $gross - ($loanInstallments + $additionalDeductions);

                $payroll->items()->create([
                    'employee_id' => $employee->id,
                    'base_salary' => $baseSalary,
                    'allowances' => $allowances,
                    'commissions' => $commissions,
                    'loan_installments' => $loanInstallments,
                    'deductions' => $additionalDeductions,
                    'net_salary' => $net,
                    'meta' => [
                        'manual' => true,
                        'inputs' => $values,
                        'input_currency' => $currency,
                        'exchange_rate' => $currency === Currency::USD ? $exchangeRate : null,
                    ],
                ]);

                $totalGross += $gross;
                $totalLoanInstallments += $loanInstallments;
                $totalOtherDeductions += $additionalDeductions;
                $totalNet += $net;
            }

            if ($payroll->items()->count() === 0) {
                throw new RuntimeException(__('لم يتم تسجيل أي موظف في المسير الحالي.'));
            }

            $payroll->update([
                'total_gross' => $totalGross,
                'total_deductions' => $totalLoanInstallments + $totalOtherDeductions,
                'total_net' => $totalNet,
                'total_loan_installments' => $totalLoanInstallments,
                'total_other_deductions' => $totalOtherDeductions,
            ]);

            return $payroll->load(['items.employee']);
        });
    }

    public function revert(Payroll $payroll, ?int $managerId = null, ?string $reason = null): Payroll
    {
        return DB::transaction(function () use ($payroll, $managerId, $reason) {
            if ($payroll->isReverted()) {
                throw new RuntimeException(__('تم التراجع عن مسير الرواتب مسبقاً.'));
            }

            $originalCode = $payroll->original_period_code ?? $payroll->period_code;
            $voidedCode = sprintf('void-%s-%s', $originalCode, now()->format('YmdHis'));

            $payroll->update([
                'period_code' => $voidedCode,
                'reverted_at' => now(),
                'reverted_by' => $managerId,
                'reverted_reason' => $reason,
            ]);

            return $payroll->fresh(['items.employee']);
        });
    }
}
