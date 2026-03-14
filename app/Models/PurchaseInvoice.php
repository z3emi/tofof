<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class PurchaseInvoice extends Model
{
    use HasFactory, LogsActivity;
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'invoice_number',
        'invoice_date',
        'total_amount',
        'notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
    ];

    /**
     * العلاقة مع المورد
     * كل فاتورة شراء تنتمي إلى مورد واحد
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * العلاقة مع بنود الفاتورة
     * كل فاتورة شراء تحتوي على العديد من بنود المنتجات
     */
    public function items()
    {
        return $this->hasMany(PurchaseInvoiceItem::class);
    }
}
