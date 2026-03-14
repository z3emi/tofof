<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManufacturingOrderMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'material_id',
        'quantity_used',
        'cost',
    ];

    protected $casts = [
        'quantity_used' => 'decimal:3',
        'cost' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(ManufacturingOrder::class, 'order_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(ManufacturingMaterial::class, 'material_id');
    }
}
