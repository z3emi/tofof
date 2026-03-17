<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductOptionCombinationImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_option_combination_id',
        'product_image_id',
        'image_path',
    ];

    public function combination()
    {
        return $this->belongsTo(ProductOptionCombination::class, 'product_option_combination_id');
    }

    public function productImage()
    {
        return $this->belongsTo(ProductImage::class, 'product_image_id');
    }
}
