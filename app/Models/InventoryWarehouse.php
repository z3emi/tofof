<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryWarehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'location',
        'notes',
    ];

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseInvoiceItem::class, 'warehouse_id');
    }
}
