<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
        'cost',
        'option_selections',
    ];

    protected $casts = [
        'option_selections' => 'array',
    ];

    /**
     * Get the product that belongs to the order item.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * ** هذه هي العلاقة المفقودة التي تمت إضافتها **
     * Get the order that the item belongs to.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function normalizedOptionSelections(): array
    {
        $value = $this->getRawOriginal('option_selections');

        if ($value === null || $value === '') {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        if (is_object($value)) {
            return (array) $value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                if (is_array($decoded)) {
                    return $decoded;
                }

                if ($decoded === null) {
                    return [];
                }

                return ['value' => $decoded];
            }

            return ['value' => $value];
        }

        return (array) $value;
    }
}
