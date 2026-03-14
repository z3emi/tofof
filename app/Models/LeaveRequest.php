<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'hr_leave_requests';

    protected $fillable = [
        'employee_id',
        'manager_id',
        'leave_type',
        'days',
        'start_date',
        'reason',
        'status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'reviewed_at' => 'datetime',
        'days' => 'integer',
    ];

    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

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
