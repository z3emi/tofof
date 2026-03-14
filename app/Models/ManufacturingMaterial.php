<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ManufacturingMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'unit',
        'cost_per_unit',
        'notes',
    ];

    protected $casts = [
        'cost_per_unit' => 'decimal:2',
    ];

    public function bomItems(): HasMany
    {
        return $this->hasMany(ManufacturingBOMItem::class, 'material_id');
    }

    public function orderMaterials(): HasMany
    {
        return $this->hasMany(ManufacturingOrderMaterial::class, 'material_id');
    }
}
