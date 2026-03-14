<?php
// app/Models/ProductRequest.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductRequest extends Model
{
    protected $fillable = [
        'user_id','product_name','brand','link','phone','notes','status',
    ];

    public function user(){ return $this->belongsTo(User::class); }
}
