<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackingEvent extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function visitor()
    {
        return $this->belongsTo(TrackingVisitor::class, 'tracking_visitor_id', 'visitor_id');
    }

    public function session()
    {
        return $this->belongsTo(TrackingSession::class, 'tracking_session_id', 'session_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
