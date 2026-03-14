<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseInvoiceItem extends Model
{
    use HasFactory;

    /**
     * تم إضافة quantity_remaining هنا للسماح بحفظها وتحديثها
     */
    protected $fillable = [
        'purchase_invoice_id',
        'product_id',
        'quantity',
        'purchase_price',
        'quantity_remaining', // <-- الإضافة هنا
    ];

    /**
     * العلاقة مع فاتورة الشراء
     */
    public function purchaseInvoice()
    {
        return $this->belongsTo(PurchaseInvoice::class);
    }

    /**
     * العلاقة مع المنتج
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
