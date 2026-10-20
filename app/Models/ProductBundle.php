<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductBundle extends Model
{
    protected $fillable = [
        'title',
        'description',
        'discount_type',
        'discount_value',
        'is_active',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'bundle_product');
    }
}
