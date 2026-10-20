<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackingSession extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function visitor()
    {
        return $this->belongsTo(TrackingVisitor::class, 'tracking_visitor_id', 'visitor_id');
    }

    public function events()
    {
        return $this->hasMany(TrackingEvent::class, 'tracking_session_id', 'session_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
