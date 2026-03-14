<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ManufacturingOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'product_id',
        'variant_name',
        'planned_quantity',
        'completed_quantity',
        'status',
        'starts_at',
        'due_at',
        'total_cost',
        'notes',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'due_at' => 'date',
        'total_cost' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(ManufacturingOrderMaterial::class, 'order_id');
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(ManufacturingShipment::class, 'order_id');
    }
}
