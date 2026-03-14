<?php
// ======================================================================
// الملف: app/Models/Customer.php (محدث)
// ======================================================================
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

// === إضافات مطلوبة لحساب الفئات + كاش + جلب الإعدادات ===
use Illuminate\Support\Facades\Cache;
use App\Models\Setting;

class Customer extends Model
{
    use HasFactory, LogsActivity;
    use HasFactory;

    /**
     * تم إضافة user_id هنا
     */
    protected $fillable = [
        'user_id', 
        'name', 
        'phone_number', 
        'email', 
        'governorate', 
        'city', 
        'address_details',
        'notes'
    ];

    // (اختياري) نخلي الفئة و الكلاس تظهر إذا نحول الموديل Array/JSON
    protected $appends = ['tier', 'tier_class'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function addresses()
    {
        return $this->hasMany(\App\Models\Address::class);
    }

    /**
     * جلب عتبات الفئات من جدول settings مع كاش 10 دقائق
     */
    protected function tierThresholds(): array
    {
        return Cache::remember('customer_tier_thresholds', 600, function () {
            // قيم افتراضية إذا ما موجودة بالسيتنجز
            $bronze = (int) (Setting::where('key', 'tier_bronze_orders')->value('value') ?? 5);
            $silver = (int) (Setting::where('key', 'tier_silver_orders')->value('value') ?? 8);
            $gold   = (int) (Setting::where('key', 'tier_gold_orders')->value('value') ?? 10);

            return [
                'bronze' => $bronze,
                'silver' => $silver,
                'gold'   => $gold,
            ];
        });
    }

    /**
     * Accessor: tier => Gold / Silver / Bronze / null
     * يعتمد على حالة 'delivered' مثل الكنترولر
     */
    public function getTierAttribute(): ?string
    {
        $ordersCount = $this->orders_count
            ?? $this->orders()->where('status', 'delivered')->count();

        $t = $this->tierThresholds();

        if ($ordersCount >= $t['gold'])   return 'Gold';
        if ($ordersCount >= $t['silver']) return 'Silver';
        if ($ordersCount >= $t['bronze']) return 'Bronze';
        return null;
    }

    /**
     * Accessor: tier_class — لتلوين الصف حسب الفئة
     */
    public function getTierClassAttribute(): ?string
    {
        return match ($this->tier) {
            'Gold'   => 'table-row-gold',
            'Silver' => 'table-row-silver',
            'Bronze' => 'table-row-bronze',
            default  => null,
        };
    }
}
