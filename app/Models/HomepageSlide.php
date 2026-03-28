<?php

namespace App\Models;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class HomepageSlide extends Model
{
    use HasFactory;

    public const SECTION_HERO = 'hero';
    public const SECTION_PROMO_PRIMARY = 'promo_primary';
    public const SECTION_PROMO_SECONDARY = 'promo_secondary';

    protected $fillable = [
        'section',
        'title',
        'title_en',
        'subtitle',
        'subtitle_en',
        'button_text',
        'button_text_en',
        'button_url',
        'background_image',
        'background_image_en',
        'alt_text',
        'sort_order',
        'is_active',
        'show_overlay',
        'overlay_color',
        'overlay_strength',
        'click_type',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function sections(): array
    {
        return [
            self::SECTION_HERO => 'الهيرو سلايدر',
            self::SECTION_PROMO_PRIMARY => 'السلايدر الترويجي الأول',
            self::SECTION_PROMO_SECONDARY => 'السلايدر الترويجي الثاني',
        ];
    }

    public static function defaultSlides(): array
    {
        return [
            self::SECTION_HERO => [
                [
                    'section' => self::SECTION_HERO,
                    'title' => 'طفوف - اكسسوارات فاخرة أصلية',
                    'subtitle' => 'اكسسوارات أصلية 100% مع دليل المنتج',
                    'button_text' => 'تسوق الآن',
                    'button_url' => '/shop',
                    'background_image' => 'https://images.unsplash.com/photo-1758185578880-b187b5ea352c?q=80&w=2532&auto=format&fit=crop',
                    'alt_text' => 'طفوف اكسسوارات فاخرة أصلية',
                    'sort_order' => 1,
                    'is_active' => true,
                ],
                [
                    'section' => self::SECTION_HERO,
                    'title' => 'اطلب بسهولة والتوصيل بسرعة البرق',
                    'subtitle' => 'اختر الاكسسوار المناسب ويوصلك بسرعة البرق داخل بغداد والمحافظات',
                    'button_text' => 'تسوق الآن',
                    'button_url' => '/shop',
                    'background_image' => 'https://images.unsplash.com/photo-1758186474576-ac14c6ea88eb?q=80&w=2532&auto=format&fit=crop',
                    'alt_text' => 'طريقة طلب سهلة وتوصيل سريع',
                    'sort_order' => 2,
                    'is_active' => true,
                ],
                [
                    'section' => self::SECTION_HERO,
                    'title' => 'هدايا مع كل طلب',
                    'subtitle' => 'من 60,000 د.ع: هديتان - من 85,000 د.ع: هديتان + توصيل مجاني',
                    'button_text' => 'اكتشفي العروض',
                    'button_url' => '/shop?on_sale=true',
                    'background_image' => 'https://images.unsplash.com/photo-1758193753344-34b12bfff304?q=80&w=2532&auto=format&fit=crop',
                    'alt_text' => 'هدايا مع كل طلب',
                    'sort_order' => 3,
                    'is_active' => true,
                ],
            ],
            self::SECTION_PROMO_PRIMARY => [
                [
                    'section' => self::SECTION_PROMO_PRIMARY,
                    'title' => 'عروضنا الأقوى',
                    'subtitle' => 'خصومات مميزة على منتجات مختارة لفترة محدودة',
                    'button_text' => 'اكتشفي العروض',
                    'button_url' => '/shop?on_sale=true',
                    'background_image' => 'https://images.unsplash.com/photo-1676570092589-a6c09ecbb373?q=80&w=1974&auto=format&fit=crop',
                    'alt_text' => 'عروض طفوف',
                    'sort_order' => 1,
                    'is_active' => true,
                ],
                [
                    'section' => self::SECTION_PROMO_PRIMARY,
                    'title' => 'ساعاتنا ترفع ستايلك',
                    'subtitle' => 'ساعات فاخرة، ديجيتال، وكلاسيكية - كل واحدة أصلية ومضمونة',
                    'button_text' => 'استكشف الساعات',
                    'button_url' => '/shop?category=watches',
                    'background_image' => 'https://images.unsplash.com/photo-1522108098940-de49801b5b40?q=80&w=2069&auto=format&fit=crop',
                    'alt_text' => 'ساعات فاخرة',
                    'sort_order' => 2,
                    'is_active' => true,
                ],
                [
                    'section' => self::SECTION_PROMO_PRIMARY,
                    'title' => 'محافظنا بستايل وأناقة',
                    'subtitle' => 'محافظ جلدية فاخرة، بتصاميم عصرية - أصلية ومضمونة',
                    'button_text' => 'استكشف المحافظ',
                    'button_url' => '/shop?category=wallets',
                    'background_image' => 'https://images.unsplash.com/photo-1574712481169-f3cbc23609fd?q=80&w=2121&auto=format&fit=crop',
                    'alt_text' => 'محافظ جلدية فاخرة',
                    'sort_order' => 3,
                    'is_active' => true,
                ],
            ],
            self::SECTION_PROMO_SECONDARY => [
                [
                    'section' => self::SECTION_PROMO_SECONDARY,
                    'title' => 'نصائح الجمال',
                    'subtitle' => 'اكتشفي أسرار العناية ببشرتك مع خبرائنا',
                    'button_text' => 'اقرئي المزيد',
                    'button_url' => '#',
                    'background_image' => 'https://images.unsplash.com/photo-1596462502278-27bfdc403348?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80',
                    'alt_text' => 'نصائح الجمال',
                    'sort_order' => 1,
                    'is_active' => true,
                ],
                [
                    'section' => self::SECTION_PROMO_SECONDARY,
                    'title' => 'بطاقات الهدايا',
                    'subtitle' => 'الهدية المثالية لمن تحبين',
                    'button_text' => 'اطلبي الآن',
                    'button_url' => '#',
                    'background_image' => 'https://images.unsplash.com/photo-1577058109956-67adf6edc586?q=80&w=2070&auto=format&fit=crop',
                    'alt_text' => 'بطاقات الهدايا',
                    'sort_order' => 2,
                    'is_active' => true,
                ],
            ],
        ];
    }

    public static function defaultSlidesForSection(string $section): Collection
    {
        return collect(static::defaultSlides()[$section] ?? [])
            ->map(fn (array $attributes) => new static($attributes));
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function getSectionLabelAttribute(): string
    {
        return static::sections()[$this->section] ?? $this->section;
    }

    public function getBackgroundImageUrlAttribute(): ?string
    {
        return $this->getImageUrl($this->background_image);
    }

    public function getBackgroundImageEnUrlAttribute(): ?string
    {
        return $this->getImageUrl($this->background_image_en);
    }

    public function getEffectiveImageUrlAttribute(): ?string
    {
        $currentLocale = app()->getLocale();
        
        if ($currentLocale === 'en') {
            return $this->background_image_en_url ?: $this->background_image_url;
        }

        return $this->background_image_url ?: $this->background_image_en_url;
    }

    protected function getImageUrl(?string $image): ?string
    {
        if (blank($image)) {
            return null;
        }

        if (Str::startsWith($image, ['http://', 'https://'])) {
            return $image;
        }

        return asset('storage/' . ltrim($image, '/'));
    }
}
