<?php

namespace App\Models;

use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        } catch (QueryException $exception) {
            $message = strtolower($exception->getMessage());
            $isMissingIdDefault = ((string) ($exception->errorInfo[1] ?? '')) === '1364'
                && str_contains($message, 'activity_logs')
                && str_contains($message, "field 'id' doesn't have a default value");

            if ($isMissingIdDefault) {
                try {
                    static::insertWithManualId($attributes);
                    return;
                } catch (Throwable $repairException) {
                    Log::error('ActivityLog::record fallback insert failed', [
                        'message' => $repairException->getMessage(),
                        'attributes' => $attributes,
                    ]);
                    report($repairException);
                    return;
                }
            }

            Log::error('ActivityLog::record failed', [
                'message' => $exception->getMessage(),
                'attributes' => $attributes,
            ]);
            report($exception);
        } catch (Throwable $exception) {
            Log::error('ActivityLog::record failed', [
                'message' => $exception->getMessage(),
                'attributes' => $attributes,
            ]);
            report($exception);
        }
    }

    private static function insertWithManualId(array $attributes): void
    {
        $now = now();

        // لا نعتمد على AUTO_INCREMENT على بعض السيرفرات: نحدد id يدويًا.
        $nextId = ((int) DB::table('activity_logs')->max('id')) + 1;

        DB::table('activity_logs')->insert([
            'id' => max($nextId, 1),
            'user_id' => $attributes['user_id'] ?? null,
            'loggable_id' => $attributes['loggable_id'] ?? null,
            'loggable_type' => $attributes['loggable_type'] ?? null,
            'action' => $attributes['action'] ?? 'admin_action',
            'before' => array_key_exists('before', $attributes) ? json_encode($attributes['before'], JSON_UNESCAPED_UNICODE) : null,
            'after' => array_key_exists('after', $attributes) ? json_encode($attributes['after'], JSON_UNESCAPED_UNICODE) : null,
            'ip_address' => $attributes['ip_address'] ?? null,
            'user_agent' => $attributes['user_agent'] ?? null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
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
