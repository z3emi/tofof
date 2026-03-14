<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManagerGovernorate extends Model
{
    use HasFactory;

    protected $table = 'user_governorates';

    protected $fillable = [
        'user_id',
        'governorate',
    ];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Manager::class, 'user_id');
    }
}
