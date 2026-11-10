<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobilePosDraft extends Model
{
    protected $fillable = ['user_id', 'customer_id', 'branch_id', 'items', 'coupon_code'];

    protected function casts(): array
    {
        return ['items' => 'array'];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
