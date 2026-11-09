<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransferDetail extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'transfer_id',
        'product_id',
        'variant_id',
        'batch_no',
        'qty',
        'net_unit_cost',
        'tax',
        'subtotal',
    ];

    public function transfer()
    {
        return $this->belongsTo(Transfer::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
