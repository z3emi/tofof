<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManufacturingBOMItem extends Model
{
    use HasFactory;

    protected $table = 'manufacturing_bom_items';

    protected $fillable = [
        'bom_id',
        'material_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
    ];

    public function bom(): BelongsTo
    {
        return $this->belongsTo(ManufacturingBOM::class, 'bom_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(ManufacturingMaterial::class, 'material_id');
    }
}
