<?php

namespace App\Models;

class Brand extends BaseModel
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'status',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'brand_id');
    }
}
