<?php

namespace App\Models;

use App\Models\Manager;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesCommission extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'hr_commissions';

    protected $fillable = [
        'order_id',
        'employee_id',
        'payroll_item_id',
        'amount',
        'status',
        'earned_at',
        'voided_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'earned_at' => 'datetime',
        'voided_at' => 'datetime',
    ];

    public const STATUS_ACCRUED = 'accrued';
    public const STATUS_VOID    = 'void';
    public const STATUS_PAID    = 'paid';

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Manager::class, 'employee_id')->withTrashed();
    }

    public function payrollItem(): BelongsTo
    {
        return $this->belongsTo(PayrollItem::class);
    }
}
