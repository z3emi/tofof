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
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')
            ->ordered()
            ->with('children')
            ->withCount('products');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
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

    /**
     * ✅ [إضافة] رابط الصورة بشكل صحيح
     */
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        return 'https://placehold.co/400x400?text=' . urlencode($this->name_ar);
    }
}