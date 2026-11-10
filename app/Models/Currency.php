<?php

namespace App\Models;

class Currency extends BaseModel
{
    protected $fillable = ['name', 'code', 'symbol', 'is_default', 'is_active', 'position', 'exchange_rate', 'created_by', 'updated_by'];

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
