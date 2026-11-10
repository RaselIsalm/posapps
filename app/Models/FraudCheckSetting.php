<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class FraudCheckSetting extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'api_key',
        'api_url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
