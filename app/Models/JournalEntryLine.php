<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntryLine extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'manager_id',
        'customer_id',
        'description',
        'currency_code',
        'currency_debit',
        'currency_credit',
        'exchange_rate',
        'debit',
        'credit',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'currency_debit' => 'decimal:2',
        'currency_credit' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
    ];

    public function entry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Manager::class)->withTrashed();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }
}
