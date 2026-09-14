<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'price', 'compare_at_price', 'image_url', 'content', 'status', 'stock_quantity', 'thumbnail_media_id', 'video_url', 'options', 'is_featured'];

    protected $casts = [
        'options' => 'array',
        'is_featured' => 'boolean',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function thumbnail()
    {
        return $this->belongsTo(Media::class, 'thumbnail_media_id');
    }

    public function gallery()
    {
        return $this->belongsToMany(Media::class, 'product_media')->withPivot('sort_order')->orderBy('sort_order');
    }

    public function variations()
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function averageRating()
    {
        return $this->reviews()->where('status', 'approved')->avg('rating') ?? 0;
    }

    public function reviewsCount()
    {
        return $this->reviews()->where('status', 'approved')->count();
    }
}
