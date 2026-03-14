<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashBox extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'currency',
        'balance',
        'is_primary',
        'notes',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'is_primary' => 'boolean',
    ];

    public const TYPE_CASH = 'cash';
    public const TYPE_BANK = 'bank';
    public const TYPE_WALLET = 'wallet';

    public static function types(): array
    {
        return [
            self::TYPE_CASH => 'صندوق نقدي',
            self::TYPE_BANK => 'حساب بنكي',
            self::TYPE_WALLET => 'محفظة إلكترونية',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CashBoxTransaction::class);
    }

    public function receiptVouchers(): HasMany
    {
        return $this->hasMany(ReceiptVoucher::class);
    }

    public function depositVouchers(): HasMany
    {
        return $this->hasMany(DepositVoucher::class);
    }
}
