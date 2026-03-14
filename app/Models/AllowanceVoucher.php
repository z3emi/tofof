<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AllowanceVoucher extends Model
{
    use HasFactory, LogsActivity;

    public const TYPE_INCREASE = 'increase';
    public const TYPE_DECREASE = 'decrease';

    protected $fillable = [
        'number',
        'voucher_date',
        'customer_id',
        'manager_id',
        'type',
        'amount',
        'description',
        'customer_transaction_id',
    ];

    protected $casts = [
        'voucher_date' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public static function typeLabels(): array
    {
        return [
            self::TYPE_INCREASE => __('سند سماح له (إضافة دين)'),
            self::TYPE_DECREASE => __('سند سماح عليه (إسقاط دين)'),
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Manager::class)->withTrashed();
    }

    public function customerTransaction(): BelongsTo
    {
        return $this->belongsTo(CustomerTransaction::class);
    }
}
