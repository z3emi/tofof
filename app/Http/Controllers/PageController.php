<?php

namespace App\Http\Controllers;

use App\Models\HomepageSlide;
use App\Models\Product;
use App\Models\Category;
use App\Models\PrimaryCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Schema;

class PageController extends Controller
{
    /**
     * الصفحة الرئيسية
     */
public function homepage(Request $request)
{
    $now = Carbon::now();
    $heroSlides = collect();
    $promoPrimarySlides = collect();
    $promoSecondarySlides = collect();
    $homepageSlides = collect();

    // 1) جديد المتجر
    $newProducts = Product::query()
        ->where('is_active', true)
        ->with('firstImage')
        ->latest('created_at')
        ->take(12)
        ->get();

    // 2) عروض
    $saleProducts = Product::query()
        ->where('is_active', true)
        ->whereNotNull('sale_price')->where('sale_price', '>', 0)
        ->where(function ($q) use ($now) {
            $q->whereNull('sale_starts_at')->orWhere('sale_starts_at', '<=', $now);
        })
        ->where(function ($q) use ($now) {
            $q->whereNull('sale_ends_at')->orWhere('sale_ends_at', '>=', $now);
        })
        ->with('firstImage')
        ->inRandomOrder()
        ->take(12)
        ->get();

    // 3) الأكثر مبيعًا + تكملة لو أقل من 14
    $bestSellingProducts = Product::query()
        ->where('is_active', true)
        ->with('firstImage')
        ->withCount('orderItems')
        ->whereHas('orderItems')
        ->orderByDesc('order_items_count')
        ->take(12)
        ->get();

    $needed = 12 - $bestSellingProducts->count();
    if ($needed > 0) {
        $excludedIds = $bestSellingProducts->pluck('id');
        $extraProducts = Product::query()
            ->where('is_active', true)
            ->whereNotIn('id', $excludedIds)
            ->with('firstImage')
            ->inRandomOrder()
            ->take($needed)
            ->get();

        $bestSellingProducts = $bestSellingProducts->concat($extraProducts);
    }

    // 4) فئات (للبلوك مال "تصفحي حسب البراند")
    $categories = Category::query()
        ->whereNull('parent_id')
        ->withCount(['products as products_count' => function($q) {
            $q->where('is_active', true);
        }])
        ->ordered()
        ->get();

    // 5) فئات رئيسية (pc)
    $primaryCategories2 = PrimaryCategory::query()
        ->withCount(['products as products_count' => function($q) {
            $q->where('is_active', true);
        }])
        ->ordered()
        ->get();

    // 6) قائمة التنقّل
    $navCategories = Category::query()
        ->with([
            'children.children',
            'children' => function($q) {
                $q->withCount(['products as products_count' => function($qq) {
                    $qq->where('is_active', true);
                }])->ordered();
            },
            'children.children' => function($q) {
                $q->withCount(['products as products_count' => function($qq) {
                    $qq->where('is_active', true);
                }])->ordered();
            },
        ])
        ->withCount(['products as products_count' => function($q) {
            $q->where('is_active', true);
        }])
        ->whereNull('parent_id')
        ->ordered()
        ->get();

    // 7) المفضّلة
    $favoriteProductIds = Auth::check()
        ? Auth::user()->favorites()->pluck('product_id')->toArray()
        : [];

    // جلب سلايدات الصفحة الرئيسية من قاعدة البيانات
    if (Schema::hasTable('homepage_slides')) {
        $homepageSlides = HomepageSlide::query()
            ->active()
            ->ordered()
            ->get()
            ->groupBy('section');

        $heroSlides = $homepageSlides->get(HomepageSlide::SECTION_HERO, collect());
        $promoPrimarySlides = $homepageSlides->get(HomepageSlide::SECTION_PROMO_PRIMARY, collect());
        $promoSecondarySlides = $homepageSlides->get(HomepageSlide::SECTION_PROMO_SECONDARY, collect());
    }

    // fallback للهيرو إذا ماكو سلايدات هيرو فعالة بالإدارة
    if ($heroSlides->isEmpty()) {
        $watchProducts = Product::query()
            ->where('is_active', true)
            ->with('firstImage')
            ->whereHas('primaryCategories', function($q) {
                $q->where('slug', 'watches')->orWhere('name_ar', 'ساعات');
            })
            ->inRandomOrder()
            ->take(3)
            ->get();

        $heroSlides = $watchProducts->map(function($product) {
            $imageUrl = $product->firstImage?->image_path
                ? asset('storage/' . ltrim($product->firstImage->image_path, '/'))
                : ($product->image_url ?? 'https://via.placeholder.com/1974x1316');

            return (object)[
                'title' => $product->name_ar ?? $product->name_en,
                'subtitle' => null,
                'button_text' => 'عرض المنتج',
                'button_url' => route('product.detail', $product),
                'background_image_url' => $imageUrl,
                'alt_text' => $product->name_ar ?? $product->name_en,
                'show_overlay' => false,
                'overlay_color' => '#000000',
                'overlay_strength' => 0.5,
            ];
        })->values();
    }

    if ($heroSlides->isEmpty()) {
        $heroSlides = HomepageSlide::defaultSlidesForSection(HomepageSlide::SECTION_HERO);
    }

    if ($promoPrimarySlides->isEmpty()) {
        $promoPrimarySlides = HomepageSlide::defaultSlidesForSection(HomepageSlide::SECTION_PROMO_PRIMARY);
    }

    if ($promoSecondarySlides->isEmpty()) {
        $promoSecondarySlides = HomepageSlide::defaultSlidesForSection(HomepageSlide::SECTION_PROMO_SECONDARY);
    }

    return view('frontend.homepage', compact(
        'newProducts',
        'saleProducts',
        'bestSellingProducts',
        'categories',
        'primaryCategories2',
        'navCategories',
        'favoriteProductIds',
        'heroSlides',
        'promoPrimarySlides',
        'promoSecondarySlides'
    ));
}

