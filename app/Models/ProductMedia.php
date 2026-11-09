<?php

namespace App\Models;

class ProductMedia extends BaseModel
{
    protected $fillable = [
        'product_id',
        'image',
        'alt',
        'title',
        'caption',
        'order',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'status',
    ];
}
