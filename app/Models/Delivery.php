<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Delivery extends BaseModel
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'courier_response' => 'array',
        'cod_amount' => 'decimal:2',
        'order_amount' => 'decimal:2',
        'delivery_charge' => 'decimal:2',
        'consignment_id' => 'string',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function courier()
    {
        return $this->belongsTo(Courier::class);
    }
}
