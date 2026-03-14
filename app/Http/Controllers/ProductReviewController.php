<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class ProductReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        $review = ProductReview::updateOrCreate(
            [
                'product_id' => $product->id,
                'user_id'    => Auth::id(),
            ],
            [
                'rating'     => $validated['rating'],
                'comment'    => $validated['comment'] ?? null,
                'status'     => 'approved',
            ]
        );

        // احسب واحفظ المتوسط/العدد في المنتج إذا الأعمدة موجودة
        $avg   = round($product->reviews()->where('status','approved')->avg('rating') ?? 0, 2);
        $count = (int) $product->reviews()->where('status','approved')->count();

        if (Schema::hasColumn('products', 'average_rating') && Schema::hasColumn('products', 'reviews_count')) {
            $product->forceFill([
                'average_rating' => $avg,
                'reviews_count'  => $count,
            ])->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ تقييمك بنجاح.',
            'review'  => [
                'id'               => $review->id,
                'user_name'        => $review->user->name ?? 'أنتِ',
                'rating'           => (int) $review->rating,
                'comment'          => $review->comment,
                'created_at_human' => $review->created_at->diffForHumans(),
            ],
            'stats' => [
                'avg'   => $avg,
                'count' => $count,
            ],
        ]);
    }

    public function destroy(Product $product, ProductReview $review)
    {
        // صاحب التعليق أو أدمن
        if ($review->user_id !== Auth::id() && !Auth::user()?->can('delete-reviews') &&
            !(method_exists(Auth::user(), 'hasRole') && Auth::user()->hasRole('super-admin'))) {
            abort(403);
        }

        if ($review->product_id !== $product->id) {
            abort(404);
        }

        $review->delete();

        // أعِد حساب المتوسط/العدد
        $avg   = round($product->reviews()->where('status','approved')->avg('rating') ?? 0, 2);
        $count = (int) $product->reviews()->where('status','approved')->count();

        if (Schema::hasColumn('products', 'average_rating') && Schema::hasColumn('products', 'reviews_count')) {
            $product->forceFill([
                'average_rating' => $avg,
                'reviews_count'  => $count,
            ])->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'تم حذف تعليقك.',
            'stats'   => ['avg' => $avg, 'count' => $count],
        ]);
    }
}
