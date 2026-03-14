<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\CashBox;
use App\Models\Customer;
use App\Models\JournalEntry;
use App\Models\Manager;
use App\Models\Order;

class ReceiptVoucher extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'number',
        'voucher_date',
        'cash_box_id',
        'customer_id',
        'manager_id',
        'approved_by',
        'approved_at',
        'journal_entry_id',
        'receiver_type',
        'amount',
        'description',
        'transaction_channel',
        'order_id',
    ];

    protected $casts = [
        'voucher_date' => 'date',
        'amount' => 'decimal:2',
        'currency_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'approved_at' => 'datetime',
    ];

    public function cashBox(): BelongsTo
    {
        return $this->belongsTo(CashBox::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Manager::class)->withTrashed();
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Manager::class, 'approved_by')->withTrashed();
    }
}
