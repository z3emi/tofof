<?php

namespace App\Models;

use App\Support\Currency;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Models\Manager;

class InternalTransfer extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'reference',
        'transfer_date',
        'source_type',
        'source_id',
        'destination_type',
        'destination_id',
        'currency_code',
        'currency_amount',
        'exchange_rate',
        'system_amount',
        'notes',
        'manager_id',
        'journal_entry_id',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'currency_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'system_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function destination(): MorphTo
    {
        return $this->morphTo();
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Manager::class, 'created_by');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Manager::class)->withTrashed();
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Manager::class, 'approved_by')->withTrashed();
    }

    public function getCurrencyAmountFormattedAttribute(): string
    {
        return Currency::formatForCurrency($this->currency_amount, $this->currency_code);
    }

    public function getSystemAmountFormattedAttribute(): string
    {
        return Currency::format($this->system_amount);
    }

    public function getSourceLabelAttribute(): string
    {
        return $this->formatParticipantLabel($this->source);
    }

    public function getDestinationLabelAttribute(): string
    {
        return $this->formatParticipantLabel($this->destination);
    }

    public function getSourceTypeLabelAttribute(): string
    {
        return $this->formatParticipantType($this->source);
    }

    public function getDestinationTypeLabelAttribute(): string
    {
        return $this->formatParticipantType($this->destination);
    }

    protected function formatParticipantLabel($participant): string
    {
        if ($participant instanceof CashAccount) {
            return $participant->name;
        }

        if ($participant instanceof Manager) {
            return $participant->name;
        }

        return __('غير معروف');
    }

    protected function formatParticipantType($participant): string
    {
        if ($participant instanceof CashAccount) {
            return __('قاصة / بنك');
        }

        if ($participant instanceof Manager) {
            return __('مندوب مبيعات');
        }

        return __('غير محدد');
    }
}
