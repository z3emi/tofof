<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class CustomerTierController extends Controller
{
    /**
     * عرض صفحة إعدادات فئات العملاء (برونز/فضة/ذهب)
     */
    public function index()
    {
        $settings = Setting::whereIn('key', [
            'tier_bronze_orders',
            'tier_silver_orders',
            'tier_gold_orders',
        ])->pluck('value', 'key');

        return view('admin.customer_tiers.index', compact('settings'));
    }

    /**
     * تحديث عتبات الطلبات لكل فئة
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'tier_bronze_orders' => 'required|integer|min:1',
            'tier_silver_orders' => 'required|integer|min:1',
            'tier_gold_orders'   => 'required|integer|min:1',
        ]);

        // حفظ القيم
        Setting::updateOrCreate(['key' => 'tier_bronze_orders'], ['value' => $data['tier_bronze_orders']]);
        Setting::updateOrCreate(['key' => 'tier_silver_orders'], ['value' => $data['tier_silver_orders']]);
        Setting::updateOrCreate(['key' => 'tier_gold_orders'],   ['value' => $data['tier_gold_orders']]);

        // مسح الكاش المستخدم في Customer@getTierAttribute
        Cache::forget('customer_tier_thresholds');

        return redirect()->back()->with('success', 'تم تحديث إعدادات فئات العملاء بنجاح.');
    }
}
