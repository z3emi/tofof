<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'fake_name',
        'rating',
        'comment',
        'admin_reply',
        'status', // approved | pending | rejected
        'show_on_homepage',
        'moderation_score',
        'moderation_flags',
    ];

    protected $casts = [
        'rating' => 'integer',
        'show_on_homepage' => 'boolean',
        'moderation_score' => 'integer',
        'moderation_flags' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
