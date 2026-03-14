<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homepage_slides', function (Blueprint $table) {
            $table->id();
            $table->string('section', 50);
            $table->string('title');
            $table->text('subtitle')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_url')->nullable();
            $table->string('background_image')->nullable();
            $table->string('alt_text')->nullable();
            $table->unsignedInteger('sort_order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['section', 'sort_order']);
        });

        DB::table('homepage_slides')->insert([
            [
                'section' => 'hero',
                'title' => 'طفوف - اكسسوارات فاخرة أصلية',
                'subtitle' => 'اكسسوارات أصلية 100% مع دليل المنتج',
                'button_text' => 'تسوق الآن',
                'button_url' => '/shop',
                'background_image' => 'https://images.unsplash.com/photo-1758185578880-b187b5ea352c?q=80&w=2532&auto=format&fit=crop',
                'alt_text' => 'طفوف اكسسوارات فاخرة أصلية',
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'section' => 'hero',
                'title' => 'اطلب بسهولة والتوصيل بسرعة البرق',
                'subtitle' => 'اختر الاكسسوار المناسب ويوصلك بسرعة البرق داخل بغداد والمحافظات',
                'button_text' => 'تسوق الآن',
                'button_url' => '/shop',
                'background_image' => 'https://images.unsplash.com/photo-1758186474576-ac14c6ea88eb?q=80&w=2532&auto=format&fit=crop',
                'alt_text' => 'طريقة طلب سهلة وتوصيل سريع',
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'section' => 'hero',
                'title' => 'هدايا مع كل طلب',
                'subtitle' => 'من 60,000 د.ع: هديتان - من 85,000 د.ع: هديتان + توصيل مجاني',
                'button_text' => 'اكتشفي العروض',
                'button_url' => '/shop?on_sale=true',
                'background_image' => 'https://images.unsplash.com/photo-1758193753344-34b12bfff304?q=80&w=2532&auto=format&fit=crop',
                'alt_text' => 'هدايا مع كل طلب',
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'section' => 'promo_primary',
                'title' => 'عروضنا الأقوى',
                'subtitle' => 'خصومات مميزة على منتجات مختارة لفترة محدودة',
                'button_text' => 'اكتشفي العروض',
                'button_url' => '/shop?on_sale=true',
                'background_image' => 'https://images.unsplash.com/photo-1676570092589-a6c09ecbb373?q=80&w=1974&auto=format&fit=crop',
                'alt_text' => 'عروض طفوف',
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'section' => 'promo_primary',
                'title' => 'ساعاتنا ترفع ستايلك',
                'subtitle' => 'ساعات فاخرة، ديجيتال، وكلاسيكية - كل واحدة أصلية ومضمونة',
                'button_text' => 'استكشف الساعات',
                'button_url' => '/shop?category=watches',
                'background_image' => 'https://images.unsplash.com/photo-1522108098940-de49801b5b40?q=80&w=2069&auto=format&fit=crop',
                'alt_text' => 'ساعات فاخرة',
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'section' => 'promo_primary',
                'title' => 'محافظنا بستايل وأناقة',
                'subtitle' => 'محافظ جلدية فاخرة، بتصاميم عصرية - أصلية ومضمونة',
                'button_text' => 'استكشف المحافظ',
                'button_url' => '/shop?category=wallets',
                'background_image' => 'https://images.unsplash.com/photo-1574712481169-f3cbc23609fd?q=80&w=2121&auto=format&fit=crop',
                'alt_text' => 'محافظ جلدية فاخرة',
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'section' => 'promo_secondary',
                'title' => 'نصائح الجمال',
                'subtitle' => 'اكتشفي أسرار العناية ببشرتك مع خبرائنا',
                'button_text' => 'اقرئي المزيد',
                'button_url' => '#',
                'background_image' => 'https://images.unsplash.com/photo-1596462502278-27bfdc403348?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80',
                'alt_text' => 'نصائح الجمال',
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'section' => 'promo_secondary',
                'title' => 'بطاقات الهدايا',
                'subtitle' => 'الهدية المثالية لمن تحبين',
                'button_text' => 'اطلبي الآن',
                'button_url' => '#',
                'background_image' => 'https://images.unsplash.com/photo-1577058109956-67adf6edc586?q=80&w=2070&auto=format&fit=crop',
                'alt_text' => 'بطاقات الهدايا',
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_slides');
    }
};
