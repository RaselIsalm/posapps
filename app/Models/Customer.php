<?php

namespace App\Models;

class Customer extends BaseModel
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'balance',
        'reward_points',
        'previous_due',
        'code',
        'note',
        'referrer_id',
        'customer_group_id',
        'created_by',
        'status',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function customerGroup()
    {
        return $this->belongsTo(CustomerGroup::class, 'customer_group_id');
    }
}
