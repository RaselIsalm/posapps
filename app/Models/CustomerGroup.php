<?php

namespace App\Models;

class CustomerGroup extends BaseModel
{
    protected $fillable = [
        'name',
        'prefix_code',
        'description',
        'discount',
        'type',
        'status',
    ];

    public function customers()
    {
        return $this->hasMany(Customer::class, 'customer_group_id');
    }
}
