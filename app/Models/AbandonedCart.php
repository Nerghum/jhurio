<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbandonedCart extends Model
{
    protected $fillable = [
        'session_id', 'phone', 'name', 'address', 'district', 'cart_data', 'user_id',
    ];

    protected $casts = [
        'cart_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
