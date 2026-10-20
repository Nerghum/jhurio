<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'shipping_address',
        'total_amount',
        'discount_amount',
        'coupon_code',
        'status',
        'delivery_method_id',
        'delivery_cost',
    ];

    public function deliveryMethod()
    {
        return $this->belongsTo(DeliveryMethod::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function logs()
    {
        return $this->hasMany(OrderLog::class)->latest();
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
