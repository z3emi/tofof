<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function user()
    {
        // سيعرض "مستخدم محذوف" إن لم يعد موجودًا
        return $this->belongsTo(User::class)->withDefault(['name' => 'مستخدم محذوف']);
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
