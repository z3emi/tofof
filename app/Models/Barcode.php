<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barcode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'target_url',
        'is_active',
        'hits',
        'last_hit_at',
        'last_ip',
        'last_user_agent',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'last_hit_at' => 'datetime',
    ];

    /**
     * تنظيف/توحيد الكود عند الحفظ.
     */
    public function setCodeAttribute($value): void
    {
        // نسمح فقط بالأحرف والأرقام والشرطة والشرطة السفلية والنقطة
        $clean = preg_replace('/[^A-Z0-9\-\._]/i', '', (string) $value);
        $this->attributes['code'] = strtoupper($clean);
    }

    /**
     * رابط التحويل العام ( /b/{code} )
     */
    public function getPublicUrlAttribute(): string
    {
        try {
            return route('barcode.go', ['code' => $this->code], true);
        } catch (\Throwable $e) {
            return url('/b/' . $this->code);
        }
    }

    /**
     * رابط صورة الـ QR  ( /b/{code}.png )
     */
    public function getQrUrlAttribute(): string
    {
        try {
            return route('barcode.qr', ['code' => $this->code], true);
        } catch (\Throwable $e) {
            return url('/b/' . $this->code . '.png');
        }
    }
}
