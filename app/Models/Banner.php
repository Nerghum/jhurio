<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'type',
        'media_id',
        'link_url',
        'is_active',
        'order',
    ];

    public function media()
    {
        return $this->belongsTo(Media::class);
    }
}
