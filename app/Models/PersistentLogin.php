<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersistentLogin extends Model
{
    protected $fillable = [
        'user_id','selector','validator_hash','ip','user_agent','last_used_at'
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
    ];
}
