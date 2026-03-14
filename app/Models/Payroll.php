<?php

namespace App\Models;

use App\Traits\LogsActivity;
use App\Models\Manager;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payroll extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'hr_payrolls';

    protected $fillable = [
        'period_code',
        'original_period_code',
        'period_start',
        'period_end',
        'processed_at',
        'processed_by',
        'currency',
        'exchange_rate_used',
        'total_gross',
        'total_deductions',
        'total_net',
        'total_loan_installments',
        'total_other_deductions',
        'notes',
        'reverted_at',
        'reverted_by',
        'revert_reason',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'processed_at' => 'datetime',
        'total_gross' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'total_net' => 'decimal:2',
        'total_loan_installments' => 'decimal:2',
        'total_other_deductions' => 'decimal:2',
        'exchange_rate_used' => 'decimal:4',
        'reverted_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(Manager::class, 'processed_by');
    }

    public function revertor(): BelongsTo
    {
        return $this->belongsTo(Manager::class, 'reverted_by');
    }

    public function isReverted(): bool
    {
        return (bool) $this->reverted_at;
    }
}
