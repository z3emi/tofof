<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscountCodeDeliveryLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'discount_code_id',
        'user_id',
        'channel',
        'status',
        'payload_hash',
        'error',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function discountCode()
    {
        return $this->belongsTo(DiscountCode::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
