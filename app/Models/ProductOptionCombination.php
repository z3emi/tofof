<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductOptionCombination extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'combination_key',
        'option_value_ids',
    ];

    protected $casts = [
        'option_value_ids' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function images()
    {
        return $this->hasMany(ProductOptionCombinationImage::class)->orderBy('id');
    }
}
