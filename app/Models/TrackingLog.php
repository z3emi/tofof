<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackingLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'employee_tracking_logs';

    protected $fillable = [
        'employee_id',
        'gps_lat',
        'gps_long',
        'address',
        'action',
        'speed',
        'battery_level',
        'device_id',
        'recorded_at',
    ];

    protected $casts = [
        'gps_lat' => 'float',
        'gps_long' => 'float',
        'speed' => 'float',
        'battery_level' => 'float',
        'recorded_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        $employeeModel = config('tracking.employee_model', Manager::class);

        return $this->belongsTo($employeeModel, 'employee_id');
    }
}
