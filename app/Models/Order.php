<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;

class Order extends Model
{
    use HasFactory, LogsActivity, SoftDeletes; // AND 'SoftDeletes' MUST BE USED HERE


    protected $fillable = [
        'user_id',
        'customer_id',
        'governorate',
        'city',
        'address_details',
        'nearest_landmark',
        'notes',
        'is_gift',
        'gift_recipient_name',
        'gift_recipient_phone',
        'gift_recipient_address_details',
        'gift_message',
        'total_amount',
        'total_cost',
        'shipping_cost',
        'status',
        'discount_amount',     
        'discount_code_id',
        'payment_method',
        'payment_status',
    ];

    protected $casts = [
        'is_gift' => 'boolean',
    ];

    /**
     * العلاقة بين الطلب والمستخدم
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * العلاقة بين الطلب والعميل
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * العلاقة بين الطلب وعناصر الطلب (OrderItem)
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * العلاقة بين الطلب والمنتجات عبر جدول order_items
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_items', 'order_id', 'product_id')
                    ->withPivot('quantity', 'price');
    }

    public function discountCode()
    {
        return $this->belongsTo(DiscountCode::class);
    }

    public function discountCodeUsage()
    {
        return $this->hasOne(DiscountCodeUsage::class);
    }

    public function wasPlacedByCustomer(): bool
    {
        $customerUserId = optional($this->customer)->user_id;

        if (!$customerUserId) {
            return false;
        }

        return (int) $this->user_id === (int) $customerUserId;
    }

    public function wasCreatedManually(): bool
    {
        return ! $this->wasPlacedByCustomer();
    }

}
