<?php

namespace App\Models;

class Payment extends BaseModel
{
    protected $fillable = [
        'sale_id',
        'amount',
        'due_amount',
        'change_amount',
        'payment_method',
        'transaction_id',
        'note',
        'payment_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
