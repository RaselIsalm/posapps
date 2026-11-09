<?php

namespace App\Models;

class ProductSeo extends BaseModel
{
    protected $fillable = [
        'product_id',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_title',
        'og_description',
        'og_image',
        'og_url',
        'og_locale',
        'og_type',
    ];
}
