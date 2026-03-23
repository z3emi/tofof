<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'name_ar',
        'name_en',
        'slug',
        'image',
        'parent_id',
    ];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->with('children')->withCount('products');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function discountCodes()
    {
        return $this->belongsToMany(DiscountCode::class, 'category_discount_code');
    }

    public function getNameTranslatedAttribute(): string
    {
        $locale = app()->getLocale();
        $value = $this->getAttribute('name_' . $locale);
        return $value ?: $this->name_ar;
    }

    public function getTotalProductsCountAttribute()
    {
        $count = $this->products_count;
        foreach ($this->children as $child) {
            $count += $child->total_products_count;
        }
        return $count;
    }

    /**
     * ✅ [إضافة] الدالة المفقودة لجلب جميع الأبناء والأحفاد
     *
     * @return \Illuminate\Support\Collection
     */
    public function descendantsAndSelf()
    {
        $collection = collect([$this]);
        foreach ($this->children as $child) {
            $collection = $collection->merge($child->descendantsAndSelf());
        }
        return $collection;
    }
}