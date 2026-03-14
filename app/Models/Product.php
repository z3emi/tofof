<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;
use App\Models\ProductReview;

use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name_ar',
        'name_en',
        'sku',
        'description_ar',
        'description_en',
        'price',
        'sale_price',
        'sale_starts_at',
        'sale_ends_at',
        'image_url',
        'is_active',
        'stock_quantity',
    ];

    protected $casts = [
        'sale_starts_at' => 'datetime',
        'sale_ends_at' => 'datetime',
    ];

    /**
     * Get the translated name based on the current locale.
     */
    public function getNameTranslatedAttribute(): string
    {
        $locale = app()->getLocale();
        $value = $this->getAttribute('name_' . $locale);
        return $value ?: $this->name_ar;
    }

    /**
     * Get the translated description based on the current locale.
     */
    public function getDescriptionTranslatedAttribute(): string
    {
        $locale = app()->getLocale();
        $value = $this->getAttribute('description_' . $locale);
        return $value ?: $this->description_ar;
    }



    // =======================================================
    // باقي العلاقات والدوال في المودل تبقى كما هي
    // =======================================================

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    public function orderItems()
    {
        // تأكد من أن اسم المودل هو OrderItem أو ما يتوافق مع مشروعك
        return $this->hasMany(OrderItem::class);    
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_items', 'product_id', 'order_id')
                     ->withPivot('quantity', 'price');
    }

    /**
     * الأكواد المرتبطة بالمنتج.
     */
    public function discountCodes()
    {
        return $this->belongsToMany(DiscountCode::class, 'discount_code_product');
    }

    public function isFavorited()
    {
        return auth()->check() && $this->favorites()->where('user_id', auth()->id())->exists();
    }
        public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * Relationship to get just the first image (useful for display).
     */
    public function firstImage()
    {
        return $this->hasOne(ProductImage::class)->oldestOfMany();
    }

    /**
     * Determine if the product is currently on sale.
     */
    public function isOnSale(): bool
    {
        $now = now();
        return $this->sale_price !== null
            && $this->sale_price > 0
            && ($this->sale_starts_at === null || $this->sale_starts_at <= $now)
            && ($this->sale_ends_at === null || $this->sale_ends_at >= $now);
    }

    /**
     * Get the price taking into account any active sale.
     */
    public function getCurrentPriceAttribute(): float
    {
        return $this->isOnSale() ? $this->sale_price : $this->price;
    }
    public function reviews()
{
    return $this->hasMany(ProductReview::class)->where('status', 'approved');
}

/** متوسط التقييم (يقرأ من العمود إن موجود، وإلا يحسبه ديناميكياً) */
public function getAverageRatingAttribute()
{
    if (array_key_exists('average_rating', $this->attributes)) {
        return (float) $this->attributes['average_rating'];
    }
    return round((float) $this->reviews()->avg('rating'), 2);
}

/** عدد التقييمات (يقرأ من العمود إن موجود، وإلا يحسبه ديناميكياً) */
public function getReviewsCountAttribute()
{
    if (array_key_exists('reviews_count', $this->attributes)) {
        return (int) $this->attributes['reviews_count'];
    }
    return (int) $this->reviews()->count();
}


public function availableQuantity(): int
{
    return (int) $this->stock_quantity;
}

/** Accessor مريح: $product->available_quantity */
public function getAvailableQuantityAttribute(): int
{
    return (int) $this->stock_quantity;
}

public function primaryCategories()
{
    return $this->belongsToMany(PrimaryCategory::class, 'primary_category_product');
}

}