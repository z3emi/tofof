<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\LogsActivity;
use Illuminate\Support\Str;
use App\Models\ProductReview;
use App\Models\WalletTransaction;
use App\Models\Setting;
use NotificationChannels\WebPush\HasPushSubscriptions;
use Illuminate\Support\Facades\Storage;

use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, LogsActivity, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'password',
        'type',
        'banned_at',
        'governorate',
        'city',
        'address',
        'latitude',
        'longitude',
        'whatsapp_otp',
        'whatsapp_otp_expires_at',
        'phone_verified_at',
        'avatar',
        'referral_code',
        'referred_by',
        'referral_reward_claimed',
        'referrer_bonus_awarded',
        'wallet_balance',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'whatsapp_otp',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at'       => 'datetime',
        'password'                => 'hashed',
        'banned_at'               => 'datetime',
        'whatsapp_otp_expires_at' => 'datetime',
        'phone_verified_at'       => 'datetime',
        // 👇 إضافات المحفظة
        'wallet_balance'          => 'decimal:2',
        'wallet_notify_on_change' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function booted()
    {
        static::creating(function ($user) {
            if (empty($user->referral_code)) {
                $user->referral_code = self::generateUniqueReferralCode();
            }
        });
    }

    /**
     * Generate a unique referral code.
     */
    public static function generateUniqueReferralCode()
    {
        do {
            $code = Str::upper(Str::random(8));
        } while (self::where('referral_code', $code)->exists());

        return $code;
    }

    /**
     * Get the user who referred this user.
     */
    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    /**
     * Get the orders for the user.
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }



    /**
     * Get the user's favorites.
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'user_id');
    }

    /**
     * Check if the user has favorited a product.
     */
    public function hasFavorited($product)
    {
        return $this->favorites()
            ->where('product_id', $product->id)
            ->exists();
    }

    /**
     * Get the user's addresses.
     */
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function getTierAttribute()
    {
        $settings = Setting::whereIn('key', ['tier_bronze_orders', 'tier_silver_orders', 'tier_gold_orders'])->pluck('value', 'key');
        
        $bronzeMin = $settings['tier_bronze_orders'] ?? 5;
        $silverMin = $settings['tier_silver_orders'] ?? 8;
        $goldMin   = $settings['tier_gold_orders'] ?? 10;

        $deliveredOrdersCount = $this->orders()->where('status', 'delivered')->count();

        if ($deliveredOrdersCount >= $goldMin) {
            return 'Gold';
        } elseif ($deliveredOrdersCount >= $silverMin) {
            return 'Silver';
        } elseif ($deliveredOrdersCount >= $bronzeMin) {
            return 'Bronze';
        }
    }

    public function productReviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    /**
     * معاملات المحفظة (أحدث أولًا)
     */
    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class, 'user_id')->latest();
    }
    public function getAvatarUrlAttribute(): string
{
    $val = $this->avatar;

    // لو مخزّن URL كامل (طرف ثالث مثلاً)
    if (!empty($val) && str_starts_with($val, 'http')) {
        return $val;
    }

    // لو مخزّن مسار نسبي مثل "avatars/xxx.jpg"
    if (!empty($val) && Storage::disk('public')->exists($val)) {
        // مهم: نستخدم asset('storage/...') حتى يحترم ASSET_URL=/public
        return asset('storage/'.$val);
    }

    // Fallback للديفولت داخل storage/app/public/avatars/default.jpg
    return asset('storage/avatars/default.jpg');
}
}
