<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentVoucher extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'number',
        'voucher_date',
        'cash_account_id',
        'expense_account_id',
        'amount',
        'currency_code',
        'currency_amount',
        'exchange_rate',
        'description',
        'manager_id',
        'approved_by',
        'approved_at',
        'journal_entry_id',
    ];

    protected $casts = [
        'voucher_date' => 'date',
        'amount' => 'decimal:2',
        'currency_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'approved_at' => 'datetime',
    ];

    public function cashAccount(): BelongsTo
    {
        return $this->belongsTo(CashAccount::class);
    }

    public function expenseAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'expense_account_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Manager::class)->withTrashed();
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Manager::class, 'approved_by')->withTrashed();
    }
}
