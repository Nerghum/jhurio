<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'price', 'compare_at_price', 'image_url', 'content', 'status', 'stock_quantity', 'thumbnail_media_id', 'video_url', 'video_reels', 'options', 'is_featured', 'bundle_settings', 'image_text_sections'];

    protected $casts = [
        'options' => 'array',
        'is_featured' => 'boolean',
        'bundle_settings' => 'array',
        'image_text_sections' => 'array',
        'video_reels' => 'array',
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

    public function bundles()
    {
        return $this->belongsToMany(ProductBundle::class, 'bundle_product')->where('is_active', true);
    }
}
