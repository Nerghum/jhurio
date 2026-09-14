<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'media_id'];

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    public function media()
    {
        return $this->belongsTo(Media::class);
    }
}
