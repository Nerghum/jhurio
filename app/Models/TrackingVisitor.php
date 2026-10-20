<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackingVisitor extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    public function sessions()
    {
        return $this->hasMany(TrackingSession::class, 'tracking_visitor_id', 'visitor_id');
    }

    public function events()
    {
        return $this->hasMany(TrackingEvent::class, 'tracking_visitor_id', 'visitor_id');
    }
}