    /**
     * صفحة المتجر + الفلاتر
     */
    public function shop(Request $request)
    {
        $now = now();

        $query = Product::query()
            ->where('is_active', true)
            ->with(['firstImage', 'images']);

        // البحث
        $search = $request->input('q', $request->input('query'));
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%");
            });
        }

        // السعر
        $min = $request->has('min_price') && is_numeric($request->min_price) ? (int) $request->min_price : null;
        $max = $request->has('max_price') && is_numeric($request->max_price) ? (int) $request->max_price : null;
        if (!is_null($min) || !is_null($max)) {
            $query->where(function ($q2) use ($min, $max) {
                if (!is_null($min) && !is_null($max)) {
                    $q2->whereRaw('COALESCE(sale_price, price) BETWEEN ? AND ?', [$min, $max]);
                } elseif (!is_null($min)) {
                    $q2->whereRaw('COALESCE(sale_price, price) >= ?', [$min]);
                } else {
                    $q2->whereRaw('COALESCE(sale_price, price) <= ?', [$max]);
                }
            });
        }

        // ✅ فلترة البراند (pc أو brand)
        $brand = null;
        if ($request->filled('pc') || $request->filled('brand')) {
            if ($request->filled('pc') && is_numeric($request->pc)) {
                $brand = PrimaryCategory::find((int)$request->pc);
            } elseif ($request->filled('brand')) {
                $brand = PrimaryCategory::where('slug', $request->brand)->first();
            }

            if ($brand) {
                $ids = method_exists($brand, 'descendantsAndSelf')
                    ? $brand->descendantsAndSelf()->pluck('id')
                    : collect([$brand->id]);

                if (Schema::hasColumn('products', 'primary_category_id')) {
                    $query->whereIn('primary_category_id', $ids);
                } elseif (method_exists(app(Product::class), 'primaryCategories')) {
                    $query->whereHas('primaryCategories', function ($q) use ($ids) {
                        $q->whereIn('primary_categories.id', $ids);
                    });
                } elseif (method_exists(app(Product::class), 'primaryCategory')) {
                    $query->whereHas('primaryCategory', function ($q) use ($ids) {
                        $q->whereIn('primary_categories.id', $ids);
                    });
                }
            }
        }

        // ✅ فلترة الفئة (category)
        $category = null;
        if ($request->filled('category')) {
            $key = $request->input('category');
            $category = is_numeric($key)
                ? Category::find((int)$key)
                : Category::where('slug', $key)->first();

            if ($category) {
                $ids = method_exists($category, 'descendantsAndSelf')
                    ? $category->descendantsAndSelf()->pluck('id')
                    : collect([$category->id]);

                if (Schema::hasColumn('products', 'category_id')) {
                    $query->whereIn('category_id', $ids);
                } elseif (method_exists(app(Product::class), 'categories')) {
                    $query->whereHas('categories', function ($q) use ($ids) {
                        $q->whereIn('categories.id', $ids);
                    });
                } elseif (method_exists(app(Product::class), 'category')) {
                    $query->whereHas('category', function ($q) use ($ids) {
                        $q->whereIn('categories.id', $ids);
                    });
                }
            }
        }

        // عروض
        if ($request->boolean('on_sale')) {
            $query->whereNotNull('sale_price')
                ->where('sale_price', '>', 0)
                ->where(function ($q) use ($now) {
                    $q->whereNull('sale_starts_at')->orWhere('sale_starts_at', '<=', $now);
                })
                ->where(function ($q) use ($now) {
                    $q->whereNull('sale_ends_at')->orWhere('sale_ends_at', '>=', $now);
                });
        }

        // ترتيب
        $sort = $request->input('sort');
        switch ($sort) {
            case 'price_asc':
                $query->orderByRaw('COALESCE(sale_price, price) ASC');
                break;
            case 'price_desc':
                $query->orderByRaw('COALESCE(sale_price, price) DESC');
                break;
            case 'rating_desc':
                $query->orderBy('average_rating', 'DESC');
                break;
            case 'bestseller':
                $query->orderByDesc(
                    OrderItem::selectRaw('sum(quantity)')
                        ->whereColumn('order_items.product_id', 'products.id')
                );
                break;
            default:
                $query->latest();
                break;
        }

        $products = $query->paginate(20)->appends($request->query());

        // فلاتر
        $brands = PrimaryCategory::with(['children' => fn($q) => $q->withCount('products')])
            ->withCount('products')
            ->ordered()
            ->get();
            
        $categories = Category::whereNull('parent_id')
            ->with(['children' => fn($q) => $q->withCount('products')])
            ->withCount('products')
            ->ordered()
            ->get();

        foreach ($categories as $cat) {
            $cat->total_products_count = $cat->products_count + $cat->children->sum('products_count');
        }
        foreach ($brands as $brandItem) {
            $brandItem->total_products_count = $brandItem->products_count + $brandItem->children->sum('products_count');
        }

        // العنوان
        $pageTitle = 'المتجر';
        if ($category) {
            $pageTitle = $category->name_ar ?? $category->name ?? $pageTitle;
        } elseif ($brand) {
            $pageTitle = $brand->name_ar ?? $brand->name ?? $pageTitle;
        } elseif (!empty($search)) {
            $pageTitle = 'نتائج البحث عن: "' . e($search) . '"';
        }

        $favoriteProductIds = Auth::check()
            ? Auth::user()->favorites()->pluck('product_id')->toArray()
            : [];

        return view('frontend.shop', compact(
            'products', 'categories', 'brands', 'pageTitle', 'favoriteProductIds'
        ));
    }

    public function productDetail(Product $product)
    {
        if (!$product->is_active) {
            abort(404);
        }

        $product->load([
            'images',
            'category',
            'reviews.user',
            'options.values',
            'optionCombinations.images.productImage',
        ]);

        $productOptionsPayload = $product->options
            ->sortBy('sort_order')
            ->values()
            ->map(function ($option) {
                return [
                    'id' => (int) $option->id,
                    'name' => $option->name_ar,
                    'is_required' => (bool) $option->is_required,
                    'values' => $option->values
                        ->sortBy('sort_order')
                        ->values()
                        ->map(function ($value) {
                            return [
                                'id' => (int) $value->id,
                                'label' => $value->value_ar,
                            ];
                        })
                        ->toArray(),
                ];
            })
            ->toArray();

        $combinationImageMap = [];
        foreach ($product->optionCombinations as $combination) {
            $comboImage = $combination->images->first();
            if (!$comboImage) {
                continue;
            }

            $imagePath = $comboImage->image_path ?: $comboImage->productImage?->image_path;
            if (!$imagePath) {
                continue;
            }

            $combinationImageMap[$combination->combination_key] = asset('storage/' . $imagePath);
        }

        $relatedProducts = collect();
        if ($product->category_id) {
            $relatedProducts = Product::where('is_active', true)
                ->where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->with('firstImage')
                ->inRandomOrder()
                ->take(5)
                ->get();
        }

        $favoriteProductIds = [];
        if (Auth::check()) {
            $favoriteProductIds = Auth::user()->favorites()->pluck('product_id')->toArray();
        }

        $isFavorited = in_array($product->id, $favoriteProductIds);

        return view('frontend.product-detail', compact(
            'product',
            'relatedProducts',
            'isFavorited',
            'favoriteProductIds',
            'productOptionsPayload',
            'combinationImageMap'
        ));
    }

    public function privacyPolicy()
    {
        return view('frontend.pages.privacy-policy');
    }

    public function categories()
    {
        $fiatTree = \App\Models\PrimaryCategory::whereNull('parent_id')
            ->with(['children' => function ($query) {
                $query->withCount('products')->ordered();
            }])
            ->withCount('products')
            ->ordered()
            ->get();

        foreach ($fiatTree as $fia) {
            try {
                $primaryIds = $fia->children->pluck('id')->push($fia->id)->unique()->values();

                $productIds = \Illuminate\Support\Facades\DB::table('primary_category_product')
                    ->whereIn('primary_category_id', $primaryIds)
                    ->pluck('product_id');

                $categoryIds = \App\Models\Product::whereIn('id', $productIds)
                    ->whereNotNull('category_id')
                    ->distinct()
                    ->pluck('category_id');

                $fia->categories = $categoryIds->isNotEmpty()
                    ? \App\Models\Category::whereIn('id', $categoryIds)
                        ->withCount('products')
                        ->ordered()
                        ->get()
                    : collect();
            } catch (\Exception $e) {
                $fia->categories = collect();
            }
        }

        return view('frontend.pages.categories', compact('fiatTree'));
    }

    public function paymentAndDelivery()
    {
        return view('frontend.pages.payment-and-delivery');
    }

    public function returnpolicy()
    {
        return view('frontend.pages.return-policy');
    }

    public function faq()
    {
        return view('frontend.pages.faq');
    }

    public function getCategoriesForPrimaryCategory(PrimaryCategory $primaryCategory)
    {
        $productIds = $primaryCategory->products()->where('is_active', true)->pluck('products.id');
        $categoryIds = Product::whereIn('id', $productIds)->distinct()->pluck('category_id');

        $categories = Category::whereIn('id', $categoryIds)
            ->whereNull('parent_id')
            ->with(['children' => fn ($query) => $query->ordered()])
            ->withCount('products')
            ->ordered()
            ->get();

        return response()->json($categories);
    }
}
