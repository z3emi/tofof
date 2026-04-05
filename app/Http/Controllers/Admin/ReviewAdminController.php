<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ReviewAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductReview::query()->with(['product:id,name_ar,name_en', 'user:id,name,avatar']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
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
        ];

        return view('admin.reviews.index', compact('reviews', 'counts'));
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
}
