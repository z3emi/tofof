<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;
use App\Models\User;
use App\Models\PrimaryCategory;

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
        'audience_mode',
        'order_count_operator',
        'order_count_threshold',
        'amount_operator',
        'amount_threshold',
        'notify_via_bell',
        'notify_via_push',
        'sent_at',
    ];

    protected $casts = [
        'expires_at'          => 'datetime',
        'is_active'           => 'bool',
        'value'               => 'float',
        'max_discount_amount' => 'float',
        'max_uses'            => 'int',
        'max_uses_per_user'   => 'int',
        'order_count_threshold' => 'int',
        'amount_threshold'      => 'float',
        'notify_via_bell'       => 'bool',
        'notify_via_push'       => 'bool',
        'sent_at'               => 'datetime',
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

    public function targetUsers()
    {
        return $this->belongsToMany(User::class, 'discount_code_user');
    }

    public function targetPrimaryCategories()
    {
        return $this->belongsToMany(PrimaryCategory::class, 'discount_code_primary_category');
    }

    public function deliveryLogs()
    {
        return $this->hasMany(DiscountCodeDeliveryLog::class);
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
