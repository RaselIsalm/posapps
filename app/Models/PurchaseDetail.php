<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseDetail extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'product_id',
        'variant_id',
        'batch_no',
        'expire_date',
        'qty',
        'net_unit_cost',
        'discount',
        'tax',
        'total',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
