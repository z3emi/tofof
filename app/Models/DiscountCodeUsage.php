<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscountCodeUsage extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'discount_code_id', 
        'user_id',   // ملاحظة: هنا تم تعديل `customer_id` إلى `user_id`
        'order_id'
    ];

    // العلاقة مع نموذج المستخدم
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // العلاقة مع نموذج الطلب
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    // العلاقة مع كود الخصم
    public function discountCode()
    {
        return $this->belongsTo(DiscountCode::class, 'discount_code_id');
    }
}
