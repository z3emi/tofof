<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use Illuminate\Support\Facades\Schema;

class ReviewAdminController extends Controller
{
    // حذف أي تعليق/تقييم من الأدمن
    public function destroy(ProductReview $review)
    {
        $user = auth()->user();

        // صلاحيات بسيطة: يمتلك صلاحية delete-reviews أو عنده دور super-admin (Spatie)
        $isSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('super-admin');
        $canDelete = $user->can('delete-reviews') || $isSuperAdmin;

        abort_unless($canDelete, 403);

        $product = $review->product;
        $review->delete();

        // إعادة حساب المتوسط/العدد
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

        return back()->with('success', 'تم حذف التعليق بنجاح.');
    }
}
