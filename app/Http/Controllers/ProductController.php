<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * البحث عن المنتجات وعرض النتائج.
     */
    public function search(Request $request)
    {
        // الحصول على كلمة البحث من الطلب
        $query = $request->input('query') ?: $request->input('q') ?: $request->input('search');

        // جلب الفئات الرئيسية مع الأبناء (للفلتر الجانبي)
        $categoriesFilter = \App\Models\Category::with('children')->whereNull('parent_id')->get();

        // 1. البحث عن البراندات (PrimaryCategory)
        $matchedBrands = collect();
        if ($query) {
            $hasBrandNameEn = Schema::hasColumn((new \App\Models\PrimaryCategory)->getTable(), 'name_en');
            $matchedBrands = \App\Models\PrimaryCategory::where('is_active', true)
                ->where(function ($q) use ($query, $hasBrandNameEn) {
                    $q->where('name_ar', 'LIKE', "%{$query}%");
                    if ($hasBrandNameEn) {
                        $q->orWhere('name_en', 'LIKE', "%{$query}%");
                    }
                })
                ->take(6)
                ->get();
        }

        // 2. البحث عن الفئات
        $matchedCategories = collect();
        if ($query) {
            $hasCatNameEn = Schema::hasColumn((new \App\Models\Category)->getTable(), 'name_en');
            $matchedCategories = \App\Models\Category::where(function ($q) use ($query, $hasCatNameEn) {
                $q->where('name_ar', 'LIKE', "%{$query}%");
                if ($hasCatNameEn) {
                    $q->orWhere('name_en', 'LIKE', "%{$query}%");
                }
            })
            ->take(6)
            ->get();
        }

        // 3. البحث عن المنتجات
        $productsQuery = Product::query();
        if ($query) {
            $productsQuery->where(function ($q) use ($query) {
                $q->where('name_ar', 'LIKE', "%{$query}%")
                  ->orWhere('name_en', 'LIKE', "%{$query}%")
                  ->orWhere('description_ar', 'LIKE', "%{$query}%")
                  ->orWhere('description_en', 'LIKE', "%{$query}%");
            });
        }

        // جلب المنتجات مع تقسيم الصفحات
        $products = $productsQuery->latest()->paginate(16);

        // إضافة كلمة البحث إلى روابط الصفحات
        $products->appends(['query' => $query]);

        // إرسال البيانات إلى الواجهة
        return view('frontend.shop', [
            'products'          => $products,
            'pageTitle'         => 'نتائج البحث عن: "' . e($query) . '"',
            'searchQuery'       => $query,
            'categories'        => $categoriesFilter, // للفلتر الجانبي
            'matchedBrands'     => $matchedBrands,
            'matchedCategories' => $matchedCategories,
        ]);
    }
    public function liveSearch(Request $request)
    {
        $q = trim($request->get('query') ?: $request->get('q') ?: $request->get('search') ?: '');
        if (mb_strlen($q) < 1) {
            return response()->json(['brands' => [], 'categories' => [], 'products' => []]);
        }

        // 1. Brands (PrimaryCategory)
        $brands = \App\Models\PrimaryCategory::where('is_active', true)
            ->where(function ($query) use ($q) {
                $query->where('name_ar', 'LIKE', "%{$q}%")
                      ->orWhere('name_en', 'LIKE', "%{$q}%");
            })
            ->take(3)
            ->get(['id', 'name_ar', 'name_en', 'slug', 'image']);

        // 2. Categories
        $categoryTable = (new \App\Models\Category)->getTable();
        $hasCatNameEn = \Schema::hasColumn($categoryTable, 'name_en');

        $categories = \App\Models\Category::where(function ($query) use ($q, $hasCatNameEn) {
            $query->where('name_ar', 'LIKE', "%{$q}%");
            if ($hasCatNameEn) {
                $query->orWhere('name_en', 'LIKE', "%{$q}%");
            }
        })
        ->take(3)
        ->get(['id', 'name_ar', 'parent_id', 'slug', 'image']);

        // 3. Products
        $productTable = (new Product)->getTable();
        $hasNameAr = Schema::hasColumn($productTable, 'name_ar');
        $hasNameEn = Schema::hasColumn($productTable, 'name_en');
        $hasSlug = Schema::hasColumn($productTable, 'slug');

        $query = Product::query();
        if (Schema::hasColumn($productTable, 'is_active')) {
            $query->where('is_active', true);
        }

        $query->where(function ($qq) use ($q, $hasNameAr, $hasNameEn) {
            if ($hasNameAr) $qq->where('name_ar', 'LIKE', "%{$q}%");
            if ($hasNameEn) ($hasNameAr ? $qq->orWhere('name_en', 'LIKE', "%{$q}%") : $qq->where('name_en', 'LIKE', "%{$q}%"));
        });

        if ($hasNameAr) {
            $query->orderByRaw("CASE WHEN name_ar LIKE ? THEN 0 ELSE 1 END", ["{$q}%"]);
        }

        $imgTable = 'product_images';
        $existing = Schema::hasTable($imgTable) ? Schema::getColumnListing($imgTable) : [];
        $candidates = ['path','image','image_path','url','file','filename','src','photo','picture'];
        $imgPathCol = null;
        foreach ($candidates as $c) {
            if (in_array($c, $existing, true)) { $imgPathCol = $c; break; }
        }

        $imgSelect = [$imgTable.'.id', $imgTable.'.product_id'];
        if ($imgPathCol) $imgSelect[] = $imgTable.'.'.$imgPathCol;

        $products = $query
            ->with([
                'firstImage' => function ($q) use ($imgSelect) {
                    if (!empty($imgSelect)) $q->select($imgSelect);
                },
                'category:id,name_ar',
            ])
            ->latest()
            ->take(10)
            ->get(array_values(array_filter([
                'id',
                'category_id',
                $hasNameAr ? 'name_ar' : null,
                $hasNameEn ? 'name_en' : null,
                $hasSlug ? 'slug' : null
            ])));

        $formattedProducts = $products->map(function ($p) use ($imgPathCol, $candidates) {
            $img = null;
            if ($p->firstImage) {
                if ($imgPathCol && isset($p->firstImage->{$imgPathCol}) && $p->firstImage->{$imgPathCol}) {
                    $img = $p->firstImage->{$imgPathCol};
                } else {
                    foreach ($candidates as $c) {
                        if (isset($p->firstImage->{$c}) && $p->firstImage->{$c}) { $img = $p->firstImage->{$c}; break; }
                    }
                }
            }

            $imageUrl = null;
            if ($img) {
                $imgStr = ltrim((string)$img, '/');
                if (preg_match('#^https?://#i', $imgStr)) {
                    $imageUrl = $imgStr;
                } elseif (Storage::disk('public')->exists($imgStr)) {
                    $imageUrl = asset('storage/'.$imgStr);
                } elseif (file_exists(public_path($imgStr))) {
                    $imageUrl = asset($imgStr);
                } elseif (str_starts_with($imgStr, 'storage/')) {
                    $imageUrl = asset($imgStr);
                } else {
                    $imageUrl = asset('storage/'.$imgStr);
                }
            }

            return [
                'id'            => $p->id,
                'name_ar'       => $p->name_ar ?? ($p->name_en ?? ''),
                'name_en'       => $p->name_en ?? null,
                'slug'          => $p->slug ?? $p->id,
                'image_url'     => $imageUrl,
                'category_name' => optional($p->category)->name_ar ?? '',
            ];
        });

        return response()->json([
            'brands'     => $brands,
            'categories' => $categories,
            'products'   => $formattedProducts
        ]);
    }
}