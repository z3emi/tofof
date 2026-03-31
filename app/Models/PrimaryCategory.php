<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class PrimaryCategory extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name_ar','name_en','slug',
        'sort_order','is_active',
        'icon','image',
        'parent_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')
                    ->orderBy('sort_order')->orderBy('id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'primary_category_product');
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public function scopeOrdered($q)
    {
        return $q->orderBy('sort_order')->orderBy('id');
    }

    /**
     * ✅ [إضافة] الدالة المفقودة لجلب جميع الأبناء والأحفاد
     *
     * @return \Illuminate\Support\Collection
     */
    public function getNameTranslatedAttribute(): string
    {
        $locale = app()->getLocale();
        $value = $this->getAttribute('name_' . $locale);
        return $value ?: $this->name_ar;
    }

    public function descendantsAndSelf()
    {
        $collection = collect([$this]);
        foreach ($this->children as $child) {
            $collection = $collection->merge($child->descendantsAndSelf());
        }
        return $collection;
    }

    /**
     * ✅ رابط الصورة بشكل صحيح
     */
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        return 'https://placehold.co/400x400?text=' . urlencode($this->name_ar);
    }
}