<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class GiftCard extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'card_no',
        'value',
        'price',
        'balance',
        'expiry_date',
        'status',
        'created_by',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'value' => 'decimal:2',
        'price' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function transactions()
    {
        return $this->hasMany(GiftCardTransaction::class)->orderBy('id', 'desc');
    }

    public function isValid()
    {
        return $this->status === 'active'
            && $this->balance > 0
            && $this->expiry_date >= now()->startOfDay();
    }
}
