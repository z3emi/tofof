<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\DiscountCode;
use App\Models\HomepageSlide;
use App\Models\PrimaryCategory;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class StoreController extends Controller
{
    public function sliders(Request $request): JsonResponse
    {
        $locale = strtolower((string) $request->input('locale', app()->getLocale()));
        $isEnglish = $locale === 'en';

        $slidesBySection = HomepageSlide::query()
            ->active()
            ->ordered()
            ->get()
            ->groupBy('section');

        $heroSlides = $slidesBySection->get(HomepageSlide::SECTION_HERO, HomepageSlide::defaultSlidesForSection(HomepageSlide::SECTION_HERO));
        $promoPrimarySlides = $slidesBySection->get(HomepageSlide::SECTION_PROMO_PRIMARY, HomepageSlide::defaultSlidesForSection(HomepageSlide::SECTION_PROMO_PRIMARY));
        $promoSecondarySlides = $slidesBySection->get(HomepageSlide::SECTION_PROMO_SECONDARY, HomepageSlide::defaultSlidesForSection(HomepageSlide::SECTION_PROMO_SECONDARY));

        return response()->json([
            'success' => true,
            'message' => __('تم جلب السلايدرات بنجاح.'),
            'data' => [
                'hero' => $this->transformSlides($heroSlides, $isEnglish),
                'promo_primary' => $this->transformSlides($promoPrimarySlides, $isEnglish),
                'promo_secondary' => $this->transformSlides($promoSecondarySlides, $isEnglish),
            ],
        ], Response::HTTP_OK);
    }

    public function uiContent(): JsonResponse
    {
        $settings = Setting::query()
            ->whereIn('key', [
                'show_dashboard_notification',
                'dashboard_notification_content',
                'dashboard_notification_animation',
                'dashboard_notification_bg_color',
                'dashboard_notification_text_color',
                'show_welcome_screen',
                'welcome_screen_content',
            ])
            ->pluck('value', 'key');

        $topWindowEnabled = ($settings->get('show_dashboard_notification') ?? 'off') === 'on';
        $popupEnabled = ($settings->get('show_welcome_screen') ?? 'off') === 'on';

        $topWindowContent = (string) ($settings->get('dashboard_notification_content') ?? '');
        $popupContent = (string) ($settings->get('welcome_screen_content') ?? '');

        return response()->json([
            'success' => true,
            'message' => __('تم جلب محتوى الواجهة بنجاح.'),
            'data' => [
                'top_window' => [
                    'enabled' => $topWindowEnabled && $topWindowContent !== '',
                    'content_html' => $topWindowContent,
                    'content_text' => trim(strip_tags($topWindowContent)),
                    'animation' => (string) ($settings->get('dashboard_notification_animation') ?? 'none'),
                    'background_color' => $this->normalizeHexColor($settings->get('dashboard_notification_bg_color'), '#000000'),
                    'text_color' => $this->normalizeHexColor($settings->get('dashboard_notification_text_color'), '#FFFFFF'),
                ],
                'popup_notification' => [
                    'enabled' => $popupEnabled && $popupContent !== '',
                    'content_html' => $popupContent,
                    'content_text' => trim(strip_tags($popupContent)),
                ],
            ],
        ], Response::HTTP_OK);
    }

    public function sections(): JsonResponse
    {
        $sections = PrimaryCategory::query()
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->with([
                'children' => fn ($q) => $q->where('is_active', true)->withCount(['products' => fn ($p) => $p->where('is_active', true)]),
            ])
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->ordered()
            ->get()
            ->map(fn (PrimaryCategory $section) => [
                'id' => $section->id,
                'name_ar' => $section->name_ar,
                'name_en' => $section->name_en,
                'name' => $section->name_translated,
                'slug' => $section->slug,
                'image_url' => $section->image_url,
                'products_count' => (int) ($section->products_count ?? 0),
                'children' => $section->children->map(fn (PrimaryCategory $child) => [
                    'id' => $child->id,
                    'name_ar' => $child->name_ar,
                    'name_en' => $child->name_en,
                    'name' => $child->name_translated,
                    'slug' => $child->slug,
                    'image_url' => $child->image_url,
                    'products_count' => (int) ($child->products_count ?? 0),
                ])->values(),
            ])
            ->values();

        return response()->json([
            'success' => true,
            'message' => __('تم جلب الأقسام بنجاح.'),
            'data' => $sections,
        ], Response::HTTP_OK);
    }

    public function categories(Request $request): JsonResponse
    {
        $primaryCategoryId = $request->integer('primary_category_id');

        $categories = Category::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->with([
                'children' => function ($q) {
                    $q->where('is_active', true)
                        ->withCount(['products' => fn ($p) => $p->where('is_active', true)]);
                },
            ])
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->ordered()
            ->when($primaryCategoryId, function (Builder $query) use ($primaryCategoryId) {
                $query->whereHas('products.primaryCategories', function (Builder $q) use ($primaryCategoryId) {
                    $q->where('primary_categories.id', $primaryCategoryId);
                });
            })
            ->get()
            ->map(fn (Category $category) => [
                'id' => $category->id,
                'name_ar' => $category->name_ar,
                'name_en' => $category->name_en,
                'name' => $category->name_translated,
                'slug' => $category->slug,
                'image_url' => $category->image_url,
                'products_count' => (int) ($category->products_count ?? 0),
                'children' => $category->children->map(fn (Category $child) => [
                    'id' => $child->id,
                    'name_ar' => $child->name_ar,
                    'name_en' => $child->name_en,
                    'name' => $child->name_translated,
                    'slug' => $child->slug,
                    'image_url' => $child->image_url,
                    'products_count' => (int) ($child->products_count ?? 0),
                ])->values(),
            ])
            ->values();

        return response()->json([
            'success' => true,
            'message' => __('تم جلب الفئات بنجاح.'),
            'data' => $categories,
        ], Response::HTTP_OK);
    }

    public function products(Request $request): JsonResponse
    {
        $query = Product::query()
            ->where('is_active', true)
            ->with(['firstImage:id,product_id,image_path', 'category:id,name_ar,name_en,slug'])
            ->withCount('reviews');

        if ($request->filled('q')) {
            $keyword = trim((string) $request->input('q'));
            $query->where(function (Builder $q) use ($keyword) {
                $q->where('name_ar', 'like', "%{$keyword}%")
                    ->orWhere('name_en', 'like', "%{$keyword}%")
                    ->orWhere('description_ar', 'like', "%{$keyword}%")
                    ->orWhere('description_en', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', (int) $request->input('category_id'));
        }

        if ($request->filled('section_id')) {
            $sectionId = (int) $request->input('section_id');
            $query->whereHas('primaryCategories', function (Builder $q) use ($sectionId) {
                $q->where('primary_categories.id', $sectionId);
            });
        }

        if ($request->boolean('on_sale')) {
            $now = now();
            $query
                ->whereNotNull('sale_price')
                ->where('sale_price', '>', 0)
                ->where(function (Builder $q) use ($now) {
                    $q->whereNull('sale_starts_at')->orWhere('sale_starts_at', '<=', $now);
                })
                ->where(function (Builder $q) use ($now) {
                    $q->whereNull('sale_ends_at')->orWhere('sale_ends_at', '>=', $now);
                });
        }

        $sort = (string) $request->input('sort', 'latest');
        if ($sort === 'price_asc') {
            $query->orderByRaw('COALESCE(sale_price, price) asc');
        } elseif ($sort === 'price_desc') {
            $query->orderByRaw('COALESCE(sale_price, price) desc');
        } elseif ($sort === 'top_rated') {
            $query->orderByDesc('average_rating')->orderByDesc('reviews_count');
        } else {
            $query->latest();
        }

        $perPage = (int) $request->input('per_page', 20);
        $perPage = max(1, min($perPage, 60));

        $products = $query->paginate($perPage)->appends($request->query());

        $data = $products->through(function (Product $product) {
            $imagePath = $product->firstImage?->image_path;

            return [
                'id' => $product->id,
                'name_ar' => $product->name_ar,
                'name_en' => $product->name_en,
                'name' => $product->name_translated,
                'description_ar' => $product->description_ar,
                'description_en' => $product->description_en,
                'description' => $product->description_translated,
                'sku' => $product->sku,
                'price' => (float) $product->price,
                'sale_price' => $product->sale_price !== null ? (float) $product->sale_price : null,
                'current_price' => (float) $product->current_price,
                'is_on_sale' => $product->isOnSale(),
                'stock_quantity' => (int) $product->stock_quantity,
                'is_active' => (bool) $product->is_active,
                'average_rating' => (float) $product->average_rating,
                'reviews_count' => (int) $product->reviews_count,
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name_ar' => $product->category->name_ar,
                    'name_en' => $product->category->name_en,
                    'slug' => $product->category->slug,
                ] : null,
                'image_url' => $imagePath ? asset('storage/' . ltrim($imagePath, '/')) : null,
                'created_at' => optional($product->created_at)?->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => __('تم جلب المنتجات بنجاح.'),
            'data' => $data->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ], Response::HTTP_OK);
    }

    public function product($identifier): JsonResponse
    {
        $product = Product::query()
            ->where('is_active', true)
            ->with(['images', 'category', 'reviews', 'reviews.user:id,name,avatar'])
            ->withCount('reviews')
            ->where(function (Builder $query) use ($identifier) {
                $query->where('id', $identifier)
                      ->orWhere('slug', $identifier);
            })
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => __('المنتج غير موجود.'),
            ], Response::HTTP_NOT_FOUND);
        }

        $data = [
            'id' => $product->id,
            'name_ar' => $product->name_ar,
            'name_en' => $product->name_en,
            'name' => $product->name_translated,
            'description_ar' => $product->description_ar,
            'description_en' => $product->description_en,
            'description' => $product->description_translated,
            'sku' => $product->sku,
            'price' => (float) $product->price,
            'sale_price' => $product->sale_price !== null ? (float) $product->sale_price : null,
            'current_price' => (float) $product->current_price,
            'is_on_sale' => $product->isOnSale(),
            'stock_quantity' => (int) $product->stock_quantity,
            'is_active' => (bool) $product->is_active,
            'average_rating' => (float) $product->average_rating,
            'reviews_count' => (int) $product->reviews_count,
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name_ar' => $product->category->name_ar,
                'name_en' => $product->category->name_en,
                'slug' => $product->category->slug,
            ] : null,
            'images' => $product->images->map(fn($img) => [
                'id' => $img->id,
                'url' => asset('storage/' . ltrim($img->image_path, '/')),
                'is_primary' => (bool)$img->is_primary,
            ])->values(),
            'reviews' => $product->reviews->map(fn($review) => [
                'id' => $review->id,
                'user' => [
                    'id' => $review->user?->id,
                    'name' => $review->user?->name,
                    'avatar' => $review->user?->avatar ? asset('storage/' . ltrim($review->user->avatar, '/')) : null,
                ],
                'rating' => (float) $review->rating,
                'comment' => $review->comment,
                'created_at' => optional($review->created_at)->toIso8601String(),
            ])->values(),
            'created_at' => optional($product->created_at)?->toIso8601String(),
        ];

        return response()->json([
            'success' => true,
            'message' => __('تم جلب بيانات المنتج بنجاح.'),
            'data' => $data,
        ], Response::HTTP_OK);
    }

    public function discountCodes(Request $request): JsonResponse
    {
        $user = $request->user();

        $discounts = DiscountCode::query()
            ->where('is_active', true)
            ->where(function (Builder $q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->where(function (Builder $query) use ($user) {
                $query->whereIn('audience_mode', ['all', 'eligible']);

                if ($user) {
                    $query->orWhereHas('targetUsers', function (Builder $q) use ($user) {
                        $q->where('users.id', $user->id);
                    });
                }
            })
            ->latest()
            ->get()
            ->map(fn (DiscountCode $discount) => [
                'id' => $discount->id,
                'code' => $discount->code,
                'type' => $discount->type,
                'value' => (float) $discount->value,
                'max_discount_amount' => $discount->max_discount_amount !== null ? (float) $discount->max_discount_amount : null,
                'max_uses' => $discount->max_uses,
                'max_uses_per_user' => $discount->max_uses_per_user,
                'expires_at' => optional($discount->expires_at)?->toIso8601String(),
                'is_active' => (bool) $discount->is_active,
                'audience_mode' => $discount->audience_mode,
            ])
            ->values();

        return response()->json([
            'success' => true,
            'message' => __('تم جلب أكواد الخصم بنجاح.'),
            'data' => $discounts,
        ], Response::HTTP_OK);
    }

    public function notifications(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user || ! method_exists($user, 'notifications')) {
            return response()->json([
                'success' => false,
                'message' => __('المستخدم الحالي لا يدعم الإشعارات.'),
                'data' => [],
            ], Response::HTTP_FORBIDDEN);
        }

        $limit = (int) $request->input('limit', 20);
        $limit = max(1, min($limit, 100));

        $notifications = $user->notifications()
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn ($notification) => [
                'id' => $notification->id,
                'type' => $notification->type,
                'title' => data_get($notification->data, 'title'),
                'body' => data_get($notification->data, 'message', data_get($notification->data, 'body')),
                'data' => $notification->data,
                'read' => $notification->read_at !== null,
                'read_at' => optional($notification->read_at)?->toIso8601String(),
                'created_at' => optional($notification->created_at)?->toIso8601String(),
            ])
            ->values();

        return response()->json([
            'success' => true,
            'message' => __('تم جلب الإشعارات بنجاح.'),
            'data' => $notifications,
            'meta' => [
                'unread_count' => (int) $user->unreadNotifications()->count(),
            ],
        ], Response::HTTP_OK);
    }

    private function transformSlides($slides, bool $isEnglish): array
    {
        return collect($slides)
            ->values()
            ->map(function (HomepageSlide $slide) use ($isEnglish) {
                $title = $isEnglish && filled($slide->title_en) ? $slide->title_en : $slide->title;
                $subtitle = $isEnglish && filled($slide->subtitle_en) ? $slide->subtitle_en : $slide->subtitle;
                $buttonText = $isEnglish && filled($slide->button_text_en) ? $slide->button_text_en : $slide->button_text;

                return [
                    'id' => $slide->id,
                    'section' => $slide->section,
                    'title' => $title,
                    'subtitle' => $subtitle,
                    'button_text' => $buttonText,
                    'button_url' => $slide->button_url,
                    'click_type' => $slide->click_type,
                    'image_url' => $this->resolveSlideImage($slide, $isEnglish),
                    'alt_text' => $slide->alt_text,
                    'show_overlay' => (bool) ($slide->show_overlay ?? false),
                    'overlay_color' => $slide->overlay_color,
                    'overlay_strength' => $slide->overlay_strength !== null ? (float) $slide->overlay_strength : null,
                    'sort_order' => (int) ($slide->sort_order ?? 0),
                ];
            })
            ->all();
    }

    private function resolveSlideImage(HomepageSlide $slide, bool $isEnglish): ?string
    {
        $preferred = $isEnglish ? $slide->background_image_en : $slide->background_image;
        $fallback = $isEnglish ? $slide->background_image : $slide->background_image_en;
        $path = $preferred ?: $fallback;

        if (blank($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return asset('storage/' . ltrim((string) $path, '/'));
    }

    private function normalizeHexColor(mixed $value, string $default): string
    {
        $raw = trim((string) ($value ?? ''));
        if (preg_match('/^#(?:[0-9a-fA-F]{3}){1,2}$/', $raw) === 1) {
            return strtoupper($raw);
        }

        return $default;
    }
}
