<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManufacturingShipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'warehouse_id',
        'shipped_quantity',
        'shipped_at',
        'tracking_number',
        'notes',
    ];

    protected $casts = [
        'shipped_at' => 'date',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(ManufacturingOrder::class, 'order_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(InventoryWarehouse::class, 'warehouse_id');
    }
}
