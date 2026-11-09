<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Courier extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'phone',
        'address',
        'has_api',
        'api_config',
        'default_charge',
        'is_active',
    ];

    protected $casts = [
        'api_config' => 'array',
        'has_api' => 'boolean',
        'is_active' => 'boolean',
        'default_charge' => 'decimal:2',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }
}
