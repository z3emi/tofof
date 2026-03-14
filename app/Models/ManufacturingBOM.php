<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ManufacturingBOM extends Model
{
    use HasFactory;

    protected $table = 'manufacturing_boms';

    protected $fillable = [
        'product_id',
        'variant_name',
        'notes',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ManufacturingBOMItem::class, 'bom_id');
    }
}
