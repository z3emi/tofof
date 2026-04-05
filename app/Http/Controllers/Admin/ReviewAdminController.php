<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use App\Support\RepairsPrimaryKeyAutoIncrement;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ReviewAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductReview::query()->with(['product:id,name_ar,name_en', 'user:id,name,avatar']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('homepage')) {
            $homepage = $request->string('homepage')->toString();
            if ($homepage === 'shown') {
                $query->where('show_on_homepage', true);
            } elseif ($homepage === 'hidden') {
                $query->where(function ($q) {
                    $q->where('show_on_homepage', false)
                      ->orWhereNull('show_on_homepage');
                });
            }
        }

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $query->where(function ($q) use ($search) {
                $q->where('comment', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('product', function ($p) use ($search) {
                        $p->where('name_ar', 'like', "%{$search}%")
                            ->orWhere('name_en', 'like', "%{$search}%");
                    });
            });
        }

        $reviews = $query->latest()->paginate(20)->withQueryString();

        $counts = [
            'all' => ProductReview::count(),
            'approved' => ProductReview::where('status', 'approved')->count(),
            'pending' => ProductReview::where('status', 'pending')->count(),
            'rejected' => ProductReview::where('status', 'rejected')->count(),
            'homepage_shown' => ProductReview::where('show_on_homepage', true)->count(),
        ];

        $products = Product::query()
            ->select(['id', 'name_ar', 'name_en'])
            ->latest('id')
            ->take(200)
            ->get();

        return view('admin.reviews.index', compact('reviews', 'counts', 'products'));
    }

    public function storeFake(Request $request)
    {
        $this->ensureReviewPermission();

        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'fake_name' => ['required', 'string', 'max:120'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'max:2000'],
            'status' => ['nullable', 'in:approved,pending,rejected'],
            'show_on_homepage' => ['nullable', 'boolean'],
        ]);

        $user = $this->createSyntheticReviewer((string) $validated['fake_name']);

        $status = (string) ($validated['status'] ?? 'approved');
        $showOnHomepage = (bool) ($validated['show_on_homepage'] ?? false);

        ProductReview::create([
            'product_id' => (int) $validated['product_id'],
            'user_id' => (int) $user->id,
            'rating' => (int) $validated['rating'],
            'comment' => trim((string) $validated['comment']),
            'status' => $status,
            'show_on_homepage' => $showOnHomepage && $status === 'approved',
        ]);

        return back()->with('success', 'تم إنشاء التعليق الوهمي بنجاح.');
    }

    // حذف أي تعليق/تقييم من الأدمن
    public function destroy(Product $product, ProductReview $review)
    {
        abort_unless($review->product_id === $product->id, 404);

        $this->ensureReviewPermission();

        $review->delete();

        $this->refreshProductReviewStats($product);

        return back()->with('success', 'تم حذف التعليق بنجاح.');
    }

    // إضافة/تحديث رد الأدمن على التعليق
    public function reply(Request $request, Product $product, ProductReview $review)
    {
        abort_unless($review->product_id === $product->id, 404);

        $this->ensureReviewPermission();

        if (! Schema::hasColumn('product_reviews', 'admin_reply')) {
            return back()->with('error', 'حقل رد الأدمن غير موجود. يرجى تشغيل الترحيلات (migrations) أولاً.');
        }

        $validated = $request->validate([
            'admin_reply' => ['nullable', 'string', 'max:2000'],
        ]);

        $reply = trim((string) ($validated['admin_reply'] ?? ''));
        $review->admin_reply = $reply !== '' ? $reply : null;
        $review->save();

        return back()->with('success', 'تم حفظ رد الأدمن بنجاح.');
    }

    // حذف رد الأدمن فقط بدون حذف التعليق
    public function destroyReply(Product $product, ProductReview $review)
    {
        abort_unless($review->product_id === $product->id, 404);

        $this->ensureReviewPermission();

        if (! Schema::hasColumn('product_reviews', 'admin_reply')) {
            return back()->with('error', 'حقل رد الأدمن غير موجود. يرجى تشغيل الترحيلات (migrations) أولاً.');
        }

        $review->admin_reply = null;
        $review->save();

        return back()->with('success', 'تم حذف رد الأدمن بنجاح.');
    }

    public function updateStatus(Request $request, ProductReview $review)
    {
        $this->ensureReviewPermission();

        $validated = $request->validate([
            'status' => ['required', 'in:approved,pending,rejected'],
        ]);

        $review->status = $validated['status'];
        $review->save();

        if ($review->product) {
            $this->refreshProductReviewStats($review->product);
        }

        return back()->with('success', 'تم تحديث حالة التعليق بنجاح.');
    }

    public function toggleFeatured(Request $request, ProductReview $review)
    {
        $this->ensureReviewPermission();

        if (! Schema::hasColumn('product_reviews', 'show_on_homepage')) {
            return back()->with('error', 'حقل عرض التعليق في الواجهة غير موجود. يرجى تشغيل الترحيلات (migrations) أولاً.');
        }

        $validated = $request->validate([
            'show_on_homepage' => ['required', 'boolean'],
        ]);

        $showOnHomepage = (bool) $validated['show_on_homepage'];
        if ($showOnHomepage && $review->status !== 'approved') {
            return back()->with('error', 'يمكن عرض التعليقات المنشورة فقط في الواجهة.');
        }

        $review->show_on_homepage = $showOnHomepage;
        $review->save();

        return back()->with('success', $showOnHomepage
            ? 'تمت إضافة التعليق لقسم آراء العملاء في الواجهة.'
            : 'تمت إزالة التعليق من قسم آراء العملاء في الواجهة.');
    }

    private function ensureReviewPermission(): void
    {
        $user = auth('admin')->user() ?? auth()->user();
        $isSuperAdmin = $user && method_exists($user, 'hasRole') && $user->hasRole('super-admin');
        $canManage = $user && ($user->can('delete-reviews') || $user->can('manage-reviews') || $isSuperAdmin);

        abort_unless($canManage, 403);
    }

    private function refreshProductReviewStats(Product $product): void
    {
        $agg = $product->reviews()
            ->where('status', 'approved')
            ->selectRaw('AVG(rating) as avg, COUNT(*) as cnt')
            ->first();

        if (Schema::hasColumn('products', 'average_rating') && Schema::hasColumn('products', 'reviews_count')) {
            $product->forceFill([
                'average_rating' => round((float) ($agg->avg ?? 0), 2),
                'reviews_count'  => (int) ($agg->cnt ?? 0),
            ])->save();
        }
    }

    private function createSyntheticReviewer(string $name): User
    {
        $safeName = trim($name) !== '' ? trim($name) : 'عميل';

        $phone = $this->generateUniquePhoneNumber();

        $payload = [
            'name' => $safeName,
            'email' => null,
            'phone_number' => $phone,
            'type' => 'user',
            'phone_verified_at' => now(),
            'password' => Hash::make(Str::random(32)),
        ];

        try {
            return User::create($payload);
        } catch (QueryException $exception) {
            if (! RepairsPrimaryKeyAutoIncrement::isMissingAutoIncrementError($exception, 'users')) {
                throw $exception;
            }

            RepairsPrimaryKeyAutoIncrement::ensure('users');

            return User::create($payload);
        }
    }

    private function generateUniquePhoneNumber(): string
    {
        do {
            $phone = '79' . str_pad((string) random_int(1, 99999999), 8, '0', STR_PAD_LEFT);
        } while (User::withTrashed()->where('phone_number', $phone)->exists());

        return $phone;
    }
}
