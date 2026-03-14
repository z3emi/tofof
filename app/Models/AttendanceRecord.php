<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'hr_attendance_records';

    protected $fillable = [
        'employee_id',
        'recorded_by',
        'attendance_date',
        'check_in_at',
        'check_out_at',
        'check_in_latitude',
        'check_in_longitude',
        'check_out_latitude',
        'check_out_longitude',
        'status',
        'notes',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'check_in_at' => 'datetime:H:i',
        'check_out_at' => 'datetime:H:i',
        'check_in_latitude' => 'float',
        'check_in_longitude' => 'float',
        'check_out_latitude' => 'float',
        'check_out_longitude' => 'float',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Manager::class, 'employee_id')->withTrashed();
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(Manager::class, 'recorded_by')->withTrashed();
    }
}
