<?php

namespace App\Models;

class ProductSale extends BaseModel
{
    protected $table = 'product_sales';

    protected $fillable = [
        'sale_id',
        'product_id',
        'variant_id',
        'discount_type',
        'discount_value',
        'discount_amount',
        'tax_percent',
        'tax_amount',
        'qty',
        'unit_price',
        'purchase_unit_cost',
        'total_price',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'purchase_unit_cost' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
