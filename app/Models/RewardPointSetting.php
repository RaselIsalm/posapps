<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class RewardPointSetting extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'is_active',
        'min_purchase_amount',
        'point_earning_rate',
        'min_redeem_point',
        'point_redemption_rate',
    ];
}
