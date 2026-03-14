<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvanceRequest extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'hr_advance_requests';

    protected $fillable = [
        'employee_id',
        'manager_id',
        'amount',
        'repayment_date',
        'status',
        'reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'repayment_date' => 'date',
        'reviewed_at' => 'datetime',
    ];

    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_SETTLED  = 'settled';

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Manager::class, 'manager_id')->withTrashed();
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Manager::class, 'employee_id')->withTrashed();
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Manager::class, 'reviewed_by')->withTrashed();
    }
}
