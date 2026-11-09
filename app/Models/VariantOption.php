<?php

namespace App\Models;

class VariantOption extends BaseModel
{
    protected $fillable = [
        'product_id',
        'name',
    ];

    public function values()
    {
        return $this->hasMany(VariantValue::class);
    }
}
