<?php

namespace App\Models;

class VariantValue extends BaseModel
{
    protected $fillable = [
        'variant_option_id',
        'value',
    ];

    public function option()
    {
        return $this->belongsTo(VariantOption::class);
    }
}
