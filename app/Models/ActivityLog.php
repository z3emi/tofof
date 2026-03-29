<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'loggable_id',
        'loggable_type',
        'action',        // created, updated, deleted, login, logout, failed_login, ...
        'before',
        'after',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'before' => 'array',
        'after'  => 'array',
    ];

    public static function record(array $attributes): void
    {
        try {
            static::create($attributes);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    public function user()
    {
        // سيعرض "مدير" إن لم يعد موجودًا (تغير من مستخدم محذوف بناء على طلب المستخدم)
        // تم تغييرها لـ Manager لأن جدول activity_logs.user_id يشير لجدول managers في قواعد البيانات
        return $this->belongsTo(Manager::class)->withDefault(['name' => 'مدير']);
    }

    public function loggable()
    {
        return $this->morphTo();
    }

    /** اسم الصنف بدون الـ namespace (للعرض والفلاتر) */
    public function getTypeBaseNameAttribute(): string
    {
        return class_basename($this->loggable_type ?? '');
    }
}
