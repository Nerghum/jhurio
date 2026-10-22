<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'reviewer_name',
        'reviewer_email',
        'order_id',
        'rating',
        'comment',
        'is_verified_purchase',
        'status',
        'media',
        'created_at',
    ];

    protected $casts = [
        'media' => 'array',
        'is_verified_purchase' => 'boolean',
    ];

    public function getAuthorNameAttribute(): string
    {
        return $this->reviewer_name ?: ($this->user?->name ?? 'Customer');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
