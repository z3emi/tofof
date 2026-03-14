<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Favorite;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // جلب المفضلة مع تحميل بيانات المنتج والصورة الأولى لكل منتج بكفاءة
        $favorites = $user->favorites()->with('product.firstImage')->get();
        
        // نحتاج إلى قائمة IDs للمنتجات المفضلة لتمريرها إلى بطاقة المنتج
        $favoriteProductIds = $favorites->pluck('product_id')->toArray();

        return view('frontend.profile.wishlist', compact('favorites', 'favoriteProductIds'));
    }

    public function toggle(Request $request, $productId)
    {
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'الرجاء تسجيل الدخول أولاً.'], 401);
            }
            return redirect()->route('login');
        }

        $userId = Auth::id();
        $favorite = Favorite::where('user_id', $userId)->where('product_id', $productId)->first();
        $wasAdded = false;

        if ($favorite) {
            $favorite->delete();
            $message = 'تمت الإزالة من المفضلة.';
        } else {
            Favorite::create(['user_id' => $userId, 'product_id' => $productId]);
            $message = 'تمت الإضافة إلى المفضلة بنجاح!';
            $wasAdded = true;
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'was_added' => $wasAdded,
                'wishlistCount' => Auth::user()->favorites()->count(),
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    public function count()
    {
        $count = Auth::check() ? Auth::user()->favorites()->count() : 0;
        return response()->json(['count' => $count]);
    }
}
