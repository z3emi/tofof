<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory, LogsActivity;

    public const SALE_TYPE_CASH            = 'cash';
    public const SALE_TYPE_CREDIT          = 'credit';
    public const SALE_TYPE_PARTIAL_PAYMENT = 'partial_payment';
    public const SALE_TYPE_QUOTATION       = 'quotation';

    public const SALE_TYPES = [
        self::SALE_TYPE_CASH,
        self::SALE_TYPE_CREDIT,
        self::SALE_TYPE_PARTIAL_PAYMENT,
        self::SALE_TYPE_QUOTATION,
    ];

    protected $fillable = [
        'number',
        'invoice_date',
        'payment_type',
        'sale_type',
        'customer_id',
        'subtotal',
        'tax_total',
        'total',
        'amount_paid',
        'manager_id',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    protected $appends = ['remaining_due'];

    protected $attributes = [
        'sale_type' => self::SALE_TYPE_CASH,
    ];

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Manager::class);
    }

    public static function saleTypeLabels(): array
    {
        return [
            self::SALE_TYPE_CASH            => 'بيع نقدي',
            self::SALE_TYPE_CREDIT          => 'بيع آجل',
            self::SALE_TYPE_PARTIAL_PAYMENT => 'بيع بسداد جزئي',
            self::SALE_TYPE_QUOTATION       => 'عرض سعر',
        ];
    }

    public function getSaleTypeLabelAttribute(): string
    {
        return self::saleTypeLabels()[$this->sale_type] ?? $this->sale_type;
    }

    public function getRemainingDueAttribute(): float
    {
        $remaining = (float) $this->total - (float) $this->amount_paid;

        return max(0, round($remaining, 2));
    }

    public function refreshPaymentTotals(): void
    {
        $totalPaid = $this->payments()->sum('amount');
        $this->forceFill(['amount_paid' => $totalPaid])->saveQuietly();
    }
}
