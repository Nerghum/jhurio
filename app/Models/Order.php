<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'tracking_visitor_id',
        'tracking_session_id',
        'ip_address',
        'user_agent',
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

    public function trackingVisitor()
    {
        return $this->belongsTo(TrackingVisitor::class, 'tracking_visitor_id', 'visitor_id');
    }

    public function trackingSession()
    {
        return $this->belongsTo(TrackingSession::class, 'tracking_session_id', 'session_id');
    }
}
