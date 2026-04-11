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
        $normalized = $this->normalizeOptionValue($this->getRawOriginal('option_selections'));

        if ($normalized === null || $normalized === '') {
            return [];
        }

        if (is_array($normalized)) {
            return $normalized;
        }

        if (is_object($normalized)) {
            return (array) $normalized;
        }

        return ['value' => $normalized];
    }

    private function normalizeOptionValue(mixed $value, int $depth = 0): mixed
    {
        if ($depth > 3 || $value === null) {
            return $value;
        }

        if (is_array($value)) {
            $normalized = [];
            foreach ($value as $key => $item) {
                $normalized[$key] = $this->normalizeOptionValue($item, $depth + 1);
            }
            return $normalized;
        }

        if (is_object($value)) {
            return $this->normalizeOptionValue((array) $value, $depth + 1);
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '') {
                return '';
            }

            $decoded = json_decode($trimmed, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $this->normalizeOptionValue($decoded, $depth + 1);
            }
        }

        return $value;
    }
}
