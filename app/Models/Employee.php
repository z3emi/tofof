<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employees';

    protected $fillable = [
        'name',
        'department',
        'position',
        'email',
        'phone',
    ];

    public function trackingLogs(): HasMany
    {
        return $this->hasMany(TrackingLog::class);
    }
}
