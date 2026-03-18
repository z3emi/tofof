<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Setting extends Model
{
    use HasFactory, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Retrieve a setting value with graceful fallback handling.
     */
    public static function getValue(string $key, $default = null)
    {
        try {
            $value = static::where('key', $key)->value('value');
            return is_null($value) ? $default : $value;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /**
     * Resolve the shipping cost from settings with a sensible fallback.
     */
    public static function shippingCost(): float
    {
        $default = (float) config('shop.default_shipping_cost', 5000);
        $value = static::getValue('shipping_cost', $default);

        if (is_null($value) || !is_numeric($value)) {
            return $default;
        }

        return max(0, (float) $value);
    }

    /**
     * Resolve free shipping threshold from settings with fallback.
     */
    public static function freeShippingThreshold(): int
    {
        $default = (int) config('shop.free_shipping_threshold', 85000);
        $value = static::getValue('free_shipping_threshold', $default);

        if (is_null($value) || !is_numeric($value)) {
            return $default;
        }

        return max(0, (int) $value);
    }

    /**
     * Check if shipping is enabled.
     */
    public static function isShippingEnabled(): bool
    {
        $default = (bool) config('shop.shipping_enabled', true);
        $value = static::getValue('shipping_enabled', $default ? '1' : '0');

        return (bool) ($value === '1' || $value === 1 || $value === true);
    }

    /**
     * Check if free shipping feature is enabled.
     */
    public static function isFreeShippingEnabled(): bool
    {
        $value = static::getValue('free_shipping_enabled', '1');

        return (bool) ($value === '1' || $value === 1 || $value === true);
    }
}
