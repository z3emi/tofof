<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

use Illuminate\Database\Eloquent\SoftDeletes;

class DiscountCode extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    public const TYPE_FIXED         = 'fixed';
    public const TYPE_PERCENTAGE    = 'percentage';
    public const TYPE_FREE_SHIPPING = 'free_shipping';

    protected $fillable = [
        'code',
        'type',
        'value',
        'max_discount_amount',
        'expires_at',
        'max_uses',
        'max_uses_per_user',
        'is_active',
    ];

    protected $casts = [
        'expires_at'          => 'datetime',
        'is_active'           => 'bool',
        'value'               => 'float',
        'max_discount_amount' => 'float',
        'max_uses'            => 'int',
        'max_uses_per_user'   => 'int',
    ];

    // لو عندك جدول usages قديم — نتركه كما هو
    public function usages()
    {
        return $this->hasMany(DiscountCodeUsage::class, 'discount_code_id');
    }

    // هذا الأهم: عدد الطلبات التي استخدمت الكود فعليًا
    public function orders()
    {
        return $this->hasMany(Order::class, 'discount_code_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'discount_code_product');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_discount_code');
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isFreeShipping(): bool
    {
        return $this->type === self::TYPE_FREE_SHIPPING;
    }
}
