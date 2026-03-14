<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class OrderTrackingController extends Controller
{
    /**
     * عرض صفحة فورم تتبع الطلب.
     */
    public function showTrackingForm()
    {
        return view('frontend.tracking.index');
    }

    /**
     * البحث عن الطلب وعرض حالته.
     */
    public function trackOrder(Request $request)
    {
        // التحقق من صحة المدخلات
        $request->validate([
            'order_id' => 'required|integer',
            'phone_number' => 'required|string',
        ]);

        // البحث عن الطلب الذي يتطابق رقمه مع رقم هاتف العميل
        $order = Order::where('id', $request->order_id)
                      ->whereHas('customer', function ($query) use ($request) {
                          $query->where('phone_number', $request->phone_number);
                      })
                      ->first();

        // إذا لم يتم العثور على الطلب، يتم إعادة التوجيه مع رسالة خطأ
        if (!$order) {
            return redirect()->back()->with('error', 'لم يتم العثور على طلب بهذه التفاصيل. يرجى التأكد من المعلومات المدخلة.');
        }

        // إذا تم العثور على الطلب، يتم عرضه في نفس الصفحة
        return view('frontend.tracking.index', compact('order'));
    }
}
