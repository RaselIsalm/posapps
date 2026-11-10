<?php

namespace App\Models;

class Category extends BaseModel
{
    protected $fillable = ['name', 'slug', 'parent_id', 'description', 'image', 'status'];

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function child()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
