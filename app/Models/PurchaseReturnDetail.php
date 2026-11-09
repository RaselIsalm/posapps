<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseReturnDetail extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'purchase_return_id',
        'product_id',
        'variant_id',
        'qty',
        'unit_price',
        'total',
    ];

    public function return()
    {
        return $this->belongsTo(PurchaseReturn::class, 'purchase_return_id');
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
